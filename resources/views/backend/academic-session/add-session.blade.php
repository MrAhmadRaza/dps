@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>Add Academic Session</h3>
            <a href="{{route('admin.academic-session.index')}}" class="btn btn-primary btn-sm">
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

                        <form method="POST" action="{{ route('admin.academic-session.store') }}">
                            @csrf

                            <div class="row">

                                {{-- Session Name --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Session Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="session_name"
                                           value="{{ old('session_name') }}"
                                           class="form-control"
                                           placeholder="e.g 2021-2022"
                                           required>
                                    @error('session_name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Start Date --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Start Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                           name="start_date"
                                           value="{{ old('start_date') }}"
                                           class="form-control"
                                           required>
                                    @error('start_date')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- End Date --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        End Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                           name="end_date"
                                           value="{{ old('end_date') }}"
                                           class="form-control"
                                           required>
                                    @error('end_date')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                              {{-- Status --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Status <span class="text-danger">*</span>
                                    </label>

                                    <select name="status" class="form-select form-select" required style="height:40px;">
                                        <option value="">-- Select Status --</option>
                                        <option value="active" >
                                            Active
                                        </option>
                                        <option value="inactive">
                                            Inactive
                                        </option>
                                    </select>

                                    @error('status')
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
        </div>

        {{-- Session Items --}}
        <div class="row">
            <h4>Class & Section</h4>
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <form method="POST" action="{{route('admin.academic-session-item.store')}}">
                            @csrf
                            <div class="row">
                                {{-- Sessions --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                                    <select name="academic_session_id" class="form-select" required>
                                        <option value="">-- Select Session --</option>
                                        @forelse($sessions as $session)
                                            <option value="{{ $session->id }}">
                                                {{ $session->session_name }}
                                            </option>
                                        @empty
                                            <option value="" disabled>-- Not Available --</option>
                                        @endforelse
                                    </select>
                                    @error('academic_session_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                

                                  {{-- Class --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Class <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="class"
                                           value="{{ old('class') }}"
                                           class="form-control" required>
                                    @error('class')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Section --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Section <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="section"
                                           value="{{ old('section') }}"
                                           class="form-control" required>
                                    @error('section')
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
        </div>

        {{-- All Class and Section --}}
        <h5>All Classes & Sections</h5>
       
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover text-center data-table align-middle table-striped">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No</th>
                                <th>Class</th>
                                <th>Section</th>
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
            ajax: "{{ route('admin.academic-session.class-section') }}",
            scrollX: true,
            scrollCollapse: false,
            autoWidth: false,
            columns: [
                { data: 'DT_RowIndex', width: "50px", orderable: false, searchable: true },
                { data: 'class', name: 'class' },
                { data: 'section', name: 'section' },
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>',
                emptyTable: "No Class & Section found"
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