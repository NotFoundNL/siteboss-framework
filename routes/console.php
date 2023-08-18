<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use NotFound\Framework\Services\Indexer\IndexBuilderService;

Artisan::command('siteboss:indexSite {--debug : Whether debug messages should be displayed}', function ($debug) {
    //
    $indexer = new IndexBuilderService($debug);
    $indexer->run();

    return Command::SUCCESS;
})->purpose('Index site with SOLR');
