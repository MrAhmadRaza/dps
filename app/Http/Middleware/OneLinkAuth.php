<?php

namespace App\Http\Middleware;

use App\Support\OneLinkResponse as R;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the 1LINK endpoints: service switch, IP whitelist, bearer token, bank mnemonic.
 * Failures return HTTP 200 with response code 04 (or 08 when the service is disabled).
 */
class OneLinkAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('onelink.enabled')) {
            return $this->reject($request, R::PROCESSING_FAILURE, 'Service temporarily unavailable');
        }

        if (!$this->ipAllowed($request->ip())) {
            return $this->reject($request, R::INVALID_CREDENTIALS);
        }

        $expected = (string) config('onelink.token');
        $given    = (string) $request->bearerToken();
        if ($expected === '' || $given === '' || !hash_equals($expected, $given)) {
            return $this->reject($request, R::INVALID_CREDENTIALS);
        }

        $mnemonic = (string) config('onelink.bank_mnemonic');
        if ($mnemonic !== '' && strcasecmp($mnemonic, (string) $request->input('bank_mnemonic')) !== 0) {
            return $this->reject($request, R::INVALID_CREDENTIALS);
        }

        return $next($request);
    }

    private function ipAllowed(?string $ip): bool
    {
        $allowed = config('onelink.allowed_ips', []);

        if (in_array('*', $allowed, true)) {
            return true;
        }

        return $ip !== null && $allowed !== [] && IpUtils::checkIp($ip, $allowed);
    }

    private function reject(Request $request, string $code, ?string $description = null): Response
    {
        return R::send($request, 'rejected_' . R::typeFor($request), R::error($code, $description));
    }
}
