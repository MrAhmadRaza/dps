<?php

namespace App\Services;

use App\Models\ChallanPayment;
use App\Models\FeeChallan;
use App\Models\GeneralSetting;
use Carbon\Carbon;

/**
 * Business rules for 1LINK bill inquiry / payment.
 *
 * Consumer number = general_settings.one_bill_prefix + fee_challans.challan_no
 * Unpaid          = challan status "pending" or "review" (slip uploaded but not yet approved)
 * Paid            = challan status "approved"
 * Receivable      = total_amount up to and including due_date, amount_after_due_date after it
 */
class OneLinkBillService
{
    public function now(): Carbon
    {
        return Carbon::now(config('onelink.timezone', 'Asia/Karachi'));
    }

    /**
     * Find the challan for a consumer number. Tries, in order:
     *   1. the number as sent (challan_no without prefix)
     *   2. the number with our 1Bill prefix stripped
     *   3. both of the above with leading zeros removed (old 3-4 digit challan numbers)
     */
    public function resolveChallan(string $consumerNumber, bool $lockForUpdate = false): ?FeeChallan
    {
        foreach ($this->candidateChallanNumbers($consumerNumber) as $candidate) {
            $query = FeeChallan::where('challan_no', $candidate)->whereHas('student');

            if ($lockForUpdate) {
                $query->lockForUpdate();
            }

            if ($challan = $query->first()) {
                return $challan;
            }
        }

        return null;
    }

    public function candidateChallanNumbers(string $consumerNumber): array
    {
        $number = trim($consumerNumber);
        if ($number === '') {
            return [];
        }

        $prefix = (string) (GeneralSetting::first()?->one_bill_prefix ?? '');

        $candidates = [$number];
        if ($prefix !== '' && str_starts_with($number, $prefix) && strlen($number) > strlen($prefix)) {
            $candidates[] = substr($number, strlen($prefix));
        }

        foreach ($candidates as $c) {
            $trimmed = ltrim($c, '0');
            if ($trimmed !== '') {
                $candidates[] = $trimmed;
            }
        }

        return array_values(array_unique($candidates));
    }

    public function isPaid(FeeChallan $challan): bool
    {
        return $challan->status === 'approved';
    }

    /**
     * Amounts for a challan as of a given (Pakistan-time) date.
     */
    public function amountsFor(FeeChallan $challan, ?Carbon $asOf = null): array
    {
        $asOf ??= $this->now();

        $within = round((float) $challan->total_amount, 2);
        $after  = round((float) ($challan->amount_after_due_date ?? $within), 2);
        $after  = max($after, $within);

        $isLate = $challan->due_date
            && $asOf->toDateString() > Carbon::parse($challan->due_date)->toDateString();

        return [
            'within'     => $within,
            'after'      => $after,
            'late_fee'   => round($after - $within, 2),
            'is_late'    => $isLate,
            'receivable' => $isLate ? $after : $within,
        ];
    }

    public function dueDate(FeeChallan $challan): string
    {
        return $challan->due_date ? Carbon::parse($challan->due_date)->format('Ymd') : '';
    }

    /**
     * "2026-09" -> "202609". For a range ("2026-09 to 2026-12") the first month is used.
     */
    public function billingMonth(FeeChallan $challan): string
    {
        if (preg_match('/^(\d{4})-(\d{2})/', trim((string) $challan->month), $m)) {
            return $m[1] . $m[2];
        }

        return '';
    }

    public function consumerDetail(FeeChallan $challan): string
    {
        $student = $challan->student;
        if (!$student) {
            return '';
        }

        $relation = $student->gender === 'Female' ? 'D/O' : 'S/O';
        $father   = $student->parent?->father_name;

        return trim($student->name . ($father ? " {$relation} {$father}" : ''));
    }

    public function instituteName(): string
    {
        return (string) (config('onelink.institute_name') ?: GeneralSetting::first()?->name ?? '');
    }

    /**
     * Record a successful 1LINK payment. Must be called inside a DB transaction
     * with the challan row locked.
     */
    public function recordPayment(FeeChallan $challan, array $data): ChallanPayment
    {
        // A parent may already have uploaded a bank slip (status "review"); reuse that row
        // so the unique fee_challan_id index is respected and the image is kept.
        $payment = ChallanPayment::withTrashed()
            ->where('fee_challan_id', $challan->id)
            ->first() ?? new ChallanPayment(['fee_challan_id' => $challan->id]);

        if ($payment->trashed()) {
            $payment->restore();
        }

        $payment->fill([
            'source'         => ChallanPayment::SOURCE_ONELINK,
            'bank_name'      => '1LINK / ' . $data['bank_mnemonic'],
            'bank_mnemonic'  => $data['bank_mnemonic'],
            'transaction_id' => $data['tran_auth_id'],
            'tran_auth_id'   => $data['tran_auth_id'],
            'amount'         => $data['amount'],
            'channel'        => $data['channel'],
            'paid_date'      => $data['paid_date'], // bank's transaction date (Pakistan time)
            'paid_at'        => $data['paid_at'],
        ]);
        $payment->save();

        $challan->update(['status' => 'approved']);

        return $payment;
    }
}
