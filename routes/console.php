<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use NotFound\Framework\Services\Indexer\IndexBuilderService;

Artisan::command('siteboss:index-site {--debug : Whether debug messages should be displayed} {--clean : Truncate search table}', function ($debug, $clean) {

    $indexer = new IndexBuilderService($debug, $clean);
    $indexer->run();

    return Command::SUCCESS;
})->purpose('Index site for local search');
