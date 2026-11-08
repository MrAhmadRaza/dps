<?php

namespace App\Http\Controllers\Parent\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Parant;
use App\Services\ParentPortal\ParentPortal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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

        // CNICs may be stored with dashes (12345-1234567-1, what the admission form saves)
        // or as plain digits (older/imported records). Accept either.
        $parent = $this->findParentByCnic($validated['father_nic']);
        $signedIn = $parent && $this->passwordMatches($validated['password'], $parent);

        if ($signedIn) {
            Auth::guard('parents')->login($parent, $request->boolean('remember') && $this->canRemember());
        }

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

    private function findParentByCnic(string $input): ?Parant
    {
        $digits = preg_replace('/\D/', '', $input);
        if (strlen($digits) !== 13) {
            return null;
        }

        return Parant::whereIn('father_nic', [ParentPortal::formatCnic($digits), $digits])->first()
            // Any other spacing/dash variant
            ?? Parant::whereRaw("REPLACE(REPLACE(father_nic, '-', ''), ' ', '') = ?", [$digits])->first();
    }

    /**
     * "Keep me signed in" needs parents.remember_token (migration
     * 2026_11_08_000001). Until that migration has run, sign in without it
     * instead of failing.
     */
    private function canRemember(): bool
    {
        try {
            return Schema::hasColumn('parents', 'remember_token');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function passwordMatches(string $password, Parant $parent): bool
    {
        try {
            return Hash::check($password, $parent->getAuthPassword());
        } catch (\RuntimeException $e) {
            // Stored value isn't a bcrypt hash (e.g. an imported plain-text password).
            // Refuse it; the office can reset the password from the admin portal.
            \Log::warning('Parent sign-in: stored password is not a valid hash', ['parent_id' => $parent->id]);
            return false;
        }
    }

    private function throttleKey(Request $request): string
    {
        return 'parent-login:' . preg_replace('/\D/', '', (string) $request->input('father_nic')) . '|' . $request->ip();
    }
}
