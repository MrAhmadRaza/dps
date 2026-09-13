@extends('frontend.layout.master')

@section('title', 'DPS Dunyapur — Parent Portal')
@section('description', 'The official Parent Portal of Dunyapur Public School. Track attendance, fees, results and circulars — securely, in one place.')

@section('content')

{{-- ============ HERO ============ --}}
<header class="dps-hero" id="home">
    <div id="dpsHeroCarousel" class="carousel slide dps-hero-carousel" data-bs-ride="carousel" data-bs-interval="4500">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('frontend_assets/dps_images/IMG-20260709-WA0055.jpg') }}" loading="lazy" alt="School Corridor">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('frontend_assets/dps_images/IMG-20260709-WA0045.jpg') }}" loading="lazy" alt="Students walking on campus">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('frontend_assets/dps_images/IMG-20260709-WA0013.jpg') }}" loading="lazy" alt="Group of graduating students">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('frontend_assets/dps_images/IMG-20260709-WA0069.jpg') }}" loading="lazy" alt="Campus view">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('frontend_assets/dps_images/IMG-20260709-WA0068.jpg') }}" loading="lazy" alt="School building">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('frontend_assets/dps_images/IMG-20260709-WA0110.jpg') }}" loading="lazy" alt="Students">
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#dpsHeroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#dpsHeroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <div class="dps-hero-overlay"></div>
</header>
{{-- ============ TRUST / STATS STRIP ============ --}}
<section class="dps-stats" id="stats">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                <div class="dps-stat-item">
                     <i class="fas fa-user-plus"></i>
                    <h3>2.3k</h3>
                    <p>Active Admissions</p>
                </div>
            </div>

             <div class="col-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="dps-stat-item">
                   <i class="fas fa-user-graduate"></i>
                    <h3>66k</h3>
                    <p>Alumni</p>
                </div>
            </div>
            
            <div class="col-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                <div class="dps-stat-item">
                <i class="fas fa-users"></i>
                    <h3>30k</h3>
                    <p>Parents</p>
                </div>
            </div>
           
        </div>
    </div>
</section>

{{-- ============ PRINCIPAL MESSAGE ============ --}}
<section class="dps-principal">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-4 text-center" data-aos="fade-right">
                <div class="dps-principal-photo-wrap">
                    <img src="{{asset('frontend_assets/dps_images/principal.jpeg')}}" loading="lazy" alt="Portrait of the Principal of DPS Dunyapur" class="dps-principal-photo">
                </div>
                <h5 class="dps-principal-name">Mr. Muhammad Amjad </h5>
                <span class="dps-principal-role">Principal, DPS Dunyapur</span>
            </div>
            <div class="col-lg-8" data-aos="fade-left">
                <i class="fa-solid fa-quote-left dps-quote-icon"></i>
                <span class="dps-section-eyebrow">Principal's Message</span>
                <h2 class="dps-section-title">"Every child deserves a school that truly sees them."</h2>
                <p class="dps-about-text">
                    At DPS Dunyapur, we believe education is a partnership between school and home. The Parent
                    Portal is our commitment to that partnership — bringing you closer to your child's daily
                    progress, achievements and needs, with complete transparency and care.
                </p>
                <p class="dps-about-text">
                    On behalf of our entire faculty, thank you for trusting us with your child's future. We look
                    forward to walking this journey together.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ============ ABOUT ============ --}}
<section class="dps-about" id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="dps-about-img-wrap">
                    <img src="{{asset('frontend_assets/dps_images/building.jpeg')}}" loading="lazy" alt="DPS Dunyapur campus building" class="dps-about-img-main">
                    <div class="dps-badge-float">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>
                            <strong>Trusted Since 1992</strong>
                            <span>Four decades of academic legacy</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <span class="dps-section-eyebrow">About Our School</span>
                <h2 class="dps-section-title">A legacy of learning, rooted in Dunyapur</h2>
                <p class="dps-about-text">
                    Dunyapur Public School has shaped generations of confident, capable learners since 1985.
                    We combine a rigorous academic curriculum with strong character education, modern facilities,
                    and a genuinely caring campus culture — so every child is known, supported and challenged
                    to do their best.
                </p>
                <p class="dps-about-text">
                    The Parent Portal extends that same care beyond the school gates, giving families a clear,
                    real-time window into their child's academic journey.
                </p>
                <ul class="dps-about-list">
                    <li><i class="fa-solid fa-circle-check"></i> Purpose-built classrooms &amp; science labs</li>
                    <li><i class="fa-solid fa-circle-check"></i> Experienced, degree-qualified faculty</li>
                    <li><i class="fa-solid fa-circle-check"></i> CCTV-monitored, secure campus</li>
                </ul>
                <a href="#services" class="btn dps-btn-outline">Explore Parent Services</a>
            </div>
        </div>
    </div>
</section>

{{-- ============ PARENT SERVICES ============ --}}
<section class="dps-services" id="services">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="dps-section-eyebrow">Parent Services</span>
            <h2 class="dps-section-title">Everything you need, in one dashboard</h2>
            <p class="dps-section-sub">Built for busy parents who want clarity, not paperwork.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="0">
                <div class="dps-service-card">
                    <div class="dps-service-icon"><i class="fa-solid fa-credit-card"></i></div>
                    <h5>Fee Payment &amp; Invoices</h5>
                    <p>Pay tuition fees online and download printable invoices in seconds.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                <div class="dps-service-card">
                    <div class="dps-service-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <h5>Attendance Tracking</h5>
                    <p>Get real-time attendance updates and daily notifications for your child.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                <div class="dps-service-card">
                    <div class="dps-service-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                    <h5>Results &amp; Report Cards</h5>
                    <p>View term results, progress reports and download report cards anytime.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                <div class="dps-service-card">
                    <div class="dps-service-icon"><i class="fa-solid fa-bullhorn"></i></div>
                    <h5>Homework &amp; Circulars</h5>
                    <p>Never miss homework, school notices or event circulars again.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ SECURE LOGIN PROCESS ============ --}}
<section class="dps-login-process" id="login-process">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="dps-section-eyebrow dps-eyebrow-light">Getting Started</span>
            <h2 class="dps-section-title dps-title-light">Secure login, in three simple steps</h2>
            <p class="dps-section-sub dps-sub-light">Your child's data is protected end-to-end at every step.</p>
        </div>

        <div class="row g-4 dps-step-row">
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
                <div class="dps-step-card">
                    <div class="dps-step-number">1</div>
                    <i class="fa-solid fa-id-badge dps-step-icon"></i>
                    <h5>Get Your Credentials</h5>
                    <p>Collect your unique Parent ID and temporary password from the school office.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="150">
                <div class="dps-step-card">
                    <div class="dps-step-number">2</div>
                    <i class="fa-solid fa-shield-halved dps-step-icon"></i>
                    <h5>Verify Your Identity</h5>
                    <p>Log in and confirm your details with a one-time OTP sent to your registered number.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="dps-step-card">
                    <div class="dps-step-number">3</div>
                    <i class="fa-solid fa-gauge-high dps-step-icon"></i>
                    <h5>Access Your Dashboard</h5>
                    <p>View attendance, results, fees and school updates — all from one screen.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
            <a href="{{ route('parent.signIn') }}" class="btn dps-btn-primary dps-btn-lg">
                <i class="fa-solid fa-right-to-bracket"></i> Go to Login Portal
            </a>
        </div>
    </div>
</section>

{{-- ============ WHY CHOOSE DPS ============ --}}
<section class="dps-why" id="why-dps">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="dps-section-eyebrow">Why Families Choose Us</span>
            <h2 class="dps-section-title">Why Choose DPS Dunyapur</h2>
        </div>

        <div class="row g-4">
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="0">
                <div class="dps-why-card">
                    <div class="dps-why-icon"><i class="fa-solid fa-award"></i></div>
                    <h5>Academic Excellence</h5>
                    <p>A rigorous, well-rounded curriculum delivered by experienced educators, consistently producing top board results.</p>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="150">
                <div class="dps-why-card">
                    <div class="dps-why-icon"><i class="fa-solid fa-lock"></i></div>
                    <h5>Safe Campus</h5>
                    <p>CCTV-monitored premises, trained security staff and strict visitor protocols keep every student safe.</p>
                </div>
            </div>
            <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                <div class="dps-why-card">
                    <div class="dps-why-icon"><i class="fa-solid fa-eye"></i></div>
                    <h5>Digital Transparency</h5>
                    <p>Real-time access to attendance, results and fee records means no surprises — just clear, honest visibility.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ CAMPUS GALLERY ============ --}}
<section class="dps-gallery" id="gallery">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="dps-section-eyebrow">Life at DPS</span>
            <h2 class="dps-section-title">Campus Gallery</h2>
            <p class="dps-section-sub">A glimpse into everyday life at Dunyapur Public School.</p>
        </div>

        <div class="row g-3 dps-gallery-grid">
            <div class="col-lg-6" data-aos="fade-up">
                <div class="dps-gallery-item dps-gallery-big">
                    <img src="{{asset('frontend_assets/dps_images/IMG-20260709-WA0092.jpg')}}" loading="lazy" alt="Students in the school library">
                    <div class="dps-gallery-caption"><i class="fa-solid fa-camera"></i> Campus Library</div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-3 h-100">
                    <div class="col-6" data-aos="fade-up" data-aos-delay="0">
                        <div class="dps-gallery-item dps-gallery-small">
                            <img src="{{asset('frontend_assets/dps_images/IMG-20260709-WA0031.jpg')}}" loading="lazy" alt="Computer science lab">
                            <div class="dps-gallery-caption"><i class="fa-solid fa-camera"></i> Physics Lab</div>
                        </div>
                    </div>
                    <div class="col-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="dps-gallery-item dps-gallery-small">
                            <img src="{{asset('frontend_assets/dps_images/IMG-20260709-WA0078.jpg')}}" loading="lazy" alt="Young students writing in classroom">
                            <div class="dps-gallery-caption"><i class="fa-solid fa-camera"></i> Classrooms</div>
                        </div>
                    </div>
                    <div class="col-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="dps-gallery-item dps-gallery-small">
                            <img src="{{asset('frontend_assets/dps_images/IMG-20260709-WA0090.jpg')}}" loading="lazy" alt="Books and study materials">
                            <div class="dps-gallery-caption"><i class="fa-solid fa-camera"></i> Reading Corner</div>
                        </div>
                    </div>
                    <div class="col-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="dps-gallery-item dps-gallery-small">
                            <img src="{{asset('frontend_assets/dps_images/IMG-20260709-WA0069.jpg')}}" loading="lazy" alt="Graduation ceremony">
                            <div class="dps-gallery-caption"><i class="fa-solid fa-camera"></i> Convocation Day</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ FINAL CTA ============ --}}
<section class="dps-final-cta">
    <div class="container text-center" data-aos="zoom-in">
        <i class="fa-solid fa-graduation-cap dps-final-cta-icon"></i>
        <h2>Stay connected to your child's school journey</h2>
        <p>Join thousands of DPS Dunyapur parents already using the portal.</p>
        <a href="{{ route('parent.signIn') }}" class="btn dps-btn-primary dps-btn-lg">
            <i class="fa-solid fa-right-to-bracket"></i> Access Parent Portal
        </a>
    </div>
</section>

@endsection

@section('js')
<script>
    // Navbar background swap on scroll
    const dpsNavbar = document.getElementById('dpsNavbar');
    const toggleNavbarBg = () => {
        if (window.scrollY > 60) {
            dpsNavbar.classList.add('dps-scrolled');
        } else {
            dpsNavbar.classList.remove('dps-scrolled');
        }
    };
    window.addEventListener('scroll', toggleNavbarBg);
    toggleNavbarBg();

    // Ensure hero carousel auto-runs even if data attributes are overridden
    document.addEventListener('DOMContentLoaded', function () {
        const heroCarouselEl = document.getElementById('dpsHeroCarousel');
        if (heroCarouselEl && window.bootstrap) {
            new bootstrap.Carousel(heroCarouselEl, {
                interval: 4500,
                ride: 'carousel',
                pause: false,
                wrap: true
            });
        }
    });
</script>
@endsection