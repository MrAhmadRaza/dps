<?php

namespace App\Http\Middleware\Parent;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends an already signed-in parent from the sign-in page to their dashboard.
 */
class RedirectIfParent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guard('parents')->check()) {
            return redirect()->route('parent.index');
        }

        return $next($request);
    }
}
