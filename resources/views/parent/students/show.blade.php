@extends('parent.layouts.app')

@section('content')
@php
    $parent   = $student->parent;
    $guardian = $student->guardian;
    $sibling  = $student->sibling;
    $class    = trim(($student->sessionItem->class ?? '') . ' ' . ($student->sessionItem->section ?? ''));
    $date     = fn ($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : null;
@endphp

<a class="pp-back" href="{{ route('parent.student.index') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All children</a>

<section class="pp-profile">
    @include('parent.partials.avatar', ['student' => $student])
    <div>
        <h1>{{ $student->name }}</h1>
        <p>{{ $class ? 'Class ' . $class : 'Class not assigned' }}{{ $student->academicSession?->session_name ? ', session ' . $student->academicSession->session_name : '' }}</p>
        <div class="pp-tags">
            @if ($student->register_no)<span class="pp-tag">Reg. no. {{ $student->register_no }}</span>@endif
            @if ($student->admission_date)<span class="pp-tag">Admitted {{ $date($student->admission_date) }}</span>@endif
            <span class="pp-tag">{{ ucfirst($student->status) }}</span>
        </div>
    </div>
</section>

<div class="pp-grid-2 pp-section">
    <section class="pp-card"><div class="pp-card-body">
        <h2 class="pp-card-title"><i class="fa-solid fa-user-graduate" aria-hidden="true"></i> Student</h2>
        <dl class="pp-dl">
            @include('parent.partials.dl-row', ['label' => 'Full name', 'value' => $student->name])
            @include('parent.partials.dl-row', ['label' => 'Date of birth', 'value' => $date($student->date_of_birth)])
            @include('parent.partials.dl-row', ['label' => 'Gender', 'value' => $student->gender])
            @include('parent.partials.dl-row', ['label' => 'B-Form no.', 'value' => $student->b_form_no])
            @include('parent.partials.dl-row', ['label' => 'Religion', 'value' => $student->religion])
            @include('parent.partials.dl-row', ['label' => 'Caste', 'value' => $student->caste])
            @include('parent.partials.dl-row', ['label' => 'Domicile', 'value' => $student->domicile])
        </dl>

        <h3 class="pp-subhead">Schooling</h3>
        <dl class="pp-dl">
            @include('parent.partials.dl-row', ['label' => 'Session', 'value' => $student->academicSession->session_name ?? null])
            @include('parent.partials.dl-row', ['label' => 'Class', 'value' => $student->sessionItem->class ?? null])
            @include('parent.partials.dl-row', ['label' => 'Section', 'value' => $student->sessionItem->section ?? null])
            @include('parent.partials.dl-row', ['label' => 'Admission date', 'value' => $date($student->admission_date)])
            @include('parent.partials.dl-row', ['label' => 'Previous school', 'value' => $student->previous_school])
            @include('parent.partials.dl-row', ['label' => 'Sports', 'value' => $student->games_sports])
            @include('parent.partials.dl-row', ['label' => 'Activities', 'value' => $student->extra_curricular])
        </dl>
    </div></section>

    <div class="pp-stack">
        <section class="pp-card"><div class="pp-card-body">
            <h2 class="pp-card-title"><i class="fa-solid fa-people-roof" aria-hidden="true"></i> Parents</h2>
            @if ($parent)
                <h3 class="pp-subhead">Father</h3>
                <dl class="pp-dl">
                    @include('parent.partials.dl-row', ['label' => 'Name', 'value' => $parent->father_name])
                    @include('parent.partials.dl-row', ['label' => 'CNIC', 'value' => $parent->father_nic])
                    @include('parent.partials.dl-row', ['label' => 'Occupation', 'value' => $parent->occupation])
                    @include('parent.partials.dl-row', ['label' => 'Contact', 'value' => $parent->contact_no])
                    @include('parent.partials.dl-row', ['label' => 'Address', 'value' => $parent->address])
                </dl>
                <h3 class="pp-subhead">Mother</h3>
                <dl class="pp-dl">
                    @include('parent.partials.dl-row', ['label' => 'Name', 'value' => $parent->mother_name])
                </dl>
            @else
                <p class="pp-muted pp-flush">No parent details recorded.</p>
            @endif
        </div></section>

        <section class="pp-card"><div class="pp-card-body">
            <h2 class="pp-card-title"><i class="fa-solid fa-user-shield" aria-hidden="true"></i> Guardian</h2>
            @if ($guardian)
                <dl class="pp-dl">
                    @include('parent.partials.dl-row', ['label' => 'Name', 'value' => $guardian->guardian_name])
                    @include('parent.partials.dl-row', ['label' => 'Relation', 'value' => $guardian->relation])
                    @include('parent.partials.dl-row', ['label' => 'CNIC', 'value' => $guardian->guardian_nic])
                    @include('parent.partials.dl-row', ['label' => 'Contact', 'value' => $guardian->contact_no])
                </dl>
            @else
                <p class="pp-muted pp-flush">No guardian recorded. The parents are the contacts for this student.</p>
            @endif
        </div></section>

        <section class="pp-card"><div class="pp-card-body">
            <h2 class="pp-card-title"><i class="fa-solid fa-children" aria-hidden="true"></i> Brothers and sisters at DPS</h2>
            @if ($siblings->isNotEmpty())
                <ul class="pp-list">
                    @foreach ($siblings as $s)
                        <li><a class="pp-row pp-row--flush" href="{{ route('parent.view.student', $s->id) }}">
                            <span class="pp-row-main"><strong>{{ $s->name }}</strong><span>Class {{ $s->sessionItem->class ?? '—' }} {{ $s->sessionItem->section ?? '' }}</span></span>
                            <i class="fa-solid fa-chevron-right pp-muted" aria-hidden="true"></i>
                        </a></li>
                    @endforeach
                </ul>
            @elseif ($sibling && filled($sibling->sibling_name))
                <dl class="pp-dl">
                    @include('parent.partials.dl-row', ['label' => 'Name', 'value' => $sibling->sibling_name])
                    @include('parent.partials.dl-row', ['label' => 'Class', 'value' => trim(($sibling->sibling_class ?? '') . ' ' . ($sibling->sibling_section ?? ''))])
                </dl>
            @else
                <p class="pp-muted pp-flush">No brothers or sisters recorded at DPS.</p>
            @endif
        </div></section>
    </div>
</div>

<div class="pp-grid-2 pp-section">
    <section class="pp-card"><div class="pp-card-body">
        <h2 class="pp-card-title"><i class="fa-solid fa-coins" aria-hidden="true"></i> Fee structure</h2>
        @if ($student->discount)
            <table class="pp-fees">
                <tbody><tr><td>Monthly fee (concession)</td><td>Rs {{ number_format((float) $student->discount_amount) }}</td></tr></tbody>
            </table>
            @if ($student->discount_notes)
                <p class="pp-muted pp-mt pp-flush-b">{{ $student->discount_notes }}</p>
            @endif
        @elseif ($voucher && $voucher->items->isNotEmpty())
            <table class="pp-fees">
                <tbody>
                    @foreach ($voucher->items as $item)
                        <tr><td>{{ $item->fee_name }}</td><td>Rs {{ number_format((float) $item->amount) }}</td></tr>
                    @endforeach
                </tbody>
                <tfoot><tr><td>Total</td><td>Rs {{ number_format((float) $voucher->items->sum('amount')) }}</td></tr></tfoot>
            </table>
            <p class="pp-muted pp-small pp-mt pp-flush-b">The fee set for class {{ $class }}. One-time charges such as admission fee appear only on the first challan.</p>
        @else
            <p class="pp-muted pp-flush">The fee structure for this class hasn't been published yet.</p>
        @endif
    </div></section>

    <section class="pp-card"><div class="pp-card-body">
        <h2 class="pp-card-title"><i class="fa-solid fa-file-invoice" aria-hidden="true"></i> Recent challans</h2>
        @if ($recent->isEmpty())
            <p class="pp-muted pp-flush">No challans issued yet.</p>
        @else
            <ul class="pp-list">
                @foreach ($recent as $p)
                    <li><a class="pp-row pp-row--flush" href="{{ route('parent.view.challan', $p['challan']->id) }}">
                        <span class="pp-row-main"><strong>{{ $p['month'] }}</strong><span>Rs {{ number_format($p['can_pay'] ? $p['amounts']['receivable'] : $p['amounts']['within']) }}</span></span>
                        @include('parent.partials.status', ['status' => $p['status']])
                    </a></li>
                @endforeach
            </ul>
            <a class="pp-btn pp-btn-outline pp-mt" href="{{ route('parent.fee-challan', ['student' => $student->id]) }}">All challans for {{ $student->name }}</a>
        @endif
    </div></section>
</div>

<section class="pp-section">
    @include('parent.partials.help', ['title' => 'Something in these details wrong?'])
</section>
@endsection
