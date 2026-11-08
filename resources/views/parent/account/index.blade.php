@extends('parent.layouts.app')

@section('content')
<div class="pp-page-head">
    <h1>Account</h1>
    <p>Your details as recorded by the school, and your sign-in password.</p>
</div>

<div class="pp-grid-2">
    <section class="pp-card"><div class="pp-card-body">
        <h2 class="pp-card-title"><i class="fa-solid fa-id-card" aria-hidden="true"></i> Your details</h2>
        <dl class="pp-dl">
            @include('parent.partials.dl-row', ['label' => 'Father\'s name', 'value' => $parent->father_name])
            @include('parent.partials.dl-row', ['label' => 'CNIC', 'value' => \App\Services\ParentPortal\ParentPortal::formatCnic($parent->father_nic)])
            @include('parent.partials.dl-row', ['label' => 'Mother\'s name', 'value' => $parent->mother_name])
            @include('parent.partials.dl-row', ['label' => 'Contact', 'value' => $parent->contact_no])
            @include('parent.partials.dl-row', ['label' => 'Address', 'value' => $parent->address])
            @include('parent.partials.dl-row', ['label' => 'Children', 'value' => $children->pluck('name')->join(', ')])
        </dl>
        <p class="pp-muted pp-small pp-mt pp-flush-b">To change any of these details, contact the school office.</p>
    </div></section>

    <section class="pp-card"><div class="pp-card-body">
        <h2 class="pp-card-title"><i class="fa-solid fa-key" aria-hidden="true"></i> Change password</h2>
        <form method="post" action="{{ route('parent.account.password') }}" class="pp-form" data-once novalidate>
            @csrf
            <div class="pp-field">
                <label for="current_password">Current password</label>
                <div class="pp-input-wrap">
                    <input type="password" id="current_password" name="current_password" class="pp-input @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                    <button type="button" class="pp-reveal" data-reveal="current_password" aria-label="Show password" aria-pressed="false"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                </div>
                @error('current_password') <span class="pp-error">{{ $message }}</span> @enderror
            </div>
            <div class="pp-field">
                <label for="password">New password</label>
                <div class="pp-input-wrap">
                    <input type="password" id="password" name="password" class="pp-input @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" required aria-describedby="password-hint">
                    <button type="button" class="pp-reveal" data-reveal="password" aria-label="Show password" aria-pressed="false"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                </div>
                <span class="hint" id="password-hint">At least 8 characters.</span>
                @error('password') <span class="pp-error">{{ $message }}</span> @enderror
            </div>
            <div class="pp-field">
                <label for="password_confirmation">Repeat new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="pp-input" autocomplete="new-password" required>
            </div>
            <button type="submit" class="pp-btn pp-btn-primary" data-busy="Saving…">Change password</button>
        </form>
    </div></section>
</div>

<section class="pp-section">
    @include('parent.partials.help')
</section>
@endsection
