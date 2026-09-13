@extends('backend.parent.layout.master')
@section('title', $pagetitle ?? 'Dashboard')
@section('content')
<style>
.table thead th {
    font-size: 0.95rem !important;
    text-transform: capitalize !important;
    letter-spacing: 0px !important;
    padding: 10px 15px 6px 10px !important;
    text-align: center !important;                      
}
.font-arial {
    font-family: Arial, sans-serif;
}
</style>

    <div class="container">
        <div class="page-inner">
            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <h3 class="mb-2 mb-md-0 font-arial">Students</h3>
            </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover text-center data-table align-middle table-striped">
                        <thead class="table-light text-center font-arial">
                            <tr>
                                <th>No</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Gender</th>  
                                <th>Session</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Admission Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    let table;

    $(function () {
        table = $('.data-table').DataTable({
            processing: false,
            serverSide: true,
            ajax: "{{ route('parent.student.index') }}",
            scrollX: true,
            scrollCollapse: false,
            autoWidth: false,
            columns: [
                { data: 'DT_RowIndex', width: "50px", orderable: false, searchable: false },
                { data: 'photo_path', name: 'photo_path' },
                { data: 'name', name: 'name' },
                { data: 'gender', name: 'gender' },
                { data: 'academic_session_id', name: 'academic_session_id' },
                { data: 'class', name: 'class' },
                { data: 'section', name: 'section' },   
                { data: 'admission_date', name: 'admission_date' },
                { data: 'status', name: 'status',},
                { data: 'action', name: 'action', width: "140px", className: "text-center", orderable: false, searchable: false }
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
                emptyTable: "No admissions found"
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