@extends('frontend.layout.master')

@php
    $s = config('school');
    $years = now()->year - $s['founded'];
    $img = fn ($name) => asset('frontend_assets/img/' . $name . '.jpg');
@endphp

@section('title', 'Dunyapur Public School — Knowledge · Discipline · Success')
@section('description', 'Dunyapur Public School (DPS Dunyapur): a trusted school in Dunyapur, Punjab since ' . $s['founded'] . '. Science and computer labs, a well-stocked library, a secure green campus and an online Parent Portal.')

@section('head')
<link rel="preload" as="image" href="{{ $img('hero-main') }}">
@endsection

@section('content')

{{-- ============ HERO ============ --}}
<section class="dps-hero" id="home" aria-label="Welcome">
    <div class="dps-hero-slides" aria-hidden="true">
        <div class="dps-hero-slide is-active" style="background-image:url('{{ $img('hero-main') }}')"></div>
        <div class="dps-hero-slide" data-bg="{{ $img('hero-courtyard') }}"></div>
        <div class="dps-hero-slide" data-bg="{{ $img('hero-arcade') }}"></div>
    </div>
    <div class="dps-hero-shade"></div>

    <div class="container dps-hero-inner">
        <div class="dps-hero-content">
            <span class="dps-eyebrow dps-eyebrow-light">Est. {{ $s['founded'] }} &nbsp;·&nbsp; Dunyapur, Punjab</span>
            <h1 class="dps-hero-title">Shaping minds.<br>Building <em>character.</em></h1>
            <p class="dps-hero-lead">
                For over {{ $years }} years, Dunyapur Public School has prepared young people to think clearly,
                act with integrity and lead with confidence.
            </p>
            <div class="dps-hero-actions">
                <a href="#admissions" class="btn dps-btn dps-btn-gold dps-btn-lg">Admissions {{ $s['admissions_session'] }} <i class="fa-solid fa-arrow-right"></i></a>
                <a href="#about" class="btn dps-btn dps-btn-outline-light dps-btn-lg">Discover DPS</a>
            </div>
        </div>
        <div class="dps-hero-dots" role="tablist" aria-label="Hero images">
            <button class="is-active" aria-label="Image 1"></button>
            <button aria-label="Image 2"></button>
            <button aria-label="Image 3"></button>
        </div>
    </div>
</section>

{{-- ============ QUICK LINKS ============ --}}
<section class="dps-quick" aria-label="Quick links">
    <div class="container">
        <div class="dps-quick-grid">
            <a href="#admissions" class="dps-quick-item">
                <i class="fa-solid fa-graduation-cap"></i>
                <span><strong>Admissions</strong><small>Join DPS for {{ $s['admissions_session'] }}</small></span>
                <i class="fa-solid fa-arrow-right dps-quick-arrow"></i>
            </a>
            <a href="{{ route('parent.signIn') }}" class="dps-quick-item">
                <i class="fa-solid fa-user-shield"></i>
                <span><strong>Parent Portal</strong><small>Profiles &amp; fee records</small></span>
                <i class="fa-solid fa-arrow-right dps-quick-arrow"></i>
            </a>
            <a href="{{ route('parent.signIn') }}" class="dps-quick-item">
                <i class="fa-solid fa-file-invoice"></i>
                <span><strong>Fee Challans</strong><small>View, print &amp; upload proof</small></span>
                <i class="fa-solid fa-arrow-right dps-quick-arrow"></i>
            </a>
            <a href="#contact" class="dps-quick-item">
                <i class="fa-solid fa-location-dot"></i>
                <span><strong>Visit Campus</strong><small>Directions &amp; contact</small></span>
                <i class="fa-solid fa-arrow-right dps-quick-arrow"></i>
            </a>
        </div>
    </div>
</section>

{{-- ============ WELCOME / ABOUT ============ --}}
<section class="dps-section dps-about" id="about">
    <div class="container">
        <div class="row align-items-center gy-5 gx-lg-5">
            <div class="col-lg-6 order-lg-2" data-aos="fade-up">
                <span class="dps-eyebrow">Welcome to DPS</span>
                <h2 class="dps-title">A legacy of learning in the heart of Dunyapur</h2>
                <p class="dps-lead">
                    Since {{ $s['founded'] }}, Dunyapur Public School has shaped generations of confident,
                    capable learners — combining a rigorous academic programme with strong character education.
                </p>
                <p>
                    Our students learn in purpose-built classrooms, practise science in dedicated physics,
                    chemistry and biology laboratories, and read widely in a well-stocked library — all on a
                    green, secure campus where every child is known, supported and challenged to do their best.
                </p>
                <div class="dps-about-actions">
                    <a href="#principal" class="dps-link">Read the Principal's message <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-6 order-lg-1" data-aos="fade-up" data-aos-delay="100">
                <div class="dps-about-media">
                    <img src="{{ $img('campus-building') }}" alt="The red-brick main building of Dunyapur Public School" class="dps-about-main" loading="lazy">
                    <img src="{{ $img('lab-physics') }}" alt="Students in a physics practical with their teacher" class="dps-about-inset" loading="lazy">
                    <div class="dps-about-seal">
                        <span>Since</span>
                        <strong>{{ $s['founded'] }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="dps-stats" data-aos="fade-up">
            <div class="dps-stat">
                <strong><span data-count="{{ $years }}">{{ $years }}</span>+</strong>
                <span>Years of legacy</span>
            </div>
            @foreach ($s['stats'] as $stat)
                <div class="dps-stat">
                    <strong><span data-count="{{ $stat['value'] }}">{{ number_format($stat['value']) }}</span>{{ $stat['suffix'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ VALUES (from the school crest) ============ --}}
<section class="dps-section dps-values" aria-labelledby="valuesTitle">
    <div class="container">
        <div class="dps-section-head text-center" data-aos="fade-up">
            <span class="dps-eyebrow dps-eyebrow-light">Our Values</span>
            <h2 class="dps-title dps-title-light" id="valuesTitle">Three words on our crest. One promise to every family.</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4" data-aos="fade-up">
                <div class="dps-value">
                    <span class="dps-value-num">I</span>
                    <h3>Knowledge</h3>
                    <p>A rigorous, well-rounded curriculum taught by experienced educators, with hands-on learning in the lab, the library and the classroom.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="dps-value">
                    <span class="dps-value-num">II</span>
                    <h3>Discipline</h3>
                    <p>Clear expectations, good manners and self-control — habits that help students focus, respect others and take ownership of their work.</p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="dps-value">
                    <span class="dps-value-num">III</span>
                    <h3>Success</h3>
                    <p>Strong results, yes — but also confident, capable young people ready for higher studies, careers and responsible citizenship.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ PRINCIPAL'S MESSAGE ============ --}}
<section class="dps-section dps-principal" id="principal">
    <div class="container">
        <div class="row align-items-center gy-5 gx-lg-5">
            <div class="col-lg-4 col-md-6 mx-auto" data-aos="fade-up">
                <figure class="dps-principal-photo">
                    <img src="{{ $img('principal') }}" alt="Portrait of {{ $s['principal']['name'] }}" loading="lazy">
                </figure>
            </div>
            <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
                <span class="dps-eyebrow">From the Principal's Desk</span>
                <blockquote class="dps-quote">
                    “Every child deserves a school that truly sees them.”
                </blockquote>
                <p>
                    At DPS Dunyapur, we believe education is a partnership between school and home. Our teachers
                    know each student by name, and our doors are always open to parents who want to be part of
                    their child's journey.
                </p>
                <p>
                    On behalf of our entire faculty, thank you for trusting us with your child's future. We look
                    forward to walking this journey together.
                </p>
                <div class="dps-signature">
                    <strong>{{ $s['principal']['name'] }}</strong>
                    <span>{{ $s['principal']['title'] }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ ACADEMICS ============ --}}
<section class="dps-section dps-academics" id="academics">
    <div class="container">
        <div class="dps-section-head d-lg-flex align-items-end justify-content-between" data-aos="fade-up">
            <div>
                <span class="dps-eyebrow">Academics</span>
                <h2 class="dps-title">Learning that grows with your child</h2>
            </div>
            <p class="dps-section-sub">A structured path from the early grades to matriculation, built on strong foundations and practical learning.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6" data-aos="fade-up">
                <article class="dps-program">
                    <div class="dps-program-img"><img src="{{ $img('library-shelves') }}" alt="Students choosing books in the library" loading="lazy"></div>
                    <div class="dps-program-body">
                        <span class="dps-program-tag">Stage 01</span>
                        <h3>Primary Years</h3>
                        <p>Strong literacy and numeracy, a love of reading and the habits of curiosity that carry a child through school.</p>
                    </div>
                </article>
            </div>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <article class="dps-program">
                    <div class="dps-program-img"><img src="{{ $img('computer-lab') }}" alt="A teacher explaining code in the computer lab" loading="lazy"></div>
                    <div class="dps-program-body">
                        <span class="dps-program-tag">Stage 02</span>
                        <h3>Middle Years</h3>
                        <p>Broader subjects, computer literacy and growing independence, with close guidance from subject specialists.</p>
                    </div>
                </article>
            </div>
            <div class="col-lg-4 col-md-12" data-aos="fade-up" data-aos-delay="200">
                <article class="dps-program">
                    <div class="dps-program-img"><img src="{{ $img('lab-chemistry') }}" alt="Students performing a chemistry experiment" loading="lazy"></div>
                    <div class="dps-program-body">
                        <span class="dps-program-tag">Stage 03</span>
                        <h3>Secondary &amp; Matriculation</h3>
                        <p>Focused board preparation in science and humanities streams, with regular practicals in our dedicated laboratories.</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

{{-- ============ CAMPUS & FACILITIES ============ --}}
<section class="dps-section dps-campus" id="campus">
    <div class="container">
        <div class="dps-section-head text-center" data-aos="fade-up">
            <span class="dps-eyebrow">Campus Life</span>
            <h2 class="dps-title">A campus built for learning</h2>
            <p class="dps-section-sub mx-auto">Laboratories, a library, open grounds and green walkways — spaces that make every school day count.</p>
        </div>

        <div class="dps-bento">
            <figure class="dps-tile dps-tile-xl" data-aos="fade-up">
                <img src="{{ $img('lab-practical') }}" alt="Students and teacher around a physics apparatus" loading="lazy">
                <figcaption><i class="fa-solid fa-flask"></i><strong>Science Laboratories</strong><span>Dedicated physics, chemistry and biology labs for regular hands-on practicals.</span></figcaption>
            </figure>
            <figure class="dps-tile" data-aos="fade-up" data-aos-delay="50">
                <img src="{{ $img('library') }}" alt="Students reading in the school library" loading="lazy">
                <figcaption><i class="fa-solid fa-book-open"></i><strong>Library</strong><span>A quiet reading room with a broad collection.</span></figcaption>
            </figure>
            <figure class="dps-tile" data-aos="fade-up" data-aos-delay="100">
                <img src="{{ $img('computer-lab-2') }}" alt="Teacher at the whiteboard in the computer lab" loading="lazy">
                <figcaption><i class="fa-solid fa-computer"></i><strong>Computer Lab</strong><span>Practical computing from the middle years.</span></figcaption>
            </figure>
            <figure class="dps-tile" data-aos="fade-up" data-aos-delay="50">
                <img src="{{ $img('grounds') }}" alt="Open lawns in front of the school buildings" loading="lazy">
                <figcaption><i class="fa-solid fa-futbol"></i><strong>Sports Grounds</strong><span>Wide lawns for games, sports and assemblies.</span></figcaption>
            </figure>
            <figure class="dps-tile" data-aos="fade-up" data-aos-delay="100">
                <img src="{{ $img('entrance') }}" alt="The guarded main entrance of the school" loading="lazy">
                <figcaption><i class="fa-solid fa-shield-halved"></i><strong>Secure Campus</strong><span>Controlled entry and a watchful, caring staff.</span></figcaption>
            </figure>
            <figure class="dps-tile dps-tile-wide" data-aos="fade-up" data-aos-delay="150">
                <img src="{{ $img('pathway') }}" alt="A tree-lined pathway with flowering bougainvillea" loading="lazy">
                <figcaption><i class="fa-solid fa-leaf"></i><strong>Green Campus</strong><span>Tree-lined walkways, gardens and open courtyards.</span></figcaption>
            </figure>
            <figure class="dps-tile dps-tile-wide" data-aos="fade-up" data-aos-delay="200">
                <img src="{{ $img('playground') }}" alt="Play equipment in the school garden" loading="lazy">
                <figcaption><i class="fa-solid fa-children"></i><strong>Play Areas</strong><span>Safe outdoor play spaces set among lawns and flowering trees.</span></figcaption>
            </figure>
        </div>
    </div>
</section>

{{-- ============ PARENT PORTAL ============ --}}
<section class="dps-section dps-portal" id="portal">
    <div class="container">
        <div class="row align-items-center gy-5 gx-lg-5">
            <div class="col-lg-6" data-aos="fade-up">
                <span class="dps-eyebrow dps-eyebrow-light">DPS Parent Portal</span>
                <h2 class="dps-title dps-title-light">Your child's school records, a click away</h2>
                <p class="dps-portal-lead">
                    The Parent Portal gives every DPS family secure, round-the-clock access to their children's
                    profiles and fee records — no queues, no lost paperwork.
                </p>
                <ul class="dps-portal-features">
                    <li><i class="fa-solid fa-children"></i><div><strong>All your children, one login</strong><span>See each child's admission profile and class details.</span></div></li>
                    <li><i class="fa-solid fa-file-invoice"></i><div><strong>Fee challans online</strong><span>View and print monthly challans whenever you need them.</span></div></li>
                    <li><i class="fa-solid fa-cloud-arrow-up"></i><div><strong>Upload payment proof</strong><span>Paid at the bank? Upload the receipt and track its approval.</span></div></li>
                </ul>
                <div class="dps-portal-actions">
                    <a href="{{ route('parent.signIn') }}" class="btn dps-btn dps-btn-gold dps-btn-lg"><i class="fa-solid fa-right-to-bracket"></i> Sign in to the Portal</a>
                    <span class="dps-portal-hint"><i class="fa-solid fa-circle-info"></i> Sign in with the father's CNIC and the password issued by the school office.</span>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
                <div class="dps-portal-mock" aria-hidden="true">
                    <div class="dps-mock-bar"><span></span><span></span><span></span><em>Parent Portal</em></div>
                    <div class="dps-mock-body">
                        <div class="dps-mock-head">
                            <img src="{{ asset('frontend_assets/img/crest.png') }}" alt="">
                            <div><strong>Fee Challans</strong><small>Academic session {{ $s['admissions_session'] }}</small></div>
                        </div>
                        <div class="dps-mock-row"><div><b>Monthly Tuition Fee</b><small>Challan · Current month</small></div><span class="dps-pill dps-pill-warn">Pending</span></div>
                        <div class="dps-mock-row"><div><b>Monthly Tuition Fee</b><small>Challan · Last month</small></div><span class="dps-pill dps-pill-ok">Approved</span></div>
                        <div class="dps-mock-row"><div><b>Admission &amp; Annual Charges</b><small>Challan · New session</small></div><span class="dps-pill dps-pill-ok">Approved</span></div>
                        <div class="dps-mock-actions"><span><i class="fa-solid fa-print"></i> Print challan</span><span><i class="fa-solid fa-cloud-arrow-up"></i> Upload receipt</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ ADMISSIONS ============ --}}
<section class="dps-section dps-admissions" id="admissions" style="--bg:url('{{ $img('grounds-wide') }}')">
    <div class="container">
        <div class="dps-admissions-card" data-aos="fade-up">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-5">
                    <span class="dps-eyebrow">Admissions {{ $s['admissions_session'] }}</span>
                    <h2 class="dps-title">Admissions are open</h2>
                    <p>We welcome families who share our commitment to learning and good character. Visit the campus, meet our team and see DPS for yourself.</p>
                    <div class="dps-admissions-actions">
                        <a href="tel:{{ preg_replace('/\s+/', '', $s['phone']) }}" class="btn dps-btn dps-btn-navy"><i class="fa-solid fa-phone"></i> Call Admissions</a>
                        <a href="https://wa.me/{{ $s['whatsapp'] }}" target="_blank" rel="noopener" class="btn dps-btn dps-btn-outline"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
                    </div>
                </div>
                <div class="col-lg-7">
                    <ol class="dps-steps">
                        <li>
                            <span class="dps-step-num">1</span>
                            <div><strong>Enquire &amp; visit</strong><p>Call, message or visit the admissions office during school hours to ask about seats and fees.</p></div>
                        </li>
                        <li>
                            <span class="dps-step-num">2</span>
                            <div><strong>Apply</strong><p>Complete the admission form at the school office with the child's B-Form, photographs and previous school record.</p></div>
                        </li>
                        <li>
                            <span class="dps-step-num">3</span>
                            <div><strong>Confirm your seat</strong><p>Receive your fee challan, pay at the bank, and get your Parent Portal login to follow along online.</p></div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ GALLERY ============ --}}
<section class="dps-section dps-gallery" id="gallery">
    <div class="container">
        <div class="dps-section-head d-lg-flex align-items-end justify-content-between" data-aos="fade-up">
            <div>
                <span class="dps-eyebrow">Gallery</span>
                <h2 class="dps-title">Life at DPS</h2>
            </div>
            <p class="dps-section-sub">A glimpse of everyday life on our campus. Click any photo to view it larger.</p>
        </div>

        @php
            $gallery = [
                ['dps-sign', 'The DPS garden sign at the entrance', 'g-tall'],
                ['hero-courtyard', 'The central courtyard', 'g-wide'],
                ['microscopes', 'Biology practical with microscopes', ''],
                ['arcade', 'Arched verandas along the classrooms', ''],
                ['lab-biology', 'Students in the biology lab', ''],
                ['red-block', 'The red-brick academic block', 'g-wide'],
                ['garden', 'Gardens and play area', ''],
                ['west-wing', 'Classroom wing and lawns', ''],
            ];
        @endphp
        <div class="dps-gallery-grid">
            @foreach ($gallery as [$file, $alt, $cls])
                <button type="button" class="dps-gallery-item {{ $cls }}" data-full="{{ $img($file) }}" data-caption="{{ $alt }}" data-aos="fade-up">
                    <img src="{{ $img($file) }}" alt="{{ $alt }}" loading="lazy">
                    <span class="dps-gallery-zoom"><i class="fa-solid fa-expand"></i></span>
                </button>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ CONTACT ============ --}}
<section class="dps-section dps-contact" id="contact">
    <div class="container">
        <div class="row gy-5 gx-lg-5">
            <div class="col-lg-5" data-aos="fade-up">
                <span class="dps-eyebrow">Contact</span>
                <h2 class="dps-title">Visit us in Dunyapur</h2>
                <p>Our office is happy to answer questions about admissions, fees or the Parent Portal.</p>
                <ul class="dps-contact-list">
                    <li><i class="fa-solid fa-location-dot"></i><div><strong>Address</strong><span>{{ $s['address'] }}</span></div></li>
                    <li><i class="fa-solid fa-phone"></i><div><strong>Phone</strong><a href="tel:{{ preg_replace('/\s+/', '', $s['phone']) }}">{{ $s['phone'] }}</a></div></li>
                    <li><i class="fa-solid fa-envelope"></i><div><strong>Email</strong><a href="mailto:{{ $s['email'] }}">{{ $s['email'] }}</a></div></li>
                    <li><i class="fa-regular fa-clock"></i><div><strong>Office hours</strong><span>{{ $s['hours'] }}</span></div></li>
                </ul>
            </div>
            <div class="col-lg-7" data-aos="fade-up" data-aos-delay="100">
                <div class="dps-map">
                    <iframe title="Map showing Dunyapur Public School"
                        src="https://maps.google.com/maps?q={{ urlencode($s['map_query']) }}&z=15&output=embed"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============ LIGHTBOX ============ --}}
<div class="dps-lightbox" id="dpsLightbox" role="dialog" aria-modal="true" aria-label="Photo viewer" hidden>
    <button type="button" class="dps-lb-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    <button type="button" class="dps-lb-prev" aria-label="Previous photo"><i class="fa-solid fa-chevron-left"></i></button>
    <figure><img src="" alt=""><figcaption></figcaption></figure>
    <button type="button" class="dps-lb-next" aria-label="Next photo"><i class="fa-solid fa-chevron-right"></i></button>
</div>

@endsection
