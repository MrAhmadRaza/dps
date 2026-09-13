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
</style>

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Parents</h3>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <table class="table table-hover text-center data-table align-middle table-striped">
            <thead class="table-light text-center ">
                <tr>
                    <th>No</th>
                    <th>Father Name</th>
                    <th>Father NIC</th>
                    <th>Contact</th>
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
        ajax: "{{ route('admin.parent.show') }}",
        scrollX: true,
        scrollCollapse: false,
        autoWidth: false,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'father_name', name: 'father_name' },
            { data: 'father_nic', name: 'father_nic'},
            { data: 'contact_no', name: 'contact_no' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin fa-3x fa-fw"></i>',
            emptyTable: "No parents found"
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
    // Adjust columns on window resize
    $('body').on('expanded.pushMenu collapsed.pushMenu', function() {
            setTimeout(function() {
                table.columns.adjust().draw(false);  
            }, 350);
    });
    $(document).on('click', '[data-widget="pushmenu"], .sidebar-toggle, .sidebar-toggler, .nav-link[data-toggle="sidebar"]', function() {
        setTimeout(function() {
            table.columns.adjust().draw(false);
        }, 300);
    });
    $(window).on('resize', function() {
        table.columns.adjust().draw(false);
    });
});
</script>
@endsection