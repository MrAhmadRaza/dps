@php $s = config('school'); @endphp
<!-- ============ FOOTER ============ -->
<footer class="dps-footer">
    <div class="container">
        <div class="row gy-5">
            <div class="col-lg-4 col-md-6">
                <a href="{{ url('/') }}" class="dps-footer-brand">
                    <img src="{{ asset('frontend_assets/img/crest.png') }}" alt="{{ $s['name'] }} crest" width="64" height="64" loading="lazy">
                    <span>
                        <strong>Dunyapur Public School</strong>
                        <small>{{ implode(' · ', $s['motto']) }}</small>
                    </span>
                </a>
                <p class="dps-footer-about">
                    Educating the young people of Dunyapur since {{ $s['founded'] }} — with rigorous academics,
                    strong values and a campus where every child is known.
                </p>
                <div class="dps-footer-social">
                    <a href="{{ $s['social']['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="{{ $s['social']['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="{{ $s['social']['youtube'] }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="https://wa.me/{{ $s['whatsapp'] }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="dps-footer-heading">The School</h6>
                <ul class="dps-footer-links">
                    <li><a href="#about">About DPS</a></li>
                    <li><a href="#principal">Principal's Message</a></li>
                    <li><a href="#academics">Academics</a></li>
                    <li><a href="#campus">Campus Life</a></li>
                    <li><a href="#gallery">Gallery</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="dps-footer-heading">Quick Links</h6>
                <ul class="dps-footer-links">
                    <li><a href="#admissions">Admissions</a></li>
                    <li><a href="{{ route('parent.signIn') }}">Parent Portal</a></li>
                    <li><a href="{{ route('parent.signIn') }}">Fee Challans</a></li>
                    <li><a href="{{ route('admin.signIn') }}">Staff Login</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h6 class="dps-footer-heading">Get in Touch</h6>
                <ul class="dps-footer-contact">
                    <li><i class="fa-solid fa-location-dot"></i><span>{{ $s['address'] }}</span></li>
                    <li><i class="fa-solid fa-phone"></i><a href="tel:{{ preg_replace('/\s+/', '', $s['phone']) }}">{{ $s['phone'] }}</a></li>
                    <li><i class="fa-solid fa-envelope"></i><a href="mailto:{{ $s['email'] }}">{{ $s['email'] }}</a></li>
                    <li><i class="fa-regular fa-clock"></i><span>{{ $s['hours'] }}</span></li>
                </ul>
            </div>
        </div>

        <div class="dps-footer-bottom">
            <p>&copy; {{ date('Y') }} {{ $s['name'] }}. All rights reserved.</p>
            <p class="dps-footer-motto">{{ implode(' · ', $s['motto']) }}</p>
        </div>
    </div>
</footer>

<a href="#main" class="dps-to-top" id="dpsToTop" aria-label="Back to top"><i class="fa-solid fa-arrow-up"></i></a>
