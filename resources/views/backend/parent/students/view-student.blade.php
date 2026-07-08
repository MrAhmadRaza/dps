@extends('backend.parent.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')

<style>
    :root {
        --primary: #0d6efd;
        --success: #198754;
        --dark: #212529;
        --light: #f8f9fa;
        --gray: #6c757d;
    }
    .profile-header {
        background: linear-gradient(135deg, var(--primary), #0b5ed7);
        color: white;
        padding: 2.5rem 2rem;
        border-radius: 12px 12px 0 0;
        position: relative;
        overflow: hidden;
    }
    .profile-avatar {
        width: 110px;
        height: 110px;
        border: 5px solid white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .info-label {
        font-weight: 600;
        color: var(--gray);
        margin-bottom: 0.25rem;
        display: block;
        font-size: 0.9rem;
    }
    .info-value {
        font-weight: 500;
        color: var(--dark);
        margin-bottom: 1rem;
    }
    .tab-content .card {
        border: none;
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        border-radius: 10px;
    }
    .guardian-item {
        border-left: 4px solid var(--primary);
        padding-left: 1rem;
        margin-bottom: 1.5rem;
    }
    .fee-item {
        background: #f8f9fa;
        border-left: 4px solid var(--success);
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    .fee-total {
        background: #d4edda;
        padding: 1.25rem;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
    }
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: var(--gray);
    }
</style>

<div class="container-fluid py-4">

    <!-- Header with Back & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">Student Details</h3>
        <div>
            <a href="{{ route('parent.student.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fa fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Main Profile Card -->
    <div class="card shadow border-0 rounded-3 overflow-hidden">
        <div class="profile-header">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($student->photo_path)
                        <img src="{{ asset($student->photo_path) }}" 
                             alt="{{ $student->name }}" 
                             class="profile-avatar rounded-circle">
                    @else
                        <div class="profile-avatar rounded-circle bg-white d-flex align-items-center justify-content-center">
                            <i class="fa fa-user fa-4x text-primary"></i>
                        </div>
                    @endif
                </div>
                <div class="col">
                    <h2 class="mb-1 fw-bold">{{ $student->name }}</h2>
                    <p class="mb-0 opacity-90">
                        <i class="fa fa-graduation-cap me-2"></i>
                        {{ $student->sessionItem->class ?? 'No Class' }} • {{ $student->sessionItem->section ?? 'No Section' }}
                    </p>
                    <p class="mb-0 opacity-75 mt-1">
                        Admission Date: {{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('d M Y') : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-justified border-bottom px-4 pt-3" id="studentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="student-tab" data-bs-toggle="tab" data-bs-target="#student" type="button" role="tab">
                        <i class="fa fa-user me-2"></i> Student Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="parent-tab" data-bs-toggle="tab" data-bs-target="#parent" type="button" role="tab">
                        <i class="fa fa-users me-2"></i> Parent / Guardian / Siblings
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fee-tab" data-bs-toggle="tab" data-bs-target="#fee" type="button" role="tab">
                        <i class="fa fa-money-bill-wave me-2"></i> Fee Details
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4">
                <!-- Student Tab -->
                <div class="tab-pane fade show active" id="student" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6">
                   
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value">{{ $student->date_of_birth?->format('d M Y') ?? 'N/A' }}</div>

                            <div class="info-label">Gender</div>
                            <div class="info-value">{{ $student->gender ?? 'N/A' }}</div>

                            <div class="info-label">B-Form No</div>
                            <div class="info-value">{{ $student->b_form_no ?? 'N/A' }}</div>

                            <div class="info-label">Religion</div>
                            <div class="info-value">{{ $student->religion ?? 'N/A' }}</div>

                        </div>

                        <div class="col-md-6">
                            <div class="info-label">Domicile</div>
                            <div class="info-value">{{ $student->domicile ?? 'N/A' }}</div>

                            <div class="info-label">Previous School</div>
                            <div class="info-value">{{ $student->previous_school ?? 'N/A' }}</div>

                            <div class="info-label">Last Fee Paid Upto</div>
                            <div class="info-value">{{ $student->last_fee_paid_upto?->format('d M Y') ?? 'N/A' }}</div>

                            <div class="info-label">Caste</div>
                            <div class="info-value">{{ $student->caste ?? 'N/A' }}</div>

                        </div>
                    </div>
                </div>

                <!-- Parent/Guardian Tab -->
                <div class="tab-pane fade" id="parent" role="tabpanel">
                    <div class="row g-4">
                        <!-- Father -->
                        @if($student->parent)
                            <div class="col-md-6">
                                <div class="card border-0 bg-light shadow-sm h-100">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="fa fa-male me-2"></i>Father Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="info-label">Name</div>
                                        <div class="info-value">{{ $student->parent->father_name ?? 'N/A' }}</div>

                                        <div class="info-label">NIC / CNIC</div>
                                        <div class="info-value">{{ $student->parent->father_nic ?? 'N/A' }}</div>

                                        <div class="info-label">Occupation</div>
                                        <div class="info-value">{{ $student->parent->occupation ?? 'N/A' }}</div>

                                        <div class="info-label">Icome</div>
                                        <div class="info-value">RS {{ $student->parent->income ?? 'N/A' }}</div>

                                        <div class="info-label">Contact</div>
                                        <div class="info-value">{{ $student->parent->contact_no ?? 'N/A' }}</div>

                                        <div class="info-label">Address</div>
                                        <div class="info-value">{{ $student->parent->address ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mother -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light shadow-sm h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fa fa-female me-2"></i>Mother Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="info-label">Name</div>
                                        <div class="info-value">{{ $student->parent->mother_name ?? 'N/A' }}</div>

                                        <div class="info-label">Occupation</div>
                                        <div class="info-value">{{ $student->parent->occupation ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    No parent information available.
                                </div>
                            </div>
                        @endif

                        <!-- Guardians -->
                        <div class="col-12 mt-4">
                            <h5 class="mb-3 fw-bold"><i class="fa fa-user-shield me-2"></i>Guardians</h5>
                            <div class="guardian-item bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-label">Name</div>
                                        <div class="info-value">{{ $student->guardian->guardian_name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-label">Relation</div>
                                        <div class="info-value">{{ $student->guardian->relation ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-label">Contact</div>
                                        <div class="info-value">{{ $student->guardian->contact_no ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="info-label">NIC / CNIC</div>
                                    <div class="info-value">{{ $student->guardian->guardian_nic ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Siblings -->
                        <div class="col-12 mt-4">
                            <h5 class="mb-3 fw-bold"><i class="fa fa-users me-2"></i>Sibings</h5>
                            <div class="guardian-item bg-light p-3 rounded">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-label">Sibling Name</div>
                                        <div class="info-value">{{ $student->sibling->sibling_name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-label">Sibling Class</div>
                                        <div class="info-value">{{ $student->sibling->sibling_class ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-label">Sibling Section</div>
                                        <div class="info-value">{{ $student->sibling->sibling_section ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="info-label">Sibling In Dps</div>
                                    <div class="info-value">{{ $student->sibling->sibling_in_dps ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Fee Details Tab -->
                <div class="tab-pane fade" id="fee" role="tabpanel">
                    <h5 class="text-success mb-4"><i class="fa fa-money-bill-wave me-2"></i>Admission Fee Details</h5>

                    @if($student->total_fee_paid > 0)
                        <div class="row g-4">
                            <div class="col-md-3">
                                <div class="fee-item">
                                    <div class="info-label">Admission Fee</div>
                                    <div class="info-value">Rs. {{ number_format($student->admission_fee ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fee-item">
                                    <div class="info-label">Tuition Fee</div>
                                    <div class="info-value">Rs. {{ number_format($student->tuition_fee ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fee-item">
                                    <div class="info-label">Stationary Fee</div>
                                    <div class="info-value">Rs. {{ number_format($student->stationary_fee ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fee-item">
                                    <div class="info-label">Library Fee</div>
                                    <div class="info-value">Rs. {{ number_format($student->library_fee ?? 0, 2) }}</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="fee-item">
                                    <div class="info-label">Sports Fund</div>
                                    <div class="info-value">Rs. {{ number_format($student->sports_fund ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fee-item">
                                    <div class="info-label">Security Deposit</div>
                                    <div class="info-value">Rs. {{ number_format($student->security_deposit ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fee-item">
                                    <div class="info-label">Dev Fund</div>
                                    <div class="info-value">Rs. {{ number_format($student->development_fund ?? 0, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="fee-item">
                                    <div class="info-label">Misc. Charges</div>
                                    <div class="info-value">Rs. {{ number_format($student->misc_charges ?? 0, 2) }}</div>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <div class="fee-total">
                                    <strong>TOTAL AMOUNT PAID</strong> 
                                    <span class="underline-long">
                                        Rs. {{ number_format(
                                            ($student->admission_fee ?? 0) +
                                            ($student->tuition_fee ?? 0) +
                                            ($student->stationary_fee ?? 0) +
                                            ($student->library_fee ?? 0) +
                                            ($student->sports_fund ?? 0) +
                                            ($student->security_deposit ?? 0) +
                                            ($student->development_fund ?? 0) +
                                            ($student->misc_charges ?? 0),
                                            2
                                        ) }}
                                    </span><br>
                                    @if($student->receipt_no)
                                        <br><small>Receipt No: {{ $student->receipt_no }}</small>
                                    @endif
                                    @if($student->fee_paid_date)
                                        <br><small>Paid on: {{ \Carbon\Carbon::parse($student->fee_paid_date)->format('d M Y') }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fa fa-money-bill-wave fa-3x mb-3 text-muted"></i>
                            <h5 class="text-muted">No fee details recorded</h5>
                            <p>Fee information was not entered during admission.</p>
                            <small>Click "Edit Details" to add fee records.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="card-footer bg-light border-0 text-end p-4">
            <a href="{{ route('parent.student.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fa fa-arrow-left me-1"></i> Back to List
            </a>
          
        </div>
    </div>

</div>

@endsection