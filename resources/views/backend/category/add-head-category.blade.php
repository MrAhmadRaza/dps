@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
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

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>Add Head Category</h3>
            <a href="{{route('admin.categories.index')}}" class="btn btn-primary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <form method="POST" action="{{ route('admin.categories.headstore') }}">
                            @csrf
                            <div class="row">
                                {{-- Status --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Category <span class="text-danger">*</span>
                                    </label>
                                    <select name="category_id" class="form-select form-select" required style="height:40px;">
                                        @if(!empty($category))
                                        <option value="{{$category->id}}" selected>{{$category->category_name ?? ''}}</option>
                                        @else
                                            <option value="">-- Not Available --</option>
                                        @endif
                                    </select>
                                    @error('category_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                   {{-- Head Name --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Head Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="head_name"
                                           value="{{ old('head_name') }}"
                                           class="form-control"
                                           required>
                                    @error('head_name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                  <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Bill No 
                                    </label>
                                    <input type="text"
                                           name="bill_no"
                                           value="{{ old('bill_no') }}"
                                           class="form-control"
                                           >
                                    @error('bill_no')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                  <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                           name="date"
                                           value="{{ old('date') }}"
                                           class="form-control"
                                           required>
                                    @error('date')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                 <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Amount <span class="text-danger">*</span>
                                    </label>
                                    <input type="number"
                                           name="amount"
                                           value="{{ old('amount') }}"
                                           class="form-control"
                                           min="1"
                                           required>
                                    @error('amount')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
            

            {{-- Show Head Categories --}}
                <div class="col-12">
                    <h3>Head Categories</h3>
    
                    <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                          {{-- Filters --}}
                   <div class="row mb-3 align-items-end">
                        <div class="col-md-4">
                            <label>From</label>
                            <input type="date" id="from_date" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label>To</label>
                            <input type="date" id="to_date" class="form-control">
                        </div>

                        <div class="col-md-4 d-flex gap-2">
                            <button id="filterBtn" class="btn btn-primary flex-fill">
                                <i class="fa fa-search"></i> Search
                            </button>

                            <a id="printBtn" class="btn btn-success flex-fill">
                                <i class="fa fa-print"></i> Print
                            </a>
                        </div>

                    </div>
                        <div class="table-responsive">
                            <table class="table table-hover text-center data-table align-middle table-striped">
                                <thead class="table-light text-center font-arial">
                                    <tr>
                                        <th>No</th>
                                        <th>Head Name</th>
                                        <th>Bill No</th>
                                        <th>Date</th>
                                        <th>Amount</th>
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
             ajax: {
                url: "{{ route('admin.categories.showHeadCategories',$category->id)}}",
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
                { data: 'DT_RowIndex', width: "50px", orderable: false, searchable: false },
                { data: 'head_name', name: 'head_name'},
                { data: 'bill_no', name: 'bill_no'},
                { data: 'date', name: 'date'},
                { data: 'amount', name: 'amount'},

                { data: 'created_at', name: 'created_at', width: "110px" },
                { data: 'action', name: 'action', width: "140px", className: "text-center", orderable: false, searchable: false }
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
                emptyTable: "No heads found under category"
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
            let from = $('#from_date').val();
            let to = $('#to_date').val();
            let url = "{{ route('admin.categories.head.print',$category->id) }}?" +
                    "&from_date=" + from +
                    "&to_date=" + to;
            window.open(url, '_blank');
        });
    });
</script>
@endsection