@extends('admin.layouts.app')
@section('content')
<div class="ad-head">
    <div>
        <h1>Challans</h1>
        <p>Monthly fee challans for every student. Approve proofs parents upload, or generate a challan for a custom period.</p>
    </div>
    <div class="ad-head-actions">
        <a href="#custom" class="btn btn-primary" data-open-custom><i class="fa-solid fa-file-circle-plus"></i>Custom challan</a>
    </div>
</div>

{{-- Custom challan generator --}}
@php $customOpen = $errors->hasAny(['student_id', 'start_month', 'end_month']) || old('student_id'); @endphp
<section class="panel mb-3" id="custom">
    <button class="tool-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#customBody" aria-expanded="{{ $customOpen ? 'true' : 'false' }}" aria-controls="customBody">
        <i class="fa-solid fa-file-circle-plus text-muted"></i>
        <span>Custom challan <span class="sub d-none d-md-inline">· one challan covering one or more months for a single student</span></span>
        <i class="fa-solid fa-chevron-down chev"></i>
    </button>
    <div class="collapse {{ $customOpen ? 'show' : '' }}" id="customBody">
        <form method="POST" action="{{ route('admin.challan.custom.generate') }}" class="panel-body border-top">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="student_id" class="form-label">Student</label>
                    <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required placeholder="Type a name or register no…">
                        <option value="">Type a name or register no…</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                {{ $student->name }}@if ($student->sessionItem) — Class {{ $student->sessionItem->class }}-{{ $student->sessionItem->section }}@endif @if ($student->register_no)({{ $student->register_no }})@endif
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-lg-2">
                    <label for="start_month" class="form-label">From month</label>
                    <input type="month" name="start_month" id="start_month" value="{{ old('start_month') }}" class="form-control @error('start_month') is-invalid @enderror" required>
                    @error('start_month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-6 col-lg-2">
                    <label for="end_month" class="form-label">To month</label>
                    <input type="month" name="end_month" id="end_month" value="{{ old('end_month') }}" class="form-control @error('end_month') is-invalid @enderror" required>
                    <div class="invalid-feedback" id="endMonthHint">Must be the same as or after the start month.</div>
                    @error('end_month')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-lg-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-wand-magic-sparkles"></i>Generate challan</button>
                </div>
            </div>
            <p class="form-text mb-0 mt-2">Amounts come from the student's class fee voucher. The new challan appears at the top of the list below.</p>
        </form>
    </div>
</section>

{{-- Status tabs --}}
<nav aria-label="Filter challans by status">
    <ul class="seg" id="statusTabs">
        @foreach (\App\Services\AdminPortal\ChallanStatus::FILTERS as $key => $label)
            <li>
                <a href="{{ route('admin.challan.index', $key === 'all' ? [] : ['status' => $key]) }}"
                   class="is-{{ $key }} {{ $filter === $key ? 'active' : '' }}" data-status="{{ $key }}"
                   @if ($filter === $key) aria-current="true" @endif>
                    {{ $label }} <span class="count">{{ number_format($counts[$key] ?? 0) }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

<section class="panel">
    <div class="panel-body">
        <table class="table table-hover align-middle data-table w-100" id="challanTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Proof</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Session</th>
                    <th>Month</th>
                    <th class="text-end">Amount (Rs)</th>
                    <th>Type</th>
                    <th>Paid on</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</section>
@endsection

@section('scripts')
<script>
$(function () {
    var filter = @json($filter);
    var empty = {
        all: 'No challans yet.',
        review: 'No proofs waiting for review.',
        unpaid: 'No unpaid challans.',
        overdue: 'Nothing overdue.',
        paid: 'No paid challans yet.',
        expired: 'No expired challans.'
    };

    var table = $('#challanTable').DataTable({
        serverSide: true,
        ajax: { url: @json(route('admin.challan.index')), data: function (d) { d.status = filter; } },
        scrollX: true,
        autoWidth: false,
        order: [],
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '32px' },
            { data: 'voucher_image', name: 'voucher_image', orderable: false, searchable: false },
            { data: 'student_name', name: 'student_name', orderable: false },
            { data: 'class', name: 'class', orderable: false },
            { data: 'academic_session_id', name: 'academic_session_id', orderable: false },
            { data: 'month', name: 'month' },
            { data: 'amount', name: 'total_amount', className: 'text-end' },
            { data: 'challan_type', name: 'challan_type', orderable: false },
            { data: 'paid_date', name: 'paid_date', orderable: false, searchable: false },
            { data: 'status', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: { emptyTable: empty[filter] || empty.all }
    });

    // Switch tabs without a page reload (the links still work without JS)
    $('#statusTabs').on('click', 'a[data-status]', function (e) {
        e.preventDefault();
        filter = $(this).data('status');
        $('#statusTabs a').removeClass('active').removeAttr('aria-current');
        $(this).addClass('active').attr('aria-current', 'true');
        table.settings()[0].oLanguage.sEmptyTable = empty[filter] || empty.all;
        history.replaceState(null, '', filter === 'all' ? location.pathname : location.pathname + '?status=' + filter);
        table.ajax.reload();
    });

    // Searchable student picker
    if (window.TomSelect) {
        new TomSelect('#student_id', { maxOptions: 300, allowEmptyOption: false, create: false });
    }

    // Open the generator when coming from "Custom challan" links
    function openCustom() {
        var el = document.getElementById('customBody');
        bootstrap.Collapse.getOrCreateInstance(el, { toggle: false }).show();
        document.getElementById('custom').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    if (location.hash === '#custom') openCustom();
    $('[data-open-custom]').on('click', function (e) { e.preventDefault(); openCustom(); });

    $('#start_month, #end_month').on('change', function () {
        var s = $('#start_month').val(), e = $('#end_month').val();
        $('#end_month').toggleClass('is-invalid', !!(s && e && e < s));
    });
});
</script>
@endsection
