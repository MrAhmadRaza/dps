@php $s = config('school'); @endphp
<!-- ============ MASTHEAD (utility bar + main nav) ============ -->
<div class="dps-masthead" id="dpsMasthead">
    <div class="dps-topbar">
        <div class="container d-flex align-items-center justify-content-between">
            <ul class="dps-topbar-info d-none d-md-flex">
                <li><i class="fa-solid fa-phone"></i> <a href="tel:{{ preg_replace('/\s+/', '', $s['phone']) }}">{{ $s['phone'] }}</a></li>
                <li><i class="fa-solid fa-envelope"></i> <a href="mailto:{{ $s['email'] }}">{{ $s['email'] }}</a></li>
                <li class="d-none d-xl-flex"><i class="fa-regular fa-clock"></i> {{ $s['hours'] }}</li>
            </ul>
            <ul class="dps-topbar-links">
                <li><a href="{{ route('parent.signIn') }}"><i class="fa-solid fa-user-shield"></i> Parent Portal</a></li>
                <li><a href="{{ route('admin.signIn') }}"><i class="fa-solid fa-lock"></i> Staff Login</a></li>
            </ul>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg dps-navbar" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand dps-brand" href="{{ url('/') }}">
                <img src="{{ asset('frontend_assets/img/crest.png') }}" alt="{{ $s['name'] }} crest" class="dps-brand-crest" width="56" height="56">
                <span class="dps-brand-text">
                    <span class="dps-brand-name">Dunyapur Public School</span>
                    <span class="dps-brand-motto">{{ implode(' · ', $s['motto']) }}</span>
                </span>
            </a>

            <button class="navbar-toggler dps-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dpsNav" aria-controls="dpsNav" aria-expanded="false" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>

            <div class="collapse navbar-collapse" id="dpsNav">
                <ul class="navbar-nav ms-auto dps-nav">
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#academics">Academics</a></li>
                    <li class="nav-item"><a class="nav-link" href="#campus">Campus Life</a></li>
                    <li class="nav-item"><a class="nav-link" href="#portal">Parent Portal</a></li>
                    <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
                <a href="#admissions" class="btn dps-btn dps-btn-gold dps-nav-cta">Apply Now</a>
                <div class="dps-nav-mobile-links d-lg-none">
                    <a href="{{ route('parent.signIn') }}"><i class="fa-solid fa-user-shield"></i> Parent Portal</a>
                    <a href="{{ route('admin.signIn') }}"><i class="fa-solid fa-lock"></i> Staff Login</a>
                </div>
            </div>
        </div>
    </nav>
</div>
