<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website routes
|--------------------------------------------------------------------------
| The staff portal lives in routes/admin.php and the parent portal in
| routes/parent.php (both loaded from bootstrap/app.php).
*/

Route::get('/', function () {
    return view('frontend.home.index');
})->name('/');
