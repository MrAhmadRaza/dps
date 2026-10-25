<?php

namespace App\Support;

use App\Models\OneLinkLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Response codes and JSON shapes for the 1LINK API.
 * Field names and casing follow BOP's sample document exactly:
 * inquiry uses "response_code", payment uses "response_Code".
 */
class OneLinkResponse
{
    public const SUCCESS       = '00';
    public const AUTH_OR_VOUCHER = '01'; // bad token / IP / unknown consumer number
    public const INVALID_STATE = '03';   // missing fields, already paid, etc.
    public const SYSTEM_OR_AMOUNT = '05';

    public static function codeKey(string $type): string
    {
        return $type === 'payment' ? 'response_Code' : 'response_code';
    }

    public static function error(string $type, string $code, string $message): array
    {
        return [
            self::codeKey($type) => $code,
            'reserved'           => $message,
        ];
    }

    /**
     * Log the exchange and return it. Always HTTP 200: the result is in the response code.
     */
    public static function send(Request $request, string $type, array $body, ?int $challanId = null): JsonResponse
    {
        try {
            OneLinkLog::create([
                'type'            => $type,
                'consumer_number' => substr((string) $request->input('consumer_number', ''), 0, 50) ?: null,
                'tran_auth_id'    => substr((string) $request->input('tran_auth_id', ''), 0, 100) ?: null,
                'amount'          => substr((string) $request->input('transaction_amount', ''), 0, 30) ?: null,
                'response_code'   => $body[self::codeKey($type)] ?? $body['response_code'] ?? $body['response_Code'] ?? null,
                'fee_challan_id'  => $challanId,
                'ip'              => $request->ip(),
                'request'         => $request->json()->all() ?: $request->all(),
                'response'        => $body,
            ]);
        } catch (\Throwable $e) {
            // Logging must never break a payment response.
            report($e);
        }

        return response()->json($body, 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
