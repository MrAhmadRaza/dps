<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfLoggedIn
{
    public function handle(Request $request, Closure $next): Response
    {
       
        if (Auth::check()) {

            $userRole = Auth::user()->role->role_name;

            // Check if the user is trying to access any of the sign-in pages
            if ($request->is('admin/sign-in') || $request->is('agent/sign-in') || $request->is('user/sign-in')) {
                // Redirect based on the user's role
                if ($userRole === 'admin' || $userRole === 'superadmin') {
                    // Admin or Superadmin
                    return redirect()->route('admin.dashboard');
                }  else {
                    // If the role is invalid or unauthorized, abort with a 403
                    return abort(403, 'Unauthorized access.');
                }
            }
        }

        return $next($request);
    }
}
