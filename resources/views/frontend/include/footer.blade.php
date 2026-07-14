{{-- resources/views/frontend/include/footer.blade.php --}}

<!-- ============ FOOTER ============ -->
<footer class="dps-footer">
    <div class="dps-footer-glow"></div>
    <div class="container">
        <div class="row gy-5 dps-footer-top">
            <div class="col-lg-4">
                <a href="{{ url('/') }}" class="dps-brand dps-footer-brand">
                    <span class="dps-brand-mark">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </span>
                    <span class="dps-brand-text">
                        DPS <strong>DUNYAPUR</strong>
                        <small>Dunyapur Public School</small>
                    </span>
                </a>
                <p class="dps-footer-about">
                    Nurturing character, curiosity and excellence in the heart of Dunyapur since 1985.
                    A trusted institution for generations of families.
                </p>
                <div class="dps-footer-social">
                    <a href="https://www.facebook.com/" target="_blank" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://www.youtube.com"  target="_blank" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="https://www.instagram.com" target="_blank" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://wa.me/923280990563" target="_blank" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-sm-4">
                <h6 class="dps-footer-heading">Explore</h6>
                <ul class="dps-footer-links">
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#services">Parent Services</a></li>
                    <li><a href="#why-dps">Why DPS</a></li>
                    <li><a href="#gallery">Campus Gallery</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-sm-4">
                <h6 class="dps-footer-heading">Portal</h6>
                <ul class="dps-footer-links">
                    <li><a href="{{ route('parent.signIn') }}">Parent Login</a></li>
                    <li><a href="#login-process">How It Works</a></li>
                    <li><a href="#">Fee Payment</a></li>
                    <li><a href="#">Result Cards</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-sm-4">
                <h6 class="dps-footer-heading">Contact</h6>
                <ul class="dps-footer-contact">
                    <li><i class="fa-solid fa-location-dot"></i> Multan Road, Dunyapur, Punjab, Pakistan</li>
                    <li><i class="fa-solid fa-phone"></i> +92 300 1234567</li>
                    <li><i class="fa-solid fa-envelope"></i> info@dpsdunyapur.edu.pk</li>
                </ul>
            </div>
        </div>

        <hr class="dps-footer-divider">

        <div class="dps-footer-bottom">
            <p class="mb-0">&copy; {{ date('Y') }} Dunyapur Public School (DPS Dunyapur). All rights reserved.</p>
            <p class="mb-0 dps-footer-credit">Designed for a brighter, more transparent school community.</p>
        </div>
    </div>
</footer>