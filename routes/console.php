<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use NotFound\Framework\Services\CmsExchange\ExchangeConsoleService;
use NotFound\Framework\Services\Indexer\IndexBuilderService;

Artisan::command('siteboss:index-site {--debug : Whether debug messages should be displayed} {--clean : Truncate search table}', function ($debug, $clean) {
    $indexer = new IndexBuilderService($debug, $clean);
    $indexer->run();

    return Command::SUCCESS;
})->purpose('Index site for local search');

Artisan::command('siteboss:cms-import {--debug : Whether debug messages should be displayed} {--dry : Dry Run}', function ($debug, $dry) {
    $indexer = new ExchangeConsoleService($debug, $dry);
    $indexer->import();

    return Command::SUCCESS;
})->purpose('Import CMS changes to the database');
