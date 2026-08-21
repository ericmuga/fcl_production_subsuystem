<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recipe source table
    |--------------------------------------------------------------------------
    |
    | Which table the recipe graph is read from when production orders are
    | generated from a stuffing weighing.
    |
    | 'RecipeData'        - the live table, kept in step with BC. Read-only as far
    |                       as this feature is concerned.
    |
    | 'recipe_data_draft' - the editable working copy managed under
    |                       Recipe Data (Draft). Use while proving the generation
    |                       out, so recipes can be corrected without touching live.
    |
    | Flip it with RECIPE_DATA_TABLE in .env, then run `php artisan config:clear`
    | and `php artisan cache:clear` - the recipe graph is cached for 10 hours, so
    | the old table is read until that cache is dropped.
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
        'stuffing_products',
    ],

];
