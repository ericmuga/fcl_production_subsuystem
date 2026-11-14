<?php

/*
|--------------------------------------------------------------------------
| BC MRP daily plan (BC -> WMS), integration map steps S3-S5
|--------------------------------------------------------------------------
|
| A plan order is a BC Released production order for one item + location + day. WMS shows it as the
| day's target and every execution order carries its number (plan_order_no), so BC can net the plan.
|
| source 'sql' - read the BC tables through the 'bc240' connection (local mirror / fallback route, CP17).
|                While the FARMERSCHOICE extension with the plan fields is not deployed, every Released order
|                that BC planning created (not a WMS execution order) is treated as an open plan ("pilot mode").
| source 'api' - read the BC API pages /api/farmerschoice/mrp/v1.0 (target route, CP17).
|
*/

return [
    'source' => env('BC_PLAN_SOURCE', 'sql'),

    // BC stores DateTime in UTC; WMS timestamps are local (Nairobi, UTC+3).
    'utc_offset_hours' => (int) env('BC_UTC_OFFSET_HOURS', 3),

    // Push execution orders that found no open plan as well (they reach BC without a plan no., as today).
    'push_unplanned' => (bool) env('BC_PUSH_UNPLANNED', false),

    // Run bc:sync-plans (every 15 min) and bc:push-executions (every 5 min) from the Laravel scheduler.
    'schedule' => (bool) env('BC_PLAN_SCHEDULE', false),

    // How many days around today a sync covers when no dates are given.
    'days_back' => (int) env('BC_PLAN_DAYS_BACK', 1),
    'days_ahead' => (int) env('BC_PLAN_DAYS_AHEAD', 7),

    // Incremental sync ignores plans due more than this many days ago.
    'history_days' => (int) env('BC_PLAN_HISTORY_DAYS', 30),

    // WMS section by BC location code prefix (longest prefix wins). A plan's sections come from the locations of the
    // work centres on its routing; the section of the last operation is its main section.
    'sections' => [
        '10' => 'Slaughter',
        '15' => 'Butchery',
        '20' => 'Sausage',
        '25' => 'High Care',
        '30' => 'Despatch',
        '35' => 'Despatch',
        '36' => 'Despatch',
    ],

    // Weighing station (/planned-orders/weigh): which scale_configs section each WMS section reads its scales from
    // (scale_configs.section; the scale settings pages of each section maintain them).
    'section_scales' => [
        'Slaughter' => 'slaughter',
        'Butchery' => 'butchery',
        'Sausage' => 'sausage',
        'High Care' => 'highcare1',
        'Despatch' => 'despatch',
    ],
    // Allow typing the gross weight when no scale answers (testing, scale down). Turn off on the shop floor.
    'weigh_manual' => (bool) env('BC_PLAN_WEIGH_MANUAL', true),
    // Who may change the vessel tares (users.role).
    'vessel_admin_roles' => array_values(array_filter(explode(',', (string) env('BC_PLAN_VESSEL_ADMIN_ROLES', 'admin')))),

    'sql' => [
        'connection' => env('BC_PLAN_SQL_CONNECTION', 'bc240'),
        'company' => env('BC_COMPANY', 'FCL1'),
        // Base application tables
        'base_app_id' => env('BC_BASE_APP_ID', '437dbf0e-84ff-417a-965d-ed2bb9650972'),
        // FARMERSCHOICE extension (holds the WMS plan fields once deployed)
        'ext_app_id' => env('BC_EXT_APP_ID', '23dc970e-11e8-4d9b-8613-b7582aec86ba'),
        // Locations whose plans WMS shows; empty = all
        'locations' => array_filter(explode(',', (string) env('BC_PLAN_LOCATIONS', ''))),
    ],

    'api' => [
        'tenant' => env('BC_API_TENANT'),
        'environment' => env('BC_API_ENVIRONMENT', 'Production'),
        'company_id' => env('BC_API_COMPANY_ID'),
        'client_id' => env('BC_API_CLIENT_ID'),
        'client_secret' => env('BC_API_CLIENT_SECRET'),
        'base_url' => env('BC_API_BASE_URL', 'https://api.businesscentral.dynamics.com/v2.0'),
        'token_url' => env('BC_API_TOKEN_URL', 'https://login.microsoftonline.com/%s/oauth2/v2.0/token'),
        'scope' => env('BC_API_SCOPE', 'https://api.businesscentral.dynamics.com/.default'),
        'timeout' => (int) env('BC_API_TIMEOUT', 30),
    ],
];
