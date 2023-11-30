<?php

use Illuminate\Support\Facades\Route;
use NotFound\Framework\Http\Controllers\CmsEditor\CmsEditorController;
use NotFound\Framework\Http\Controllers\CmsEditor\CmsEditorImportExportController;
use NotFound\Framework\Http\Controllers\CmsEditor\CmsEditorLangController;
use NotFound\Framework\Http\Controllers\CmsEditor\CmsEditorMenuController;
use NotFound\Framework\Http\Controllers\CmsEditor\CmsEditorTableController;
use NotFound\Framework\Http\Controllers\CmsEditor\CmsEditorTableItemController;
use NotFound\Framework\Http\Controllers\CmsEditor\CmsEditorTemplateController;
use NotFound\Framework\Http\Controllers\CmsEditor\CmsEditorTemplateItemController;

Route::get('', [CmsEditorController::class, 'index']);

// import/export
Route::post('table-export', [CmsEditorImportExportController::class, 'exportAllTables']);
Route::get('import', [CmsEditorImportExportController::class, 'import']);

// table
Route::prefix('table')->group(function () {
    Route::get('', [CmsEditorTableController::class, 'index']);
    Route::post('', [CmsEditorTableController::class, 'create']);

    Route::prefix('{table}')->group(function () {
        Route::get('', [CmsEditorTableController::class, 'readOne']);
        Route::post('', [CmsEditorTableController::class, 'update']);

        Route::put('move', [CmsEditorTableController::class, 'updatePosition']);
        Route::post('add-field', [CmsEditorTableController::class, 'addField']);

        Route::prefix('{tableItem}')->group(function () {
            Route::get('', [CmsEditorTableItemController::class, 'readOne']);
            Route::post('', [CmsEditorTableItemController::class, 'update']);
            Route::post('enabled', [CmsEditorTableItemController::class, 'enabled']);
        });
    });
});

// pages
Route::prefix('page')->group(function () {
    Route::get('', [CmsEditorTemplateController::class, 'index']);
    Route::post('', [CmsEditorTemplateController::class, 'create']);

    Route::prefix('{table}')->group(function () {
        Route::get('', [CmsEditorTemplateController::class, 'readOne']);
        Route::post('', [CmsEditorTemplateController::class, 'update']);
        Route::post('import', [CmsEditorImportExportController::class, 'importTemplate']);

        Route::put('move', [CmsEditorTemplateController::class, 'updatePosition']);
        Route::post('add-field', [CmsEditorTemplateController::class, 'addField']);

        Route::prefix('{tableItem}')->group(function () {
            Route::get('', [CmsEditorTemplateItemController::class, 'readOne']);
            Route::post('', [CmsEditorTemplateItemController::class, 'update']);
            Route::post('enabled', [CmsEditorTemplateItemController::class, 'enabled']);
        });
    });
});

Route::prefix('menu')->group(function () {
    Route::get('', [CmsEditorMenuController::class, 'index']);
    Route::post('', [CmsEditorMenuController::class, 'addItem']);

    Route::put('move', [CmsEditorMenuController::class, 'updatePosition']);

    Route::prefix('{menuItem}')->group(function () {
        Route::get('', [CmsEditorMenuController::class, 'readOne']);
        Route::post('', [CmsEditorMenuController::class, 'update']);
        Route::delete('', [CmsEditorMenuController::class, 'deleteRecord']);
    });
});

Route::prefix('lang')->group(function () {
    Route::get('', [CmsEditorLangController::class, 'index']);
    Route::post('', [CmsEditorLangController::class, 'create']);

    Route::put('move', [CmsEditorLangController::class, 'updatePosition']);

    Route::prefix('{lang:id}')->group(function () {
        Route::get('', [CmsEditorLangController::class, 'readOne']);
        Route::post('', [CmsEditorLangController::class, 'update']);
        Route::delete('', [CmsEditorLangController::class, 'deleteRecord']);
    });
});
