@extends('admin.layouts.app')
@section('content')
<div class="ad-head">
    <div>
        <h1>Settings</h1>
        <p>School details used on challans and by 1LINK / 1Bill.</p>
    </div>
</div>

@if (!$settings)
    <div class="alert alert-warning">No settings record exists yet. Ask the developer to create one before saving here.</div>
@endif

<form method="POST" action="{{ route('admin.general-settings.store') }}" class="panel" style="max-width: 900px">
    @csrf
    <input type="hidden" value="{{ $settings?->id }}" name="setting_id">

    <div class="form-section">
        <h2 class="form-section-title">School and bank</h2>
        <p class="form-section-sub">Printed on every fee challan.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">School name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $settings?->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="account_no">Fee collection account no.</label>
                <input type="text" id="account_no" name="account_no" inputmode="numeric" value="{{ old('account_no', $settings?->account_no) }}" class="form-control mono @error('account_no') is-invalid @enderror" required>
                @error('account_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <h2 class="form-section-title">Fees and 1Bill</h2>
        <p class="form-section-sub">The prefix changes the 1Bill ID on every challan straight away. The late fee is used for challans created after you save.</p>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="one_bill_prefix">1Bill prefix</label>
                <input type="text" id="one_bill_prefix" name="one_bill_prefix" inputmode="numeric" value="{{ old('one_bill_prefix', $settings?->one_bill_prefix) }}" class="form-control mono @error('one_bill_prefix') is-invalid @enderror" placeholder="e.g. 10045">
                @error('one_bill_prefix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Given by the bank. Parents enter this plus the challan number in their banking app.</div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="late_fee_fine">Late fee fine (Rs)</label>
                <input type="text" id="late_fee_fine" name="late_fee_fine" inputmode="numeric" value="{{ old('late_fee_fine', $settings?->late_fee_fine) }}" class="form-control @error('late_fee_fine') is-invalid @enderror" placeholder="e.g. 500">
                @error('late_fee_fine')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Added to the amount payable after the due date.</div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" @disabled(!$settings)><i class="fa-solid fa-floppy-disk"></i>Save settings</button>
    </div>
</form>
@endsection
