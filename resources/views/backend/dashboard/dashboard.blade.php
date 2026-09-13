@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')

<style>
    .stat-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .stat-card {
        display: flex;
        align-items: center;
        padding: 22px 20px;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.04);
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, rgba(13,110,253,0.15), rgba(13,110,253,0.05));
        color: #0d6efd;
    }

    .stat-icon.green {
        background: linear-gradient(135deg, rgba(25,135,84,0.15), rgba(25,135,84,0.05));
        color: #198754;
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, rgba(255,159,67,0.18), rgba(255,159,67,0.06));
        color: #ff9f43;
    }

    .stat-content {
        margin-left: 16px;
    }

    .stat-content span {
        font-size: 13px;
        color: #6c757d;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    .stat-content h3 {
        margin: 4px 0 0;
        font-weight: 700;
        font-size: 26px;
        color: #1a1a1a;
        line-height: 1.2;
    }
</style>

<div class="container">
    <div class="page-inner">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0">Dashboard Analytics</h4>
        </div>

        <div class="row g-4">

            <!-- Admission -->
            <div class="col-md-4">
                <a href="" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-content">
                            <span>Active Admissions</span>
                            <h3>2.3k</h3>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Alumni -->
            <div class="col-md-4">
                <a href="" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-content">
                            <span>Alumni</span>
                            <h3>66k</h3>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Parents -->
            <div class="col-md-4">
                <a href="" class="stat-card-link">
                    <div class="stat-card">
                        <div class="stat-icon orange">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <span>Parents</span>
                            <h3>30k</h3>
                        </div>
                    </div>
                </a>
            </div>

        </div>
    </div>
</div>
@endsection