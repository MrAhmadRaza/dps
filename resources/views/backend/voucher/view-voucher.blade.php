@extends('backend.layout.master')

@section('title', $pageTitle ?? 'Voucher Details')

@section('content')

    <style>
        .voucher-wrapper {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .voucher {
            width: 33%;
            border: 1.5px solid #000;
            padding: 6px 8px 8px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            font-weight: 700;
            line-height: 1.05;
            display: flex;
            flex-direction: column;
            min-height: 520px;
        }

        .bank {
            text-align: center;
            font-weight: 900;
            font-size: 13px;
        }

        .branch {
            font-size: 11px;
            text-align: center;
        }

        .school {
            font-size: 14pt;
            font-weight: 900;
            text-align: center;
            margin: 3px 0;
        }

        .thick-line {
            border-top: 2px solid #000;
            margin: 3px 0;
        }

        .box-row {
            display: flex;
            align-items: center;
            margin: 2px 0;
        }

        .box-label {
            width: 120px;
            font-weight: 800;
        }

        .box-label::after {
            content: " :";
        }

        .box-value {
            flex: 1;
            border: 1px solid #000;
            padding: 2px 5px;
            min-height: 16px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1px 0;
        }

        .label {
            width: 150px;
            font-weight: 800;
        }

        .amount-box {
            width: 75px;
            border: 1px solid #000;
            text-align: right;
            padding-right: 4px;
        }

        .total-row {
            margin-top: 3px;
            font-weight: 900;
        }

        .full-box-row {
            display: flex;
            margin-top: 4px;
        }

        .full-box-label {
            width: 120px;
            font-weight: 800;
        }

        .full-box-label::after {
            content: " :";
        }

        .full-box {
            flex: 1;
            border: 1px solid #000;
            padding: 3px;
            font-size: 9pt;
        }

        .note {
            font-size: 9pt;
            margin-top: 5px;
            padding-top: 3px;
            border-top: 1px solid #000;
        }

        .sign {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 10px;
            font-size: 9px;
            white-space: nowrap;
        }

        .copy-name {
            font-weight: 900;
        }
    </style>

    <div class="container">
        <div class="page-inner">

            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <h3>Voucher Details</h3>

                <a href="{{ route('admin.voucher.index') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>

            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    @php
                        $generalSetting = \App\Models\GeneralSetting::first();
                        $copies = ['Bank Copy', 'School Copy', 'Student Copy'];
                    @endphp

                    <div class="voucher-wrapper">

                        @foreach ($copies as $copy)
                            @php $total = 0; @endphp

                            <div class="voucher">

                                <div class="bank">{{ $generalSetting->name ?? 'N/A' }}</div>

                                <div class="branch">Dunyapur (Branch)</div>

                                <div class="branch">
                                    Due Date: {{ \Carbon\Carbon::parse($voucher->due_date)->format('d F Y') }}
                                </div>

                                <div class="branch">
                                    Fee Slip A/C # {{ $generalSetting->account_no ?? 'N/A' }}
                                </div>

                                <div class="school">D.P.S DUNYA PUR</div>

                                <hr class="thick-line">

                                <div class="box-row">
                                    <div class="box-label">Month</div>
                                    <div class="box-value">
                                     
                                    </div>
                                </div>

                                <div class="box-row">
                                    <div class="box-label">Name</div>
                                    <div class="box-value">
                                        
                                    </div>
                                </div>

                                <div class="box-row">
                                    <div class="box-label">Father Name</div>
                                    <div class="box-value">
                                       
                                    </div>
                                </div>

                                <div class="box-row">
                                    <div class="box-label">Class</div>
                                    <div class="box-value">
                                        {{ $voucher->sessionItem->class ?? '' }}
                                    </div>
                                </div>

                                <div class="box-row">
                                    <div class="box-label">Section</div>
                                    <div class="box-value">
                                        {{ $voucher->sessionItem->section ?? '' }}
                                    </div>
                                </div>

                                @foreach ($voucher->items as $item)
                                    @php
                                        $amount = $item->amount ?? 0;
                                        $total += $amount;
                                    @endphp

                                    <div class="row">
                                        <span class="label">{{ $item->fee_name ?? '' }}</span>

                                        <span class="amount-box">
                                            {{ $amount > 0 ? number_format($amount, 0) : '---' }}
                                        </span>
                                    </div>
                                @endforeach

                                <div class="row total-row">
                                    <span class="label">Total Dues</span>
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

                                <div class="full-box-row">
                                    <div class="full-box-label">In Words</div>

                                    <div class="full-box">
                                        {{ \NumberFormatter::create('en', \NumberFormatter::SPELLOUT)->format($total) }}
                                        rupees
                                    </div>

                                </div>

                                <div class="note">
                                    <strong>Note:</strong> After Due Date Rs.200/- will be charged. Fee once paid is non
                                    refundable.
                                </div>

                                <div class="sign">
                                    <div>Accountant __________</div>
                                    <div>Bank Cashier __________</div>
                                    <div class="copy-name">{{ $copy }}</div>
                                </div>

                            </div>
                        @endforeach

                    </div>

                </div>
            </div>

        </div>
    </div>

@endsection
