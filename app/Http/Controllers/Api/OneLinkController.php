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
 */
class OneLinkController extends Controller
{
    public function __construct(private OneLinkBillService $bills)
    {
    }

    /**
     * POST /api/onelink/inquiry
     */
    public function inquiry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'consumer_number' => ['required', 'string', 'max:50'],
            'bank_mnemonic'   => ['required', 'string', 'max:20'],
            'channel'         => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return R::send($request, 'inquiry', R::error('inquiry', R::INVALID_STATE,
                'Missing or invalid field: ' . implode(', ', $validator->errors()->keys())));
        }

        try {
            $challan = $this->bills->resolveChallan((string) $request->input('consumer_number'));

            if (!$challan) {
                return R::send($request, 'inquiry', R::error('inquiry', R::AUTH_OR_VOUCHER, 'Consumer number not found'));
            }

            $challan->load(['student.parent', 'payment']);
            $amounts = $this->bills->amountsFor($challan);

            if ($amounts['within'] <= 0) {
                return R::send($request, 'inquiry', R::error('inquiry', R::INVALID_STATE, 'No payable amount on this challan'), $challan->id);
            }

            $paid = $this->bills->isPaid($challan);

            $body = [
                'response_code'         => R::SUCCESS,
                'institute_code'        => (string) config('onelink.institute_code'),
                'institute_name'        => $this->bills->instituteName(),
                'one_link_channel'      => (int) config('onelink.channel'),
                'consumer_detail'       => $this->bills->consumerDetail($challan),
                'bill_status'           => $paid ? 'P' : 'U',
                'due_date'              => $this->bills->dueDate($challan),
                'amount_within_dueDate' => OneLinkAmount::format($amounts['within']),
                'amount_after_dueDate'  => OneLinkAmount::format($amounts['after']),
                'amount_receivable'     => OneLinkAmount::format($amounts['receivable']),
                'late_fee'              => config('onelink.amount_format') === 'padded'
                                            ? OneLinkAmount::format($amounts['late_fee'])
                                            : number_format($amounts['late_fee'], 2, '.', ''),
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

            return R::send($request, 'inquiry', R::error('inquiry', R::SYSTEM_OR_AMOUNT, 'System error, please try again'));
        }
    }

    /**
     * POST /api/onelink/payment
     */
    public function payment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'consumer_number'    => ['required', 'string', 'max:50'],
            'bank_mnemonic'      => ['required', 'string', 'max:20'],
            'transaction_amount' => ['required'],
            'tran_auth_id'       => ['required', 'string', 'max:100'],
            'tran_date'          => ['required', 'digits:8', 'date_format:Ymd'],
            'tran_time'          => ['required', 'digits:6', 'date_format:His'],
            'channel'            => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return R::send($request, 'payment', R::error('payment', R::INVALID_STATE,
                'Missing or invalid field: ' . implode(', ', $validator->errors()->keys())));
        }

        $amount = OneLinkAmount::parse($request->input('transaction_amount'));
        if ($amount === null || $amount <= 0) {
            return R::send($request, 'payment', R::error('payment', R::INVALID_STATE, 'Invalid transaction_amount'));
        }

        $tz       = config('onelink.timezone', 'Asia/Karachi');
        $tranDate = Carbon::createFromFormat('Ymd His', $request->input('tran_date') . ' ' . $request->input('tran_time'), $tz);
        $tranId   = trim((string) $request->input('tran_auth_id'));

        $challanId = null;

        try {
            $body = DB::transaction(function () use ($request, $amount, $tranDate, $tranId, &$challanId) {
                // Lock the challan row so two payments for the same bill cannot both succeed.
                $challan = $this->bills->resolveChallan((string) $request->input('consumer_number'), true);

                if (!$challan) {
                    return R::error('payment', R::AUTH_OR_VOUCHER, 'Consumer number not found');
                }
                $challanId = $challan->id;
                $challan->load(['student.parent', 'payment']);

                // Idempotency: BOP retried a payment we already recorded.
                $existing = ChallanPayment::withTrashed()->where('tran_auth_id', $tranId)->first();
                if ($existing) {
                    return (int) $existing->fee_challan_id === (int) $challan->id
                        ? $this->paymentSuccessBody($challan)
                        : R::error('payment', R::SYSTEM_OR_AMOUNT, 'Duplicate transaction ID');
                }

                if ($this->bills->isPaid($challan)) {
                    return R::error('payment', R::INVALID_STATE, 'Bill already paid');
                }

                // Amount due as of the bank's transaction date (not our clock), so a payment
                // made on the due date is not rejected if it reaches us after midnight.
                $amounts = $this->bills->amountsFor($challan, $tranDate);

                if ($amounts['within'] <= 0) {
                    return R::error('payment', R::INVALID_STATE, 'No payable amount on this challan');
                }

                if (!OneLinkAmount::equals($amount, $amounts['receivable'])) {
                    return R::error('payment', R::SYSTEM_OR_AMOUNT, 'Amount entered does not match the unpaid amount');
                }

                $this->bills->recordPayment($challan, [
                    'tran_auth_id'  => $tranId,
                    'amount'        => $amount,
                    'channel'       => (int) $request->input('channel'),
                    'bank_mnemonic' => strtoupper((string) $request->input('bank_mnemonic')),
                    'paid_at'       => $tranDate->copy()->setTimezone(config('app.timezone')),
                    'paid_date'     => $tranDate->toDateString(),
                ]);

                return $this->paymentSuccessBody($challan);
            });
        } catch (\Throwable $e) {
            Log::error('1LINK payment failed: ' . $e->getMessage(), ['exception' => $e]);
            $body = R::error('payment', R::SYSTEM_OR_AMOUNT, 'System error, payment not processed');
        }

        return R::send($request, 'payment', $body, $challanId);
    }

    private function paymentSuccessBody(FeeChallan $challan): array
    {
        return [
            'response_Code'            => R::SUCCESS,
            'institute_code'           => (string) config('onelink.institute_code'),
            'institute_name'           => $this->bills->instituteName(),
            'one_link_channel'         => (int) config('onelink.channel'),
            'Identification_parameter' => $this->bills->consumerDetail($challan),
            'reserved'                 => 'Thank you for the payment.',
        ];
    }
}
