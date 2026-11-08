<?php

namespace App\Http\Middleware\Parent;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives the parent portal its own session cookie, scoped to /parent.
 *
 * Parents and school staff then have separate sessions on the same domain:
 * signing out of one portal never signs anyone out of the other, and the
 * parent cookie is never sent to /admin. Runs before StartSession.
 */
class UseParentSessionCookie
{
    public const COOKIE = 'dps_parent_session';
    public const PATH   = '/parent';

    /** The app's normal session cookie settings, captured on first use. */
    private static ?array $defaults = null;

    public function handle(Request $request, Closure $next): Response
    {
        self::$defaults ??= [
            'cookie' => config('session.cookie'),
            'path'   => config('session.path'),
        ];

        [$cookie, $path] = $request->is('parent', 'parent/*')
            ? [self::COOKIE, self::PATH]
            : [self::$defaults['cookie'], self::$defaults['path']];

        config(['session.cookie' => $cookie, 'session.path' => $path]);

        // If the session store was already built (it normally isn't yet), rename it too.
        if (app()->resolved('session.store')) {
            app('session.store')->setName($cookie);
        }

        return $next($request);
    }
}
