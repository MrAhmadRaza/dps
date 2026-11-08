{{--
    A challan shown as a fee slip. Expects:
    $p        presented challan (ParentPortal::present)
    $student  the child (optional; shows the child header)
    $oneBill  whether 1Bill payment is available
--}}
@php
    $c = $p['challan'];
    $a = $p['amounts'];
@endphp
<article class="pp-slip is-{{ $p['status']['key'] }}">
    <div class="pp-slip-head">
        @isset($student)
            @include('parent.partials.avatar', ['student' => $student])
            <div class="pp-slip-who">
                <strong>{{ $student->name }}</strong>
                <span>{{ $p['month'] }} fee, class {{ $student->sessionItem->class ?? '—' }}{{ $student->sessionItem?->section ? ' ' . $student->sessionItem->section : '' }}</span>
            </div>
        @else
            <div class="pp-slip-who">
                <strong>{{ $p['month'] }}</strong>
                <span>Challan {{ $c->challan_no ?? $c->id }}</span>
            </div>
        @endisset
        @include('parent.partials.status', ['status' => $p['status']])
    </div>

    <div class="pp-slip-tear" aria-hidden="true"></div>

    <div class="pp-slip-body">
        <div class="pp-slip-row">
            <div>
                <span class="pp-slip-label">{{ $p['can_pay'] ? 'Amount to pay' : 'Amount' }}</span>
                <span class="pp-slip-amount"><small>Rs</small>{{ number_format($p['can_pay'] ? $a['receivable'] : $a['within']) }}</span>
            </div>
            <div class="pp-slip-due">
                <span class="pp-slip-label">Due date</span>
                <strong class="num">{{ $p['due_date']?->format('d M Y') ?? '—' }}</strong>
            </div>
        </div>

        @if ($p['can_pay'] && $a['late_fee'] > 0)
            <p class="pp-slip-late">
                @if ($a['is_late'])
                    Includes a late fee of <strong class="num">Rs {{ number_format($a['late_fee']) }}</strong>.
                @else
                    After the due date: <strong class="num">Rs {{ number_format($a['after']) }}</strong>
                @endif
            </p>
        @endif

        @if ($p['status']['key'] === 'review')
            <p class="pp-slip-late">We've received your payment proof. The office will confirm it shortly.</p>
        @endif

        @if ($p['can_pay'] && ($oneBill ?? false))
            @include('parent.partials.consumer', ['number' => $p['consumer']])
        @endif

        <div class="pp-slip-actions">
            @if ($showDetails ?? true)
                <a class="pp-btn pp-btn-outline" href="{{ route('parent.view.challan', $c->id) }}">Details</a>
            @endif
            <a class="pp-btn pp-btn-outline" href="{{ route('parent.print.challan', $c->id) }}" target="_blank" rel="noopener"><i class="fa-solid fa-print" aria-hidden="true"></i> Print</a>
            @if ($p['can_pay'])
                <a class="pp-btn pp-btn-primary" href="{{ route('parent.challan.upload.form', $c->id) }}"><i class="fa-solid fa-upload" aria-hidden="true"></i> Upload proof</a>
            @endif
        </div>
    </div>
</article>
