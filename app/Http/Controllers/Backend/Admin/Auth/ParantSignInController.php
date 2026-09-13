<?php

namespace App\Http\Controllers\Backend\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
        // Check Login Credentials for Parent
        $attempt = auth()->guard('parents')->attempt([
            'father_nic' => $validated['father_nic'],
            'password' => $validated['password'],
        ]);
           
        if ($attempt) {
            return redirect()->route('parent.index')->with('success', 'Parent logged in successfully');
        }
        return redirect()->back()->with('error', 'Invalid Credentials');
    }

    // Logout Parent
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        // Invalidate the existing session
        $request->session()->invalidate();
        // Regenerate session token
        $request->session()->regenerateToken();

        return redirect()->route('parent.signIn');
    }

}
