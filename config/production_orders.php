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

];
