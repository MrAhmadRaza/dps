@extends('backend.parent.layout.master')
@section('title', $pageTitle ?? 'N/A')

@section('content')
<style>
        .stat-card {
        display: flex;
        align-items: center;
        padding: 20px;
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.08);
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        border-radius: 12px;
        background: rgba(13,110,253,0.1);
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .stat-icon.green {
        background: rgba(25,135,84,0.1);
        color: #198754;
    }

    .stat-icon.orange {
        background: rgba(255,159,67,0.15);
        color: #ff9f43;
    }

    .stat-icon.red {
        background: rgba(220,53,69,0.1);
        color: #dc3545;
    }

    .stat-content {
        margin-left: 15px;
    }

    .stat-content span {
        font-size: 13px;
        color: #6c757d;
    }

    .stat-content h3 {
        margin: 2px 0 0;
        font-weight: 600;
        font-size: 22px;
        color: #111;
    }
</style>
<div class="container">
    <div class="page-inner">
        {{-- Success Message --}}
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

                <!-- Students -->
                <div class="col-md-3">
                    <a href="{{route('parent.student.index')}}" class="stat-card-link">
                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-content">
                                <span>Students</span>
                                <h3>{{$students ?? 0}}</h3>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Challans -->
                <div class="col-md-3">
                    <a href="{{route('parent.fee-challan')}}" class="stat-card-link">
                        <div class="stat-card">
                            <div class="stat-icon orange">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="stat-content">
                                <span>Challans</span>
                                <h3>{{$challans ?? 0}}</h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection