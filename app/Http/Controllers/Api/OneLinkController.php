<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChallanPayment;
use App\Models\FeeChallan;
use App\Services\OneLinkBillService;
use App\Support\OneLinkAmount;
use App\Support\OneLinkResponse as R;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * 1LINK / BOP bill payment endpoints. Called by BOP, not by users.
 * Authentication is done by the OneLinkAuth middleware.
 *
 * channel: 0 = branch counter, 1 = 1Bill.
 */
class OneLinkController extends Controller
{
    private const CONSUMER_NUMBER_RULE = ['required', 'regex:/^\d{1,20}$/'];
    private const CHANNEL_RULE         = ['required', 'integer', 'in:0,1'];

    public function __construct(private OneLinkBillService $bills)
    {
    }

    /**
     * POST /api/onelink/inquiry
     */
    public function inquiry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'consumer_number' => self::CONSUMER_NUMBER_RULE,
            'bank_mnemonic'   => ['required', 'string', 'max:20'],
            'channel'         => self::CHANNEL_RULE,
        ]);

        if ($validator->fails()) {
            return R::send($request, 'inquiry', $this->invalidRequest($validator));
        }

        try {
            $challan = $this->bills->resolveChallan((string) $request->input('consumer_number'));

            if (!$challan) {
                return R::send($request, 'inquiry', R::error(R::VOUCHER_INVALID));
            }

            if ($this->bills->isExpired($challan)) {
                return R::send($request, 'inquiry', R::error(R::VOUCHER_EXPIRED), $challan->id);
            }

            $challan->load(['student.parent', 'payment']);
            $amounts = $this->bills->amountsFor($challan);

            if ($amounts['within'] <= 0) {
                return R::send($request, 'inquiry', R::error(R::VOUCHER_INVALID, 'Voucher has no payable amount'), $challan->id);
            }

            $paid = $this->bills->isPaid($challan);

            $body = [
                'response_code'         => $paid ? R::ALREADY_PAID : R::SUCCESS,
                'response_description'  => $paid ? R::DESCRIPTIONS[R::ALREADY_PAID] : 'Voucher is valid',
                'institute_code'        => (string) config('onelink.institute_code'),
                'institute_name'        => $this->bills->instituteName(),
                'one_link_channel'      => (int) $request->input('channel'),
                'consumer_detail'       => $this->bills->consumerDetail($challan),
                'bill_status'           => $paid ? 'P' : 'U',
                'due_date'              => $this->bills->dueDate($challan),
                'amount_within_dueDate' => OneLinkAmount::format($amounts['within']),
                'amount_after_dueDate'  => OneLinkAmount::format($amounts['after']),
                'amount_receivable'     => OneLinkAmount::format($amounts['receivable']),
                'late_fee'              => OneLinkAmount::format($amounts['late_fee']),
                'billing_month'         => $this->bills->billingMonth($challan),
                'tran_auth_Id'          => '',
            ];

            if ($paid) {
                $payment = $challan->payment;
                if ($payment?->amount !== null) {
                    $body['amount_receivable'] = OneLinkAmount::format((float) $payment->amount);
                }
                $body['tran_auth_Id'] = (string) ($payment?->tran_auth_id ?? '');
                $body['date_paid']    = $payment?->paid_date ? Carbon::parse($payment->paid_date)->format('Ymd') : '';
            }

            return R::send($request, 'inquiry', $body, $challan->id);
        } catch (\Throwable $e) {
            Log::error('1LINK inquiry failed: ' . $e->getMessage(), ['exception' => $e]);

            return R::send($request, 'inquiry', R::error(R::PROCESSING_FAILURE));
        }
    }

    /**
     * POST /api/onelink/payment
     */
    public function payment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'consumer_number'    => self::CONSUMER_NUMBER_RULE,
            'bank_mnemonic'      => ['required', 'string', 'max:20'],
            'transaction_amount' => ['required'],
            'tran_auth_id'       => ['required', 'string', 'max:100'],
            'tran_date'          => ['required', 'digits:8', 'date_format:Ymd'],
            'tran_time'          => ['required', 'digits:6', 'date_format:His'],
            'channel'            => self::CHANNEL_RULE,
        ]);

        if ($validator->fails()) {
            return R::send($request, 'payment', $this->invalidRequest($validator));
        }

        $amount = OneLinkAmount::parse($request->input('transaction_amount'));
        if ($amount === null || $amount <= 0) {
            return R::send($request, 'payment', R::error(R::INVALID_REQUEST, 'Invalid request: transaction_amount'));
        }

        $tz       = config('onelink.timezone', 'Asia/Karachi');
        $tranDate = Carbon::createFromFormat('Ymd His', $request->input('tran_date') . ' ' . $request->input('tran_time'), $tz);
        $tranId   = trim((string) $request->input('tran_auth_id'));
        $channel  = (int) $request->input('channel');

        $challanId = null;

        try {
            $body = DB::transaction(function () use ($request, $amount, $tranDate, $tranId, $channel, &$challanId) {
                // Lock the challan row so two payments for the same bill cannot both succeed.
                $challan = $this->bills->resolveChallan((string) $request->input('consumer_number'), true);

                if (!$challan) {
                    return R::error(R::VOUCHER_INVALID);
                }
                $challanId = $challan->id;
                $challan->load(['student.parent', 'payment']);

                // Idempotency: BOP retried a payment we already recorded.
                $existing = ChallanPayment::withTrashed()->where('tran_auth_id', $tranId)->first();
                if ($existing) {
                    return (int) $existing->fee_challan_id === (int) $challan->id
                        ? $this->paymentSuccessBody($challan, $channel)
                        : R::error(R::DUPLICATE_TRAN);
                }

                if ($this->bills->isPaid($challan)) {
                    return R::error(R::ALREADY_PAID);
                }

                if ($this->bills->isExpired($challan)) {
                    return R::error(R::VOUCHER_EXPIRED);
                }

                // Amount due as of the bank's transaction date (not our clock), so a payment
                // made on the due date is not rejected if it reaches us after midnight.
                $amounts = $this->bills->amountsFor($challan, $tranDate);

                if ($amounts['within'] <= 0) {
                    return R::error(R::VOUCHER_INVALID, 'Voucher has no payable amount');
                }

                if (!OneLinkAmount::equals($amount, $amounts['receivable'])) {
                    return R::error(R::AMOUNT_MISMATCH);
                }

                $this->bills->recordPayment($challan, [
                    'tran_auth_id'  => $tranId,
                    'amount'        => $amount,
                    'channel'       => $channel,
                    'bank_mnemonic' => strtoupper((string) $request->input('bank_mnemonic')),
                    'paid_at'       => $tranDate->copy()->setTimezone(config('app.timezone')),
                    'paid_date'     => $tranDate->toDateString(),
                ]);

                return $this->paymentSuccessBody($challan, $channel);
            });
        } catch (\Throwable $e) {
            Log::error('1LINK payment failed: ' . $e->getMessage(), ['exception' => $e]);
            $body = R::error(R::PROCESSING_FAILURE);
        }

        return R::send($request, 'payment', $body, $challanId);
    }

    private function invalidRequest(\Illuminate\Validation\Validator $validator): array
    {
        return R::error(R::INVALID_REQUEST, 'Invalid request: ' . implode(', ', $validator->errors()->keys()));
    }

    private function paymentSuccessBody(FeeChallan $challan, int $channel): array
    {
        return [
            'response_code'            => R::SUCCESS,
            'response_description'     => 'Payment posted successfully',
            'institute_code'           => (string) config('onelink.institute_code'),
            'institute_name'           => $this->bills->instituteName(),
            'one_link_channel'         => $channel,
            'Identification_parameter' => $this->bills->consumerDetail($challan),
        ];
    }
}
