@extends('backend.layout.master')
@section('title', $pageTitle ?? 'Add Voucher')

@section('content')
<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>Add Voucher</h3>

            <a href="{{ route('admin.voucher.index') }}"
               class="btn btn-primary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert">
                </button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body">

                <form method="POST"
                      action="{{ route('admin.voucher.store') }}">
                    @csrf

                    <div class="row">

                        {{-- Academic Session --}}
                        <div class="col-md-6 mb-3">
                            <label>
                                Session
                                <span class="text-danger">*</span>
                            </label>

                            <select name="academic_session_id"
                                    id="exit_academic_session_id"
                                    class="form-select"
                                    required>

                                <option value="">Select</option>

                                @forelse($sessions as $session)
                                    <option value="{{ $session->id }}">
                                        {{ $session->session_name }}
                                    </option>
                                @empty
                                    <option value="" disabled>
                                        --Not Available--
                                    </option>
                                @endforelse

                            </select>

                            @error('academic_session_id')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Class / Section --}}
                        <div class="col-md-6 mb-3">
                            <label>
                                Class & Section
                                <span class="text-danger">*</span>
                            </label>

                            <select name="session_item_id"
                                    id="session_item_id"
                                    class="form-select"
                                    required>

                                <option value="">Select</option>

                            </select>

                            @error('session_item_id')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Fee Details --}}
                        <div class="col-12 mt-3" id="fee-details-section">

                            <h5 class="text-primary mb-3">
                                Fee Details
                            </h5>

                            @forelse($defaultFees as $fee)
                                <div class="row mb-2">

                                    <div class="col-md-6">
                                        <input type="text"
                                            name="fee_names[]"
                                            value="{{ $fee }}"
                                            class="form-control"
                                            readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <input type="number"
                                            name="amounts[]"
                                            class="form-control fee-amount"
                                            value="0"
                                            data-fee-name="{{ $fee }}"
                                            data-original-amount="0"
                                            min="0"
                                            step="0.01">
                                    </div>

                                </div>
                            @empty
                                <p class="text-center">
                                    No Fee Items Available
                                </p>
                            @endforelse

                        </div>

                        {{-- Due Date --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Due Date
                                <span class="text-danger">*</span>
                            </label>

                            <input type="date"
                                   name="due_date"
                                   id="due_date"
                                   value="{{ old('due_date') }}"
                                   class="form-control"
                                   required>

                            @error('due_date')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="col-12 mb-3">
                            <label class="form-label">
                                Notes
                            </label>

                            <textarea name="notes" id="notes"
                                      rows="3"
                                      class="form-control">{{ old('notes') }}</textarea>

                            @error('notes')
                                <div class="text-danger mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                    <div class="mt-2">
                        <button type="submit"
                                class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i>
                            Save Voucher
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.classSectionUrl = "{{ route('admin.voucher.class.section') }}";
    window.voucherAmountsUrl = "{{ route('admin.voucher.amounts') }}";
    window.selectedSessionItemId = "";
</script>

<script src="{{ asset('backend_assets/js/exit-voucher-session.js') }}"></script>
@endsection