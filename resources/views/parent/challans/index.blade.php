@extends('parent.layouts.app')

@section('content')
<div class="pp-page-head">
    <h1>Fees</h1>
    <p>Every fee challan issued for your children, newest first.</p>
</div>

<div class="pp-filters">
    @if ($students->count() > 1)
        <div class="pp-chips" role="group" aria-label="Filter by child">
            <a class="pp-chip" href="{{ route('parent.fee-challan', array_filter(['status' => $status])) }}" @if(!$studentId) aria-current="true" @endif>All children</a>
            @foreach ($students as $s)
                <a class="pp-chip" href="{{ route('parent.fee-challan', array_filter(['student' => $s->id, 'status' => $status])) }}" @if($studentId === $s->id) aria-current="true" @endif>{{ $s->name }}</a>
            @endforeach
        </div>
    @endif
    <div class="pp-chips" role="group" aria-label="Filter by status">
        <a class="pp-chip" href="{{ route('parent.fee-challan', array_filter(['student' => $studentId])) }}" @if(!$status) aria-current="true" @endif>Any status</a>
        @foreach ($filters as $key => $label)
            <a class="pp-chip" href="{{ route('parent.fee-challan', array_filter(['student' => $studentId, 'status' => $key])) }}" @if($status === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </div>
</div>

<div class="pp-card">
    @if ($challans->isEmpty())
        <div class="pp-empty">
            <i class="fa-solid fa-file-invoice" aria-hidden="true"></i>
            <strong>No challans found</strong>
            @if ($studentId || $status)
                Try another filter, or <a href="{{ route('parent.fee-challan') }}">show all challans</a>.
            @else
                Challans appear here once the school issues them.
            @endif
        </div>
    @else
        <ul class="pp-list">
            @foreach ($challans as $p)
                @php $c = $p['challan']; @endphp
                <li>
                    <a class="pp-row" href="{{ route('parent.view.challan', $c->id) }}">
                        <span class="pp-row-main">
                            <strong>{{ $p['month'] }}</strong>
                            <span>{{ $c->student->name }}, challan {{ $c->challan_no ?? $c->id }}</span>
                        </span>
                        <span class="pp-row-meta">
                            @if ($c->status === 'approved' && $c->payment?->paid_date)
                                Paid {{ $c->payment->paid_date->format('d M Y') }}
                            @elseif ($p['due_date'])
                                Due {{ $p['due_date']->format('d M Y') }}
                            @endif
                        </span>
                        <span class="pp-row-side">
                            <span class="pp-row-amount">Rs {{ number_format($p['can_pay'] ? $p['amounts']['receivable'] : $p['amounts']['within']) }}</span>
                            @include('parent.partials.status', ['status' => $p['status']])
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</div>

{{ $challans->links('pagination::bootstrap-5') }}
@endsection
