<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Services\ParentPortal\ParentPortal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(ParentPortal $portal): View
    {
        return view('parent.account.index', [
            'pageTitle' => 'Account',
            'parent'    => $portal->parent(),
            'children'  => $portal->students()->with('sessionItem')->orderBy('name')->get(),
        ]);
    }

    public function updatePassword(Request $request, ParentPortal $portal): RedirectResponse
    {
        $parent = $portal->parent();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'password.min'       => 'Use at least 8 characters for the new password.',
            'password.confirmed' => 'The two new passwords don\'t match.',
            'password.different' => 'The new password must be different from the current one.',
        ]);

        if (!Hash::check($validated['current_password'], $parent->getAuthPassword())) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $parent->forceFill(['password' => Hash::make($validated['password'])])->save();

        // Sign out other devices that were using the old password
        $request->session()->regenerate();

        return redirect()->route('parent.account')->with('success', 'Password changed.');
    }
}
