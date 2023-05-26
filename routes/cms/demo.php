<?php

use App\Http\Controllers\Demo\DemoController;
use Illuminate\Support\Facades\Route;

Route::get('/grid', [DemoController::class, 'index']);
