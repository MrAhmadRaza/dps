@extends('parent.layouts.app')

@section('content')
@php
    $firstName = trim(strtok((string) $parent->father_name, ' ')) ?: $parent->father_name;
@endphp

<section class="pp-summary" aria-labelledby="summary-title">
    <div>
        <p class="pp-summary-greet" id="summary-title">Assalam-o-Alaikum, {{ $firstName }}</p>
        @if ($dueCount > 0)
            <p class="pp-summary-amount"><small>Rs</small>{{ number_format($totalDue) }}</p>
            <p class="pp-summary-note">
                To pay across {{ $dueCount }} {{ \Illuminate\Support\Str::plural('challan', $dueCount) }}.@if ($overdueCount) <strong>{{ $overdueCount }} {{ $overdueCount === 1 ? 'is' : 'are' }} past the due date.</strong>@endif @if ($nextDue) Next due {{ $nextDue->format('d M Y') }}.@endif
            </p>
        @else
            <p class="pp-summary-amount">All fees paid</p>
            <p class="pp-summary-note">There's nothing to pay right now. New challans will appear here when the school issues them.</p>
        @endif
    </div>
    <div class="pp-summary-facts">
        <span class="pp-fact"><i class="fa-solid fa-children" aria-hidden="true"></i> {{ $children->count() }} {{ \Illuminate\Support\Str::plural('child', $children->count()) }} enrolled</span>
        @if ($overdueCount)
            <span class="pp-fact is-alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $overdueCount }} overdue</span>
        @endif
        @if ($reviewCount)
            <span class="pp-fact"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i> {{ $reviewCount }} awaiting confirmation</span>
        @endif
    </div>
</section>

<section class="pp-section" aria-labelledby="kids-title">
    <h2 class="pp-section-title" id="kids-title">
        <span>Fees by child</span>
        <a href="{{ route('parent.fee-challan') }}">All challans</a>
    </h2>

    @if ($children->isEmpty())
        <div class="pp-card pp-empty">
            <i class="fa-solid fa-children" aria-hidden="true"></i>
            <strong>No active students on this account</strong>
            If your child is enrolled at DPS, contact the school office to link them to your account.
        </div>
    @else
        <div class="pp-slips">
            @foreach ($children as $child)
                @forelse ($child['open'] as $p)
                    @include('parent.partials.slip', ['p' => $p, 'student' => $child['student'], 'oneBill' => $oneBill])
                @empty
                    <article class="pp-slip is-approved">
                        <div class="pp-slip-head">
                            @include('parent.partials.avatar', ['student' => $child['student']])
                            <div class="pp-slip-who">
                                <strong>{{ $child['student']->name }}</strong>
                                <span>Class {{ $child['student']->sessionItem->class ?? '—' }} {{ $child['student']->sessionItem->section ?? '' }}</span>
                            </div>
                            <span class="pp-status pp-status--success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Up to date</span>
                        </div>
                        <div class="pp-slip-tear" aria-hidden="true"></div>
                        <div class="pp-slip-clear">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            <span>
                                @if ($child['last_paid'])
                                    {{ $child['last_paid']['month'] }} fee paid
                                    @if ($child['last_paid']['challan']->payment?->paid_date)
                                        on {{ $child['last_paid']['challan']->payment->paid_date->format('d M Y') }}
                                    @endif.
                                    <a href="{{ route('parent.view.challan', $child['last_paid']['challan']->id) }}">View challan</a>
                                @else
                                    No fees due for {{ $child['student']->name }}.
                                @endif
                            </span>
                        </div>
                    </article>
                @endforelse
            @endforeach
        </div>
    @endif
</section>

@if ($dueCount > 0)
<section class="pp-section" aria-labelledby="pay-title">
    <h2 class="pp-section-title" id="pay-title"><span>How to pay</span></h2>
    <div class="pp-card"><div class="pp-card-body">
        <ol class="pp-steps">
            @if ($oneBill)
                <li><span><strong>Pay online with 1Bill.</strong> In your bank or wallet app, open bill payments, choose 1Bill (invoice or voucher) and enter the consumer number from the challan. The payment is recorded automatically.</span></li>
                <li><span><strong>Or pay at the bank.</strong> Print the challan and pay it at any Bank of Punjab branch.</span></li>
                <li><span><strong>Paid at the bank or by transfer?</strong> Upload a photo of the stamped challan or receipt so the office can mark it paid.</span></li>
            @else
                <li><span><strong>Print the challan</strong> and pay it at any Bank of Punjab branch before the due date.</span></li>
                <li><span><strong>Upload a photo</strong> of the stamped challan or receipt so the office can mark it paid.</span></li>
            @endif
        </ol>
    </div></div>
</section>
@endif

<section class="pp-section">
    @include('parent.partials.help')
</section>
@endsection
