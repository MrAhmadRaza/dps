<?php

namespace App\Http\Controllers\Backend\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\Rules\Password;

class AdminSignInController extends Controller
{
    // SignIn as Admin View
    public function signIn(): View
    {
        $pageTitle = 'Admin Portal';
        return view('backend.auth.sign-in', compact('pageTitle'));
    }

    // SignIn Submit
    public function signInSubmit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $attempt = Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        if ($attempt) {
            if (auth()->user()->isSuperAdmin()) {
                return redirect()->route('admin.dashboard')->with('success', 'Superadmin logged in successfully');
            } elseif (auth()->user()->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('success', 'Admin logged in successfully');
            }
            // If the user doesn't have the correct role, log them out and show error
            Auth::logout(); // Log out the unauthorized user
            return back()->withErrors(['email' => 'Unauthorized access']);
        }

        return redirect()->back()->with('error', 'Invalid Credentials');
    }

    public function logout(Request $request): RedirectResponse
    {

        Auth::logout();
        // Invalidate the existing session
        $request->session()->invalidate();
        // Regenerate session token
        $request->session()->regenerateToken();

        return redirect()->route('admin.signIn');
    }
}
