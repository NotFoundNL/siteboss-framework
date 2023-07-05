<?php

//this file is published by the siteboss-framework package

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

    'api_prefix' => env('SITEBOSS_BACKEND_API_PREFIX', '/siteboss/api'),


    /*
    |--------------------------------------------------------------------------
    | API prefix
    |--------------------------------------------------------------------------
    |
    | All calls in routes/api.php will be prefixed with this value.
    |
    */

    'frontend_api_prefix' => env('SITEBOSS_FRONTEND_API_PREFIX', 'api'),

];
