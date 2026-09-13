@extends('backend.layout.master')
@section('title', $pagetitle ?? 'Category')
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

        <div class="page-header d-flex justify-content-between mb-4">
            <h3>Categories</h3>
            <a href="{{ route('admin.categories.add') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add Category
            </a>
        </div>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    
        {{-- Filters --}}
        <div class="row mb-3 align-items-end">
            <div class="col-md-3">
                <label>Account Type</label>
                <select id="filter_type" class="form-control">
                    <option value="">All</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>From</label>
                <input type="date" id="from_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label>To</label>
                <input type="date" id="to_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="d-block">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button id="filterBtn" class="btn btn-primary flex-fill">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <a id="printBtn" class="btn btn-success flex-fill">
                        <i class="fa fa-print"></i> Print
                    </a>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body">
                <table class="table table-hover text-center data-table align-middle table-striped">
                    <thead class="table-light text-center font-arial">
                        <tr>
                            <th>No</th>
                            <th>Category</th>
                            <th>Account Type</th>
                            <th>Total Amount</th> 
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    let table;
    $(function () {

        table = $('.data-table').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.categories.index') }}",
                data: function (d) {
                    d.type = $('#filter_type').val();
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                }
            },
            scrollX: true,
            scrollCollapse: false,
            autoWidth: false,
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'category_name' },
                { data: 'type' },
                { data: 'total_amount' },
                { data: 'created_at' },
                { data: 'action', orderable: false, searchable: false }
            ],
            language: {
                processing: '<i class="fas fa-spinner fa-spin fa-3x fa-fw"></i>',
                emptyTable: "No categories found"
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

        // 🔍 Search Button
        $('#filterBtn').click(function () {
            table.draw();
        });

        // Print 
        $('#printBtn').click(function () {
            let type = $('#filter_type').val();
            let from = $('#from_date').val();
            let to = $('#to_date').val();
            let url = "{{ route('admin.categories.print') }}?" +
                    "type=" + type +
                    "&from_date=" + from +
                    "&to_date=" + to;
            window.open(url, '_blank');
        });
    });
</script>
@endsection