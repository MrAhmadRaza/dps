@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')
<style>
    body {
        background: #f4f7fb;
    }

    .font-arial {
        font-family: Arial, sans-serif;
    }

    /* Full Width Card */
    #admissionForm {
        background: #ffffff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 35px rgba(0,0,0,0.06);
        width: 100%;
    }

    h2.text-primary {
        color: #0b3d91;
        letter-spacing: 0.5px;
    }

    /* =============================== Form Styling ================================ */
    label {
        font-weight: 500;
        margin-bottom: 6px;
        color: #34495e;
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid #dbe3ff;
        padding: 0.65rem 1rem;
        font-size: 0.95rem;
        background-color: #f9fbff;
        transition: 0.25s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        background-color: #fff;
        box-shadow: 0 0 0 0.15rem rgba(13,110,253,0.15);
    }

    /* Validation */
    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.1rem rgba(220,53,69,0.15);
    }

    .invalid-feedback {
        font-size: 0.8rem;
    }

    /* =============================== Progress Steps ================================ */
    .step-progress {
        margin-bottom: 40px;
    }

    .step {
        text-align: center;
        flex: 1;
        position: relative;
    }

    .step .circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9efff;
        color: #6c757d;
        line-height: 40px;
        font-weight: 600;
        margin: 0 auto 8px;
        transition: 0.3s ease;
    }

    .step .label {
        font-size: 0.85rem;
        color: #6c757d;
    }

    .step.active .circle, .step.completed .circle {
        background: linear-gradient(135deg, #0d6efd, #4e8cff);
        color: #fff;
    }

    .step.active .label, .step.completed .label {
        color: #0d6efd;
        font-weight: 600;
    }

    .step:not(:last-child)::after {
        content: "";
        position: absolute;
        top: 20px;
        left: 50%;
        width: 100%;
        height: 3px;
        background: #e9efff;
        z-index: -1;
    }

    .step.completed:not(:last-child)::after {
        background: #0d6efd;
    }

    /* =============================== Webcam Styling ================================ */
    #webcam, #photoPreview {
        width: 240px;
        height: 190px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #dbe3ff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }

    #webcam { display: none; }

    /* =============================== Buttons ================================ */
    .btn-primary {
        background: linear-gradient(135deg, #0d6efd, #4e8cff);
        border: none;
        padding: 0.55rem 1.8rem;
        /* border-radius: 10px; */
        font-weight: 500;
        transition: 0.25s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(13,110,253,0.25);
    }

    .btn-success {
        background: linear-gradient(135deg, #198754, #28c76f);
        border: none;
        padding: 0.55rem 1.8rem;
        border-radius: 10px;
        font-weight: 500;
    }

    .btn-secondary {
        border-radius: 10px;
        padding: 0.5rem 1.6rem;
    }

    /* Section Titles */
    .tab-pane h5 {
        font-weight: 600;
        margin-bottom: 25px;
        color: #0b3d91;
        border-left: 4px solid #0d6efd;
        padding-left: 12px;
    }
</style>

<div class="container">
    <div class="page-inner">
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
            <h3 class="mb-2 mb-md-0 font-arial ">Add Admission</h3>
            <a href="{{ route('admin.admission.index') }}" class="btn btn-primary">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>
       @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>There were some problems with your input:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <!-- Close Button -->
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Session Msg --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center"
                role="alert">
                <strong>{{ session('success') }}</strong>

                @if(session('admission_student_id'))
                    <a href="{{ route('admin.print.admission', ['id' => session('admission_student_id')]) }}"
                    target="_blank"
                    class="btn btn-primary btn-sm ms-3">
                        <i class="fas fa-print"></i> Print Application
                    </a>
                @endif

                @if(session('admission_challan_id'))
                    <a href="{{ route('admin.challan.print', session('admission_challan_id')) }}"
                    target="_blank"
                    class="btn btn-success btn-sm ms-2">
                        <i class="fas fa-file-invoice"></i> Print Challan
                    </a>
                @endif

                <button type="button"
                        class="btn-close ms-auto"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>
            </div>
        @endif
        {{-- End Session Msg --}}
        <form method="POST" action="{{ route('admin.admission.submit') }}" id="admissionForm" novalidate enctype="multipart/form-data">
            @csrf
            <!-- Progress Steps -->
            <div class="step-progress d-flex justify-content-between mb-4">
                <div class="step active" data-step="0">
                    <div class="circle">1</div>
                    <div class="label">Student & Parent</div>
                </div>
                <div class="step" data-step="1">
                    <div class="circle">2</div>
                    <div class="label">Guardian & Siblings</div>
                </div>
                <div class="step" data-step="2">
                    <div class="circle">3</div>
                    <div class="label">Admission Details</div>
                </div>
            </div>

            <div class="tab-content">

                <!-- STEP 1: Student & Parent -->
                <div class="tab-pane fade show active">
                    <h5 class="text-primary mb-3">Student Information</h5>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label>Student Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{old('name')}}" class="form-control" placeholder="Full Name" required>
                            <div class="invalid-feedback">Please enter the student's full name.</div>
                        </div>

                        <div class="col-md-6">
                            <label>Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" value="{{old('date_of_birth')}}" name="date_of_birth" class="form-control" required>
                            <div class="invalid-feedback">Please select date of birth.</div>
                        </div>

                        <div class="col-md-6">
                            <label>Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="" {{ old('gender') ? '' : 'selected' }}>Select</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            <div class="invalid-feedback">Please select gender.</div>
                        </div>

                        <div class="col-md-6">
                            <label>B-Form Number (Optional)</label>
                            <input type="text" name="b_form_no" id="b_form_no" value="{{old('b_form_no')}}" class="form-control" placeholder="12345-1234567-1"
                                maxlength="15"
                                inputmode="numeric">
                        </div>

                        <div class="col-md-6">
                            <label>Religion (Optional)</label>
                            <input type="text" name="religion" value="{{old('religion')}}" class="form-control" placeholder="Religion">
                        </div>

                        <div class="col-md-6">
                            <label>Caste (Optional)</label>
                            <input type="text" name="caste" value="{{old('caste')}}" class="form-control" placeholder="Caste">
                        </div>

                        <div class="col-md-6">
                            <label>Domicile (Optional)</label>
                            <input type="text" name="domicile" value="{{old('domicile')}}" class="form-control" placeholder="Domicile ">
                        </div>
                        <!-- Parent Fields -->
                       
                        <div class="col-md-6">
                            <label>Father NIC <span class="text-danger">*</span></label>
                            <input type="text" name="father_nic" id="father_nic" value="{{old('father_nic')}}" class="form-control" placeholder="12345-1234567-1" maxlength="15"
                                inputmode="numeric" required>
                        </div>

                         <div class="col-md-6">
                            <label>Father Name <span class="text-danger">*</span></label>
                            <input type="text" name="father_name" id="father_name" value="{{old('father_name')}}" class="form-control" placeholder=" Father Name" required>
                            <div class="invalid-feedback">Please enter father's full name.</div>
                        </div>

                         <div class="col-md-6">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="text" name="password" id="password" readonly value="{{old('password')}}" class="form-control" placeholder="password "">
                            </div>

                        <div class="col-md-6">
                            <label>Mother Name (Optional)</label>
                            <input type="text" name="mother_name" id="mother_name" value="{{old('mother_name')}}" class="form-control" placeholder="Mother Name ">
                        </div>

                        <div class="col-md-6">
                            <label>Occupation <span class="text-danger">*</span></label>
                            <input type="text" name="occupation" id="occupation" value="{{old('occupation')}}" class="form-control" placeholder="Occupation" required>
                            <div class="invalid-feedback">Please enter occupation.</div>
                        </div>

                         <div class="col-md-6">
                            <label>Income (Optional)</label>
                            <input type="text" name="income" id="income" value="{{old('income')}}" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label>Contact Number <span class="text-danger">*</span></label>
                            <input type="tel" name="contact_no" id="contact_no" value="{{old('contact_no')}}" class="form-control"  placeholder="0300-1234567"
                                maxlength="12"
                                inputmode="numeric"
                                required>
                            <div class="invalid-feedback">Please enter a contact number.</div>
                        </div>

                         <div class="col-md-6">
                            <label>Address <span class="text-danger">*</span></label>
                            <textarea name="address" id="address" class="form-control" rows="2" placeholder="Address" required>{{ old('address') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label>Status <span class="text-danger">*</span></label>

                            <select name="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>

                            <div class="invalid-feedback">
                                Please select status.
                            </div>
                        </div>

                        <!-- Webcam -->
                        <div class="col-md-6">
                            <label>Capture Student Photo <span class="text-danger">*</span></label>
                            <video id="webcam" autoplay playsinline style="display:none; width:100%; border-radius:5px;"></video>
                            <canvas id="canvas" style="display:none;"></canvas>
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" id="startCameraBtn" class="btn btn-info btn-sm">Start Camera</button>
                                <button type="button" id="captureBtn" class="btn btn-success btn-sm" disabled>Capture Photo</button>
                            </div>
                            <input type="hidden" name="student_photo" id="student_photo" value="{{ old('student_photo') }}" required>
                            <img id="photoPreview" class="mt-2" 
                                src="{{ old('student_photo') }}"
                                style="{{ old('student_photo') ? 'display:block;' : 'display:none;' }} max-width:100%; border:1px solid #ccc; border-radius:5px;">
                            <div class="invalid-feedback">Please capture student photo.</div>
                        </div>

                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-primary prev-step d-none">← Previous</button>
                        <button type="button" class="btn btn-primary next-step">Next →</button>
                    </div>
                </div>

                <!-- STEP 2: Guardian & Siblings -->
                <div class="tab-pane fade">
                    <h5 class="text-primary mb-3">Guardian & Siblings Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Guardian Name (Optional)</label>
                            <input type="text" name="guardian_name" value="{{old('guardian_name')}}" class="form-control" placeholder="Guardian Name">
                        </div>
                        <div class="col-md-6">
                            <label>Relation (Optional)</label>
                            <input type="text" name="relation" value="{{old('relation')}}" class="form-control" placeholder="Relation ">
                        </div>
                        <div class="col-md-6">
                            <label>Guardian NIC (Optional)</label>
                            <input type="text" name="guardian_nic" id="guardian_nic" value="{{old('guardian_nic')}}" class="form-control"  placeholder="12345-1234567-1"
                                maxlength="15"
                                inputmode="numeric">
                        </div>
                        <div class="col-md-6">
                            <label>Contact Number (Optional)</label>
                            <input type="tel"  id="contact_no" value="{{old('guardian_contact_no')}}" name="guardian_contact_no" class="form-control"  placeholder="0300-1234567"
                                maxlength="12"
                                inputmode="numeric">
                        </div>

                         <div class="col-md-6">
                            <label>Siblings Names (Optional)</label>
                            <input type="text" name="sibling_name" value="{{old('sibling_name')}}" class="form-control" placeholder="Siblings Names">
                        </div>

                         <div class="col-md-6">
                            <label>Class (Optional)</label>
                            <input type="text" name="sibling_class" value="{{old('sibling_class')}}" class="form-control" placeholder="Class">
                        </div>
                         <div class="col-md-6">
                            <label>Section (Optional)</label>
                            <input type="text" name="sibling_section" value="{{old('sibling_section')}}" class="form-control" placeholder="Section">
                        </div>
                        <div class="col-md-6">
                            <label>Siblings in School</label>
                            <input type="number" name="sibling_in_dps" value="{{old('sibling_in_dps')}}" class="form-control" min="0" value="0">
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-primary prev-step">← Previous</button>
                        <button type="button" class="btn btn-primary next-step">Next →</button>
                    </div>
                </div>

                <!-- STEP 3: Admission Details -->
                <div class="tab-pane fade">
                    <h5 class="text-primary mb-3">Admission Details</h5>
                    <div class="row g-3">
                        {{-- Session --}}
                        <div class="col-md-6">
                            <label>Session <span class="text-danger">*</span></label>
                            <select name="academic_session_id" value="{{old('academic_session_id')}}" id="exit_academic_session_id"  class="form-select" required>
                            <option value="">Select</option>
                                @forelse ($sessions as $session_list)     
                                    <option value="{{$session_list->id}}"> {{$session_list->session_name}}</option>
                                    @empty
                                    <option value="" disabled>--Not Available--</option>
                                @endforelse
                            </select>
                        </div>
                        {{-- Section & Class --}}
                        <div class="col-md-6">
                            <label>Class & Section <span class="text-danger">*</span></label>
                            <select name="session_item_id" value="{{old('session_item_id')}}" id="session_item_id" class="form-select" required>
                                <option value="">Select</option>
                               
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label>Previous School <span class="text-danger">*</span></label>
                            <input type="text" name="previous_school" value="{{old('previous_school')}}" class="form-control" placeholder="Previous School" required>
                        </div>
                        <div class="col-md-6">
                            <label>Last Fee Paid Upto (Optional)</label>
                            <input type="date" name="last_fee_paid_upto" value="{{old('last_fee_paid_upto')}}" class="form-control" placeholder="Last Fee Paid Upto">
                        </div>
                        <div class="col-md-6">
                            <label>Fees Paid Last Institution?</label>
                            <select name="fees_paid_last_institution" class="form-select" required>
                                <option value="">Select</option>
                                <option value="1" {{ old('fees_paid_last_institution') == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('fees_paid_last_institution') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Games / Sports (Optional)</label>
                            <input type="text" name="games_sports" value="{{old('games_sports')}}" class="form-control" placeholder="Games / Sports ">
                        </div>
                        <div class="col-md-6">
                            <label>Extra Curricular Activities (Optional)</label>
                            <input type="text" name="extra_curricular" value="{{old('extra_curricular')}}" class="form-control" placeholder="Extra Curricular Activities">
                        </div>
                        <div class="col-md-6">
                            <label>Admission Date (Optional)</label>
                            <input type="date" name="admission_date" value="{{old('admission_date')}}" class="form-control">
                        </div>
                       <!-- Fee Details -->
                        <div class="col-md-12 mt-4" id="fee-details-section" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="text-primary mb-0">Fee Details</h5>
                                {{-- Discount Toggle --}}
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="discount" value="1" id="discount_toggle">
                                    <label class="form-check-label fw-bold" for="discount_toggle"> Discount </label>
                                </div>
                            </div>
                            {{-- Discount Input --}}
                            <div class="row mb-3"id="discount-section" style="display: none;">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Discount Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="discount_amount" id="discount_amount" class="form-control"
                                        step="0.01"
                                        min="0"
                                        value="{{ old('discount_amount', 0) }}"
                                        placeholder="0.00" required>
                                </div>

                                  {{-- Due Date --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Due Date
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="discount_due_date" value="{{ old('due_date') }}" class="form-control" required>
                                    @error('discount_due_date')
                                        <div class="text-danger mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                {{-- Notes --}}
                                <div class="col-12 mb-3">
                                    <label class="form-label">
                                        Notes
                                    </label>

                                    <textarea name="discount_notes" 
                                            rows="3"
                                            class="form-control">{{ old('notes') }}</textarea>
                                    @error('discount_notes')
                                        <div class="text-danger mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            {{-- Voucher Fee Details --}}
                            <div class="row" id="fee-details-container">
                                {{-- Voucher items AJAX --}}
                            </div>
                            <div class="row mt-3">
                                {{-- Total Amount --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Total Amount </label>
                                    <input type="number" name="total_amount" id="total_amount" class="form-control" value="" readonly>
                                </div>
                                {{-- Receipt number --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Receipt (Optional)</label>
                                    <input type="text" name="receipt_no"  class="form-control" value="{{old('receipt_no')}}">
                                </div>
                                {{-- Fee Paid Date --}}
                                <div class="col-md-4">
                                    <label>Fee Paid Date (Optional)</label>
                                    <input type="date" name="fee_paid_date" value="{{ old('fee_paid_date') }}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-primary prev-step">← Previous</button>
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const steps = Array.from(document.querySelectorAll('.tab-pane'));
        const progressSteps = Array.from(document.querySelectorAll('.step-progress .step'));
        const nextButtons = document.querySelectorAll('.next-step');
        const prevButtons = document.querySelectorAll('.prev-step');

        let currentStep = 0;

        // ==========================================================
        // Webcam
        // ==========================================================

        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const startCameraBtn = document.getElementById('startCameraBtn');
        const captureBtn = document.getElementById('captureBtn');
        const photoInput = document.getElementById('student_photo');
        const photoPreview = document.getElementById('photoPreview');

        let stream = null;

        if (photoInput.value) {
            photoInput.classList.remove('is-invalid');
            photoInput.classList.add('is-valid');
        }

        if (startCameraBtn) {
            startCameraBtn.addEventListener('click', async () => {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    video.srcObject = stream;
                    video.style.display = 'block';
                    captureBtn.disabled = false;
                    startCameraBtn.disabled = true;
                } catch (err) {
                    alert('Webcam not accessible: ' + err);
                }
            });
        }

        if (captureBtn) {
            captureBtn.addEventListener('click', () => {
                const maxWidth = 800;
                const maxHeight = 800;
                const quality = 0.7;

                let width = video.videoWidth;
                let height = video.videoHeight;

                const ratio = Math.min(maxWidth / width, maxHeight / height, 1);

                width = Math.round(width * ratio);
                height = Math.round(height * ratio);

                canvas.width = width;
                canvas.height = height;

                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, width, height);

                const dataURL = canvas.toDataURL('image/jpeg', quality);

                photoInput.value = dataURL;
                photoPreview.src = dataURL;
                photoPreview.style.display = 'block';

                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    video.style.display = 'none';
                    captureBtn.disabled = true;
                }

                photoInput.classList.remove('is-invalid');
                photoInput.classList.add('is-valid');
            });
        }


        // ==========================================================
        // Show Step
        // ==========================================================

        function showStep(i) {

            steps.forEach((step, index) => {

                step.classList.remove('show', 'active');

                if (index === i) {
                    step.classList.add('show', 'active');
                }
            });

            prevButtons.forEach(button => {

                button.style.display =
                    i === 0 ? 'none' : 'inline-block';
            });

            progressSteps.forEach((step, index) => {

                step.classList.remove('active', 'completed');

                if (index < i) {
                    step.classList.add('completed');
                }

                if (index === i) {
                    step.classList.add('active');
                }
            });
        }


        // ==========================================================
        // Pattern Configuration
        // ==========================================================

        const patterns = {

            father_nic: /^\d{5}-\d{7}-\d$/,

            b_form_no: /^\d{5}-\d{7}-\d$/,

            guardian_nic: /^\d{5}-\d{7}-\d$/,

            contact_no: /^03\d{2}-\d{7}$/,

            guardian_contact_no: /^03\d{2}-\d{7}$/
        };


        // ==========================================================
        // Auto Format NIC / B-Form
        // 12345-1234567-1
        // ==========================================================

        function formatNic(input) {

            let value = input.value.replace(/\D/g, '');

            // Maximum 13 digits
            value = value.substring(0, 13);

            if (value.length > 12) {

                value =
                    value.substring(0, 5) +
                    '-' +
                    value.substring(5, 12) +
                    '-' +
                    value.substring(12);

            } else if (value.length > 5) {

                value =
                    value.substring(0, 5) +
                    '-' +
                    value.substring(5);
            }

            input.value = value;
        }


        // ==========================================================
        // Auto Format Contact
        // 0300-1234567
        // ==========================================================

        function formatContact(input) {

            let value = input.value.replace(/\D/g, '');

            // Maximum 11 digits
            value = value.substring(0, 11);

            if (value.length > 4) {

                value =
                    value.substring(0, 4) +
                    '-' +
                    value.substring(4);
            }

            input.value = value;
        }


        // ==========================================================
        // NIC / B-Form / Contact Input Handling
        // ==========================================================

        Object.keys(patterns).forEach(name => {

            const input = document.querySelector(`[name="${name}"]`);

            if (!input) {
                return;
            }

            input.addEventListener('input', function () {

                if (
                    name === 'father_nic' ||
                    name === 'b_form_no' ||
                    name === 'guardian_nic'
                ) {

                    formatNic(this);

                } else if (
                    name === 'contact_no' ||
                    name === 'guardian_contact_no'
                ) {

                    formatContact(this);
                }

                // Remove invalid state while typing
                this.classList.remove('is-invalid');
            });


            input.addEventListener('blur', function () {

                const value = this.value.trim();

                // Optional field
                if (
                    !this.hasAttribute('required') &&
                    value === ''
                ) {

                    this.classList.remove(
                        'is-invalid',
                        'is-valid'
                    );

                    return;
                }

                if (patterns[name].test(value)) {

                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');

                } else {

                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });


        // ==========================================================
        // Step Validation
        // ==========================================================

        function validateStep(step) {

            let valid = true;

            const fields = step.querySelectorAll(
                'input, select, textarea'
            );

            fields.forEach(field => {

                const value = field.value.trim();

                // ------------------------------------------
                // Required Validation
                // ------------------------------------------

                if (
                    field.hasAttribute('required') &&
                    value === ''
                ) {

                    valid = false;

                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');

                    return;
                }


                // ------------------------------------------
                // Optional Empty Field
                // ------------------------------------------

                if (value === '') {

                    field.classList.remove(
                        'is-invalid',
                        'is-valid'
                    );

                    return;
                }


                // ------------------------------------------
                // Pattern Validation
                // ------------------------------------------

                const fieldName = field.getAttribute('name');

                if (
                    fieldName &&
                    patterns[fieldName]
                ) {

                    if (!patterns[fieldName].test(value)) {

                        valid = false;

                        field.classList.add('is-invalid');
                        field.classList.remove('is-valid');

                        return;
                    }
                }


                // ------------------------------------------
                // HTML Pattern Validation
                // ------------------------------------------

                if (field.hasAttribute('pattern')) {

                    const pattern = new RegExp(
                        '^(?:' +
                        field.getAttribute('pattern') +
                        ')$'
                    );

                    if (!pattern.test(value)) {

                        valid = false;

                        field.classList.add('is-invalid');
                        field.classList.remove('is-valid');

                        return;
                    }
                }


                // ------------------------------------------
                // Valid
                // ------------------------------------------

                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            });


            return valid;
        }


        // ==========================================================
        // Next Button
        // ==========================================================

        nextButtons.forEach(button => {

            button.addEventListener('click', () => {

                const step = steps[currentStep];

                if (validateStep(step)) {

                    currentStep = Math.min(
                        currentStep + 1,
                        steps.length - 1
                    );

                    showStep(currentStep);

                } else {

                    const firstInvalid =
                        step.querySelector('.is-invalid');

                    if (firstInvalid) {

                        firstInvalid.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        firstInvalid.focus();
                    }
                }
            });
        });


        // ==========================================================
        // Previous Button
        // ==========================================================

        prevButtons.forEach(button => {

            button.addEventListener('click', () => {

                currentStep = Math.max(
                    currentStep - 1,
                    0
                );

                showStep(currentStep);
            });
        });
        // ==========================================================
        // Initial Step
        // ==========================================================
        showStep(currentStep);
    });
    // Password Generate
    function generatePassword(length = 4) {
        const chars ='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let password = '';
        for (let i = 0; i < length; i++) {
            password += chars[
                Math.floor(
                    Math.random() * chars.length
                )
            ];
        }
        return password;
    }
    const passwordField =document.getElementById('password');
    if (passwordField && !passwordField.value) {

        passwordField.value =
            generatePassword(4);
    }
</script>
<script>
    // Input fields client side valdation check NIC and B Form number
    document.querySelectorAll('#father_nic, #b_form_no, #guardian_nic, #contact_no, #guardian_contact_no').forEach(input => {
        input.addEventListener('input', function () {
            // Sirf numbers rakhein
            let value = this.value.replace(/\D/g, '');
            // Maximum 13 digits
            value = value.substring(0, 13);
            // Format: 12345-1234567-1
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5);
            }
            if (value.length > 13) {
                value = value.substring(0, 13) + '-' + value.substring(13);
            }
            this.value = value;
        });

        input.addEventListener('blur', function () {
            const pattern = /^\d{5}-\d{7}-\d$/;
            if (this.value === '') {
                this.classList.remove('is-valid', 'is-invalid');
            } else if (pattern.test(this.value)) {
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });

    });
    // Auto Fill if Parent records exits
    let parentSearchTimeout;
    function searchParentByNic() {
        const fatherNic = $('#father_nic').val().trim();
        // Sirf complete NIC par search
        const nicPattern = /^\d{5}-\d{7}-\d$/;

        if (!nicPattern.test(fatherNic)) {
            return;
        }
        $.ajax({
            url: "{{ route('admin.search-parant') }}",
            type: "GET",
            data: {
                q: fatherNic
            },
            success: function (data) {
                const passwordField = document.getElementById('password');
                if (data.statuscode === 200) {
                    // Existing Parent
                    $('#father_name').val(data.father_name);
                    $('#mother_name').val(data.mother_name);
                    $('#occupation').val(data.occupation);
                    $('#income').val(data.income);
                    $('#address').val(data.address);
                    $('#contact_no').val(data.contact_no)
                    // Existing parent => password required nahi
                    if (passwordField) {
                        passwordField.value = '';
                        passwordField.removeAttribute('required');
                    }
                } else {
                    // New Parent
                    $('#father_name').val('');
                    $('#mother_name').val('');
                    $('#occupation').val('');
                    $('#income').val('');
                    $('#address').val('');
                    $('#contact_no').val('');

                    // New parent => password generate
                    if (passwordField) {
                        passwordField.value = generatePassword(4);
                        passwordField.setAttribute('required', 'required');
                    }
                }
            },
            error: function (xhr) {
                console.log('Parent search error:', xhr);
            }
        });
    }
    // NIC Input / Paste - Debounce Search
    $('#father_nic').on('input', function () {
        // Previous timeout cancel
        clearTimeout(parentSearchTimeout);
        const value = $(this).val().trim();
        // Complete NIC nahi hai to request nahi
        const nicPattern = /^\d{5}-\d{7}-\d$/;
        if (!nicPattern.test(value)) {
            return;
        }
        // User typing/paste ke baad 800ms wait
        parentSearchTimeout = setTimeout(function () {
            searchParentByNic();
        }, 800);
    });
</script>

<script>
    window.classSectionUrl = "{{ route('admin.admission.class.section') }}";
    window.voucherAmountsUrl = "{{ route('admin.admission.amounts') }}";
    window.selectedAcademicSessionId = "";
    window.selectedSessionItemId = "";
    // Add page par discount OFF
    window.isDiscountEnabled = 0;
    window.discountAmount = 0;
</script>

<script src="{{ asset('backend_assets/js/academic-session.js') }}"></script>
<script src="{{ asset('backend_assets/js/voucher.js') }}"></script>
@endsection
