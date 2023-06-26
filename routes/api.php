<?php

use Illuminate\Support\Facades\Route;
use NotFound\Framework\Http\Controllers\AboutController;
use NotFound\Framework\Http\Controllers\ContentBlocks\ContentBlockController;
use NotFound\Framework\Http\Controllers\Forms\DataController;
use NotFound\Framework\Http\Controllers\Forms\DownloadController;
use NotFound\Framework\Http\Controllers\Forms\FieldController;
use NotFound\Framework\Http\Controllers\InfoController;
use NotFound\Framework\Http\Controllers\SettingsController;
use NotFound\Framework\Http\Controllers\Support\SupportController;
use NotFound\Framework\Http\Controllers\UserPreferencesController;
use Spatie\Honeypot\ProtectAgainstSpam;

// ContentBlock
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix(config('siteboss.api_prefix'))->group(function () {
    // Unauthenticated routes
    Route::prefix('api')->group(function () {
        Route::namespace('Forms')->group(function () {
            Route::post('forms/{form:id}/{langurl}', [DataController::class, 'create'])->middleware(ProtectAgainstSpam::class)->name('formbuilder.post');
            Route::get('fields/{id}', [FieldController::class, 'readOneJson']);
            // RIGHTS!!!!!
            Route::get('download/{submitid}/{fieldId}/{UUID}', [DownloadController::class, 'unauthenticatedDownload']);
        });
    });

    Route::get('{locale}/oidc', [InfoController::class, 'oidc'])->where('name', '[A-Za-z]{2}');

    // Settings for the login page
    Route::get('settings', [InfoController::class, 'settings']);

    // Authenticated routes
    Route::group(['middleware' => ['auth:openid', 'api']], function () {
        // Language for messages (not the language used for storing data)
        Route::group(['prefix' => '/{locale}', 'middleware' => 'set-forget-locale'], function () {
            Route::get('info', [InfoController::class, 'index']);

            // TODO: remove this route?
            Route::get('contentblocks/{csvTables}', [ContentBlockController::class, 'get']);

            // Table editor
            Route::prefix('table')->group(__DIR__.'/cms/table.php');

            // Form builder
            Route::middleware('role:forms')->group(__DIR__.'/cms/forms.php');

            Route::prefix('app')->group(function () {
                if (file_exists(base_path().'/routes/siteboss.php')) {
                    Route::prefix('site')->group(
                        base_path().'/routes/siteboss.php'
                    );
                }

                // /menu
                Route::prefix('menu')->group(__DIR__.'/cms/menu.php');

                // Page editor
                Route::prefix('page')->group(__DIR__.'/cms/page.php');

                // Settings
                Route::get('settings', [SettingsController::class, 'index']);
                Route::get('settings/{setting}', [SettingsController::class, 'readOne']);
                Route::post('settings/{setting}', [SettingsController::class, 'update']); // Settings

                // User preferences
                Route::get('preferences', [UserPreferencesController::class, 'index']);
                Route::post('preferences', [UserPreferencesController::class, 'update']);

                Route::prefix('users')->group(__DIR__.'/cms/users.php');

                // CMS Editor
                Route::prefix('editor')->group(__DIR__.'/cms/editor.php');

                // About SiteBoss CMS page
                Route::get('about', [AboutController::class, 'index']);

                // Support page for SiteBoss CMS
                Route::prefix('support')->group(function () {
                    Route::get('', [SupportController::class, 'index']);
                    Route::post('', [SupportController::class, 'update']);
                });

                // AutoLayout Demo pages
                Route::prefix('demo')->group(__DIR__.'/cms/demo.php');

                // Domain forward manager
                Route::prefix('forwards')->group(__DIR__.'/cms/forwards.php');
            });
        });
    });
});
