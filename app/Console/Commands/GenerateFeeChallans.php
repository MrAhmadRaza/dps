<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Voucher;
use App\Models\FeeChallan;

class GenerateFeeChallans extends Command
{
    protected $signature = 'fee:generate';
    protected $description = 'Generate monthly fee challans for students';

    public function handle()
    {
        // Year + Month 
        $month = now()->format('Y-m');
        $students = Student::whereNotNull('academic_session_id')
            ->whereNotNull('session_item_id')
            ->get();
        // loop for students  
        foreach ($students as $student) {
            $voucher = Voucher::where('academic_session_id', $student->academic_session_id)
                ->where('session_item_id', $student->session_item_id)
                ->first();
            if (!$voucher) {
                continue;
            }
            FeeChallan::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'voucher_id' => $voucher->id,
                    'month'      => $month,
                ],
                [
                    'status' => 'pending',
                ]
            );
        }
        $this->info("Fee Challans generated successfully for {$month}");
    }
}