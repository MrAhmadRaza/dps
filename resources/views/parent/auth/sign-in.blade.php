@extends('parent.layouts.auth')

@section('content')
@php
    $contact = \App\Services\ParentPortal\ParentPortal::contact();
    $photo = is_file(public_path('parent_assets/img/campus.jpg')) ? asset('parent_assets/img/campus.jpg') : null;
@endphp
<div class="pp-auth">
    <aside class="pp-auth-side" @if($photo) style="--auth-photo: url('{{ $photo }}')" @endif>
        <a href="{{ url('/') }}" class="pp-home"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to the school website</a>
        <div>
            <h2>Your children's fees, in one place.</h2>
            <p>See what's due for each child, pay through your bank app with the 1Bill number, and send the school your payment proof.</p>
            <ul>
                <li><i class="fa-solid fa-file-invoice" aria-hidden="true"></i> Current and past fee challans</li>
                <li><i class="fa-solid fa-print" aria-hidden="true"></i> Print a challan for the bank</li>
                <li><i class="fa-solid fa-camera" aria-hidden="true"></i> Upload a photo of the paid challan</li>
            </ul>
        </div>
        <span class="pp-auth-foot">{{ $contact['name'] }}</span>
    </aside>

    <main class="pp-auth-main">
        <div class="pp-auth-card">
            <a href="{{ url('/') }}" class="pp-auth-brand">
                <img src="{{ asset('parent_assets/img/crest.png') }}" alt="" width="52" height="52">
                <span><strong>{{ $contact['name'] }}</strong><span>Parent portal</span></span>
            </a>

            <h1>Sign in</h1>
            <p>Use the father's CNIC and the password given by the school office.</p>

            @include('parent.partials.flash')

            <form action="{{ route('parent.signIn.submit') }}" method="post" class="pp-form" data-once novalidate>
                @csrf
                <div class="pp-field">
                    <label for="father_nic">Father's CNIC</label>
                    <input type="text" name="father_nic" id="father_nic" value="{{ old('father_nic') }}"
                           class="pp-input num @error('father_nic') is-invalid @enderror"
                           inputmode="numeric" autocomplete="username" placeholder="12345-1234567-1" maxlength="15"
                           required data-cnic autofocus>
                    @error('father_nic') <span class="pp-error">{{ $message }}</span> @enderror
                </div>

                <div class="pp-field">
                    <label for="password">Password</label>
                    <div class="pp-input-wrap">
                        <input type="password" name="password" id="password"
                               class="pp-input @error('password') is-invalid @enderror"
                               autocomplete="current-password" required>
                        <button type="button" class="pp-reveal" data-reveal="password" aria-label="Show password" aria-pressed="false"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                    </div>
                    @error('password') <span class="pp-error">{{ $message }}</span> @enderror
                </div>

                <label class="pp-check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Keep me signed in on this device
                </label>

                <button class="pp-btn pp-btn-primary pp-btn-block" type="submit" data-busy="Signing in…">Sign in</button>
            </form>

            <div class="pp-auth-help">
                <p><strong>Forgot your password?</strong> The school office can reset it for you.</p>
                <div class="pp-actions">
                    @if ($contact['tel'])
                        <a class="pp-btn pp-btn-outline" href="tel:{{ $contact['tel'] }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $contact['phone'] }}</a>
                    @endif
                    @if ($contact['whatsapp'])
                        <a class="pp-btn pp-btn-outline" href="https://wa.me/{{ $contact['whatsapp'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
