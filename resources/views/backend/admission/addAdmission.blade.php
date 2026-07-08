@extends('backend.layout.master')
@section('title', $pageTitle ?? 'N/A')
@section('content')
<style>
    body {
        background: #f4f7fb;
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
        border-radius: 10px;
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
            <h3 class="mb-2 mb-md-0">Add Admission</h3>
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
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <div class="invalid-feedback">Please select gender.</div>
                        </div>

                        <div class="col-md-6">
                            <label>B-Form Number (Optional)</label>
                            <input type="text" name="b_form_no" value="{{old('b_form_no')}}" class="form-control" placeholder="B-Form Number">
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
                                <label>Parent Portal Id <span class="text-danger">*</span></label>
                                <select id="select-portal" class="form-control select2" name="portal_id" required>
                                    <option value="">Select Portal</option>
                               
                                </select>
                            </div>

                             <div class="col-md-6">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="text" name="password" id="password" readonly value="{{old('password')}}" class="form-control" placeholder="password "">
                            </div>

                        <div class="col-md-6">
                            <label>Father Name <span class="text-danger">*</span></label>
                            <input type="text" name="father_name" id="father_name" value="{{old('father_name')}}" class="form-control" placeholder=" Father Name" required>
                            <div class="invalid-feedback">Please enter father's full name.</div>
                        </div>

                        <div class="col-md-6">
                            <label>Father NIC <span class="text-danger">*</span></label>
                            <input type="text" name="father_nic" id="father_nic" value="{{old('father_nic')}}" class="form-control" placeholder="Father Nic" required>
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
                            <label>Income</label>
                            <input type="text" name="income" id="income" value="{{old('income')}}" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label> Address <span class="text-danger">*</span></label>
                            <textarea name="address" id="address" class="form-control" value="{{old('address')}}" rows="2" placeholder="Address" required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label>Contact Number <span class="text-danger">*</span></label>
                            <input type="tel" name="contact_no" id="contact_no" value="{{old('contact_no')}}" class="form-control" pattern="[0-9]{10,15}" placeholder="Contact Number" required>
                            <div class="invalid-feedback">Please enter a contact number.</div>
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
                            <input type="hidden" name="student_photo" id="student_photo" required>
                            <img id="photoPreview" class="mt-2" style="display:none; max-width:100%; border:1px solid #ccc; border-radius:5px;">
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
                            <input type="text" name="guardian_nic" value="{{old('guardian_nic')}}" class="form-control" placeholder="Guardian NIC">
                        </div>
                        <div class="col-md-6">
                            <label>Contact Number (Optional)</label>
                            <input type="tel" pattern="[0-9]{10,15}" value="{{old('guardian_contact_no')}}" name="guardian_contact_no" class="form-control" placeholder="Contact Number">
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
                            <select name="academic_session_id" value="{{old('academic_session_id')}}" id="academic_session_id"  class="form-select" required>
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
                        <div class="col-md-12 mt-4">
                             <h5 class="text-primary mb-3">Fee Details</h5>
                        </div>

                        <div class="col-md-4">
                            <label>Admission Fee</label>
                            <input type="number" name="admission_fee" value="{{old('admission_fee')}}" step="0.01" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label>Tuition Fee (Monthly)</label>
                            <input type="number" name="tuition_fee" value="{{old('tuition_fee')}}" step="0.01" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label>Stationary Fee</label>
                            <input type="number" name="stationary_fee" value="{{old('sationary_fee')}}" step="0.01" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label>Library Fee</label>
                            <input type="number" name="library_fee" value="{{old('library_fee')}}" step="0.01" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label>Sports Fund</label>
                            <input type="number" name="sports_fund" value="{{old('sports_fund')}}" step="0.01" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label>Security Deposit</label>
                            <input type="number" name="security_deposit" value="{{old('security_deposit')}}" step="0.01" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label>Development Fund</label>
                            <input type="number" name="development_fund" value="{{old('development_fund')}}" step="0.01" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label>Misc. Charges</label>
                            <input type="number" name="misc_charges" value="{{old('misc_charges')}}" step="0.01" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label>Total Amount Paid (at admission)</label>
                            <input type="number" name="total_fee_paid" value="{{old('total_fee_paid')}}" step="0.01" class="form-control" placeholder="0.00">
                        </div>

                        <div class="col-md-3">
                            <label>Receipt No</label>
                            <input type="text" name="receipt_no" value="{{old('receipt_no')}}" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label>Fee Paid Date</label>
                            <input type="date" name="fee_paid_date" value="{{old('fee_paid_date')}}" class="form-control">
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
    document.addEventListener('DOMContentLoaded', function(){
        const steps = Array.from(document.querySelectorAll('.tab-pane'));
        const progressSteps = Array.from(document.querySelectorAll('.step-progress .step'));
        const nextButtons = document.querySelectorAll('.next-step');
        const prevButtons = document.querySelectorAll('.prev-step');
        let currentStep = 0;
        // Webcam
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const startCameraBtn = document.getElementById('startCameraBtn');
        const captureBtn = document.getElementById('captureBtn');
        const photoInput = document.getElementById('student_photo');
        const photoPreview = document.getElementById('photoPreview');
        let stream = null;

        startCameraBtn.addEventListener('click', async () => {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                video.style.display = 'block';
                captureBtn.disabled = false;
                startCameraBtn.disabled = true;
            } catch(err) { alert('Webcam not accessible: ' + err); }
        });

        captureBtn.addEventListener('click', ()=>{
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video,0,0);
            const dataURL = canvas.toDataURL('image/png');
            photoInput.value = dataURL;
            photoPreview.src = dataURL;
            photoPreview.style.display = 'block';
            if(stream){ stream.getTracks().forEach(track => track.stop()); video.style.display='none'; captureBtn.disabled=true; }
        });

        function showStep(i){
            steps.forEach((s, idx)=>{ s.classList.remove('show','active'); if(idx===i) s.classList.add('show','active'); });
            prevButtons.forEach(btn => btn.style.display = (i===0)?'none':'inline-block');
            progressSteps.forEach((step, idx)=>{
                step.classList.remove('active','completed');
                if(idx<i) step.classList.add('completed');
                if(idx===i) step.classList.add('active');
            });
        }

        function validateStep(step){
            let valid = true;
            step.querySelectorAll('input[required], select[required]').forEach(field=>{
                if(!field.value.trim()){ valid=false; field.classList.add('is-invalid'); } 
                else field.classList.remove('is-invalid');
            });
            return valid;
        }

        nextButtons.forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const step = steps[currentStep];
                if(validateStep(step)){
                    currentStep = Math.min(currentStep+1, steps.length-1);
                    showStep(currentStep);
                }
            });
        });

        prevButtons.forEach(btn=>{
            btn.addEventListener('click', ()=>{
                currentStep = Math.max(currentStep-1,0);
                showStep(currentStep);
            });
        });

        showStep(currentStep);

        // Password Generate four digits
       function generatePassword(length = 4) {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let password = '';
            for (let i = 0; i < length; i++) {
                password += chars[Math.floor(Math.random() * chars.length)];
            }
            return password;
        }
            const passwordField = document.getElementById('password');
            passwordField.value = generatePassword(4);
    });
    // Session Loads 
    $('#academic_session_id').on('change', function() {
        let academic_session_id = $(this).val();
        let token = $('meta[name="csrf-token"]').attr('content');
        const route_url = "{{ route('admin.admission.class.section') }}";
        if(academic_session_id){
            loadSessionItems(route_url, token , academic_session_id);
        }
    });
</script>
<script src="{{asset('backend_assets/js/academic-session.js')}}"></script>
<script>
    let searchTimeout;
    new TomSelect('#select-portal', {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        create: true,
        //  AJAX Search with Debounce
        load: function(query, callback) {
            if (!query.length) return callback();
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: "{{ route('admin.search-portal') }}?q=" + query,
                    type: 'GET',
                    success: function(res) { callback(res); },
                    error: function() { callback(); }
                });
            }, 500); // 500ms delay after user stops typing
        },

        // Auto-fill on select
        onChange: function(value) {
            if (!value) return;
            $.ajax({
                url: "{{ route('admin.parent-portal-info', '') }}/" + value,
                type: 'GET',
                success: function(data) {
                    if (data.statuscode === 200) {
                    // password empty if exit parent select
                    const passwordField = document.getElementById('password');
                     passwordField.value = "";
                     // remove required attr
                        $('#password').removeAttr('required');
                        $('#father_name').val(data.father_name);
                        $('#father_nic').val(data.father_nic);
                        $('#mother_name').val(data.mother_name);
                        $('#occupation').val(data.occupation);
                        $('#income').val(data.income);
                        $('#address').val(data.address);
                        $('#contact_no').val(data.contact_no);
                    }
                }
            });
        }
    });
</script>
@endsection
