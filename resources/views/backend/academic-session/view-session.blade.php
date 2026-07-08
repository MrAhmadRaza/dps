@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>View Academic Session</h3>
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
                            <div class="row">

                                {{-- Session Name --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Session Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="session_name"
                                           value="{{ old('session_name',$session->session_name ?? 'N/A') }}"
                                           class="form-control"
                                           placeholder="e.g 2021-2022"
                                           disabled>
                                   
                                </div>

                                {{-- Start Date --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Start Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                           name="start_date"
                                           value="{{ old('start_date',\Carbon\Carbon::parse($session->start_date)->format('Y-m-d') ?? '') }}"
                                           class="form-control"
                                            disabled>
                                </div>

                                {{-- End Date --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        End Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date"
                                           name="end_date"
                                           value="{{ old('end_date',\Carbon\Carbon::parse($session->end_date)->format('Y-m-d') ?? '' )}}"
                                           class="form-control"
                                            disabled>
                                </div>

                              {{-- Status --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Status <span class="text-danger">*</span>
                                    </label>

                                    <select name="status" class="form-select form-select" required style="height:40px;" disabled>
                                        <option value="">-- Select Status --</option>
                                        <option value="active"  {{ $session->status === 'active' ? 'selected' : '' }}   >
                                            Active
                                        </option>
                                        <option value="inactive" {{ $session->status === 'inactive' ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                    </select>

                                </div>
                            </div>
                           
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
                        <div class="row">
                            
                            <div class="table-responsive text-center">
                                <table class="table table-bordered table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th>Class</th>
                                            <th>Section</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($session->sessionItems as $key => $item)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $item->class }}</td>
                                                <td>{{ $item->section }}</td>
                                               
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">
                                                       No Class and Section Found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection