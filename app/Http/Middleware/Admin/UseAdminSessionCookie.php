<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives the staff portal its own session cookie, scoped to /admin.
 *
 * Staff, parents and website visitors then have separate sessions on the
 * same domain: the staff cookie is never sent to public pages or to
 * /parent, and signing out of one portal never signs anyone out of another.
 *
 * Runs before StartSession. The previous cookie settings are put back once
 * the request is handled, so nothing leaks into the next request when the
 * app runs in a long-lived process (queue worker, tests).
 */
class UseAdminSessionCookie
{
    public const COOKIE = 'dps_admin_session';
    public const PATH   = '/admin';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin', 'admin/*')) {
            return $next($request);
        }

        $previous = [
            'session.cookie' => config('session.cookie'),
            'session.path'   => config('session.path'),
        ];

        $this->apply(self::COOKIE, self::PATH);

        try {
            return $next($request);
        } finally {
            $this->apply($previous['session.cookie'], $previous['session.path']);
        }
    }

    private function apply(?string $cookie, ?string $path): void
    {
        config(['session.cookie' => $cookie, 'session.path' => $path]);

        // If the session store was already built, rename it too.
        if ($cookie && app()->resolved('session.store')) {
            app('session.store')->setName($cookie);
        }
    }
}
