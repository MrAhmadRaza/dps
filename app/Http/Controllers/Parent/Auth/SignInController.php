<?php

namespace App\Http\Controllers\Parent\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class SignInController extends Controller
{
    /** Failed attempts allowed per CNIC + IP before a one-minute lockout. */
    public const MAX_ATTEMPTS = 5;

    public function signIn(): View
    {
        return view('parent.auth.sign-in', ['pageTitle' => 'Sign in']);
    }

    public function signInSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'father_nic' => 'required|string|max:15',
            'password'   => 'required|string|min:4',
        ], [
            'father_nic.required' => 'Enter the father\'s CNIC number.',
            'password.required'   => 'Enter your password.',
        ]);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withInput($request->only('father_nic'))
                ->with('error', "Too many sign-in attempts. Try again in {$seconds} seconds.");
        }

        $signedIn = Auth::guard('parents')->attempt([
            'father_nic' => $validated['father_nic'],
            'password'   => $validated['password'],
        ], $request->boolean('remember'));

        if (!$signedIn) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withInput($request->only('father_nic', 'remember'))
                ->with('error', 'The CNIC or password is incorrect.');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('parent.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('parents')->logout();

        // Only the parent session is affected: it has its own cookie (UseParentSessionCookie).
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('parent.signIn')->with('success', 'You have signed out.');
    }

    private function throttleKey(Request $request): string
    {
        return 'parent-login:' . preg_replace('/\D/', '', (string) $request->input('father_nic')) . '|' . $request->ip();
    }
}
