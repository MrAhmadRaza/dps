<?php

namespace App\Support;

use App\Models\OneLinkLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Response codes and JSON shapes for the 1LINK API.
 *
 * Every response (success or failure) is HTTP 200 with:
 *   response_code        - one of the codes below
 *   response_description - a user-friendly message
 */
class OneLinkResponse
{
    public const SUCCESS             = '00'; // inquiry OK / payment posted
    public const VOUCHER_INVALID     = '01'; // consumer number not found / no payable amount
    public const ALREADY_PAID        = '02';
    public const VOUCHER_EXPIRED     = '03';
    public const INVALID_CREDENTIALS = '04'; // bad token / IP / bank mnemonic
    public const AMOUNT_MISMATCH     = '05';
    public const DUPLICATE_TRAN      = '06'; // tran_auth_id already used on another voucher
    public const INVALID_REQUEST     = '07'; // missing / invalid fields, unknown endpoint
    public const PROCESSING_FAILURE  = '08'; // system error, service disabled, rate limited

    public const DESCRIPTIONS = [
        self::SUCCESS             => 'Success',
        self::VOUCHER_INVALID     => 'Voucher is invalid',
        self::ALREADY_PAID        => 'Voucher already paid',
        self::VOUCHER_EXPIRED     => 'Voucher expired',
        self::INVALID_CREDENTIALS => 'Invalid credentials',
        self::AMOUNT_MISMATCH     => 'Amount does not match the payable amount',
        self::DUPLICATE_TRAN      => 'Duplicate transaction ID',
        self::INVALID_REQUEST     => 'Invalid request',
        self::PROCESSING_FAILURE  => 'Processing failure, please try again',
    ];

    public static function error(string $code, ?string $description = null): array
    {
        return [
            'response_code'        => $code,
            'response_description' => $description ?? self::DESCRIPTIONS[$code] ?? '',
        ];
    }

    public static function typeFor(Request $request): string
    {
        return str_ends_with($request->path(), 'payment') ? 'payment' : 'inquiry';
    }

    /**
     * Turn any exception raised under api/onelink/* (404, 405, crashes...) into an HTTP 200 reply.
     */
    public static function fromException(Request $request, Throwable $e): JsonResponse
    {
        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        $body = in_array($status, [404, 405], true)
            ? self::error(self::INVALID_REQUEST, 'Invalid request: unknown endpoint or method')
            : self::error(self::PROCESSING_FAILURE);

        return self::send($request, 'error_' . self::typeFor($request), $body);
    }

    /**
     * Log the exchange and return it. Always HTTP 200: the result is in response_code.
     */
    public static function send(Request $request, string $type, array $body, ?int $challanId = null): JsonResponse
    {
        try {
            OneLinkLog::create([
                'type'            => $type,
                'consumer_number' => substr((string) $request->input('consumer_number', ''), 0, 50) ?: null,
                'tran_auth_id'    => substr((string) $request->input('tran_auth_id', ''), 0, 100) ?: null,
                'amount'          => substr((string) $request->input('transaction_amount', ''), 0, 30) ?: null,
                'response_code'   => $body['response_code'] ?? null,
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
