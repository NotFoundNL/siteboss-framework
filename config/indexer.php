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
    'engine' => env('INDEXER_ENGINE', 'solr'),// 'solr' or 'elastic
    'sitemap' => env('INDEXER_SITEMAP', 'public/sitemap.txt'),
];
