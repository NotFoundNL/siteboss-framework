<?php

// This file is published by the siteboss-framework package

return [
    /*
    |--------------------------------------------------------------------------
    | SiteBoss® Support Endpoint
    |--------------------------------------------------------------------------
    |
    | Here you may specify where support calls will be made to.
    | The calls are handled in SupportController.php
    */
    'endpoint' => env('SUPPORT_ENDPOINT', '/siteboss/api'),

    /*
    |--------------------------------------------------------------------------
    | Cache prefix
    |--------------------------------------------------------------------------
    |
    | Boolean to prefix the asset URL with a number based on the updated_at
    | of the AssetItem.
    |
    */

    'api_key' => env('SUPPORT_API_KEY', ''),
];
