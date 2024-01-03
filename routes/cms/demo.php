<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use NotFound\Framework\Http\Controllers\Demo\DemoController;

Route::get('/grid', [DemoController::class, 'index']);
