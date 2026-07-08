@extends('backend.layout.master')
@section('title', $pageTitle ?? 'Fee Challans')

@section('content')
<style>
.table thead th {
    font-size: 0.95rem !important;
    text-transform: capitalize !important;
    letter-spacing: 0px !important;
    padding: 10px 15px 6px 10px !important;
    text-align: center !important;                      
}
.table tbody td {
    vertical-align: middle !important;
    text-align: center !important;
}
</style>

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Fee Challans</h3>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <table class="table table-hover text-center data-table align-middle table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Voucher</th>
                    <th>Name</th>
                    <th>Session</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
               
            </tbody>
        </table>

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
        scrollCollapse: false,
        autoWidth: false,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'voucher_image', name: 'voucher_image', orderable: false, searchable: false },
            { data: 'student_name', name: 'student_name' },
            { data: 'academic_session_id', name: 'academic_session_id' },
            { data: 'class', name: 'class' },
            { data: 'section', name: 'section'},
            { data: 'amount', name: 'amount' },
            { data: 'paid_date', name: 'paid_date' },
            { data: 'payment_status', name: 'payment_status' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-3x fa-fw"></i>',
            emptyTable: "No challans found"
        },
        drawCallback: function () {
            $('.dataTables_scrollBody').css('overflow', 'visible !important');
        },
        initComplete: function () {
            $('.dataTables_scrollBody').css('overflow', 'visible !important');
        }
    });

    // Adjust columns on window resize
    $(window).on('resize', function() {
        table.columns.adjust().draw(false);
    });
});
</script>
@endsection