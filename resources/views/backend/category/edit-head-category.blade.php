@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>Edit Head Category</h3>
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

                        <form method="POST" action="{{ route('admin.categories.updatehead',$headCategory->id) }}">
                            @csrf
                            <div class="row">
                                   {{-- Head Name --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Head Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="head_name"
                                           value="{{ old('head_name',$headCategory->head_name ?? '') }}"
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
                                           value="{{ old('bill_no',$headCategory->bill_no ?? '') }}"
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
                                           value="{{ old('date', isset($headCategory) ? \Carbon\Carbon::parse($headCategory->date)->format('Y-m-d') : '') }}"
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
                                           value="{{ old('amount',$headCategory->amount ?? '') }}"
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

            </div>
        </div>

    </div>
</div>
@endsection

