<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Challan - {{ $student->name ?? 'Student' }}</title>
    <link rel="stylesheet" href="{{ asset('backend_assets/css/voucher.css') }}">
    <style>
        .no-print-bar {
            background: #ffffff;
            padding: 10px 20px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background-color: #0d6efd;
            color: #fff;
            padding: 6px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }
        .btn-back {
            background-color: #6c757d;
            color: #fff;
            padding: 6px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
        }
        @media print {
            .no-print-bar {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    @php
        $generalSetting = \App\Models\GeneralSetting::first();

        /* Copies */
        $copies = ['Bank Copy', 'School Copy', 'Student Copy'];

        /* Student Details & Session Info */
        $sessionItem = $student->sessionItem ?? null;
        $className   = $sessionItem?->class ?? 'N/A';
        $sectionName = $sessionItem?->section ?? 'N/A';

        /* Discount Logic */
        $isDiscount      = (int) ($student->discount ?? 0) === 1;
        $discountDueDate = $student->discount_due_date ?? null;
        $discountNotes   = $student->discount_notes ?? null;

        /* Amounts & Due Dates */
        $total         = (float) ($challan->total_amount ?? $challan->amount ?? 0);
        $duesAfterDate = (float) ($challan->amount_after_due_date ?? ($total > 0 ? $total + 200 : 0));
        $dueDate       = $isDiscount ? ($discountDueDate ?? $challan->due_date) : $challan->due_date;

        /* 1Bill ID Prefix */
        $oneBillPrefix = $generalSetting->one_bill_prefix ?? '';
        $challanNo     = $challan->challan_no ?? $challan->id ?? '';
        $oneBillId     = $oneBillPrefix . $challanNo;

        /* Payment Instructions */
        $paymentInstructions = config('fees.payment_instructions', config('challan.payment_instructions', []));

        /* 
        |--------------------------------------------------------------------------
        | DB Items Mapping & Cleaning (Fixes 0 Amount Bug)
        |--------------------------------------------------------------------------
        */
        if (!$challan->relationLoaded('items')) {
            $challan->load('items');
        }

        $normalizedDbItems = [];
        $rawDbItems = [];

        if ($challan->items && $challan->items->count() > 0) {
            foreach ($challan->items as $dbItem) {
                $rawName = $dbItem->fee_item_name ?? $dbItem->fee_name ?? $dbItem->label ?? 'Fee Item';
                $amt     = (float) ($dbItem->amount ?? 0);

                // Strip "(5 Months)", "(One-time)", etc. to match config keys accurately
                $cleanName = preg_replace('/\s*\(\d+\s*Months\)/i', '', $rawName);
                $cleanName = trim(str_replace('(One-time)', '', $cleanName));
                $key       = strtolower($cleanName);

                $normalizedDbItems[$key] = $amt;
                $rawDbItems[] = [
                    'display_name' => $rawName,
                    'amount'       => $amt
                ];
            }
        }

        // Config File List Fallback
        $defaultFeeItems = config('fees.default_fees', [
            'Tuition Fee',
            'Admission Fee',
            'Exam Fee',
            'Registration Fee',
            'Annual Charges',
            'Others'
        ]);
    @endphp

    <div class="wrapper">

        @foreach ($copies as $copy)
            <div class="voucher">

                {{-- LOGOS --}}
                <div class="logo-section">
                    <img src="{{ asset('upload/logo/Bank_of_Punjab_logo.png') }}" alt="Bank of Punjab" class="bop-logo">
                    <img src="{{ asset('upload/logo/logo.png') }}" alt="DPS Dunya Pur" class="dps-logo">
                </div>

                {{-- BANK & SCHOOL DETAILS --}}
                <div class="bank">
                    {{ $generalSetting->name ?? 'THE BANK OF PUNJAB' }}
                </div>

                <div class="branch">
                    Dunyapur (Branch)
                </div>

                <div class="branch">
                    Due Date: 
                    @if ($dueDate)
                        {{ \Carbon\Carbon::parse($dueDate)->format('d F Y') }}
                    @else
                        N/A
                    @endif
                </div>

                <div class="branch">
                    Fee Slip A/C # {{ $generalSetting->account_no ?? 'N/A' }}
                </div>

                <div class="school">
                    D.P.S DUNYA PUR
                </div>

                <hr class="thick-line">

                {{-- CHALLAN NO & MONTH --}}
                <div class="box-row">
                    <div class="box-label">Challan No</div>
                    <div class="box-value">{{ $oneBillId }}</div>
                </div>

                <div class="box-row">
                    <div class="box-label">Month</div>
                    <div class="box-value">{{ $monthLabel ?? $challan->month ?? 'N/A' }}</div>
                </div>

                {{-- STUDENT DETAILS --}}
                <div class="box-row">
                    <div class="box-label">Name</div>
                    <div class="box-value">{{ $student->name ?? 'N/A' }}</div>
                </div>

                <div class="box-row">
                    <div class="box-label">Father Name</div>
                    <div class="box-value">{{ $student->parent->father_name ?? $student->parent->name ?? 'N/A' }}</div>
                </div>

                <div class="box-row">
                    <div class="box-label">Class</div>
                    <div class="box-value">{{ $className }}</div>
                </div>

                <div class="box-row">
                    <div class="box-label">Section</div>
                    <div class="box-value">{{ $sectionName }}</div>
                </div>

                @if ($isDiscount)
                    <div class="note">
                        <strong>Note:</strong> Discount is enabled for this student.
                    </div>
                @endif

                {{-- ==========================================================
                     FEE ITEMS LOOP (Config Items + Dynamic DB Items Matching)
                =========================================================== --}}
                @if ($isDiscount && count($rawDbItems) > 0)
                    {{-- For Discounted Students: Show exact dynamic DB items --}}
                    @foreach ($rawDbItems as $item)
                        <div class="row">
                            <span class="label">{{ $item['display_name'] }}</span>
                            <span class="amount-box">
                                {{ number_format($item['amount'], 0) }}
                            </span>
                        </div>
                    @endforeach
                @else
                    {{-- For Regular Students: Match Config Items with DB Amounts --}}
                    @foreach ($defaultFeeItems as $feeName)
                        @php
                            $key = strtolower(trim($feeName));
                            $amount = $normalizedDbItems[$key] ?? 0;
                        @endphp
                        <div class="row">
                            <span class="label">{{ $feeName }}</span>
                            <span class="amount-box">
                                {{ $amount > 0 ? number_format($amount, 0) : '0' }}
                            </span>
                        </div>
                    @endforeach
                @endif

                {{-- TOTAL DUES --}}
                <div class="row total-row">
                    <span class="label">Total Dues</span>
                    <span class="amount-box">
                        {{ number_format($total, 0) }}
                    </span>
                </div>

                {{-- DUES AFTER DATE --}}
                <div class="row total-row">
                    <span class="label">Dues After Date</span>
                    <span class="amount-box">
                        {{ $duesAfterDate > 0 ? number_format($duesAfterDate, 0) : '0' }}
                    </span>
                </div>

                {{-- AMOUNT IN WORDS --}}
                <div class="full-box-row">
                    <div class="full-box-label">In Words</div>
                    <div class="full-box">
                        @if(class_exists('NumberFormatter'))
                            {{ \NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($total) }} rupees
                        @else
                            {{ number_format($total, 0) }} PKR
                        @endif
                    </div>
                </div>

                {{-- NOTES --}}
                @if ($isDiscount && $discountNotes)
                    <div class="note">
                        <strong>Note:</strong> {{ $discountNotes }}
                    </div>
                @elseif($challan->notes)
                    <div class="note">
                        <strong>Note:</strong> {{ $challan->notes }}
                    </div>
                @endif

                {{-- SIGNATURES & INSTRUCTIONS --}}
                <div class="sign">
                    <div>Accountant __________</div>
                    <div>Bank Cashier __________</div>
                    <div class="copy-name">{{ $copy }}</div>
                </div>

                <div class="payment-instructions">
                    <strong>{{ $paymentInstructions['heading_1'] ?? 'Fee Collection Through Cash Management' }}</strong><br>
                    <strong>{{ $paymentInstructions['heading_2'] ?? 'Payment Process Through 1 Bill' }}</strong><br>

                    @foreach ($paymentInstructions['steps'] ?? [] as $step)
                        {{ $step }}<br>
                    @endforeach

                    5) Enter the ID: {{ $oneBillId }} and proceed with payment
                </div>

            </div>
        @endforeach

    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Optional auto print
            window.print();
        });
    </script>
</body>

</html>