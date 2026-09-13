@extends('backend.layout.master')
@section('title', $pageTitle ?? 'Fee Challans')

@section('content')

<style>
    .mt-custom {
        margin-top: 4rem !important;
    }
    .table thead th {
        font-size: 0.95rem !important;
        text-transform: capitalize !important;
        letter-spacing: 0px !important;
        padding: 10px 12px !important;
        text-align: center !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
    }

    .table td {
        vertical-align: middle !important;
        white-space: nowrap !important;
        text-align: center !important;
    }

    .table td:nth-child(7),
    .table th:nth-child(7) {
        white-space: normal !important;
        min-width: 130px;
    }

    .custom-challan-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
    }

    .custom-challan-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .custom-challan-card .form-label {
        font-weight: 600;
        margin-bottom: 6px;
        font-size: 0.9rem;
    }

    .custom-challan-card .form-control,
    .custom-challan-card .form-select {
        height: 42px !important;
        padding-top: 8px !important;
        padding-bottom: 8px !important;
        font-size: 0.9rem;
    }

    .custom-challan-card .btn-generate {
        height: 42px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.9rem;
        padding-left: 12px;
        padding-right: 12px;
    }

    @media (max-width: 991.98px) {
        .custom-challan-card .form-control,
        .custom-challan-card .form-select,
        .custom-challan-card .btn-generate {
            height: 40px !important;
            font-size: 0.875rem;
        }
    }

    @media (max-width: 767.98px) {
        .custom-challan-card .card-body {
            padding: 1rem;
        }

        .custom-challan-card .form-label {
            font-size: 0.85rem;
        }

        .custom-challan-card .btn-generate {
            width: 100%;
            margin-top: 4px;
        }
    }

    @media (max-width: 575.98px) {
        .custom-challan-card .row.g-3 {
            --bs-gutter-y: 0.85rem;
        }
    }
</style>

<div class="page-inner mt-custom">
    {{-- Proper Heading --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Fee Challans</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- CUSTOM CHALLAN CARD --}}
    <div class="card custom-challan-card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Custom Challan</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.challan.custom.generate') }}">
                @csrf
                <div class="row g-3 align-items-end">
                    {{-- Student --}}
                    <div class="col-12 col-md-4 col-lg-4">
                        <label for="student_id" class="form-label">Select Student</label>
                        <select name="student_id" id="student_id" class="form-select" required>
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}"
                                    {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                    {{ $student->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Start Month --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                        <label for="start_month" class="form-label">Start Month/Year</label>
                        <input type="month" name="start_month" id="start_month"
                               value="{{ old('start_month') }}" class="form-control" required>
                        @error('start_month')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- End Month --}}
                    <div class="col-12 col-sm-6 col-md-3 col-lg-3">
                        <label for="end_month" class="form-label">End Month/Year</label>
                        <input type="month" name="end_month" id="end_month"
                               value="{{ old('end_month') }}" class="form-control" required>
                        @error('end_month')
                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Generate Button --}}
                    <div class="col-12 col-md-2 col-lg-2">
                        <button type="submit" class="btn btn-primary w-100 btn-generate">
                            <i class="fas fa-file-invoice"></i>
                            <span>Generate</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- FEE CHALLANS TABLE --}}
    <div class="card mt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover text-center data-table align-middle table-striped" style="width:100%">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Voucher</th>
                            <th>Name</th>
                            <th>Session</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Month</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Paid Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
$(function () {
    let table = $('.data-table').DataTable({
        processing: false,
        serverSide: true,
        ajax: "{{ route('admin.challan.index') }}",
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
            { data: 'voucher_image', name: 'voucher_image', orderable: false, searchable: false, width: '70px' },
            { data: 'student_name', name: 'student_name', width: '120px' },
            { data: 'academic_session_id', name: 'academic_session_id', width: '100px' },
            { data: 'class', name: 'class', width: '70px' },
            { data: 'section', name: 'section', width: '70px' },
            { data: 'month', name: 'month', width: '140px' },
            { data: 'amount', name: 'amount', width: '90px' },
            { data: 'challan_type', name: 'challan_type', width: '90px' },
            { data: 'paid_date', name: 'paid_date', width: '100px' },
            { data: 'status', name: 'status', width: '90px' },
            { data: 'action', name: 'action', orderable: false, searchable: false, width: '130px' }
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-3x fa-fw"></i>',
            emptyTable: "No challans found"
        },
        initComplete: function () {
            this.api().columns.adjust();
            $('.dataTables_scrollBody').css('overflow', 'visible !important');
        },
        drawCallback: function (settings) {
            this.api().columns.adjust();
            $('.dataTables_scrollBody').css('overflow', 'visible !important');

            var api = this.api();
            var pageInfo = api.page.info();

            if (pageInfo.recordsTotal === 0) {
                $(api.table().container()).find('.dataTables_paginate').hide();
                $(api.table().container()).find('.dataTables_info').hide();
                $(api.table().container()).find('.dataTables_length').hide();
            } else {
                $(api.table().container()).find('.dataTables_paginate').show();
                $(api.table().container()).find('.dataTables_info').show();
                $(api.table().container()).find('.dataTables_length').show();
            }
        }
    });

    $('body').on('expanded.pushMenu collapsed.pushMenu', function () {
        setTimeout(function () {
            table.columns.adjust().draw(false);
        }, 350);
    });

    $(document).on('click', '[data-widget="pushmenu"], .sidebar-toggle, .sidebar-toggler', function () {
        setTimeout(function () {
            table.columns.adjust().draw(false);
        }, 300);
    });

    $(window).on('resize', function () {
        table.columns.adjust().draw(false);
    });

    $('#start_month, #end_month').on('change', function () {
        let startMonth = $('#start_month').val();
        let endMonth = $('#end_month').val();
        if (startMonth && endMonth) {
            if (endMonth < startMonth) {
                $('#end_month').addClass('is-invalid');
            } else {
                $('#end_month').removeClass('is-invalid');
            }
        }
    });
});
</script>
@endsection