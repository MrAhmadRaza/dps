@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>Reset Password</h3>
            <a href="" class="btn btn-primary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Password --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <form method="POST" action="{{route('admin.parent.update.password')}}">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="passwordId" value="{{$passwordId}}" id="">
                                {{-- Update Password--}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                       New Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="password"
                                           value="{{ old('password') }}"
                                           class="form-control" required>
                                    @error('password')
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