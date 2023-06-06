<?php

use Illuminate\Support\Facades\Route;
use NotFound\Framework\Http\Controllers\UserManagementController;

Route::get('', [UserManagementController::class, 'readAll']);
Route::get('create', [UserManagementController::class, 'create']);
Route::post('create', [UserManagementController::class, 'createUser']);
Route::get('{user}', [UserManagementController::class, 'readOne']);
Route::post('{user}', [UserManagementController::class, 'update']);
