<?php

use NotFound\Framework\Http\Controllers\Forms\CategoryController;
use NotFound\Framework\Http\Controllers\Forms\DataController;
use NotFound\Framework\Http\Controllers\Forms\DownloadController;
use NotFound\Framework\Http\Controllers\Forms\FieldController;
use NotFound\Framework\Http\Controllers\Forms\FormController;
use Illuminate\Support\Facades\Route;

Route::namespace('Forms')->group(function () {
    // /forms
    Route::prefix('forms')->group(function () {
        Route::post('', [FormController::class, 'create']);

        Route::get('combinations', [FormController::class, 'readAllCombinations']);
        Route::get('archive', [FormController::class, 'readAllArchive']);

        // /forms/categories
        Route::prefix('categories')->group(function () {
            Route::get('', [CategoryController::class, 'readAllBasedOnRights']);
            Route::get('{category:slug}', [FormController::class, 'readAllBasedOnCategory']);
        });

        // /forms/{id}
        Route::prefix('{form:id}')->group(function () {
            Route::delete('', [FormController::class, 'delete']);
            Route::put('', [FormController::class, 'update']);
            Route::post('clone', [FormController::class, 'clone']);

            Route::get('', [FormController::class, 'readOne']);
            Route::get('csv', [DownloadController::class, 'downloadReport']);

            // /forms/{id}/settings
            Route::prefix('settings')->group(function () {
                Route::get('', [FormController::class, 'getText']);
                Route::put('', [FormController::class, 'updateText']);
            });

            // /forms/{id}/fields
            Route::prefix('fields')->group(function () {
                Route::get('', [FieldController::class, 'readOne']);
                Route::patch('', [FieldController::class, 'update']);

                Route::get('{fieldId}/records/{recordId}/files/{arrayIndex}', [DownloadController::class, 'CheckFile']);
                Route::get('{fieldId}/records/{recordId}/files/{arrayIndex}/download', [DownloadController::class, 'downloadFile']);
            });

            // /forms/{id}/data
            Route::prefix('data')->group(function () {
                Route::get('', [DataController::class, 'readOne']);
                Route::get('all', [DataController::class, 'readOneAll']);
                Route::get('filled', [DataController::class, 'readOneFilled']);

                // /forms/{id}/data/{id}
                Route::prefix('{recordId}')->group(function () {
                    Route::delete('', [DataController::class, 'deleteRow']);
                    Route::patch('', [DataController::class, 'updateField']);
                });
            });
        });
    });
});
