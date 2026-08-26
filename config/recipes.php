<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recipe source table
    |--------------------------------------------------------------------------
    |
    | Legacy default for the recipe graph source. Production order generation has
    | its own explicit switch at production_orders.recipe_table.
    |
    | 'RecipeData'        - the live table, kept in step with BC. Read-only as far
    |                       as this feature is concerned.
    |
    | 'recipe_data_draft' - the editable working copy managed under
    |                       Recipe Data (Draft). Use while proving the generation
    |                       out, so recipes can be corrected without touching live.
    |
    | Prefer PRODUCTION_ORDERS_RECIPE_TABLE for stuffing production orders. This
    | value is still used as a fallback for existing environments.
    |
    */

    'table' => env('RECIPE_DATA_TABLE', 'RecipeData'),

    /*
    | The live table, and the editable copy. Named here rather than inline so the
    | draft screen can seed itself from live whichever way the switch above is set.
    */

    'live_table' => 'RecipeData',

    'draft_table' => 'recipe_data_draft',

    /*
    | Cache keys dropped whenever the draft is edited, so a change is visible on
    | the next weighing instead of after the 10 hour TTL.
    */

    'cache_keys' => [
        'recipe_data_cache',
        'stuffing_packing_outputs',
        'stuffing_packing_outputs:RecipeData',
        'stuffing_packing_outputs:recipe_data_draft',
        'stuffing_products',
    ],

];
