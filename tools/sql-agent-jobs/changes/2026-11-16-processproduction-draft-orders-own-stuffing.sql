/*
  ProcessProduction (FCL-WMS10): let the WMS draft production orders own stuffed/packed sausage batches.

  The job's Packing step builds P20 orders from each dispatch IDT (transfer from 2055) and the Stuffing-2055
  step works P19 orders backwards from them. WMS generates the same chain forward from the stuffing weighing
  (generated_production_orders -> ProductionData, harmonized with the dispatch weight). With both running,
  BC would get every batch twice.

  Changes step 7 (Packing) only: a 2055 dispatch is skipped when WMS has draft orders for the same batch no +
  packed item AND wms_sync_config 'draft_orders_own_stuffing' = 1. Stuffing-2055 builds from the P20 orders,
  so it stops for those batches by itself. Dispatches from 1570, 2595 and 2500, and 2055 batches WMS has no
  draft orders for, carry on exactly as before.

  Deploy (sysadmin or SQLAgentOperator on FCL-WMS10), in this order:
    1. WMS migration 2026_11_16_090000 (adds the batch_no + packed_item index this check uses).
    2. This script. The switch is created OFF, so nothing changes yet.
    3. WMS .env: PRODUCTION_ORDERS_TARGET=production_data, PRODUCTION_ORDERS_HARMONIZE=review,
       PRODUCTION_ORDERS_RECIPE_TABLE=RecipeData (or keep recipe_data_draft if that is the signed-off set);
       php artisan config:clear && php artisan cache:clear.
    4. UPDATE calibra.dbo.wms_sync_config SET config_value = '1', updated_at = GETDATE()
         WHERE config_key = 'draft_orders_own_stuffing';
  Roll back: set the switch to '0' (the job resumes on its next run) and PRODUCTION_ORDERS_TARGET=local.

  Built from the job script of 16 Nov 2026 05:21; the step text is unchanged apart from the marked block.
*/
USE [calibra];
IF NOT EXISTS (SELECT 1 FROM dbo.wms_sync_config WHERE config_key = 'draft_orders_own_stuffing')
    INSERT INTO dbo.wms_sync_config (config_key, config_value, description, updated_at, updated_by)
    VALUES ('draft_orders_own_stuffing', '0',
            '1 = ProcessProduction skips 2055 dispatch packing/stuffing orders for batches WMS has draft production orders for',
            GETDATE(), SUSER_SNAME());
GO
USE [msdb];
EXEC dbo.sp_update_jobstep @job_name = N'ProcessProduction', @step_id = 7,
    @command = N'use calibra;
DECLARE 
@startdate date, @endDate date;


--set @startdate=''2025-03-24 00:00:00.000'';
set @startdate=DATEADD(d,-1,DATEDIFF(d,0,GETDATE()));


--set @endDate=DATEADD(d,0,DATEDIFF(d,0,GETDATE()));


WITH OutputData AS (
    SELECT 
        CASE 
		  WHEN (o.transfer_from=''2055'') THEN concat(''P20_'' ,REPLACE(REPLACE(REPLACE(REPLACE(r.recipe, ''1210'', ''1''),''1220'', ''2''),''1230'', ''3''),''1240'', ''4''),''_'',o.id) 
		  WHEN (o.transfer_from=''1570'') THEN concat(''P15_'' ,REPLACE(REPLACE(REPLACE(REPLACE(r.recipe, ''1210'', ''1''),''1220'', ''2''),''1230'', ''3''),''1240'', ''4''),''_'',o.id) 
		  WHEN (o.transfer_from=''2595'') THEN concat(''P22_'' ,REPLACE(REPLACE(REPLACE(REPLACE(r.recipe, ''1210'', ''1''),''1220'', ''2''),''1230'', ''3''),''1240'', ''4''),''_'',o.id) 
		  WHEN (o.transfer_from=''2500'') THEN concat(''P21_'' ,REPLACE(REPLACE(REPLACE(REPLACE(r.recipe, ''1210'', ''1''),''1220'', ''2''),''1230'', ''3''),''1240'', ''4''),''_'',o.id) 
        END as  production_order_no,
        r.recipe,
        o.product_code  AS item_code, -- Force collation
        o.location_code AS location_code,
        o.receiver_total_weight AS qty,
        r.[output_item_uom],
        CAST(o.created_at AS DATE) AS created_at,
        1 AS output_flag,
		UPPER(j.[username]) [username],
		o.transfer_from [location]
    FROM [calibra].[dbo].[idt_transfers]  as o
	inner join [calibra].[dbo].[users] as j on o.[user_id]=j.id
    inner JOIN (SELECT 
			  distinct
			   [Process]
			  ,[output_item]
			  ,[recipe]
			  ,[output_item_dec]
			  ,[output_item_uom]
			  ,[batch_size]
			  ,[output_item_location]
		   FROM [calibra].[dbo].[RecipeData] where Process=''Packing''
		   ) r 
        ON o.product_code = r.output_item 
        AND r.output_item_location = o.location_code
    WHERE o.location_code in  (''3535'',''3600'') 
        AND o.transfer_from in (''2055'',''1570'',''2595'',''2500'')
       -- AND o.transfer_from in (''2055'',''1570'')
        AND (o.created_at >= @startdate) 
      AND o.receiver_total_weight > 0
      -- Draft production orders (WMS, generated forward from the stuffing weighing) own this batch:
      -- skip the backward packing/stuffing orders for it. Switch: wms_sync_config draft_orders_own_stuffing = 1.
      AND NOT (
          o.transfer_from = ''2055''
          AND ISNULL((SELECT TOP 1 c.config_value FROM [calibra].[dbo].[wms_sync_config] c WHERE c.config_key = ''draft_orders_own_stuffing''), ''0'') = ''1''
          AND EXISTS (SELECT 1 FROM [calibra].[dbo].[generated_production_orders] g
                      WHERE g.batch_no = o.batch_no AND g.packed_item = o.product_code)
      )
    
), 



InputData AS (
    SELECT
	distinct
	    o.production_order_no,
		 o.recipe,
         r.input_item item_code, -- Force collation
         r.input_item_location AS location_code,
         (o.qty / NULLIF(r.batch_size, 0)) * r.input_item_qt_per AS qty, -- Avoid division by zero
         r.input_item_uom AS uom,
		 o.created_at,
         0 AS output_flag,
		 o.username,
		 o.[location]
    FROM OutputData o
    JOIN [calibra].[dbo].[RecipeData] r 
        ON o.item_code = r.output_item 
        AND r.output_item_location = o.location_code 
        AND r.batch_size IS NOT NULL -- Ensure valid batch sizes
	    AND r.recipe=o.recipe
		
),

InputOutPut as(SELECT * FROM OutputData
    UNION ALL
    SELECT * FROM InputData),

 CalculatedLines AS (
    SELECT 
        production_order_no AS [Production Order No_],
        item_code AS [Item No_],
        qty AS [Quantity],
        output_item_uom AS [UOM],
        location_code AS [Location Code],
        '''' AS [Bin Code],
        o.username AS [User],
        CASE 
            WHEN output_flag = 1 THEN 1000
            ELSE 1000 + (ROW_NUMBER() OVER (PARTITION BY production_order_no, output_flag ORDER BY item_code)) * 1000
        END AS [Line No_],
        CASE 
            WHEN o.[location] = ''2055'' THEN ''Packing-2055''
            WHEN o.[location] = ''1570'' THEN ''Packing-1570''
            WHEN o.[location] = ''2595'' THEN ''Packing-2595''
            WHEN o.[location] = ''2500'' THEN ''Packing-2500''
        END AS [Routing],
        created_at AS [Date Time],
        0 AS [Status],
        '''' AS [FinishedProductionOrderNo],
        o.recipe AS [External Document No_],
        o.created_at AS [Transaction Date]
    FROM InputOutPut o
)



-- ✅ Ensure the CTE is used immediately in an INSERT or SELECT
INSERT INTO [calibra].[dbo].[ProductionData]
(
    [ProductionOrderNo],
    [ItemNo],
    [Quantity],
    [UOM],
    [LocationCode],
    [BinCode],
    [UserName],
    [LineNo],
    [Routing],
    [DateTime],
    [Status],
    [FinishedProductionOrderNo],
    [ExternalDocumentNo],
    [TransactionDate]
)

SELECT 
  Distinct
    [Production Order No_],
    [Item No_],
    [Quantity],
    [UOM],
    [Location Code],
    [Bin Code],
    [User],
    [Line No_],
    [Routing],
    [Date Time],
    [Status],
    [FinishedProductionOrderNo],
    [External Document No_],
    [Transaction Date]
FROM CalculatedLines c

--where c.[Production Order No_]=''P15_2S53_594652''



WHERE NOT EXISTS (
    SELECT 1
    FROM [calibra].[dbo].[ProductionData] h
    WHERE h.[ProductionOrderNo] = c.[Production Order No_]
      AND h.[LineNo] = c.[Line No_]
);





';
GO
