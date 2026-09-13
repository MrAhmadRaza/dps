<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Voucher;
use App\Models\FeeChallan;
use App\Models\FeeChallanItem;
use App\Models\GeneralSetting;
use App\Helpers\FeeChallanHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateFeeChallans extends Command
{
    protected $signature = 'fee:generate';
    protected $description = 'Generate monthly fee challans for active students';

    public function handle()
    {
        $month = now()->format('Y-m');
        $generalSetting = GeneralSetting::first();
        $lateFee = (float) ($generalSetting->late_fee_fine ?? 0);

        $students = Student::where('status', 'active')
            ->whereNotNull('academic_session_id')
            ->whereNotNull('session_item_id')
            ->get();

        $count = 0;

        foreach ($students as $student) {
            
            // 1. DUPLICATE CHECK FOR CURRENT MONTH
            $existsThisMonth = FeeChallan::where('student_id', $student->id)
                ->where(function ($query) use ($month) {
                    $query->where('month', $month)
                          ->orWhere('month', 'LIKE', "%{$month}%");
                })
                ->exists();

            if ($existsThisMonth) {
                continue;
            }

            // 2. CHECK IF THIS IS THE STUDENT'S FIRST EVER CHALLAN (For Normal Students Only)
            $hasPreviousChallans = FeeChallan::where('student_id', $student->id)->exists();

            $issueDate = now()->toDateString();

            DB::beginTransaction();

            try {
                // ----------------------------------------------------
                // CASE 1: Discounted Student
                // ----------------------------------------------------
                if ((int) $student->discount === 1) {
                    $discountAmount = (float) ($student->discount_amount ?? 0);
                    $dueDate = $student->discount_due_date ?? now()->addDays(10)->toDateString();

                    // Strictly set include_admission_fee to false & No FeeChallanItem insertion
                    FeeChallan::create([
                        'student_id'            => $student->id,
                        'month'                 => $month,
                        'include_admission_fee' => false,
                        'status'                => 'pending',
                        'issue_date'            => $issueDate,
                        'due_date'              => $dueDate,
                        'total_amount'          => $discountAmount,
                        'amount_after_due_date' => $discountAmount + $lateFee,
                        'note'                  => $student->discount_notes,
                        'challan_no'            => FeeChallanHelper::generateChallanNo(),
                    ]);

                    DB::commit();
                    $count++;
                    continue;
                }

                // ----------------------------------------------------
                // CASE 2: Normal Student (Voucher Template)
                // ----------------------------------------------------
                $voucher = Voucher::with('items')
                    ->where('academic_session_id', $student->academic_session_id)
                    ->where('session_item_id', $student->session_item_id)
                    ->first();

                if (!$voucher) {
                    DB::rollBack();
                    continue;
                }

                $dueDate = $voucher->due_date ?? now()->addDays(10)->toDateString();
                $itemsToInsert = [];
                $calculatedTotal = 0;
                $isAdmissionIncluded = false;

                foreach ($voucher->items as $item) {
                    $amount = (float) ($item->amount ?? 0);
                    $isAdmission = stripos($item->fee_name ?? '', 'Admission') !== false;

                    // Condition: Exclude Admission Fee ONLY IF student already has previous challans
                    if ($isAdmission && $hasPreviousChallans) {
                        continue;
                    }

                    if ($isAdmission && !$hasPreviousChallans) {
                        $isAdmissionIncluded = true;
                    }

                    if ($amount > 0) {
                        $itemsToInsert[] = [
                            'fee_item_id'   => $item->id,
                            'fee_item_name' => $item->fee_name,
                            'amount'        => $amount,
                        ];
                        $calculatedTotal += $amount;
                    }
                }

                $challan = FeeChallan::create([
                    'student_id'            => $student->id,
                    'month'                 => $month,
                    'include_admission_fee' => $isAdmissionIncluded,
                    'status'                => 'pending',
                    'issue_date'            => $issueDate,
                    'due_date'              => $dueDate,
                    'total_amount'          => $calculatedTotal,
                    'amount_after_due_date' => $calculatedTotal + $lateFee,
                    'note'                  => $voucher->notes,
                    'challan_no'            => FeeChallanHelper::generateChallanNo(),
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
                $count++;

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Fee Challan generation failed for Student ID {$student->id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully generated {$count} fee challans for {$month}.");

        return self::SUCCESS;
    }
}