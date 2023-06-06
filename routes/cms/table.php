<?php

use Illuminate\Support\Facades\Route;
use NotFound\Framework\Http\Controllers\Assets\TableEditorController;
use NotFound\Framework\Http\Controllers\Assets\TableItemEditorController;
use NotFound\Framework\Http\Controllers\Assets\TableOverviewController;

Route::prefix('{table:url}')->group(function () {
    Route::get('', [TableOverviewController::class, 'index']);
    Route::put('', [TableOverviewController::class, 'updateField']);
    Route::post('', [TableOverviewController::class, 'create']);
    Route::put('move', [TableOverviewController::class, 'updatePosition']);

    // /table/{slug}/{recordId}
    Route::prefix('{recordId}')->group(function () {
        Route::delete('', [TableEditorController::class, 'deleteRecord']);

        // /table/{slug}/{recordId}/{lang}
        Route::prefix('{langSlug}')->group(function () {
            Route::get('', [TableEditorController::class, 'index']);
            Route::post('', [TableEditorController::class, 'update']);

            Route::get('{fieldInternal}', [TableItemEditorController::class, 'ajaxGet']);
            Route::post('{fieldInternal}', [TableItemEditorController::class, 'ajaxPost']);
            Route::put('{fieldInternal}', [TableItemEditorController::class, 'ajaxPut']);
        });
    });
});
