<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminSignInController extends Controller
{
    /** Failed attempts allowed per email + IP before a one-minute lockout. */
    public const MAX_ATTEMPTS = 5;

    public function signIn(): View
    {
        $pageTitle = 'Staff sign in';

        return view('admin.auth.sign-in', compact('pageTitle'));
    }

    public function signInSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'admin-sign-in:' . Str::lower($validated['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->onlyInput('email')->withErrors([
                'email' => "Too many sign-in attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $credentials = ['email' => $validated['email'], 'password' => $validated['password']];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            return back()->onlyInput('email')->withErrors([
                'email' => 'That email and password do not match a staff account.',
            ]);
        }

        $user = Auth::user();

        if (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            Auth::logout();
            RateLimiter::hit($key, 60);

            return back()->onlyInput('email')->withErrors([
                'email' => 'This account does not have access to the staff portal.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.signIn')->with('success', 'You have signed out.');
    }
}
