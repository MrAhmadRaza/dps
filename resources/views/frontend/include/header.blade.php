<!-- ============ NAVBAR ============ -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top dps-navbar" id="dpsNavbar">
    <div class="container">
        <a class="navbar-brand dps-brand" href="{{ url('/') }}">
            <span class="dps-brand-mark">
                <i class="fa-solid fa-graduation-cap"></i>
            </span>
            <span class="dps-brand-text">
                DPS <strong>DUNYAPUR</strong>
                <small>Dunyapur Public School</small>
            </span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dpsNavContent" aria-controls="dpsNavContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="dpsNavContent">
            <ul class="navbar-nav mx-auto dps-nav-links">
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#login-process">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#why-dps">Why DPS</a></li>
                <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
            </ul>

            <a href="{{ route('parent.signIn') }}" class="btn dps-btn-primary dps-nav-cta">
                <i class="fa-solid fa-shield-halved"></i> Login Portal
            </a>
        </div>
    </div>
</nav>