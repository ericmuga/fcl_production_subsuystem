<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Write target for generated production orders
    |--------------------------------------------------------------------------
    |
    | 'local'           - orders are written to generated_production_orders only.
    |                     Use while testing so nothing reaches the BC tables.
    |
    | 'production_data' - orders are additionally written into the BC
    |                     ProductionData table. Use when going live.
    |
    | The local table is written in both modes: it carries the link back to the
    | weighing (idt_transfer_id, weighed_item, packed_item, step) that
    | ProductionData has no columns for, and it is what the on-screen
    | "Generated Production Orders" panel reads.
    |
    */

    'target' => env('PRODUCTION_ORDERS_TARGET', 'local'),

    /*
    | The recipe table used when generating stuffing production orders. This is
    | intentionally separate from the write target above: recipes can be read from
    | the draft or live table without changing whether orders are pushed to BC.
    |
    | RECIPE_DATA_TABLE is kept as a fallback for existing environments.
    */

    'recipe_table' => env('PRODUCTION_ORDERS_RECIPE_TABLE', env('RECIPE_DATA_TABLE', 'RecipeData')),

    /*
    | The BC table written to when target is 'production_data'.
    */

    'production_data_table' => env('PRODUCTION_DATA_TABLE', 'ProductionData'),

    /*
    | Quantities are rounded to this many decimals when written to
    | ProductionData, matching what BC stores there.
    */

    'production_data_decimals' => 2,

    /*
    | Allowed difference in kgs when a sausage IDT is tied to a stuffing batch:
    | the IDT weights for that batch may not exceed the stuffing net weight by
    | more than this.
    */

    'stuffing_batch_tolerance_kgs' => (float) env('STUFFING_BATCH_TOLERANCE_KGS', 0.5),

    /*
    |--------------------------------------------------------------------------
    | Harmonizing stuffing orders with the dispatch weight (optional)
    |--------------------------------------------------------------------------
    |
    | Stuffing weighs the input; the IDT transfer to dispatch weighs the packed
    | output; the lines in between come from recipe yields. Harmonizing rebuilds
    | a batch (batch no + packed item) from both measured ends.
    |
    | 'off'    - as before: orders go to ProductionData as soon as they are generated
    |            (when target is production_data). The Harmonize screen still works
    |            on lines that have not reached ProductionData.
    | 'review' - orders are held locally until the batch is harmonized and released
    |            on Harmonize stuffing batches, or by `php artisan stuffing:harmonize`.
    |
    | harmonize_mode is the mode the screen and the command start with:
    | recipe | output_at_last (variance on Packing) | output_at_first (variance on Stuffing)
    |
    | harmonize_output_from is the location the dispatch transfers are made from.
    */

    'harmonize' => env('PRODUCTION_ORDERS_HARMONIZE', 'off'),

    'harmonize_mode' => env('PRODUCTION_ORDERS_HARMONIZE_MODE', 'output_at_last'),

    'harmonize_output_from' => env('PRODUCTION_ORDERS_HARMONIZE_OUTPUT_FROM', '2055'),

    /*
    |--------------------------------------------------------------------------
    | Matching draft orders to BC plans
    |--------------------------------------------------------------------------
    |
    | Each draft order is matched on its output item and location to an open or
    | released plan dated from plan_match_days_before to plan_match_days_after
    | around its production date (same day first, then earlier, then later).
    | Matching runs when a batch is harmonized and from the harmonize screen,
    | where a plan can also be picked by hand.
    |
    | planned_route: where a plan-matched order goes when it is released.
    | 'plan'            - the BC staging route (bc:push-executions) carrying the plan
    |                     no., so BC nets the plan. Orders without a plan still go
    |                     to ProductionData.
    | 'production_data' - every order goes to ProductionData (the plan link stays in
    |                     WMS for the plan screens and reports only).
    */

    'plan_match_days_before' => (int) env('PRODUCTION_ORDERS_PLAN_DAYS_BEFORE', 1),

    'plan_match_days_after' => (int) env('PRODUCTION_ORDERS_PLAN_DAYS_AFTER', 1),

    'planned_route' => env('PRODUCTION_ORDERS_PLANNED_ROUTE', 'production_data'),

];
