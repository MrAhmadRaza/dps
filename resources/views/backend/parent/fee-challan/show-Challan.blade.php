@extends('backend.parent.layout.master')
@section('title', $pageTitle ?? 'Fee Challans')

@section('content')
<style>
    .table thead th {
        font-size: 0.95rem !important;
        text-transform: capitalize !important;
        letter-spacing: 0 !important;
        padding: 10px 12px !important;
        text-align: center !important;
        white-space: nowrap !important;
        vertical-align: middle !important;
    }
    .table td {
        vertical-align: middle !important;
        text-align: center !important;
    }
    .table td:nth-child(4) { /* Month column */
        white-space: normal !important;
        min-width: 130px;
    }
</style>

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Fee Challans</h3>
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

        {{-- Student Dropdown --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label fw-bold">Select Student</label>
                <select id="student_id" class="form-select">
                    <option value="">Select Student</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover text-center data-table align-middle table-striped" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Voucher</th>
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

{{-- Upload Modal --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('parent.challan.upload') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Upload Challan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="fee_challan_id" id="challan_id">

                <div class="mb-3">
                    <label>Bank Name <span class="text-danger">*</span></label>
                    <input type="text" name="bank_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Paid Date <span class="text-danger">*</span></label>
                    <input type="date" name="paid_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Transaction ID (optional)</label>
                    <input type="text" name="transaction_id" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Upload Challan <span class="text-danger">*</span></label>
                    <input type="file" name="voucher_image" class="form-control" accept="image/*" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openUploadModal(id) {
    $('#challan_id').val(id);
    $('#uploadModal').modal('show');
}

$(function () {
    let table = $('.data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('parent.fee-challan') }}",
            data: function (d) {
                d.student_id = $('#student_id').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '40px' },
            { data: 'student_name', name: 'student_name' },
            { data: 'voucher_image', name: 'voucher_image', orderable: false, searchable: false },
            { data: 'month', name: 'month' },
            { data: 'amount', name: 'amount' },
            { data: 'challan_type', name: 'challan_type' },
            { data: 'paid_date', name: 'paid_date' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        language: {
            emptyTable: "Please select a student to view challans",
            zeroRecords: "No challans found for this student"
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
            // Agar records empty hain to pagination + info hide
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

    // Student change pe table reload
    $('#student_id').on('change', function () {
        table.ajax.reload();
    });
});
</script>
@endsection