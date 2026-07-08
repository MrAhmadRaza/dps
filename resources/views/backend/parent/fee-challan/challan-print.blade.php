<!DOCTYPE html>
<html>
<head>
    <title>Fee Challan - {{ $student->name }}</title>
    <link rel="stylesheet" href="{{ asset('backend_assets/css/voucher.css') }}" />
</head>
<body onload="window.print()">

@php
    $generalSetting = \App\Models\GeneralSetting::first();
    $total = $voucher->voucher->total_amount ?? 0;
@endphp

<div class="wrapper">

    <div class="voucher">

        <div class="bank">{{ $generalSetting->name ?? 'N/A' }}</div>
        <div class="branch">Branch: {{ $student->branch ?? 'Main' }}</div>
        <div class="branch">
            Due Date: {{ \Carbon\Carbon::parse($voucher->voucher->due_date)->format('d F Y') }}
        </div>
        <div class="branch">
            Fee Slip A/C # {{ $generalSetting->account_no ?? 'N/A' }}
        </div>
        <div class="school">D.P.S DUNYA PUR</div>

        <hr class="thick-line">

        <div class="box-row">
            <div class="box-label">Month</div>
            <div class="box-value">{{ $voucher->month ?? '' }}</div>
        </div>

        <div class="box-row">
            <div class="box-label">Name</div>
            <div class="box-value">{{ $student->name ?? '' }}</div>
        </div>

        <div class="box-row">
            <div class="box-label">Father Name</div>
            <div class="box-value">{{ $student->parent->father_name ?? '' }}</div>
        </div>

        <div class="box-row">
            <div class="box-label">Class</div>
            <div class="box-value">{{ $student->sessionItem->class ?? '' }}</div>
        </div>

        <div class="box-row">
            <div class="box-label">Section</div>
            <div class="box-value">{{ $student->sessionItem->section ?? '' }}</div>
        </div>

        {{-- Voucher Items --}}
        @if(!empty($voucher->voucher->items))
            @php $total = 0; @endphp
            @foreach($voucher->voucher->items as $item)
                @php $total += $item->amount ?? 0; @endphp
                <div class="row">
                    <span class="label">{{ $item->fee_name ?? '' }}</span>
                    <span class="amount-box">{{ number_format($item->amount ?? 0, 0) }}</span>
                </div>
            @endforeach
        @endif

        {{-- Total --}}
        <div class="row total-row">
            <span class="label">Total Amount</span>
            <span class="amount-box">{{ number_format($total, 0) }}</span>
        </div>

         <div class="row total-row">
            <span class="label">Dues After Date</span>
            <span class="amount-box">
                @if ($total > 0)
                    {{ number_format($total + 200, 0) }}
                @endif
            </span>
        </div>

        {{-- In Words --}}
        <div class="full-box-row">
            <div class="full-box-label">In Words</div>
            <div class="full-box">
                {{ \NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($total) }} rupees
            </div>
        </div>

        {{-- Notes --}}
        <div class="note">
            <strong>Note:</strong> {{ $voucher->voucher->notes ?? '' }}
        </div>

        <div class="sign">
            <div>Accountant __________</div>
            <div>Bank Cashier __________</div>
        </div>

    </div>

</div>

</body>
</html>