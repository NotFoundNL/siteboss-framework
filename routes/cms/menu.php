<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use NotFound\Framework\Http\Controllers\MenuController;

Route::get('{id?}', [MenuController::class, 'index']);
Route::put('{menu}/move', [MenuController::class, 'move']);
Route::delete('{menu}', [MenuController::class, 'delete']);
Route::post('{menu}/toggle/enabled', [MenuController::class, 'toggleEnabled']);
Route::post('{menu}/toggle/menu', [MenuController::class, 'toggleMenu']);
