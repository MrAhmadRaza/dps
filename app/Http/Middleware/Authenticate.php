<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
    
        if (! $request->expectsJson()) {

            // Admin panel
            if ($request->is('admin/*')) {
                return route('admin.signIn');
            }

           if($request->is('parent/*')) {
                return route('parent.signIn');
            }
            
            // Fallback
            return route('parent.signIn');
        }

        return null;
    }
}
