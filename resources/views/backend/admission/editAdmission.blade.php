@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')
    <style>
        body {
            background: #f4f7fb;
        }

        #admissionForm {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.06);
            width: 100%;
        }

        h2.text-primary {
            color: #0b3d91;
            letter-spacing: 0.5px;
        }

        label {
            font-weight: 500;
            margin-bottom: 6px;
            color: #34495e;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #dbe3ff;
            padding: 0.65rem 1rem;
            font-size: 0.95rem;
            background-color: #f9fbff;
            transition: 0.25s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #0d6efd;
            background-color: #fff;
            box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
        }

        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.15);
        }

        .invalid-feedback {
            font-size: 0.8rem;
        }

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

        .step.active .circle,
        .step.completed .circle {
            background: linear-gradient(135deg, #0d6efd, #4e8cff);
            color: #fff;
        }

        .step.active .label,
        .step.completed .label {
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

        #webcam,
        #photoPreview {
            width: 240px;
            height: 190px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #dbe3ff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        #webcam {
            display: none;
        }

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
            box-shadow: 0 6px 15px rgba(13, 110, 253, 0.25);
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
            <div
                class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <h3 class="mb-2 mb-md-0">Edit Admission</h3>
                <a href="{{ route('admin.admission.index') }}" class="btn btn-primary">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.update.admission', $student->id) }}" id="admissionForm"
                enctype="multipart/form-data">
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
                                <input type="text" name="name" class="form-control"
                                    value="{{ old('name', $student->name) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label>Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="{{ old('date_of_birth', \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d')) }}"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <label>Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="">Select</option>
                                    <option value="Male" {{ old('gender', $student->gender) == 'Male' ? 'selected' : '' }}>Male
                                    </option>
                                    <option value="Female" {{ old('gender', $student->gender) == 'Female' ? 'selected' : '' }}>
                                        Female</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>B-Form Number (Optional)</label>
                                <input type="text" name="b_form_no" id="b_form_no" class="form-control"
                                    value="{{ old('b_form_no', $student->b_form_no) }}" placeholder="12345-1234567-1"
                                    maxlength="15" inputmode="numeric">
                            </div>

                            <div class="col-md-6">
                                <label>Religion (Optional)</label>
                                <input type="text" name="religion" class="form-control"
                                    value="{{ old('religion', $student->religion) }}">
                            </div>

                            <div class="col-md-6">
                                <label>Caste (Optional)</label>
                                <input type="text" name="caste" class="form-control"
                                    value="{{ old('caste', $student->caste) }}">
                            </div>

                            <div class="col-md-6">
                                <label>Domicile (Optional)</label>
                                <input type="text" name="domicile" class="form-control"
                                    value="{{ old('domicile', $student->domicile) }}">
                            </div>

                            <!-- Parent Fields -->
                            

                            <div class="col-md-6">
                                <label>Father NIC <span class="text-danger">*</span></label>
                                <input type="text" id="father_nic" name="father_nic" class="form-control"
                                    value="{{ old('father_nic', $student->parent->father_nic ?? '') }}"
                                    placeholder="12345-1234567-1" maxlength="15" inputmode="numeric" required>
                            </div>

                            <div class="col-md-6">
                                <label>Father Name <span class="text-danger">*</span></label>
                                <input type="text" id="father_name" name="father_name" class="form-control"
                                    value="{{ old('father_name', $student->parent->father_name ?? '') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="text" name="password" id="password" value="{{ old('password') }}"
                                    class="form-control" placeholder="password ">
                            </div>

                            <div class="col-md-6">
                                <label>Mother Name (Optional)</label>
                                <input type="text" id="mother_name" name="mother_name" class="form-control"
                                    value="{{ old('mother_name', $student->parent->mother_name ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label>Occupation <span class="text-danger">*</span></label>
                                <input type="text" id="occupation" name="occupation" class="form-control"
                                    value="{{ old('occupation', $student->parent->occupation ?? '') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label>Income (Optional)</label>
                                <input type="text" id="income" name="income"
                                    value="{{ old('income', $student->parent->income ?? '') }}" class="form-control"
                                    placeholder="0.00">
                            </div>

                            <div class="col-md-6">
                                <label>Contact Number <span class="text-danger">*</span></label>
                                <input type="tel" name="contact_no" id="contact_no"  class="form-control"
                                    value="{{ old('contact_no', $student->parent->contact_no ?? '') }}"
                                    placeholder="0300-1234567" maxlength="12" inputmode="numeric" required>
                            </div>

                            <div class="col-md-6">
                                <label>Address <span class="text-danger">*</span></label>
                                <textarea name="address" id="address" class="form-control" rows="2" required>{{ old('address', $student->parent->address ?? '') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="">Select Status</option>
                                    <option value="active"
                                        {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="inactive"
                                        {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select status.
                                </div>
                            </div>

                            <!-- Photo Preview -->
                            <div class="col-md-6">
                                <label>Student Photo</label>
                                <video id="webcam" autoplay playsinline
                                    style="display:none; width:100%; border-radius:5px;"></video>
                                <canvas id="canvas" style="display:none;"></canvas>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" id="startCameraBtn" class="btn btn-info btn-sm">Start
                                        Camera</button>
                                    <button type="button" id="captureBtn" class="btn btn-success btn-sm"
                                        disabled>Capture Photo</button>
                                </div>

                                <input type="hidden" name="student_photo" id="student_photo" value="">

                                @if ($student->photo_path && file_exists(public_path($student->photo_path)))
                                    <img id="photoPreview" src="{{ asset($student->photo_path) }}" class="mt-2"
                                        style="max-width:100%; border:1px solid #ccc; border-radius:5px;">
                                @else
                                    <img id="photoPreview" class="mt-3" src="{{ asset('default/dummy.png') }}"
                                        style=" max-width:100%; border-radius:5px;">
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-primary prev-step d-none">← Previous</button>
                            <button type="button" class="btn btn-primary next-step">Next →</button>
                        </div>
                    </div>

                    <!-- STEP 2 & STEP 3 -->
                    <!-- Guardian & Siblings -->
                    <div class="tab-pane fade">
                        <h5 class="text-primary mb-3">Guardian & Siblings Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Guardian Name (Optional)</label>
                                <input type="text" name="guardian_name" class="form-control"
                                    value="{{ old('guardian_name', $student->guardian?->guardian_name ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label>Relation (Optional)</label>
                                <input type="text" name="relation" class="form-control"
                                    value="{{ old('relation', $student->guardian?->relation ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label>Guardian NIC (Optional)</label>
                                <input type="text" name="guardian_nic" id="guardian_nic" class="form-control"
                                    value="{{ old('guardian_nic', $student->guardian?->guardian_nic ?? '') }}"
                                    placeholder="12345-1234567-1" maxlength="15" inputmode="numeric">
                            </div>
                            <div class="col-md-6">
                                <label>Contact Number (Optional)</label>
                                <input type="tel" name="guardian_contact_no" id="contact_no" class="form-control"
                                    value="{{ old('guardian_contact_no', $student->guardian?->contact_no ?? '') }}"
                                    placeholder="0300-1234567" maxlength="12" inputmode="numeric">
                            </div>
                            <div class="col-md-6">
                                <label>Siblings Names (Optional)</label>
                                <input type="text" name="sibling_name"
                                    value="{{ old('sibling_name', $student->sibling->sibling_name) }}"
                                    class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label>Class (Optional)</label>
                                <input type="text" name="sibling_class"
                                    value="{{ old('sibling_class', $student->sibling->sibling_class) }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Section (Optional)</label>
                                <input type="text" name="sibling_section"
                                    value="{{ old('sibling_section', $student->sibling->sibling_section) }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Siblings in School</label>
                                <input type="number" name="sibling_in_dps"
                                    value="{{ old('sibling_in_dps', $student->sibling->sibling_in_dps) }}"
                                    class="form-control" min="0" value="0">
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
                                <select name="academic_session_id" id="exit_academic_session_id" class="form-select" required>
                                    <option value="">Select</option>
                                    @forelse ($sessions as $session_list)
                                        <option value="{{ $session_list->id }}"
                                            {{ $session_list->id == $student->academic_session_id ? 'selected' : '' }}>
                                            {{ $session_list->session_name }}
                                        </option>
                                    @empty
                                        <option value="" disabled>--Not Available--</option>
                                    @endforelse
                                </select>
                            </div>
                            {{-- Section & Class --}}
                            <div class="col-md-6">
                                <label>Section & Class <span class="text-danger">*</span></label>
                                <select name="session_item_id" id="session_item_id" class="form-select" required>
                                    <option value="">Select</option>

                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Previous School <span class="text-danger">*</span></label>
                                <input type="text" name="previous_school" class="form-control"
                                    value="{{ old('previous_school', $student->previous_school) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label>Last Fee Paid Upto</label>
                                <input type="date" name="last_fee_paid_upto" class="form-control"
                                    value="{{ old('last_fee_paid_upto', \Carbon\Carbon::parse($student->last_fee_paid_upto)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6">
                                <label>Fees Paid Last Institution?</label>
                                <select name="fees_paid_last_institution" class="form-select" required>
                                    <option value="">Select</option>
                                    <option value="1"
                                        {{ old('fees_paid_last_institution', $student->fees_paid_last_institution) == '1' ? 'selected' : '' }}>
                                        Yes</option>
                                    <option value="0"
                                        {{ old('fees_paid_last_institution', $student->fees_paid_last_institution) == '0' ? 'selected' : '' }}>
                                        No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Games / Sports</label>
                                <input type="text" name="games_sports" class="form-control"
                                    value="{{ old('games_sports', $student->games_sports) }}">
                            </div>
                            <div class="col-md-6">
                                <label>Extra Curricular Activities</label>
                                <input type="text" name="extra_curricular" class="form-control"
                                    value="{{ old('extra_curricular', $student->extra_curricular) }}">
                            </div>
                            <div class="col-md-6">
                                <label>Admission Date</label>
                                <input type="date" name="admission_date" class="form-control"
                                    value="{{ old('admission_date', \Carbon\Carbon::parse($student->admission_date)->format('Y-m-d')) }}">
                            </div>
                            <!-- Fee Details (Optional) -->
                            <div class="col-md-12 mt-4" id="fee-details-section" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="text-primary mb-0">Fee Details</h5>
                                    {{-- Discount Toggle --}}
                                    <input type="hidden" name="discount" value="0">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"  name="discount"  id="discount_toggle"
                                            value="1"
                                            {{ old('discount', $student->discount ?? 0) == 1 ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold"for="discount_toggle">Discount</label>
                                    </div>
                                </div>
                                {{-- Discount Input --}}
                                <div class="row mb-3" id="discount-section" style="display: none;">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Discount Amount</label>
                                        <input type="number" name="discount_amount" id="discount_amount" class="form-control"
                                            step="0.01"
                                            min="0"
                                            value="{{ old('discount_amount', $student->discount_amount ?? 0) }}"
                                            placeholder="0.00">
                                    </div>
                                      {{-- Due Date --}}
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Due Date
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="discount_due_date" value="{{ old('discount_due_date',$student->discount_due_date ? \Carbon\Carbon::parse($student->discount_due_date)->format('Y-m-d') : '') }}" class="form-control">
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
                                                class="form-control">{{ old('discount_notes' ,$student->discount_notes ?? '') }}</textarea>
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
                                        <label class="form-label fw-bold">Total Amount</label>
                                        <input type="number" name="total_amount" id="total_amount" class="form-control" value="" readonly>
                                    </div>
                                    {{-- Receipt number --}}
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Receipt (Optional)</label>
                                        <input type="text" name="receipt_no"  class="form-control" value="{{old('receipt_no',$student->receipt_no)}}">
                                    </div>
                                    {{-- Fee Paid Date --}}
                                    <div class="col-md-4">
                                        <label>Fee Paid Date (Optional)</label>
                                    <input type="date" name="fee_paid_date" value="{{ old('fee_paid_date', $student->fee_paid_date ? \Carbon\Carbon::parse($student->fee_paid_date)->format('Y-m-d') : '') }}" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between">
                            <button type="button" class="btn btn-primary prev-step">← Previous</button>
                            <button type="submit" class="btn btn-success">Update Admission</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')

<script>
    // Webcam for Edit Form
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('canvas');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const photoInput = document.getElementById('student_photo');
    const photoPreview = document.getElementById('photoPreview');
    let stream = null;

    document.addEventListener('DOMContentLoaded', function() {

        // ELEMENTS
        const steps = Array.from(document.querySelectorAll('.tab-pane'));
        const progressSteps = Array.from(document.querySelectorAll('.step-progress .step'));
        const nextButtons = document.querySelectorAll('.next-step');
        const prevButtons = document.querySelectorAll('.prev-step');
        let currentStep = 0;
        // WEBCAM
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const startCameraBtn = document.getElementById('startCameraBtn');
        const captureBtn = document.getElementById('captureBtn');
        const photoInput = document.getElementById('student_photo');
        const photoPreview = document.getElementById('photoPreview');
        let stream = null;
        // Start Camera
        if (startCameraBtn) {
            startCameraBtn.addEventListener('click', async function() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({video: true});
                    video.srcObject = stream;
                    video.style.display = 'block';
                    captureBtn.disabled = false;
                    startCameraBtn.disabled = true;
                } catch (err) {
                    alert('Webcam not accessible: ' + err);
                }
            });
        }
        // Capture Photo
        if (captureBtn) {
            captureBtn.addEventListener('click', function() {

                const maxWidth = 800;
                const maxHeight = 800;
                const quality = 0.7;

                let width = video.videoWidth;
                let height = video.videoHeight;

                const ratio = Math.min(
                    maxWidth / width,
                    maxHeight / height,
                    1
                );

                width = Math.round(width * ratio);
                height = Math.round(height * ratio);

                canvas.width = width;
                canvas.height = height;

                const context = canvas.getContext('2d');

                context.drawImage(
                    video,
                    0,
                    0,
                    width,
                    height
                );

                const dataURL = canvas.toDataURL(
                    'image/jpeg',
                    quality
                );

                // New photo
                photoInput.value = dataURL;

                // Preview
                photoPreview.src = dataURL;
                photoPreview.style.display = 'block';

                // Stop camera
                if (stream) {
                    stream
                        .getTracks()
                        .forEach(track => track.stop());

                    stream = null;
                }

                video.style.display = 'none';
                captureBtn.disabled = true;
                startCameraBtn.disabled = false;
            });
        }
        // STEP DISPLAY
        function showStep(index) {
            steps.forEach((step, idx) => {
                step.classList.remove('show', 'active');
                if (idx === index) {
                    step.classList.add('show', 'active');
                }
            });
            // Previous button
            prevButtons.forEach(button => {
                button.style.display = index === 0 ? 'none' :  'inline-block';
            });
            // Progress
            progressSteps.forEach((step, idx) => {
                step.classList.remove('active','completed');
                if (idx < index) {
                    step.classList.add('completed');
                }
                if (idx === index) {
                    step.classList.add('active');
                }
            });
        }
        // STEP VALIDATION
        function validateStep(step) {
            let valid = true;
            step.querySelectorAll(
                'input[required], select[required], textarea[required]'
            ).forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            // Pattern validation
            const patternFields = step.querySelectorAll(
                '#father_nic, #b_form_no, #guardian_nic, #contact_no, #guardian_contact_no'
            );
            patternFields.forEach(field => {
                if (!field.value) {
                    if (field.required) {
                        valid = false;
                        field.classList.add('is-invalid');
                    }
                    return;
                }
                let pattern;
                // NIC / B-Form
                if (
                    field.id === 'father_nic' ||
                    field.id === 'b_form_no' ||
                    field.id === 'guardian_nic'
                ) {
                    pattern = /^\d{5}-\d{7}-\d$/;
                }
                // Contact
                else {
                    pattern = /^03\d{2}-\d{7}$/;
                }
                if (!pattern.test(field.value)) {
                    valid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }

            });
            return valid;
        }

        // NEXT BUTTON
        nextButtons.forEach(button => {
            button.addEventListener('click', function() {
                const current = steps[currentStep];
                if (validateStep(current)) {
                    currentStep = Math.min(
                        currentStep + 1,
                        steps.length - 1
                    );
                    showStep(currentStep);
                }
            });
        });
        // PREVIOUS BUTTON
        prevButtons.forEach(button => {
            button.addEventListener('click', function() {
                currentStep = Math.max(
                    currentStep - 1,
                    0
                );
                showStep(currentStep);
            });
        });
        // Initial Step
        showStep(currentStep);
        // =====================================================
        // NIC / B-FORM / CONTACT AUTO FORMAT
        // =====================================================
        const formattedFields = document.querySelectorAll(
            '#father_nic, #b_form_no, #guardian_nic, #contact_no, #guardian_contact_no'
        );
        formattedFields.forEach(input => {
            // Input
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                // NIC / B-FORM
                // 12345-1234567-1
                if (
                    this.id === 'father_nic' ||
                    this.id === 'b_form_no' ||
                    this.id === 'guardian_nic'
                ) {
                    // Maximum 13 digits
                    value = value.substring(0, 13);
                    if (value.length > 5) {
                        value = value.substring(0, 5) + '-' + value.substring(5);
                    }
                    if (value.length > 13) {
                        value = value.substring(0, 13) + '-' + value.substring(13);
                    }
                }
                // CONTACT
                // 0300-1234567
                if (
                    this.id === 'contact_no' ||
                    this.id === 'guardian_contact_no'
                ) {
                    // Maximum 11 digits
                    value = value.substring(0, 11);
                    if (value.length > 4) {
                        value =  value.substring(0, 4) +  '-' + value.substring(4);
                    }
                }
                this.value = value;
                // Remove validation while typing
                this.classList.remove('is-invalid', 'is-valid');
            });
            // Blur Validation
            input.addEventListener('blur', function() {
                let pattern;
                // NIC / B-Form
                if (
                    this.id === 'father_nic' ||
                    this.id === 'b_form_no' ||
                    this.id === 'guardian_nic'
                ) {
                    pattern = /^\d{5}-\d{7}-\d$/;
                }
                // Contact
                else {
                    pattern = /^03\d{2}-\d{7}$/;
                }
                // Optional empty fields
                if (
                    this.value === '' &&
                    !this.required
                ) {
                    this.classList.remove(
                        'is-invalid',
                        'is-valid'
                    );
                    return;
                }
                if (pattern.test(this.value)) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });

        // SESSION / CLASS & SECTION
        const academicSession = document.getElementById('academic_session_id');
        if (academicSession) {
            const academic_session_id = academicSession.value;
            const selected_session_item_id = "{{ $student->session_item_id ?? '' }}";
            const route_url = "{{ route('admin.admission.class.section') }}";
            const token = $('meta[name="csrf-token"]').attr('content');
            // Edit page: Load existing class/section
            if (academic_session_id) {
                loadSessionItems(
                    route_url,
                    token,
                    academic_session_id,
                    selected_session_item_id
                );
            }
            // Session change
            $('#academic_session_id').on('change', function() {
                const sessionId = $(this).val()
                if (sessionId) {
                    loadSessionItems(
                        route_url,
                        token,
                        sessionId,
                        ''
                    );
                } else {

                    $('#session_item_id').html(
                        '<option value="">Select</option>'
                    );
                }
            });
        }
    });
</script>
<script type="text/javascript">
    let parentSearchTimeout;

    function searchParentByNic() {
        const fatherNic = $('#father_nic').val().trim();
        const nicPattern = /^\d{5}-\d{7}-\d$/;
        if (!nicPattern.test(fatherNic)) {
            console.log('Invalid NIC:', fatherNic);
            return;
        }
        console.log('Searching NIC:', fatherNic);
        $.ajax({
            url: "{{ route('admin.search-parant') }}",
            type: "GET",
            data: {
                q: fatherNic
            },
            success: function (data) {
                const passwordField = document.getElementById('password');
                if (data.statuscode === 200) {
                    $('#father_name').val(data.father_name);
                    $('#mother_name').val(data.mother_name);
                    $('#occupation').val(data.occupation);
                    $('#income').val(data.income);
                    $('#address').val(data.address);
                    $('#contact_no').val(data.contact_no);
                    if (passwordField) {
                        passwordField.value = '';
                        passwordField.removeAttribute('required');
                    }
                } else {
                    $('#father_name').val('');
                    $('#mother_name').val('');
                    $('#occupation').val('');
                    $('#income').val('');
                    $('#address').val('');
                    $('#contact_no').val('');
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
    // Edit NIC - Type + Paste
    $(document).on('input', '#father_nic', function () {
        clearTimeout(parentSearchTimeout);
        const value = $(this).val().trim();
        console.log('NIC input:', value);
        const nicPattern = /^\d{5}-\d{7}-\d$/;
        if (!nicPattern.test(value)) {
            return;
        }
        parentSearchTimeout = setTimeout(function () {
            searchParentByNic();
        }, 800);
    });
</script>

<script>
    window.classSectionUrl = "{{ route('admin.admission.class.section') }}";
    window.voucherAmountsUrl = "{{ route('admin.admission.amounts') }}";
    window.selectedAcademicSessionId = "{{ $student->academic_session_id ?? '' }}";
    window.selectedSessionItemId = "{{ $student->session_item_id ?? '' }}";
    // Existing student discount
    window.isDiscountEnabled = {{ (int) ($student->discount ?? 0) }};
    window.discountAmount = "{{ $student->discount_amount ?? 0 }}";
</script>
<script src="{{ asset('backend_assets/js/academic-session.js') }}"></script>
<script src="{{ asset('backend_assets/js/voucher.js') }}"></script>

@endsection
   
