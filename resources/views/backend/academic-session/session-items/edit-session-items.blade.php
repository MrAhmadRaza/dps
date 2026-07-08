@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>Edit Class & Section</h3>
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

                        <form method="POST" action="{{ route('admin.academic-session-item.update',$session_items->id) }}">
                            @csrf
                            <div class="row">

                                {{-- Section --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Section <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="section"
                                           value="{{old('section', $session_items->section ?? '')}}"
                                           class="form-control"
                                           placeholder="e.g section"
                                           required>
                                    @error('section')
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
                                           value="{{old('section',$session_items->class ?? '')}}"
                                           class="form-control"
                                           placeholder="class"
                                           required>
                                    @error('class')
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