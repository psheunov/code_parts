<?php

use Axxon\AdminPanel\Http\Controllers\AdminPanelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Card API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/

Route::controller(AdminPanelController::class)->group(function () {
        Route::get('/restart-horizon', 'restartHorizon');
        Route::get('/cache-clear', 'cacheClear');
        Route::get('/redis-clear', 'redisClear');

        Route::group(['prefix' => 'reindex'], function () {
            Route::get('/blog', 'researchBlog');
            Route::get('/site', 'researchSite');
            Route::get('/site/{locale}', 'researchSiteLocale');
        });
    }
);


Route::get('/locales', function () {
    return response()->json(config('multilingual.locales'));
});


