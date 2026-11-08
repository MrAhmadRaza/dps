@extends('parent.layouts.app')

@section('content')
@php
    $c = $p['challan'];
    $a = $p['amounts'];
@endphp

<a class="pp-back" href="{{ route('parent.view.challan', $c->id) }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to challan</a>

<div class="pp-page-head">
    <h1>Upload payment proof</h1>
    <p>{{ $c->student->name }}, {{ $p['month'] }} fee, Rs {{ number_format($a['receivable']) }}. Send a photo of the bank-stamped challan or the transfer receipt and the office will mark it paid.</p>
</div>

<div class="pp-grid-2">
    <form class="pp-card pp-card-body pp-form" method="post" action="{{ route('parent.challan.upload') }}" enctype="multipart/form-data" data-once novalidate>
        @csrf
        <input type="hidden" name="fee_challan_id" value="{{ $c->id }}">

        <div class="pp-field">
            <span class="pp-label" id="proof-label">Photo of the receipt</span>
            <label class="pp-drop" for="voucher_image">
                <i class="fa-solid fa-camera" aria-hidden="true"></i>
                <strong>Take a photo or choose one</strong>
                <span>JPG or PNG. Large photos are reduced automatically.</span>
                <input type="file" id="voucher_image" name="voucher_image" accept="image/jpeg,image/png,image/*" aria-labelledby="proof-label" required data-proof="proof-preview">
            </label>
            <div class="pp-preview" id="proof-preview" aria-live="polite">
                <img alt="Selected photo">
                <small></small>
            </div>
            @error('voucher_image') <span class="pp-error">{{ $message }}</span> @enderror
        </div>

        <div class="pp-field">
            <label for="bank_name">Bank or wallet</label>
            <input type="text" id="bank_name" name="bank_name" class="pp-input @error('bank_name') is-invalid @enderror" list="bank-list" value="{{ old('bank_name') }}" autocomplete="off" required>
            <datalist id="bank-list">
                @foreach ($banks as $bank)<option value="{{ $bank }}">@endforeach
            </datalist>
            @error('bank_name') <span class="pp-error">{{ $message }}</span> @enderror
        </div>

        <div class="pp-field">
            <label for="paid_date">Date paid</label>
            <input type="date" id="paid_date" name="paid_date" class="pp-input @error('paid_date') is-invalid @enderror" value="{{ old('paid_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required>
            @error('paid_date') <span class="pp-error">{{ $message }}</span> @enderror
        </div>

        <div class="pp-field">
            <label for="transaction_id">Transaction or receipt number <span class="pp-optional">(optional)</span></label>
            <input type="text" id="transaction_id" name="transaction_id" class="pp-input @error('transaction_id') is-invalid @enderror" value="{{ old('transaction_id') }}" autocomplete="off">
            @error('transaction_id') <span class="pp-error">{{ $message }}</span> @enderror
        </div>

        <button class="pp-btn pp-btn-primary pp-btn-block" type="submit" data-busy="Sending…"><i class="fa-solid fa-upload" aria-hidden="true"></i> Send payment proof</button>
    </form>

    <div class="pp-stack">
        <div class="pp-note">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <p>Paid with 1Bill in your banking app? You don't need to upload anything. Those payments are recorded automatically.</p>
        </div>
        <div class="pp-card"><div class="pp-card-body">
            <h2 class="pp-card-title"><i class="fa-solid fa-image" aria-hidden="true"></i> A good photo shows</h2>
            <ul class="pp-bullets">
                <li>The bank stamp or the transfer confirmation</li>
                <li>The amount and the date</li>
                <li>The challan number {{ $c->challan_no ?? $c->id }}, if it's on the receipt</li>
            </ul>
        </div></div>
    </div>
</div>
@endsection
