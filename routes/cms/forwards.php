<?php

use App\Http\Controllers\Forwards\ForwardsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ForwardsController::class, 'getOptions']);

Route::prefix('domains')->group(function () {
    Route::get('/', [ForwardsController::class, 'readAll']);
    Route::get('/{domain:id}', [ForwardsController::class, 'readOne']);
});

Route::prefix('options')->group(function () {
    Route::get('/', [ForwardsController::class, 'readAll']);
    Route::get('/rules/', [ForwardsController::class, 'readAll']);
});
