<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1LINK / BOP API rate limit (per source IP)
        RateLimiter::for('onelink', function (Request $request) {
            return Limit::perMinute(max(1, (int) config('onelink.rate_limit', 120)))->by($request->ip());
        });
    }
}
