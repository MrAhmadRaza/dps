@extends('parent.layouts.app')

@section('content')
@php
    $c = $p['challan'];
    $a = $p['amounts'];
    $student = $c->student;
    $payment = $c->payment;
@endphp

<a class="pp-back" href="{{ route('parent.fee-challan') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All challans</a>

<div class="pp-page-head">
    <h1>{{ $p['month'] }} fee</h1>
    <p>{{ $student->name }}, class {{ $student->sessionItem->class ?? '—' }} {{ $student->sessionItem->section ?? '' }}. Challan {{ $c->challan_no ?? $c->id }}.</p>
</div>

<div class="pp-grid-2">
    <div class="pp-stack">
        @include('parent.partials.slip', ['p' => $p, 'student' => $student, 'oneBill' => $oneBill, 'showDetails' => false])

        @if ($c->note)
            <div class="pp-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><p>{{ $c->note }}</p></div>
        @endif
    </div>

    <div class="pp-stack">
        <section class="pp-card"><div class="pp-card-body">
            <h2 class="pp-card-title"><i class="fa-solid fa-list" aria-hidden="true"></i> Fee breakdown</h2>
            <table class="pp-fees">
                <tbody>
                    @forelse ($breakdown['items'] as $item)
                        <tr><td>{{ $item['label'] }}</td><td>Rs {{ number_format($item['amount']) }}</td></tr>
                    @empty
                        <tr><td>{{ $breakdown['is_discount'] ? 'Monthly fee (discounted)' : 'Fee' }}</td><td>Rs {{ number_format($a['within']) }}</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr><td>Total by {{ $p['due_date']?->format('d M Y') ?? 'due date' }}</td><td>Rs {{ number_format($a['within']) }}</td></tr>
                    @if ($a['late_fee'] > 0)
                        <tr class="is-late"><td>After the due date (includes Rs {{ number_format($a['late_fee']) }} late fee)</td><td>Rs {{ number_format($a['after']) }}</td></tr>
                    @endif
                </tfoot>
            </table>
            <p class="pp-muted pp-small pp-mt pp-flush-b">Issued {{ $c->issue_date ? \Carbon\Carbon::parse($c->issue_date)->format('d M Y') : '—' }}.</p>
        </div></section>

        @if ($payment && in_array($c->status, ['review', 'approved'], true))
            <section class="pp-card"><div class="pp-card-body">
                <h2 class="pp-card-title"><i class="fa-solid fa-receipt" aria-hidden="true"></i> Payment</h2>
                <dl class="pp-dl">
                    <div><dt>Paid through</dt><dd>{{ $p['via_1link'] ? '1LINK / 1Bill' : ($payment->bank_name ?: '—') }}</dd></div>
                    <div><dt>Payment date</dt><dd>{{ $payment->paid_date?->format('d M Y') ?? '—' }}</dd></div>
                    @if ($payment->transaction_id)
                        <div><dt>Transaction ID</dt><dd class="num">{{ $payment->transaction_id }}</dd></div>
                    @endif
                    @if ($payment->amount)
                        <div><dt>Amount received</dt><dd class="num">Rs {{ number_format((float) $payment->amount) }}</dd></div>
                    @endif
                    <div><dt>Status</dt><dd>{{ $c->status === 'approved' ? 'Confirmed by the school' : 'Waiting for the office to confirm' }}</dd></div>
                </dl>
                @if ($proof)
                    <div class="pp-proof pp-mt">
                        <a href="{{ $proof }}" target="_blank" rel="noopener"><img src="{{ $proof }}" alt="Payment proof you uploaded" loading="lazy"></a>
                    </div>
                @endif
            </div></section>
        @endif

        @if ($c->status === 'expired')
            <div class="pp-note"><i class="fa-solid fa-ban" aria-hidden="true"></i><p>This challan has expired and can no longer be paid. Use the newer challan for this child, or contact the office if you have already paid it.</p></div>
        @endif
    </div>
</div>

<section class="pp-section">
    @include('parent.partials.help', ['title' => 'Something wrong with this challan?'])
</section>
@endsection
