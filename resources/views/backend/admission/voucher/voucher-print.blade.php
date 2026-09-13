<!DOCTYPE html>
<html>

<head>
    <title>DPS Fee Voucher</title>
    <link rel="stylesheet" href="{{ asset('backend_assets/css/voucher.css') }}">
</head>

<body onload="window.print()">

    @php
        $generalSetting = \App\Models\GeneralSetting::first();

        $copies = ['Bank Copy', 'School Copy', 'Student Copy'];

        /*
        |--------------------------------------------------------------------------
        | Student Session Item
        |--------------------------------------------------------------------------
        */
        $sessionItem = $student->sessionItem ?? null;

        /*
        |--------------------------------------------------------------------------
        | Discount
        |--------------------------------------------------------------------------
        */
        $isDiscount = (int) ($student->discount ?? 0) === 1;

        $discountAmount = (float) ($student->discount_amount ?? 0);

        $discountDueDate = $student->discount_due_date ?? null;

        $discountNotes = $student->discount_notes ?? null;

        /*
        |--------------------------------------------------------------------------
        | Normal Voucher Items
        |--------------------------------------------------------------------------
        */
        $voucherItems = $voucher?->items ?? [];
    @endphp


    <div class="wrapper">

        @foreach ($copies as $copy)

            @php

                /*
                |--------------------------------------------------------------------------
                | Calculate Total
                |--------------------------------------------------------------------------
                */

                $total = 0;

                if ($isDiscount) {

                    // Discount student
                    $total = $discountAmount;

                } else {

                    // Normal student
                    foreach ($voucherItems as $item) {
                        $total += (float) ($item->amount ?? 0);
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Dues After Date
                |--------------------------------------------------------------------------
                */

                $duesAfterDate = $total > 0
                    ? $total + 200
                    : 0;

            @endphp


            <div class="voucher">


                {{-- ==========================================================
                     BANK / SCHOOL INFORMATION
                =========================================================== --}}

                <div class="bank">
                    {{ $generalSetting->name ?? 'N/A' }}
                </div>


                <div class="branch">
                    Dunyapur (Branch)
                </div>


                {{-- ==========================================================
                     DUE DATE
                =========================================================== --}}

                <div class="branch">

                    Due Date:

                    @if ($isDiscount)

                        {{-- Discount Due Date --}}

                        @if ($discountDueDate)

                            {{ \Carbon\Carbon::parse($discountDueDate)->format('d F Y') }}

                        @else

                            N/A

                        @endif

                    @else

                        {{-- Normal Voucher Due Date --}}

                        @if ($voucher?->due_date)

                            {{ \Carbon\Carbon::parse($voucher->due_date)->format('d F Y') }}

                        @else

                            N/A

                        @endif

                    @endif

                </div>


                <div class="branch">

                    Fee Slip A/C #

                    {{ $generalSetting->account_no ?? 'N/A' }}

                </div>


                <div class="school">
                    D.P.S DUNYA PUR
                </div>


                <hr class="thick-line">


                {{-- ==========================================================
                     MONTH
                =========================================================== --}}

                <div class="box-row">

                    <div class="box-label">
                        Month
                    </div>

                    <div class="box-value">
                    </div>

                </div>


                {{-- ==========================================================
                     STUDENT NAME
                =========================================================== --}}

                <div class="box-row">

                    <div class="box-label">
                        Name
                    </div>

                    <div class="box-value">
                        {{ $student->name ?? '' }}
                    </div>

                </div>


                {{-- ==========================================================
                     FATHER NAME
                =========================================================== --}}

                <div class="box-row">

                    <div class="box-label">
                        Father Name
                    </div>

                    <div class="box-value">
                        {{ $student->parent->father_name ?? '' }}
                    </div>

                </div>


                {{-- ==========================================================
                     CLASS
                =========================================================== --}}

                <div class="box-row">

                    <div class="box-label">
                        Class
                    </div>

                    <div class="box-value">
                        {{ $sessionItem->class ?? 'N/A' }}
                    </div>

                </div>


                {{-- ==========================================================
                     SECTION
                =========================================================== --}}

                <div class="box-row">

                    <div class="box-label">
                        Section
                    </div>

                    <div class="box-value">
                        {{ $sessionItem->section ?? 'N/A' }}
                    </div>

                </div>


                {{-- ==========================================================
                     DISCOUNT VOUCHER
                =========================================================== --}}

                @if ($isDiscount)

                    <div class="note">

                        <strong>Note:</strong>

                        Discount is enabled for this student.

                    </div>


                    <div class="row">

                        <span class="label">
                            Discount Amount
                        </span>

                        <span class="amount-box">
                            {{ number_format($discountAmount, 0) }}
                        </span>

                    </div>


                {{-- ==========================================================
                     NORMAL VOUCHER
                =========================================================== --}}

                @else

                    @foreach ($voucherItems as $item)

                        @php
                            $amount = (float) ($item->amount ?? 0);
                        @endphp

                        <div class="row">

                            <span class="label">
                                {{ $item->fee_name ?? '' }}
                            </span>

                            <span class="amount-box">

                                @if ($amount > 0)
                                    {{ number_format($amount, 0) }}
                                @endif

                            </span>

                        </div>

                    @endforeach

                @endif


                {{-- ==========================================================
                     TOTAL DUES
                =========================================================== --}}

                <div class="row total-row">

                    <span class="label">
                        Total Dues
                    </span>

                    <span class="amount-box">
                        {{ number_format($total, 0) }}
                    </span>

                </div>


                {{-- ==========================================================
                     DUES AFTER DATE
                =========================================================== --}}

                <div class="row total-row">

                    <span class="label">
                        Dues After Date
                    </span>

                    <span class="amount-box">

                        @if ($duesAfterDate > 0)

                            {{ number_format($duesAfterDate, 0) }}

                        @endif

                    </span>

                </div>


                {{-- ==========================================================
                     AMOUNT IN WORDS
                =========================================================== --}}

                <div class="full-box-row">

                    <div class="full-box-label">
                        In Words
                    </div>

                    <div class="full-box">

                        {{ \NumberFormatter::create(
                            'en',
                            \NumberFormatter::SPELLOUT
                        )->format($total) }}

                        rupees

                    </div>

                </div>


                {{-- ==========================================================
                     NOTES
                =========================================================== --}}

                @if ($isDiscount)

                    {{-- Discount Notes --}}

                    @if ($discountNotes)

                        <div class="note">

                            <strong>
                                Note:
                            </strong>

                            {{ $discountNotes }}

                        </div>

                    @endif

                @else

                    {{-- Normal Voucher Notes --}}

                    @if ($voucher?->notes)

                        <div class="note">

                            <strong>
                                Note:
                            </strong>

                            {{ $voucher->notes }}

                        </div>

                    @endif

                @endif


                {{-- ==========================================================
                     SIGNATURES
                =========================================================== --}}

                <div class="sign">

                    <div>
                        Accountant __________
                    </div>

                    <div>
                        Bank Cashier __________
                    </div>

                    <div class="copy-name">
                        {{ $copy }}
                    </div>

                </div>


            </div>

        @endforeach

    </div>

</body>

</html>