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

    /*
    |--------------------------------------------------------------------------
    | Admin email
    |--------------------------------------------------------------------------
    |
    | Email address to send admin notifications to.
    |
    */

    'admin_email' => env('SB_ADMIN_EMAIL', null),

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    |
    | Supported locales for the application.
    |
    */

    'locales' => [
        'default' => env('SB_LOCALES_DEFAULT', 'en'),
        'supported' => explode(',', env('SB_LOCALES_SUPPORTED', 'en,nl')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding / whitelabel
    |--------------------------------------------------------------------------
    |
    | Customise the product name, logo, and documentation URL shown in the CMS.
    |
    */

    'branding' => [
        'product_name' => env('APP_WHITELABEL_NAME', 'SiteBoss'),
        'product_logo' => env('APP_WHITELABEL_LOGO', '/siteboss/images/logo.svg'),
        'client_logo' => env('APP_CLIENT_LOGO'),
        'docs_url' => env('APP_CLIENT_DOCS_URL', 'https://docs.siteboss.nl'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Login page background image
    |--------------------------------------------------------------------------
    */

    'login' => [
        'image_url' => env('APP_LOGIN_IMAGE_URL', '/siteboss/images/back.jpg'),
        'image_source_name' => env('APP_LOGIN_IMAGE_SOURCE_NAME', 'zeitfaenger.at'),
        'image_source_url' => env('APP_LOGIN_IMAGE_SOURCE_URL', 'https://flickr.com/photos/kwarz/'),
        'image_source_license' => env('APP_LOGIN_IMAGE_SOURCE_LICENSE', 'CC BY 2.0'),
    ],
];
