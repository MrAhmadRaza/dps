@php
    $adAsset = fn (string $path) => asset($path) . '?v=' . (@filemtime(public_path($path)) ?: '1');
    $school  = config('school.short_name', 'DPS');
@endphp
<!doctype html>
<html lang="en">
<head>
    @include('admin.layouts.head')
</head>
<body class="ad ad-auth">
    <section class="auth-art" style="--auth-img: url('{{ asset('admin_assets/img/campus.jpg') }}')" aria-hidden="true">
        <a href="{{ url('/') }}" class="ad-brand" tabindex="-1">
            <img src="{{ asset('admin_assets/img/crest.png') }}" alt="">
            <span>
                <span class="ad-brand-name">{{ config('school.name', 'Dunyapur Public School') }}</span>
                <span class="ad-brand-sub">Staff portal</span>
            </span>
        </a>
        <div>
            <h2>The school office, in one place.</h2>
            <p>Admissions, monthly fee challans, payment proofs and the cashbook for {{ $school }} staff.</p>
            <ul class="auth-points">
                <li><i class="fa-solid fa-magnifying-glass"></i>Review payment proofs parents upload</li>
                <li><i class="fa-solid fa-user-graduate"></i>Register students and print admission forms</li>
                <li><i class="fa-solid fa-book"></i>Keep categories and the cashbook up to date</li>
            </ul>
        </div>
        <div class="auth-foot">For authorised school staff only. Activity is logged.</div>
    </section>

    <main class="auth-panel">
        <div class="auth-card">
            <div class="auth-mobile-brand">
                <img src="{{ asset('admin_assets/img/crest.png') }}" alt="">
                <span>{{ $school }} · Staff portal</span>
            </div>

            <h1>Staff sign in</h1>
            <p class="lead-in">Use the email and password the school gave you.</p>

            @if (session('success'))
                <div class="alert alert-success py-2" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger py-2" role="alert">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.signIn.submit') }}" method="post" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           autocomplete="username" inputmode="email"
                           class="form-control @error('email') is-invalid @enderror" placeholder="name@school.pk"
                           @error('email') aria-describedby="emailError" @enderror>
                    @error('email')
                        <div class="invalid-feedback" id="emailError">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" name="password" id="password" required autocomplete="current-password"
                               class="form-control @error('password') is-invalid @enderror" placeholder="Your password">
                        <button type="button" class="pw-toggle" aria-label="Show password" aria-pressed="false" data-pw-toggle>
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check mb-0">
                        <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label fw-normal" for="remember">Keep me signed in</label>
                    </div>
                </div>

                <button class="btn btn-primary btn-lg" type="submit">
                    Sign in <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <div class="auth-help">
                Forgot your password? Ask the super admin to reset it.
                <div class="mt-2"><a href="{{ url('/') }}"><i class="fa-solid fa-arrow-left me-1"></i>Back to the website</a></div>
            </div>
        </div>
    </main>

    <script>
        document.querySelector('[data-pw-toggle]').addEventListener('click', function () {
            var input = document.getElementById('password');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            this.setAttribute('aria-pressed', show);
            this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            this.querySelector('i').className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        });
    </script>
</body>
</html>
