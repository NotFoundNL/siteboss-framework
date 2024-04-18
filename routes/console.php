<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use NotFound\Framework\Services\CmsExchange\ExchangeConsoleService;
use NotFound\Framework\Services\Indexer\IndexBuilderService;

Artisan::command('siteboss:index-site {--debug : Display debug messages} {--fresh : Empty local search table}', function ($debug, $fresh) {

    $indexer = new IndexBuilderService($debug, $fresh);
    $indexer->run();

    return Command::SUCCESS;
})->purpose('Index site for local search');

Artisan::command('siteboss:cms-import {--debug : Display debug messages} {--dry : Dry Run}', function ($debug, $dry) {

    $indexer = new ExchangeConsoleService($debug, $dry);
    $indexer->import();

    return Command::SUCCESS;
})->purpose('Import CMS changes to the database');
