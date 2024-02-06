<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use NotFound\Framework\Http\Controllers\Forwards\ForwardsController;

Route::get('/', [ForwardsController::class, 'getOptions']);

Route::prefix('domains')->group(function (): void {
    Route::get('/', [ForwardsController::class, 'readAll']);
    Route::get('/{domain:id}', [ForwardsController::class, 'readOne']);
});

Route::prefix('options')->group(function (): void {
    Route::get('/', [ForwardsController::class, 'readAll']);
    Route::get('/rules/', [ForwardsController::class, 'readAll']);
});
