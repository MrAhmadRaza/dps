<?php

namespace App\Http\Middleware;

use App\Support\OneLinkResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the 1LINK endpoints: service switch, IP whitelist, bearer token, bank mnemonic.
 * Failures return HTTP 200 with response code 01 (or 05 when disabled), as BOP expects.
 */
class OneLinkAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $type = str_ends_with($request->path(), 'payment') ? 'payment' : 'inquiry';

        if (!config('onelink.enabled')) {
            return $this->reject($request, $type, OneLinkResponse::SYSTEM_OR_AMOUNT, 'Service temporarily unavailable');
        }

        if (!$this->ipAllowed($request->ip())) {
            return $this->reject($request, $type, OneLinkResponse::AUTH_OR_VOUCHER, 'Unauthorized source');
        }

        $expected = (string) config('onelink.token');
        $given    = (string) $request->bearerToken();
        if ($expected === '' || $given === '' || !hash_equals($expected, $given)) {
            return $this->reject($request, $type, OneLinkResponse::AUTH_OR_VOUCHER, 'Invalid token');
        }

        $mnemonic = (string) config('onelink.bank_mnemonic');
        if ($mnemonic !== '' && strcasecmp($mnemonic, (string) $request->input('bank_mnemonic')) !== 0) {
            return $this->reject($request, $type, OneLinkResponse::AUTH_OR_VOUCHER, 'Invalid bank mnemonic');
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

    private function reject(Request $request, string $type, string $code, string $message): Response
    {
        return OneLinkResponse::send($request, 'rejected_' . $type, OneLinkResponse::error($type, $code, $message));
    }
}
