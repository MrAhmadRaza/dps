<?php

namespace App\Helpers;

use App\Models\FeeChallan;
use App\Models\FeeChallanItem;
use App\Models\Voucher;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FeeChallanHelper
{

    // Challan Ni Generate
    public static function generateChallanNo(): string
    {
        do {
            $challanNo = (string) random_int(100, 9999);
        } while (FeeChallan::where('challan_no', $challanNo)->exists());

        return $challanNo;
    }

    // Custom hallan Generate
    public static function createCustomChallan($student, string $startMonthStr, string $endMonthStr): ?FeeChallan
    {
        $startMonth = Carbon::createFromFormat('Y-m', $startMonthStr)->startOfMonth();
        $endMonth   = Carbon::createFromFormat('Y-m', $endMonthStr)->startOfMonth();

        if ($startMonth->gt($endMonth)) {
            return null;
        }

        // 1. Calculate Months Count
        $monthsCount   = $startMonth->diffInMonths($endMonth) + 1;
        $monthRangeStr = $startMonthStr . ' to ' . $endMonthStr;

        $generalSetting = GeneralSetting::first();
        $lateFee   = (float) ($generalSetting->late_fee_fine ?? 0);
        $issueDate = now()->toDateString();
        $dueDate   = now()->addDays(10)->toDateString();

        // ----------------------------------------------------
        // CASE 1: Discounted Student
        // ----------------------------------------------------
        if ((int) $student->discount === 1) {
            $monthlyDiscountFee = (float) ($student->discount_amount ?? 0);
            $accumulatedTotal   = $monthlyDiscountFee * $monthsCount;

            DB::beginTransaction();
            try {
                $challan = FeeChallan::create([
                    'student_id'            => $student->id,
                    'month'                 => $monthRangeStr,
                    'include_admission_fee' => false,
                    'status'                => 'pending',
                    'issue_date'            => $issueDate,
                    'due_date'              => $student->discount_due_date ?? $dueDate,
                    'total_amount'          => $accumulatedTotal,
                    'amount_after_due_date' => $accumulatedTotal + $lateFee,
                    'note'                  => $student->discount_notes,
                    'challan_no'            => self::generateChallanNo(),
                ]);

                if ($accumulatedTotal > 0) {
                    FeeChallanItem::create([
                        'fee_challan_id' => $challan->id,
                        'fee_item_id'    => 0,
                        'fee_item_name'  => "Monthly Discounted Fee ({$monthsCount} Months)",
                        'amount'         => $accumulatedTotal,
                    ]);
                }

                DB::commit();
                return $challan;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        // ----------------------------------------------------
        // CASE 2: Normal Student (Fetch Items from fee_challan_items Table)
        // ----------------------------------------------------

        // Step A: Student ka sab se last/previous challan fetch karein
        $lastChallan = FeeChallan::where('student_id', $student->id)
            ->latest('id')
            ->first();

        if (!$lastChallan) {
            return null;
        }

        // Previous challan ka months count nikalen (e.g. "Sep to Dec" = 4, "Sep" = 1)
        $previousMonthsCount = 1;
        if (str_contains($lastChallan->month, ' to ')) {
            $rangeParts = explode(' to ', $lastChallan->month);
            if (count($rangeParts) === 2) {
                try {
                    $pStart = Carbon::createFromFormat('Y-m', trim($rangeParts[0]))->startOfMonth();
                    $pEnd   = Carbon::createFromFormat('Y-m', trim($rangeParts[1]))->startOfMonth();
                    $previousMonthsCount = $pStart->diffInMonths($pEnd) + 1;
                } catch (\Exception $e) {
                    $previousMonthsCount = 1;
                }
            }
        }

        // Step B: Previous challan ke items fetch karein
        $previousItems = FeeChallanItem::where('fee_challan_id', $lastChallan->id)->get();

        if ($previousItems->isEmpty()) {
            return null;
        }

        $itemsToInsert = [];
        $accumulatedTotal = 0;

        foreach ($previousItems as $item) {
            $storedAmount = (float) $item->amount;
            $itemName     = $item->fee_item_name;

            // Step C: Admission fee bypass
            $isAdmission = stripos($itemName, 'Admission') !== false;
            if ($isAdmission) {
                continue;
            }

            if ($storedAmount > 0) {
                // 1. Calculate PER MONTH base rate
                $perMonthRate = $storedAmount / ($previousMonthsCount > 0 ? $previousMonthsCount : 1);

                // 2. Calculate NEW total for current range
                $itemTotal = $perMonthRate * $monthsCount;

                // Clean item name (remove old "(X Months)" tags)
                $cleanName = preg_replace('/\s*\(\d+\s*Months\)/i', '', $itemName);
                $cleanName = trim(str_replace('(One-time)', '', $cleanName));

                $itemsToInsert[] = [
                    'fee_item_id'   => $item->fee_item_id ?? 0,
                    'fee_item_name' => $cleanName . " ({$monthsCount} Months)",
                    'amount'        => $itemTotal,
                ];

                $accumulatedTotal += $itemTotal;
            }
        }
        DB::beginTransaction();
        try {
            $challan = FeeChallan::create([
                'student_id'            => $student->id,
                'month'                 => $monthRangeStr,
                'include_admission_fee' => false, // Always false for custom range
                'status'                => 'pending',
                'issue_date'            => $issueDate,
                'due_date'              => $lastChallan->due_date ?? $dueDate,
                'total_amount'          => $accumulatedTotal,
                'amount_after_due_date' => $accumulatedTotal + $lateFee,
                'note'                  => $lastChallan->note,
                'challan_no'            => self::generateChallanNo(),
            ]);

            foreach ($itemsToInsert as $itemData) {
                FeeChallanItem::create([
                    'fee_challan_id' => $challan->id,
                    'fee_item_id'    => $itemData['fee_item_id'],
                    'fee_item_name'  => $itemData['fee_item_name'],
                    'amount'         => $itemData['amount'],
                ]);
            }

            DB::commit();
            return $challan;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create or retrieve admission challan upon student registration.
     */
    public static function createAdmissionChallan($student, ?string $month = null): ?FeeChallan
    {
        $month = $month ?: now()->format('Y-m');
        $generalSetting = GeneralSetting::first();
        $lateFee = (float) ($generalSetting->late_fee_fine ?? 0);

        // Check if challan already exists for this student and month
        $existingChallan = FeeChallan::where('student_id', $student->id)
            ->where('month', $month)
            ->first();

        if ($existingChallan) {
            return $existingChallan;
        }

        $issueDate = now()->toDateString();

        // Safe Flexible Discount Check
        $isDiscounted = filter_var($student->discount, FILTER_VALIDATE_BOOLEAN) || ((int) $student->discount === 1);

        // ----------------------------------------------------
        // CASE 1: Discounted Student (NO FeeChallanItem Insertion)
        // ----------------------------------------------------
        if ($isDiscounted) {
            $discountAmount = (float) ($student->discount_amount ?? 0);
            $dueDate = $student->discount_due_date ?? now()->addDays(10)->toDateString();

            DB::beginTransaction();
            try {
                $challan = FeeChallan::create([
                    'student_id'            => $student->id,
                    'month'                 => $month,
                    'include_admission_fee' => false,
                    'status'                => 'pending',
                    'issue_date'            => $issueDate,
                    'due_date'              => $dueDate,
                    'total_amount'          => $discountAmount,
                    'amount_after_due_date' => $discountAmount + $lateFee,
                    'note'                  => $student->discount_notes,
                    'challan_no'            => self::generateChallanNo(),
                ]);

                // NOTE: FeeChallanItem::create() HAS BEEN REMOVED HERE STRICTLY!

                DB::commit();
                return $challan;

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        // ----------------------------------------------------
        // CASE 2: Normal Student (Using Voucher Snapshot)
        // ----------------------------------------------------
        $voucher = Voucher::with('items')
            ->where('academic_session_id', $student->academic_session_id)
            ->where('session_item_id', $student->session_item_id)
            ->first();

        if (!$voucher) {
            return null;
        }

        $includeAdmissionFee = self::resolveAdmissionFee($student, $voucher);
        $dueDate = $voucher->due_date ?? now()->addDays(10)->toDateString();

        // Collect Voucher Items (Filter out amount <= 0)
        $itemsToInsert = [];
        $calculatedTotal = 0;

        foreach ($voucher->items as $item) {
            $amount = (float) ($item->amount ?? 0);
            $isAdmission = stripos($item->fee_name ?? '', 'Admission') !== false;

            // Skip Admission Fee if it shouldn't be included
            if ($isAdmission && !$includeAdmissionFee) {
                continue;
            }

            // Only store items with amount > 0
            if ($amount > 0) {
                $itemsToInsert[] = [
                    'fee_item_id'   => $item->id,
                    'fee_item_name' => $item->fee_name,
                    'amount'        => $amount,
                ];
                $calculatedTotal += $amount;
            }
        }

        DB::beginTransaction();
        try {
            $challan = FeeChallan::create([
                'student_id'            => $student->id,
                'month'                 => $month,
                'include_admission_fee' => $includeAdmissionFee,
                'status'                => 'pending',
                'issue_date'            => $issueDate,
                'due_date'              => $dueDate,
                'total_amount'          => $calculatedTotal,
                'amount_after_due_date' => $calculatedTotal + $lateFee,
                'note'                  => $voucher->notes,
                'challan_no'            => self::generateChallanNo(),
            ]);

            // Insert Snapshot Items into fee_challan_items for normal students only
            foreach ($itemsToInsert as $itemData) {
                FeeChallanItem::create([
                    'fee_challan_id' => $challan->id,
                    'fee_item_id'    => $itemData['fee_item_id'],
                    'fee_item_name'  => $itemData['fee_item_name'],
                    'amount'         => $itemData['amount'],
                ]);
            }

            DB::commit();
            return $challan;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if Admission Fee should be added (Only once per student).
     */
    public static function resolveAdmissionFee($student, $voucher): bool
    {
        if ((int) $student->discount === 1 || !$voucher) {
            return false;
        }

        $admissionItem = $voucher->items
            ->first(function ($item) {
                return stripos($item->fee_name ?? '', 'Admission') !== false;
            });

        $admissionAmount = (float) ($admissionItem->amount ?? 0);

        if ($admissionAmount <= 0) {
            return false;
        }

        $alreadyCharged = FeeChallan::where('student_id', $student->id)
            ->where('include_admission_fee', true)
            ->exists();

        return !$alreadyCharged;
    }

    /**
     * Calculate month count from 'YYYY-MM' or 'YYYY-MM to YYYY-MM'.
     */
    public static function getChallanMonthsCount(?string $month): int
    {
        if (!$month || !str_contains($month, ' to ')) {
            return 1;
        }

        try {
            [$start, $end] = explode(' to ', $month);
            $startDate = Carbon::createFromFormat('Y-m', trim($start))->startOfMonth();
            $endDate   = Carbon::createFromFormat('Y-m', trim($end))->startOfMonth();

            return $startDate->diffInMonths($endDate) + 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Format Challan Display Month string.
     */
    public static function formatChallanMonth(?string $month): string
    {
        $month = $month ?: 'N/A';

        if (str_contains($month, ' to ')) {
            try {
                [$start, $end] = explode(' to ', $month);
                $startFormatted = Carbon::createFromFormat('Y-m', trim($start))->format('M Y');
                $endFormatted   = Carbon::createFromFormat('Y-m', trim($end))->format('M Y');
                return $startFormatted . ' to ' . $endFormatted;
            } catch (\Exception $e) {
                return $month;
            }
        }

        try {
            return Carbon::createFromFormat('Y-m', $month)->format('M Y');
        } catch (\Exception $e) {
            return $month;
        }
    }

    /**
     * Print Breakdown fetching directly from fee_challan_items (Independent of Voucher edits).
     */
    public static function getPrintBreakdown($challan): array
    {
        $challan->loadMissing(['student', 'items']);
        $monthsCount = self::getChallanMonthsCount($challan->month);

        $items = [];
        $total = 0;

        foreach ($challan->items as $item) {
            $amount = (float) $item->amount;
            $items[] = [
                'label'  => $item->fee_item_name,
                'amount' => $amount,
            ];

            $total += $amount;
        }

        return [
            'is_discount'  => (int) ($challan->student->discount ?? 0) === 1,
            'items'        => $items,
            'total'        => $total,
            'months_count' => $monthsCount,
        ];
    }
}