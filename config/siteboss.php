<?php

// This file is published by the siteboss-framework package

return [
    /*
    |--------------------------------------------------------------------------
    | SiteBoss® API prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify the prefix for the calls made by the SiteBoss®
    | framework to the API. There is no need to change this unless you
    | are using a different API prefix for your application.
    |
    */
    'api_prefix' => env('SB_BACKEND_API_PREFIX', '/siteboss/api'),

    /*
    |--------------------------------------------------------------------------
    | Cache prefix
    |--------------------------------------------------------------------------
    |
    | Boolean to prefix the asset URL with a number based on the updated_at
    | of the AssetItem.
    |
    */

    'cache_prefix' => env('SB_CACHE_PREFIX', false),

    /*
    |--------------------------------------------------------------------------
    | API prefix
    |--------------------------------------------------------------------------
    |
    | All calls in routes/api.php will be prefixed with this value.
    |
    */

    'frontend_api_prefix' => env('SB_FRONTEND_API_PREFIX', 'api'),

    /*
    |--------------------------------------------------------------------------
    | CMS importer
    |--------------------------------------------------------------------------
    |
    | Do you want to retain the database id's for tables and table items.
    |
    */

    'export_retain_ids' => env('SB_EXPORT_RETAIN_IDS', false),

];
