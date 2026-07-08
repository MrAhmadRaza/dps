@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>Add Category</h3>
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

                        <form method="POST" action="{{ route('admin.categories.store') }}">
                            @csrf
                            <div class="row">
                                {{-- Category Name --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Category Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="category_name"
                                           value="{{ old('category_name') }}"
                                           class="form-control"
                                           required>
                                    @error('category_name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Account Type <span class="text-danger">*</span>
                                    </label>
                                    <select name="type" class="form-select form-select" required style="height:40px;">
                                        <option value="">-- Select Type --</option>
                                        <option value="income" >
                                            Income
                                        </option>
                                        <option value="expense">
                                            Expense
                                        </option>
                                    </select>

                                    @error('type')
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
@endsection