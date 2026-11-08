@extends('admin.layouts.app')
@section('content')
@php
    $student  = $challan->student;
    $payment  = $challan->payment;
    $proof    = $payment?->voucher_image;
    $hasProof = $proof && is_file(public_path($proof));
    $month    = \App\Helpers\FeeChallanHelper::formatChallanMonth($challan->month);
    $who      = $student->name ?? 'this student';
    $onelink  = $payment?->source === 'onelink';
    $rs       = fn ($v) => number_format((float) $v, 0);
    $back     = url()->previous() !== url()->current() && str_contains(url()->previous(), '/admin/') ? url()->previous() : route('admin.challan.index');
@endphp

<div class="ad-head">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h1>{{ $month }} challan</h1>
            @include('admin.partials.status-chip', ['status' => $status])
        </div>
        <p>
            <a href="{{ $student ? route('admin.view.admission', $student->id) : '#' }}" class="fw-650">{{ $student->name ?? 'Unknown student' }}</a>
            @if ($student?->sessionItem) · Class {{ $student->sessionItem->class }}-{{ $student->sessionItem->section }} @endif
            · Challan no. <span class="mono">{{ $challan->challan_no ?: $challan->id }}</span>
        </p>
    </div>
    <div class="ad-head-actions">
        <a href="{{ $back }}" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i>Back</a>
        <a href="{{ route('admin.challan.print', $challan->id) }}" target="_blank" class="btn btn-light"><i class="fa-solid fa-print"></i>Print</a>
        @if ($challan->status === 'review')
            <form method="POST" action="{{ route('admin.challan.approved', $challan->id) }}" data-confirm="Mark {{ $who }}'s {{ $month }} challan as paid?" data-confirm-title="Approve payment" data-confirm-ok="Approve">
                @csrf
                <button class="btn btn-success"><i class="fa-solid fa-check"></i>Approve payment</button>
            </form>
        @elseif ($challan->status === 'pending')
            <form method="POST" action="{{ route('admin.challan.approved', $challan->id) }}" data-confirm="Mark {{ $who }}'s {{ $month }} challan as paid? Use this when the fee was paid at the school office." data-confirm-title="Mark as paid" data-confirm-ok="Mark as paid">
                @csrf
                <button class="btn btn-light"><i class="fa-solid fa-check"></i>Mark as paid</button>
            </form>
        @endif
    </div>
</div>

@if ($onelink)
    <div class="alert alert-info d-flex gap-2 align-items-start">
        <i class="fa-solid fa-building-columns mt-1"></i>
        <div>Paid through <strong>1LINK / 1Bill</strong>. The bank confirmed this payment, so it can't be set back to unpaid or deleted.</div>
    </div>
@elseif ($challan->status === 'review')
    <div class="alert alert-info d-flex gap-2 align-items-start">
        <i class="fa-solid fa-magnifying-glass mt-1"></i>
        <div>The parent uploaded a payment proof. Check the amount, date and transaction ID against the bank statement, then approve.</div>
    </div>
@elseif ($status['key'] === 'overdue')
    <div class="alert alert-danger d-flex gap-2 align-items-start">
        <i class="fa-solid fa-circle-exclamation mt-1"></i>
        <div>Past the due date. The amount now payable is <strong>Rs {{ $rs($amounts['after']) }}</strong> (includes Rs {{ $rs($amounts['late_fee']) }} late fee).</div>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-7">
        <section class="panel h-100">
            <div class="panel-head">
                <h2>Payment proof</h2>
                @if ($hasProof)
                    <div class="d-flex gap-2">
                        <a href="{{ asset($proof) }}" target="_blank" rel="noopener" class="btn btn-sm btn-light"><i class="fa-solid fa-up-right-from-square"></i>Open</a>
                        <a href="{{ asset($proof) }}" download class="btn btn-sm btn-light"><i class="fa-solid fa-download"></i>Download</a>
                    </div>
                @endif
            </div>
            <div class="panel-body">
                <div class="proof-frame">
                    @if ($hasProof)
                        <img src="{{ asset($proof) }}" alt="Payment proof uploaded for {{ $who }}" data-zoom="{{ asset($proof) }}">
                    @else
                        <div class="proof-empty">
                            <i class="fa-regular fa-image"></i>
                            @if ($onelink)
                                No photo needed. The bank sent the payment record directly.
                            @elseif ($proof)
                                A proof was recorded but the file is missing from the server.
                            @else
                                No proof uploaded yet.
                            @endif
                        </div>
                    @endif
                </div>

                <h3 class="small-caps mt-4 mb-2">Payment record</h3>
                @if ($payment)
                    <dl class="dl">
                        <dt>Paid through</dt><dd>{{ $payment->bank_name ?: '—' }}</dd>
                        <dt>Paid on</dt><dd>{{ $payment->paid_date?->format('l, j F Y') ?? '—' }}</dd>
                        <dt>Transaction ID</dt>
                        <dd>
                            @if ($payment->transaction_id)
                                <span class="mono">{{ $payment->transaction_id }}</span>
                                <button type="button" class="copy-btn" data-copy="{{ $payment->transaction_id }}" aria-label="Copy transaction ID"><i class="fa-regular fa-copy"></i></button>
                            @else — @endif
                        </dd>
                        @if ($onelink)
                            <dt>Amount received</dt><dd>Rs {{ $rs($payment->amount) }}</dd>
                            <dt>Bank time</dt><dd>{{ $payment->paid_at?->timezone(config('onelink.timezone', 'Asia/Karachi'))->format('j M Y, h:i A') ?? '—' }} · channel {{ $payment->channel ?? '—' }}</dd>
                        @endif
                        <dt>Uploaded</dt><dd>{{ $payment->created_at?->timezone(config('onelink.timezone', 'Asia/Karachi'))->format('j M Y, h:i A') ?? '—' }}</dd>
                    </dl>
                @else
                    <p class="text-muted mb-0">No payment has been recorded for this challan.</p>
                @endif
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <div class="stack">
            <section class="panel">
                <div class="panel-head"><h2>Amount</h2><span class="sub">Rs</span></div>
                <div class="panel-body">
                    @forelse ($challan->items as $item)
                        <div class="money-row"><span>{{ $item->fee_item_name }}</span><span class="v">{{ $rs($item->amount) }}</span></div>
                    @empty
                        <div class="money-row"><span class="text-muted">No breakdown saved</span><span></span></div>
                    @endforelse
                    <div class="money-row total"><span>Payable by due date</span><span class="v">{{ $rs($amounts['within']) }}</span></div>
                    <div class="money-row"><span>Payable after due date</span><span class="v">{{ $rs($amounts['after']) }}</span></div>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Challan</h2></div>
                <div class="panel-body">
                    <dl class="dl">
                        <dt>Student</dt>
                        <dd>
                            @if ($student)
                                <a href="{{ route('admin.view.admission', $student->id) }}">{{ $student->name }}</a>
                                @if ($student->register_no)<span class="text-muted fw-normal"> · {{ $student->register_no }}</span>@endif
                            @else — @endif
                        </dd>
                        <dt>Father</dt><dd>{{ $student?->parent?->father_name ?? '—' }}</dd>
                        <dt>Contact</dt><dd>{{ $student?->parent?->contact_no ?? '—' }}</dd>
                        <dt>Session</dt><dd>{{ $student?->academicSession?->session_name ?? '—' }}</dd>
                        <dt>Month</dt><dd>{{ $month }}</dd>
                        <dt>Issued</dt><dd>{{ $challan->issue_date ? \Carbon\Carbon::parse($challan->issue_date)->format('j M Y') : '—' }}</dd>
                        <dt>Due</dt><dd>{{ $challan->due_date ? \Carbon\Carbon::parse($challan->due_date)->format('j M Y') : '—' }}</dd>
                        <dt>1Bill ID</dt>
                        <dd><span class="mono">{{ $challan->consumer_number }}</span>
                            <button type="button" class="copy-btn" data-copy="{{ $challan->consumer_number }}" aria-label="Copy 1Bill ID"><i class="fa-regular fa-copy"></i></button></dd>
                        <dt>Type</dt><dd>{{ (int) $student?->discount === 1 ? 'Concession' : 'Normal' }}</dd>
                        @if ($challan->note)<dt>Note</dt><dd>{{ $challan->note }}</dd>@endif
                    </dl>
                </div>
                @if (!$onelink)
                    <div class="panel-foot d-flex flex-wrap gap-2 justify-content-between align-items-center">
                        @if ($challan->status === 'approved')
                            <form method="POST" action="{{ route('admin.challan.pending', $challan->id) }}" data-confirm="Set {{ $who }}'s {{ $month }} challan back to unpaid?" data-confirm-title="Set back to unpaid" data-confirm-ok="Set to unpaid">
                                @csrf
                                <button class="btn btn-sm btn-light"><i class="fa-solid fa-rotate-left"></i>Set back to unpaid</button>
                            </form>
                        @else <span></span> @endif
                        <form method="POST" action="{{ route('admin.challan.destroy', $challan->id) }}" data-confirm="Delete {{ $who }}'s {{ $month }} challan? Any uploaded proof is removed with it." data-confirm-title="Delete challan" data-confirm-ok="Delete" data-confirm-tone="danger">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="fa-regular fa-trash-can"></i>Delete challan</button>
                        </form>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
