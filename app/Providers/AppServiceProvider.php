<?php

namespace App\Providers;

use App\Support\OneLinkResponse;
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
            // BOP requires HTTP 200 for every response, so answer with code 08 instead of a 429.
            return Limit::perMinute(max(1, (int) config('onelink.rate_limit', 120)))
                ->by($request->ip())
                ->response(fn (Request $request) => OneLinkResponse::send(
                    $request,
                    'rejected_' . OneLinkResponse::typeFor($request),
                    OneLinkResponse::error(OneLinkResponse::PROCESSING_FAILURE, 'Too many requests, please retry shortly')
                ));
        });
    }
}
