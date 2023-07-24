<?php

//this file is published by the siteboss-framework package

return [

    /*
    |--------------------------------------------------------------------------
    | Sitemap path
    |--------------------------------------------------------------------------
    |
    | Relative path to the sitemap.txt created by the SOLR indexer.
    |
    */
    'sitemap' => env('SOLR_SITEMAP', 'public/sitemap.txt'),
];
