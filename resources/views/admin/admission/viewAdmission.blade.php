@extends('admin.layouts.app')
@section('content')
@php
    $p  = $student->parent;
    $g  = $student->guardian;
    $sb = $student->sibling;
    $item = $student->sessionItem;
    $v = fn ($x) => filled($x) ? $x : '—';
    $rs = fn ($x) => number_format((float) $x, 0);
    $hasPhoto = $student->photo_path && is_file(public_path($student->photo_path));
    $open = $challans->filter(fn ($c) => in_array($c->status, ['pending', 'review']));
@endphp

<div class="ad-head">
    <div></div>
    <div class="ad-head-actions">
        <a href="{{ route('admin.admission.index') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i>All admissions</a>
        <a href="{{ route('admin.print.admission', $student->id) }}" target="_blank" class="btn btn-light"><i class="fa-solid fa-print"></i>Application</a>
        <a href="{{ route('admin.voucher.admission', $student->id) }}" target="_blank" class="btn btn-light"><i class="fa-solid fa-file-invoice"></i>Admission voucher</a>
        <a href="{{ route('admin.edit.admission', $student->id) }}" class="btn btn-primary"><i class="fa-regular fa-pen-to-square"></i>Edit</a>
    </div>
</div>

<section class="panel mb-3">
    <div class="profile-head">
        @if ($hasPhoto)
            <img class="profile-photo" src="{{ asset($student->photo_path) }}" alt="Photo of {{ $student->name }}" data-zoom="{{ asset($student->photo_path) }}" style="cursor:zoom-in">
        @else
            <span class="profile-photo d-grid" style="place-items:center;font-size:1.8rem;font-weight:700;color:var(--brand)">{{ mb_strtoupper(mb_substr($student->name, 0, 1)) }}</span>
        @endif
        <div class="flex-grow-1 min-w-0">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h1>{{ $student->name }}</h1>
                <span class="chip {{ $student->status === 'active' ? 'chip-success' : 'chip-muted' }}">{{ ucfirst($student->status) }}</span>
                @if ((int) $student->discount === 1)<span class="chip chip-brand no-dot">Concession</span>@endif
            </div>
            <div class="profile-meta">
                <span><i class="fa-solid fa-hashtag"></i>{{ $v($student->register_no) }}</span>
                <span><i class="fa-solid fa-school"></i>{{ $item ? 'Class '.$item->class.'-'.$item->section : 'No class' }}</span>
                <span><i class="fa-solid fa-calendar-days"></i>{{ $student->academicSession->session_name ?? 'No session' }}</span>
                <span><i class="fa-solid fa-right-to-bracket"></i>Admitted {{ $student->admission_date?->format('j M Y') ?? '—' }}</span>
                @if ($p?->contact_no)<span><i class="fa-solid fa-phone"></i><a href="tel:{{ $p->contact_no }}">{{ $p->contact_no }}</a></span>@endif
            </div>
        </div>
    </div>
</section>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="stack">
            <section class="panel">
                <div class="panel-head"><h2>Student</h2></div>
                <div class="panel-body dl-cols">
                    <dl class="dl">
                        <dt>Date of birth</dt><dd>{{ $student->date_of_birth?->format('j M Y') ?? '—' }}</dd>
                        <dt>Gender</dt><dd>{{ $v($student->gender) }}</dd>
                        <dt>B-Form no.</dt><dd>{{ $v($student->b_form_no) }}</dd>
                        <dt>Religion</dt><dd>{{ $v($student->religion) }}</dd>
                    </dl>
                    <dl class="dl">
                        <dt>Caste</dt><dd>{{ $v($student->caste) }}</dd>
                        <dt>Domicile</dt><dd>{{ $v($student->domicile) }}</dd>
                        <dt>Previous school</dt><dd>{{ $v($student->previous_school) }}</dd>
                        <dt>Fee paid up to</dt><dd>{{ $student->last_fee_paid_upto?->format('j M Y') ?? '—' }}</dd>
                    </dl>
                </div>
            </section>

            <section class="panel">
                <div class="panel-head"><h2>Family</h2></div>
                <div class="panel-body dl-cols">
                    <dl class="dl">
                        <dt>Father</dt><dd>{{ $v($p?->father_name) }}</dd>
                        <dt>Father CNIC</dt><dd class="mono">{{ $v($p?->father_nic) }}</dd>
                        <dt>Occupation</dt><dd>{{ $v($p?->occupation) }}</dd>
                        <dt>Monthly income</dt><dd>{{ $p?->income ? 'Rs '.$rs($p->income) : '—' }}</dd>
                        <dt>Mother</dt><dd>{{ $v($p?->mother_name) }}</dd>
                    </dl>
                    <dl class="dl">
                        <dt>Contact</dt><dd>{{ $v($p?->contact_no) }}</dd>
                        <dt>Address</dt><dd>{{ $v($p?->address) }}</dd>
                        <dt>Guardian</dt><dd>{{ $g ? $g->guardian_name.' ('.$g->relation.')' : '—' }}</dd>
                        <dt>Guardian CNIC</dt><dd class="mono">{{ $v($g?->guardian_nic) }}</dd>
                        <dt>Guardian contact</dt><dd>{{ $v($g?->contact_no) }}</dd>
                    </dl>
                </div>
                @if ($sb && ($sb->sibling_name || $sb->sibling_class))
                    <div class="panel-foot">
                        <strong class="text-ink">Sibling:</strong> {{ $v($sb->sibling_name) }}
                        @if ($sb->sibling_class) · Class {{ $sb->sibling_class }}{{ $sb->sibling_section ? '-'.$sb->sibling_section : '' }} @endif
                        · {{ (int) $sb->sibling_in_dps === 1 ? 'Studies at DPS' : 'Not at DPS' }}
                    </div>
                @endif
            </section>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="stack">
            <section class="panel">
                <div class="panel-head">
                    <h2>Fee structure</h2>
                    <span class="sub">{{ $item ? 'Class '.$item->class.'-'.$item->section : '' }} · Rs</span>
                </div>
                <div class="panel-body">
                    @if ($voucher && $voucher->items->count())
                        @foreach ($voucher->items as $fee)
                            <div class="money-row"><span>{{ $fee->fee_name ?? 'Fee' }}</span><span class="v">{{ $rs($fee->amount) }}</span></div>
                        @endforeach
                        <div class="money-row total"><span>Total</span><span class="v">{{ $rs($voucher->items->sum('amount')) }}</span></div>
                        @if ($voucher->due_date)<div class="form-text mt-2">Voucher due date {{ \Carbon\Carbon::parse($voucher->due_date)->format('j M Y') }}{{ $voucher->notes ? ' · '.$voucher->notes : '' }}</div>@endif
                    @else
                        <p class="text-muted mb-0">No fee voucher is set up for this class yet. <a href="{{ route('admin.voucher.add') }}">Create one</a>.</p>
                    @endif
                    @if ((int) $student->discount === 1)
                        <div class="alert alert-info mt-3 mb-0 py-2">
                            <strong>Concession:</strong> monthly fee Rs {{ $rs($student->discount_amount) }}
                            @if ($student->discount_due_date) until {{ \Carbon\Carbon::parse($student->discount_due_date)->format('j M Y') }} @endif
                            @if ($student->discount_notes)<div class="small mt-1">{{ $student->discount_notes }}</div>@endif
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

<section class="panel mt-3">
    <div class="panel-head">
        <div>
            <h2>Challans</h2>
            <div class="sub">{{ $challans->count() }} in total · {{ $open->count() }} open (Rs {{ $rs($open->sum('total_amount')) }})</div>
        </div>
        <a href="{{ route('admin.challan.index') }}#custom" class="btn btn-sm btn-light"><i class="fa-solid fa-file-circle-plus"></i>Custom challan</a>
    </div>
    @if ($challans->isEmpty())
        <div class="empty"><i class="fa-solid fa-file-invoice"></i>No challans issued for this student yet.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>Month</th><th>Challan no.</th><th>Due</th><th class="text-end">Amount (Rs)</th><th>Paid on</th><th>Status</th><th class="text-center">Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($challans as $c)
                        <tr>
                            <td class="fw-650 text-ink text-nowrap">{{ \App\Helpers\FeeChallanHelper::formatChallanMonth($c->month) }}</td>
                            <td class="mono">{{ $c->challan_no ?: $c->id }}</td>
                            <td class="text-nowrap">{{ $c->due_date ? \Carbon\Carbon::parse($c->due_date)->format('j M Y') : '—' }}</td>
                            <td class="text-end num">{{ $rs($c->total_amount) }}</td>
                            <td class="text-nowrap">{{ $c->payment?->paid_date?->format('j M Y') ?? '—' }}</td>
                            <td class="text-nowrap">@include('admin.partials.status-chip', ['status' => $statuses->for($c)])</td>
                            <td class="text-center">
                                <div class="row-actions">
                                    <a href="{{ route('admin.challan.view', $c->id) }}" class="btn-icon" title="Open challan" aria-label="Open challan"><i class="fa-regular fa-eye"></i></a>
                                    <a href="{{ route('admin.challan.print', $c->id) }}" target="_blank" class="btn-icon" title="Print challan" aria-label="Print challan"><i class="fa-solid fa-print"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
