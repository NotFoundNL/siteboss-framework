<?php

use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('', [UserManagementController::class, 'readAll']);
Route::get('create', [UserManagementController::class, 'create']);
Route::post('create', [UserManagementController::class, 'createUser']);
Route::get('{user}', [UserManagementController::class, 'readOne']);
Route::post('{user}', [UserManagementController::class, 'update']);
