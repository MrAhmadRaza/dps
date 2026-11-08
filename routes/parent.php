<?php

/*
|--------------------------------------------------------------------------
| Parent portal routes
|--------------------------------------------------------------------------
| Loaded from bootstrap/app.php with the "web" middleware group. Everything
| the parent portal serves lives here, separate from the admin routes in
| routes/web.php. Route names keep the "parent." prefix used by the public
| website's links.
*/

use App\Http\Controllers\Parent\AccountController;
use App\Http\Controllers\Parent\Auth\SignInController;
use App\Http\Controllers\Parent\ChallanController;
use App\Http\Controllers\Parent\DashboardController;
use App\Http\Controllers\Parent\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('parent')->as('parent.')->group(function () {

    Route::redirect('/', '/parent/dashboard')->name('home');

    /** Authentication */
    Route::controller(SignInController::class)->group(function () {
        Route::get('/sign-in', 'signIn')->name('signIn')->middleware('parent.guest');
        Route::post('/sign-in', 'signInSubmit')->name('signIn.submit')->middleware('parent.guest');
        Route::post('/logout', 'logout')->name('logout');
    });

    Route::middleware(['auth:parents', 'preventBackHistory'])->group(function () {

        /** Home */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('index');

        /** Children */
        Route::controller(StudentController::class)->group(function () {
            Route::get('/student', 'index')->name('student.index');
            Route::get('/student/view/{id}', 'show')->name('view.student')->whereNumber('id');
        });

        /** Fees */
        Route::controller(ChallanController::class)->group(function () {
            Route::get('/challan/fee-challan', 'index')->name('fee-challan');
            Route::get('/challan/view-challan/{id}', 'show')->name('view.challan')->whereNumber('id');
            Route::get('/challan/{id}/upload', 'uploadForm')->name('challan.upload.form')->whereNumber('id');
            Route::post('/challan/upload', 'upload')->name('challan.upload');
            Route::get('/print/challan-voucher/{id}', 'print')->name('print.challan')->whereNumber('id');
        });

        /** Account */
        Route::controller(AccountController::class)->group(function () {
            Route::get('/account', 'show')->name('account');
            Route::post('/account/password', 'updatePassword')->name('account.password');
        });
    });
});
