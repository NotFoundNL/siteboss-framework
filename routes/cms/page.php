<?php

use NotFound\Framework\Http\Controllers\Assets\PageEditorController;
use NotFound\Framework\Http\Controllers\Assets\PageItemEditorController;
use NotFound\Framework\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

// /page/{id}
Route::post('new', [PageEditorController::class, 'create']);
Route::post('create/{menu}', [MenuController::class, 'create']);

// /app/page/{id}/editor/
Route::prefix('{menu}/editor/{langSlug}')->group(function () {
    Route::get('', [PageEditorController::class, 'index']);
    Route::post('', [PageEditorController::class, 'update']);
    Route::get('{fieldInternal}', [PageItemEditorController::class, 'ajaxGet']);
    Route::post('{fieldInternal}', [PageItemEditorController::class, 'ajaxPost']);
    Route::put('{fieldInternal}', [PageItemEditorController::class, 'ajaxPut']);
});
