<?php

namespace App\Http\Controllers\Backend\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

class ParantSignInController extends Controller
{
    // SignIn as Admin View
    public function signIn(): View
    {
        $pageTitle = 'Parent Portal';
        return view('backend.auth.parant-sign-in', compact('pageTitle'));
    }

      // SignIn Submit
    public function signInSubmit(Request $request): RedirectResponse
    {
        // Validation Check
        $validated = $request->validate([
            'father_nic' => 'required',
            'password' => 'required|min:4',
        ]);
        // Throttle by CNIC + IP: 5 attempts per minute
        $throttleKey = 'parent-login:' . preg_replace('/\D/', '', $validated['father_nic']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->back()->withInput($request->only('father_nic'))
                ->with('error', "Too many sign-in attempts. Try again in {$seconds} seconds.");
        }

        // Check Login Credentials for Parent
        $attempt = auth()->guard('parents')->attempt([
            'father_nic' => $validated['father_nic'],
            'password' => $validated['password'],
        ], $request->boolean('remember'));

        if ($attempt) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return redirect()->route('parent.index')->with('success', 'Parent logged in successfully');
        }
        RateLimiter::hit($throttleKey, 60);
        return redirect()->back()->withInput($request->only('father_nic'))->with('error', 'Invalid Credentials');
    }

    // Logout Parent
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('parents')->logout();
        // Invalidate the existing session
        $request->session()->invalidate();
        // Regenerate session token
        $request->session()->regenerateToken();

        return redirect()->route('parent.signIn');
    }

}
