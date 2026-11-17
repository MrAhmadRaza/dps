@extends('admin.layouts.app')
@section('content')
@php
    $tm   = $stats['this_month'];
    $rs   = fn ($v) => number_format((float) $v, 0);
    $hour = (int) $stats['today']->format('G');
    $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $first = explode(' ', trim(auth()->user()->name ?? ''))[0] ?: 'there';
    $maxBar = max(1, collect($stats['trend'])->max('billed'));
@endphp

<div class="ad-head">
    <div>
        <h1>{{ $greet }}, {{ ucfirst($first) }}</h1>
        <p>{{ $stats['today']->format('l, j F Y') }} · fees for {{ $stats['month_label'] }}</p>
    </div>
    <div class="ad-head-actions">
        <a href="{{ route('admin.challan.index') }}#custom" class="btn btn-light"><i class="fa-solid fa-file-circle-plus"></i>Custom challan</a>
        <a href="{{ route('admin.add.admission') }}" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i>New admission</a>
    </div>
</div>

<div class="kpis">
    <a class="kpi is-alert" href="{{ route('admin.challan.index', ['status' => 'review']) }}">
        <div class="kpi-label"><i class="fa-solid fa-magnifying-glass"></i>Proofs to review</div>
        <div class="kpi-value">{{ $stats['review_count'] }}</div>
        <div class="kpi-note">{{ $stats['review_count'] ? 'Uploaded by parents, waiting for you' : 'All caught up' }}</div>
    </a>
    <div class="kpi is-ok">
        <div class="kpi-label"><i class="fa-solid fa-sack-dollar"></i>Collected · {{ $stats['today']->format('M') }}</div>
        <div class="kpi-value"><small>Rs</small>{{ $rs($tm['collected']) }}</div>
        <div class="meter" aria-hidden="true"><span style="width: {{ $tm['rate'] ?? 0 }}%"></span></div>
        <div class="kpi-note">
            @if ($tm['rate'] !== null)
                {{ $tm['rate'] }}% of Rs {{ $rs($tm['billed']) }} billed · {{ $tm['paid_count'] }}/{{ $tm['count'] }} challans
            @else
                No challans issued for this month yet
            @endif
        </div>
    </div>
    <a class="kpi is-bad" href="{{ route('admin.challan.index', ['status' => 'overdue']) }}">
        <div class="kpi-label"><i class="fa-solid fa-circle-exclamation"></i>Overdue challans</div>
        <div class="kpi-value">{{ $stats['overdue_count'] }}</div>
        <div class="kpi-note">Unpaid and past the due date · Rs {{ $rs($tm['outstanding']) }} still open this month</div>
    </a>
    <a class="kpi" href="{{ route('admin.admission.index') }}">
        <div class="kpi-label"><i class="fa-solid fa-user-graduate"></i>Active students</div>
        <div class="kpi-value">{{ number_format($stats['students_active']) }}</div>
        <div class="kpi-note">{{ $stats['students_new'] }} admitted this month · {{ $stats['sessions_active'] }} active session{{ $stats['sessions_active'] == 1 ? '' : 's' }}</div>
    </a>
</div>

<div class="dash-grid">
    <div class="stack">
        <section class="panel" aria-labelledby="queueTitle">
            <div class="panel-head">
                <div>
                    <h2 id="queueTitle">Review queue</h2>
                    <div class="sub">Payment proofs uploaded by parents, oldest first</div>
                </div>
                @if ($stats['review_count'])
                    <a href="{{ route('admin.challan.index', ['status' => 'review']) }}" class="btn btn-sm btn-light">See all {{ $stats['review_count'] }}</a>
                @endif
            </div>
            @if ($stats['review_queue']->isEmpty())
                <div class="queue-empty"><i class="fa-solid fa-circle-check"></i>No proofs waiting. New uploads will appear here.</div>
            @else
                <ul class="queue">
                    @foreach ($stats['review_queue'] as $c)
                        @php $img = $c->payment?->voucher_image; @endphp
                        <li class="queue-item">
                            <a class="queue-proof" href="{{ route('admin.challan.view', $c->id) }}" aria-label="Open proof">
                                @if ($img && is_file(public_path($img)))
                                    <img src="{{ asset($img) }}" alt="" loading="lazy">
                                @else
                                    <i class="fa-regular fa-image"></i>
                                @endif
                            </a>
                            <div class="min-w-0">
                                <div class="queue-title">
                                    <a href="{{ route('admin.challan.view', $c->id) }}" class="text-ink">{{ $c->student->name ?? 'Unknown student' }}</a>
                                    <span class="queue-amount ms-1">· Rs {{ $rs($c->total_amount) }}</span>
                                </div>
                                <div class="queue-meta">
                                    <span>{{ \App\Helpers\FeeChallanHelper::formatChallanMonth($c->month) }}</span>
                                    @if ($c->student?->sessionItem)<span>Class {{ $c->student->sessionItem->class }}-{{ $c->student->sessionItem->section }}</span>@endif
                                    <span>{{ $c->payment->bank_name ?? 'Bank not given' }}</span>
                                    @if ($c->payment?->paid_date)<span>Paid {{ $c->payment->paid_date->format('j M') }}</span>@endif
                                </div>
                            </div>
                            <div class="queue-actions">
                                <a href="{{ route('admin.challan.view', $c->id) }}" class="btn btn-sm btn-light">View</a>
                                <form method="POST" action="{{ route('admin.challan.approved', $c->id) }}"
                                      data-confirm="Mark {{ $c->student->name ?? 'this' }}'s {{ \App\Helpers\FeeChallanHelper::formatChallanMonth($c->month) }} challan as paid?"
                                      data-confirm-title="Approve payment" data-confirm-ok="Approve">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i>Approve</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="panel" aria-labelledby="trendTitle">
            <div class="panel-head">
                <div>
                    <h2 id="trendTitle">Billed vs collected</h2>
                    <div class="sub">By billing month, last 6 months</div>
                </div>
                <div class="legend"><span class="l-billed">Billed</span><span class="l-collected">Collected</span></div>
            </div>
            <div class="panel-body">
                <div class="bars" style="--n: {{ count($stats['trend']) }}" role="img"
                     aria-label="@foreach ($stats['trend'] as $t){{ $t['label'] }}: billed Rs {{ $rs($t['billed']) }}, collected Rs {{ $rs($t['collected']) }}. @endforeach">
                    @foreach ($stats['trend'] as $t)
                        <div class="bar {{ $loop->last ? 'is-current' : '' }}"
                             title="{{ $t['label'] }}: billed Rs {{ $rs($t['billed']) }} · collected Rs {{ $rs($t['collected']) }}">
                            <div class="bar-track">
                                <div class="bar-billed" style="height: {{ round($t['billed'] / $maxBar * 100, 1) }}%"></div>
                                <div class="bar-collected" style="height: {{ round($t['collected'] / $maxBar * 100, 1) }}%"></div>
                            </div>
                            <div class="bar-label">{{ $t['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="panel-foot d-flex flex-wrap gap-3 justify-content-between">
                <span>This month: <strong class="text-ink">Rs {{ $rs($tm['collected']) }}</strong> collected of Rs {{ $rs($tm['billed']) }}</span>
                <a href="{{ route('admin.reports.print') }}">Cashbook report <i class="fa-solid fa-arrow-right small"></i></a>
            </div>
        </section>
    </div>

    <div class="stack">
        <section class="panel" aria-labelledby="quickTitle">
            <div class="panel-head"><h2 id="quickTitle">Quick actions</h2></div>
            <div class="quick">
                <a href="{{ route('admin.add.admission') }}"><i class="fa-solid fa-user-plus"></i>New admission</a>
                <a href="{{ route('admin.challan.index') }}#custom"><i class="fa-solid fa-file-circle-plus"></i>Custom challan</a>
                <a href="{{ route('admin.parent.show') }}"><i class="fa-solid fa-key"></i>Reset parent password</a>
                <a href="{{ route('admin.reports.print') }}"><i class="fa-solid fa-book"></i>Cashbook</a>
            </div>
        </section>

        <section class="panel" aria-labelledby="recentTitle">
            <div class="panel-head">
                <h2 id="recentTitle">Recent admissions</h2>
                <a href="{{ route('admin.admission.index') }}" class="small">All admissions</a>
            </div>
            @if ($stats['recent_students']->isEmpty())
                <div class="empty"><i class="fa-solid fa-user-graduate"></i>No admissions yet.</div>
            @else
                <ul class="list">
                    @foreach ($stats['recent_students'] as $s)
                        <li>
                            @if ($s->photo_path && is_file(public_path($s->photo_path)))
                                <img class="thumb" src="{{ asset($s->photo_path) }}" alt="" loading="lazy">
                            @else
                                <span class="thumb">{{ mb_strtoupper(mb_substr($s->name, 0, 1)) }}</span>
                            @endif
                            <div class="grow">
                                <a href="{{ route('admin.view.admission', $s->id) }}" class="title d-block">{{ $s->name }}</a>
                                <div class="meta">
                                    {{ $s->sessionItem ? 'Class '.$s->sessionItem->class.'-'.$s->sessionItem->section : 'No class' }}
                                    · {{ $s->academicSession->session_name ?? 'No session' }}
                                </div>
                            </div>
                            <span class="chip {{ $s->status === 'active' ? 'chip-success' : 'chip-muted' }}">{{ ucfirst($s->status) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</div>
@endsection
