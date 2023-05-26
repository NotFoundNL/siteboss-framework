<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ContentBlocks\ContentBlockController;
use App\Http\Controllers\Forms\DataController;
use App\Http\Controllers\Forms\DownloadController;
use App\Http\Controllers\Forms\FieldController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Support\SupportController;
use App\Http\Controllers\UserPreferencesController;
use Illuminate\Support\Facades\Route;
use Siteboss\Routes\SiteRoutes;

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
Route::prefix(config('app.api_prefix'))->group(function () {
    // Unauthenticated routes
    Route::prefix('api')->group(function () {
        Route::namespace('Forms')->group(function () {
            Route::post('forms/{form:id}/{langurl}', [DataController::class, 'create'])->name('formbuilder.post');
            Route::get('fields/{id}', [FieldController::class, 'readOneJson']);
            // RIGHTS!!!!!
            Route::get('download/{submitid}/{fieldId}/{UUID}', [DownloadController::class, 'unauthenticatedDownload']);
        });
    });

    Route::get('{locale}/oidc', [InfoController::class, 'oidc'])->where('name', '[A-Za-z]{2}');

    // Settings for the login page
    Route::get('settings', [InfoController::class, 'settings']);

    if (class_exists(SiteRoutes::class)) {
        Route::group(['middleware' => ['api']], function () {
            Route::prefix('public')->group(SiteRoutes::getPublic());
        });
    }

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
                if (class_exists(SiteRoutes::class)) {
                    // /site for custom
                    Route::prefix('site')->group(SiteRoutes::getAppSiteRoutes());
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
