@extends('frontend.layout.master')
@section('content')
    <section class="figma-hero text-center">
        <div class="container">
            <div class="portal-tag mb-4">
                <i class="fas fa-lock text-primary me-2"></i> Official Parent Self-Service Gateway
            </div>
            <h1 class="hero-main-title mb-4">
                Dunyapur Public School Portal
            </h1>
            <p class="hero-sub-paragraph mb-5">
                Welcome to the official digital touchpoint of DPS Dunyapur. This secure interface allows parents and
                guardians to seamlessly verify official student documentation and download active fee challan vouchers.
            </p>
            <div>
                <a href="{{route('parent.signIn')}}" class="btn btn-dps-blue">
                    Proceed to Login Portal <i class="fas fa-arrow-right ms-2 fs-6"></i>
                </a>
            </div>
        </div>
    </section>

    <section class="content-grid-section">
        <div class="container">
            <div class="grid-title-block">
                <span class="text-primary fw-bold small text-uppercase tracking-wider">Authorized Modules</span>
                <h2 class="grid-main-heading mt-2">Available Information Logs</h2>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-md-6">
                    <div class="figma-layout-card">
                        <div class="card-ui-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="card-heading-text">Verified Student Profile</h3>
                        <p class="card-paragraph-text">
                            Access the complete official database file of your child. View registered admission records,
                            personal information logs, section assignments, and core registration identifiers securely.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="figma-layout-card">
                        <div class="card-ui-icon">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <h3 class="card-heading-text">Fee Challan Management</h3>
                        <p class="card-paragraph-text">
                            Eliminate manual paperwork. Instantly view your current fee ledger status, generate verified
                            printable vouchers, and trace previous statement history without visiting the campus bank
                            counter.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="info-flow-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <span class="text-primary fw-bold small text-uppercase">Security Protocol</span>
                    <h2 class="grid-main-heading text-start text-white mt-2 mb-4">How to Gain System Authorization</h2>
                    <p class="text-white-50 mb-4">
                        To maintain standard data confidentiality, please follow these systematic access steps using the
                        credentials provided during the registration process.
                    </p>
                    <a href="{{route('parent.signIn')}}" class="btn btn-dps-primary py-3 px-4">Proceed to Login Portal</a>
                </div>
                <div class="col-lg-7">
                    <div class="guide-container-box">
                        <div class="flow-step">
                            <div class="flow-indicator"></div>
                            <h5 class="flow-title">Step 1: Input Identity Token</h5>
                            <p class="flow-desc">Enter your officially documented parental cell number or guardian CNIC
                                registered in the DPS admission file.</p>
                        </div>
                        <div class="flow-step">
                            <div class="flow-indicator"></div>
                            <h5 class="flow-title">Step 2: Provide Security Password</h5>
                            <p class="flow-desc">Use the unique Student B-Form sequence or original Admission ID number to
                                authenticate your profile node.</p>
                        </div>
                        <div class="flow-step">
                            <div class="flow-indicator"></div>
                            <h5 class="flow-title">Step 3: Extract Vouchers & Info</h5>
                            <p class="flow-desc">Once authorized, navigate the workspace to safely download your PDF fee
                                challans or view registration details.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="why-dps-section">
        <div class="container">
            <div class="grid-title-block">
                <span class="text-primary fw-bold small text-uppercase tracking-wider">Institutional Values</span>
                <h2 class="grid-main-heading mt-2">Why Choose DPS Dunyapur</h2>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon"><i class="fas fa-star shadow-sm"></i></div>
                        <h4 class="fw-bold mb-2 text-dark" style="font-size: 1.2rem;">Academic Excellence</h4>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">We deliver high-tier educational
                            standards through modern curricula, rigorous intellectual evaluation, and expert mentorship
                            frameworks.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon"><i class="fas fa-shield-heart shadow-sm"></i></div>
                        <h4 class="fw-bold mb-2 text-dark" style="font-size: 1.2rem;">Character & Security</h4>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">Our campus provides an fully disciplined
                            environment, heavily emphasizing student safety, strict ethical values, and leadership training.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="why-card">
                        <div class="why-icon"><i class="fas fa-laptop-code shadow-sm"></i></div>
                        <h4 class="fw-bold mb-2 text-dark" style="font-size: 1.2rem;">Modern Cloud Ecosystem</h4>
                        <p class="text-muted small mb-0" style="line-height: 1.6;">We bridge transparency gaps through
                            secure administrative workflows, allowing parents instant access to academic verifications.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content-grid-section text-center"
        style="background: #F8FAFC; border-top: 1px solid var(--border-color);">
        <div class="container py-4">
            <h2 class="grid-main-heading mb-3">Launch Secure Parent Interface</h2>
            <p class="text-muted mb-4 mx-auto" style="max-width: 520px;">Initialize direct cloud access to the Dunyapur
                Public School centralized portal infrastructure.</p>
            <a href="{{route('parent.signIn')}}" class="btn btn-dps-blue px-5 py-3">Proceed to Login Portal</a>
        </div>
    </section>
@endsection
