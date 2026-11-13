<?php

namespace Tests\Feature;

use App\Services\BcPlans\BcStaging;
use App\Services\BcPlans\ExecutionPublisher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * SQL route into BC (integration map S8-S12, CP17 fallback). BC tables are imitated in the WMS database under a test
 * company (TST1) with the same column names and SQL defaults BC creates; everything is rolled back.
 */
class BcSqlRouteTest extends TestCase
{
    use DatabaseTransactions;

    private const EXT = '23dc970e-11e8-4d9b-8613-b7582aec86ba';
    private const BASE = '437dbf0e-84ff-417a-965d-ed2bb9650972';

    protected function setUp(): void
    {
        parent::setUp();
        config(['bc_plan.sql' => [
            'connection' => config('database.default'), 'company' => 'TST1', 'base_app_id' => self::BASE,
            'ext_app_id' => self::EXT, 'locations' => [],
        ], 'bc_plan.utc_offset_hours' => 3, 'bc_plan.push_unplanned' => false]);

        $h = '[TST1$WMSProduction Order Header$' . self::EXT . ']';
        $l = '[TST1$WMS Production Journal Line$' . self::EXT . ']';
        $po = '[TST1$Production Order$' . self::BASE . ']';
        $poe = '[TST1$Production Order$' . self::EXT . ']';
        $cr = '[TST1$WMS Plan Close Request$' . self::EXT . ']';
        DB::unprepared("
            CREATE TABLE {$h} ([Entry No_] int IDENTITY(1,1) PRIMARY KEY, [Production Order No_] nvarchar(50) NOT NULL DEFAULT '',
              [Item No_] nvarchar(20) NOT NULL DEFAULT '', [Quantity] decimal(38,20) NOT NULL DEFAULT 0, [UOM] nvarchar(10) NOT NULL DEFAULT '',
              [Location Code] nvarchar(10) NOT NULL DEFAULT '', [Bin Code] nvarchar(20) NOT NULL DEFAULT '', [User] nvarchar(50) NOT NULL DEFAULT '',
              [Line No_] int NOT NULL DEFAULT 0, [Routing] nvarchar(100) NOT NULL DEFAULT '', [Date Time] datetime NOT NULL DEFAULT '1753-01-01',
              [Status] int NOT NULL DEFAULT 0, [FinishedProductionOrderNo] nvarchar(50) NOT NULL DEFAULT '',
              [External Document No_] nvarchar(50) NOT NULL DEFAULT '', [Transaction Date] datetime NOT NULL DEFAULT '1753-01-01',
              [Plan Order No_] nvarchar(20) NOT NULL DEFAULT '', [WMS Transaction ID] nvarchar(100) NOT NULL DEFAULT '',
              [Status Message] nvarchar(250) NOT NULL DEFAULT '', [Plan Netted] tinyint NOT NULL DEFAULT 0,
              [\$systemId] uniqueidentifier NOT NULL DEFAULT NEWID());
            CREATE TABLE {$l} ([Production Order No_] nvarchar(50) NOT NULL, [Line No_] int NOT NULL, [Item No_] nvarchar(20) NOT NULL DEFAULT '',
              [Quantity] decimal(38,20) NOT NULL DEFAULT 0, [UOM] nvarchar(10) NOT NULL DEFAULT '', [Location Code] nvarchar(10) NOT NULL DEFAULT '',
              [Bin Code] nvarchar(20) NOT NULL DEFAULT '', [EntryType] int NOT NULL DEFAULT 0, [Date Time] datetime NOT NULL DEFAULT '1753-01-01',
              [User] nvarchar(50) NOT NULL DEFAULT '', [Status] int NOT NULL DEFAULT 0, [Error] tinyint NOT NULL DEFAULT 0,
              [Error Message] nvarchar(250) NOT NULL DEFAULT '', [Batch No_] nvarchar(50) NOT NULL DEFAULT '',
              [Plan Component Line No_] int NOT NULL DEFAULT 0, PRIMARY KEY ([Production Order No_], [Line No_]));
            CREATE TABLE {$po} ([Status] int NOT NULL, [No_] nvarchar(20) NOT NULL, PRIMARY KEY ([Status], [No_]));
            CREATE TABLE {$poe} ([Status] int NOT NULL, [No_] nvarchar(20) NOT NULL, [WMS Plan Status] int NOT NULL DEFAULT 0, PRIMARY KEY ([Status], [No_]));
            CREATE TABLE {$cr} ([Entry No_] int IDENTITY(1,1) PRIMARY KEY, [Plan Order No_] nvarchar(20) NOT NULL DEFAULT '',
              [Requested At] datetime NOT NULL DEFAULT '1753-01-01', [Requested By] nvarchar(50) NOT NULL DEFAULT '',
              [Status] int NOT NULL DEFAULT 0, [Status Message] nvarchar(250) NOT NULL DEFAULT '', [Source] nvarchar(30) NOT NULL DEFAULT '');
            INSERT INTO {$po} VALUES (3, 'RPO-OPEN'), (4, 'RPO-SHUT');
            INSERT INTO {$poe} VALUES (3, 'RPO-OPEN', 1), (4, 'RPO-SHUT', 2);
        ");
    }

    private function order(string $no, string $plan, string $createdAt = '2026-11-16 10:30:00'): void
    {
        $base = ['production_order_no' => $no, 'plan_order_no' => $plan, 'transaction_id' => 'GPO:' . $no, 'step' => 1,
            'transaction_date' => '2026-11-16', 'created_at' => $createdAt, 'updated_at' => $createdAt, 'batch_no' => 'B-77',
            'routing' => 'Packing-2055', 'location_code' => '2055'];
        DB::table('generated_production_orders')->insert([
            $base + ['line_no' => 1000, 'item_no' => 'J31015501', 'quantity' => '250.00000', 'uom' => 'KG', 'line_type' => 'output'],
            $base + ['line_no' => 2000, 'item_no' => 'G2306', 'quantity' => '252.52500', 'uom' => 'KG', 'line_type' => 'consumption'],
        ]);
    }

    private function h(): string
    {
        return '[TST1$WMSProduction Order Header$' . self::EXT . ']';
    }

    public function test_stages_once_rejects_closed_plans_and_reads_status_back()
    {
        $this->assertTrue((new BcStaging())->supportsPlans());
        $this->order('P20_T_1', 'RPO-OPEN');
        $this->order('P20_T_2', 'RPO-SHUT');
        $this->order('P20_T_3', '');   // no plan: not pushed while push_unplanned is off
        DB::table('generated_production_orders')->where('production_order_no', 'P20_T_3')->update(['plan_order_no' => null]);

        $stats = (new ExecutionPublisher())->run();
        $this->assertSame(2, $stats['candidates']);
        $this->assertSame(1, $stats['staged']);
        $this->assertSame(1, $stats['rejected']);

        $hdr = DB::selectOne("SELECT * FROM {$this->h()} WHERE [Production Order No_] = 'P20_T_1'");
        $this->assertSame('GPO:P20_T_1', $hdr->{'WMS Transaction ID'});
        $this->assertSame('RPO-OPEN', $hdr->{'Plan Order No_'});
        $this->assertEquals(250, $hdr->Quantity);
        $this->assertStringStartsWith('2026-11-16 07:30:00', $hdr->{'Date Time'});     // UTC
        $lines = DB::select('SELECT [Line No_] AS n, [EntryType] AS t, [Batch No_] AS b FROM [TST1$WMS Production Journal Line$' . self::EXT . "] WHERE [Production Order No_] = 'P20_T_1' ORDER BY 1");
        $this->assertSame([[1000, 0, 'B-77'], [2000, 1, 'B-77']], array_map(fn($r) => [(int) $r->n, (int) $r->t, $r->b], $lines));
        $this->assertSame('rejected', DB::table('generated_production_orders')->where('production_order_no', 'P20_T_2')->value('bc_status'));
        $this->assertSame(0, (int) DB::selectOne("SELECT COUNT(*) AS n FROM {$this->h()} WHERE [Production Order No_] = 'P20_T_2'")->n);

        // Replay after an outage: same transaction id is not staged twice
        DB::table('generated_production_orders')->where('production_order_no', 'P20_T_1')->update(['bc_staged_at' => null]);
        $again = (new ExecutionPublisher())->run();
        $this->assertSame(1, $again['duplicate']);
        $this->assertSame(1, (int) DB::selectOne("SELECT COUNT(*) AS n FROM {$this->h()} WHERE [WMS Transaction ID] = 'GPO:P20_T_1'")->n);

        // BC holds it for stock (CP25); WMS reads the status back
        DB::update("UPDATE {$this->h()} SET [Status] = 5, [Status Message] = 'Held: G2306 at 2055 needs 252.525, 100 in stock.' WHERE [WMS Transaction ID] = 'GPO:P20_T_1'");
        (new ExecutionPublisher())->refreshStatuses();
        $row = DB::table('generated_production_orders')->where('production_order_no', 'P20_T_1')->first();
        $this->assertSame('held', $row->bc_status);
        $this->assertStringContainsString('needs 252.525', $row->bc_status_message);
    }

    public function test_close_request_is_written_once()
    {
        $staging = new BcStaging();
        $this->assertSame('requested', $staging->requestClose('RPO-OPEN', 'supervisor1'));
        $this->assertSame('already requested', $staging->requestClose('RPO-OPEN', 'supervisor1'));
        $row = DB::selectOne('SELECT * FROM [TST1$WMS Plan Close Request$' . self::EXT . ']');
        $this->assertSame('RPO-OPEN', $row->{'Plan Order No_'});
        $this->assertSame('supervisor1', $row->{'Requested By'});
        $this->assertSame(0, (int) $row->Status);
    }

    public function test_refuses_to_stage_before_the_bc_build_is_deployed()
    {
        $table = 'TST1$WMSProduction Order Header$' . self::EXT;
        DB::statement("EXEC sp_rename N'dbo.[{$table}].[WMS Transaction ID]', N'WMS Tx Old', N'COLUMN'");
        $this->expectExceptionMessage('deploy the FARMERSCHOICE build');
        (new BcStaging())->stageExecutionOrder(['transaction_id' => 'x'], []);
    }
}
