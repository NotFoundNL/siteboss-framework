<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use NotFound\Framework\Http\Controllers\RedirectController;

Route::get('', [RedirectController::class, 'index']);
Route::get('create', [RedirectController::class, 'create']);
Route::post('create', [RedirectController::class, 'createRedirect']);
Route::get('{redirect}', [RedirectController::class, 'readOne']);
Route::post('{redirect}', [RedirectController::class, 'update']);
Route::delete('{redirect}', [RedirectController::class, 'delete']);
