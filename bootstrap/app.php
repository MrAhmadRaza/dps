<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Staff portal and parent portal: each has its own route file, independent of the public site in web.php
            \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/admin.php'));
            \Illuminate\Support\Facades\Route::middleware('web')->group(base_path('routes/parent.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
         $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'preventBackHistory' => \App\Http\Middleware\PreventBackHistory::class,
            'redirectIfLoggedIn' => \App\Http\Middleware\RedirectIfLoggedIn::class,
            'parent.guest' => \App\Http\Middleware\Parent\RedirectIfParent::class,

        ]);

        // Parent portal and staff portal each get their own session cookie (must run before StartSession).
        // Order matters: the parent middleware resets the cookie to the app default for every other
        // path, then the admin middleware switches it for /admin.
        $middleware->web(prepend: [
            \App\Http\Middleware\Parent\UseParentSessionCookie::class,
            \App\Http\Middleware\Admin\UseAdminSessionCookie::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 1LINK / BOP requires HTTP 200 for every response, including 404/405 and crashes.
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // HttpResponseException already carries our own 200 reply (e.g. the rate limiter's).
            if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }

            if ($request->is('api/onelink', 'api/onelink/*')) {
                return \App\Support\OneLinkResponse::fromException($request, $e);
            }
        });
    })->create();
