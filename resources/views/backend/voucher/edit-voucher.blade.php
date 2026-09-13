@extends('backend.layout.master')

@section('title', $pageTitle ?? 'Edit Voucher')

@section('content')

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3>Edit Voucher</h3>
            <a href="{{ route('admin.voucher.index') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">

                <form method="POST" action="{{ route('admin.voucher.update', $vouchers->id) }}">
                    @csrf
                    <div class="row">
                        {{-- Academic Session --}}
                        <div class="col-md-6 mb-3">
                            <label>Session <span class="text-danger">*</span></label>
                            <select name="academic_session_id" id="exit_academic_session_id"  class="form-select" required>
                            <option value="">Select</option>
                                @forelse ($sessions as $session_list)     
                                    <option value="{{$session_list->id}}" {{$session_list->id === $vouchers->academicSession->id ? 'selected' : ''}}> {{$session_list->session_name}}</option>
                                    @empty
                                    <option value="" disabled>--Not Available--</option>
                                @endforelse
                            </select>
                             @error('academic_session_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Section & Class --}}
                        <div class="col-md-6 mb-3">
                            <label>Class & Section <span class="text-danger">*</span></label>
                            <select name="session_item_id" id="session_item_id" class="form-select" required>
                                <option value="">Select</option>
                               
                            </select>
                            @error('session_item_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                          {{-- Fee Items --}}
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Fee Items</label>

                            <div class="row">
                                @forelse($vouchers->items as $fee)
                                    <div class="row mb-2">
                                        <div class="col-md-6">
                                            <input type="text"
                                                name="fee_names[]"
                                                value="{{ $fee->fee_name }}"
                                                class="form-control"
                                                readonly>
                                        </div>

                                        <div class="col-md-6">
                                            <input type="number"
                                                name="amounts[]"
                                                value="{{ old('amounts.' . $loop->index, $fee->amount) }}"
                                                class="form-control fee-amount"
                                                data-fee-name="{{ $fee->fee_name }}"
                                                data-original-amount="{{ $fee->amount }}"
                                                min="0"
                                                step="0.01"
                                                placeholder="Enter Amount">
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center">No Fee Items Available</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Due Date --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date *</label>
                            <input type="date" name="due_date" id="due_date"
                                   value="{{ old('due_date', $vouchers->due_date?->format('Y-m-d')) }}"
                                   class="form-control"  style="height:26px; padding:5px 8px;" required>
                        </div>

                        {{-- Total Amount --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="number" step="0.01"
                                   name="total_amount"
                                   id="total_amount"
                                    style="height:26px; padding:5px 8px;"
                                   value="{{ old('total_amount', $vouchers->total_amount) }}"
                                   class="form-control" readonly>
                        </div>

                        {{-- Notes --}}
                        <div class="col-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" id="notes"
                                class="form-control">{{ old('notes', $vouchers->notes) }}</textarea>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save me-1"></i> Update Voucher
                    </button>

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
    window.selectedSessionItemId = "{{ $vouchers->session_item_id ?? '' }}";
</script>

<script src="{{ asset('backend_assets/js/exit-voucher-session.js') }}"></script>

@endsection