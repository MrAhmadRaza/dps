@extends('backend.layout.master')
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
</style>

    <div class="container">
        <div class="page-inner">
            <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <h3 class="mb-2 mb-md-0">Academic Sessions</h3>
            </div>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

      
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover text-center data-table align-middle table-striped">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No</th>
                                <th>Session</th>
                                <th>Start Date</th>  
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Created</th>
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
            ajax: "{{ route('admin.academic-session.index') }}",
            scrollX: true,
            scrollCollapse: false,
            autoWidth: false,
            columns: [
                { data: 'DT_RowIndex', width: "50px", orderable: false, searchable: false },
                { data: 'session_name', name: 'session_name' },
                { data: 'start_date', name: 'start_date' },
                { data: 'end_date', name: 'end_date'},
                { data: 'status', name: 'status'},
                { data: 'created_at', name: 'created_at', width: "110px" },
                { data: 'action', name: 'action', width: "140px", className: "text-center", orderable: false, searchable: false }
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
                emptyTable: "No sessions found"
            },

            drawCallback: function () {
                $('.dataTables_scrollBody').css('overflow', 'visible !important');
            },
            initComplete: function () {
                $('.dataTables_scrollBody').css('overflow', 'visible !important');
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