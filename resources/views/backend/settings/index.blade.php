@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>General Settings</h3>
            
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        

        {{-- Session Items --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        <form method="POST" action="{{route('admin.general-settings.store')}}">
                            @csrf
                            <div class="row">
                            
                                  {{-- Name --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="hidden" value="{{$settings?->id}}" name="setting_id" id="">
                                    <input type="text"
                                           name="name"
                                           value="{{ old('name',$settings?->name) }}"
                                           class="form-control"
                                           placeholder="name"
                                           required>
                                    @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                   {{-- Name --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Account No <span class="text-danger">*</span>
                                    </label>
                                    <input type="text"
                                           name="account_no"
                                           value="{{ old('account_no',$settings?->account_no) }}"
                                           class="form-control"
                                           placeholder="account no"
                                            required>
                                    @error('account_no')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        One Bill Prefix 
                                    </label>
                                    <input type="text"
                                           name="one_bill_prefix"
                                           value="{{ old('one_bill_prefix',$settings?->one_bill_prefix) }}"
                                           class="form-control"
                                           placeholder="031234"
                                            required>
                                    @error('one_bill_prefix')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                         Late Fee Fine 
                                    </label>
                                    <input type="text"
                                           name="late_fee_fine"
                                           value="{{ old('late_fee_fine',$settings?->late_fee_fine) }}"
                                           class="form-control"
                                           placeholder="200">
                                    @error('late_fee_fine')
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