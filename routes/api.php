<?php

use App\Http\Controllers\Api\OneLinkController;
use App\Http\Middleware\OneLinkAuth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1LINK / BOP bill payment API
|--------------------------------------------------------------------------
| Base URL to give BOP: https://<your-domain>/api/onelink
| No session / CSRF (api middleware group). Auth: OneLinkAuth (IP whitelist + bearer token).
*/

Route::prefix('onelink')
    ->middleware([OneLinkAuth::class, 'throttle:onelink'])
    ->controller(OneLinkController::class)
    ->group(function () {
        Route::post('/inquiry', 'inquiry')->name('onelink.inquiry');
        Route::post('/payment', 'payment')->name('onelink.payment');
    });
