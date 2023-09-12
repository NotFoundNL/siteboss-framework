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
    | Leave this null to disable the support widget.
    */
    'endpoint' => env('SUPPORT_ENDPOINT', null),

    /*
    |--------------------------------------------------------------------------
    | Support API Key
    |--------------------------------------------------------------------------
    |
    | String will be sent in the Bearer header to the support endpoint.
    |
    */
    'api_key' => env('SUPPORT_API_KEY', ''),
];
