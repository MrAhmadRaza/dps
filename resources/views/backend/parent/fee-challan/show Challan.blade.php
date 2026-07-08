@extends('backend.parent.layout.master')
@section('title', $pagetitle ?? 'Fee Challans')

@section('content')
<style>
.table thead th {
    font-size: 0.95rem !important;
    text-transform: capitalize !important;
    letter-spacing: 0px !important;
    padding: 10px 15px 6px 10px !important;
    text-align: center !important;                      
}
</style>

<div class="container">
    <div class="page-inner">

        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Fee Challans</h3>
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
          {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif


        {{-- Student Dropdown --}}
        <div class="col-md-4 mb-3">
            <label>Select Student</label>
            <select id="student" class="form-select">
                <option value="">Select</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </select>
        </div>

        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Session</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Month</th>
                    <th>Total Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="challan-body">
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Select student to view challans
                    </td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

<!-- Upload Challan Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('parent.challan.upload') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Upload Challan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="fee_challan_id" id="challan_id">

                <div class="mb-3">
                    <label>Bank Name <span class="text-danger">*</span> </label>
                    <input type="text" name="bank_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Paid Date <span class="text-danger">*</span></label>
                    <input type="date" name="paid_date" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Transaction ID (optional)</label>
                    <input type="text" name="transaction_id" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Upload Challan <span class="text-danger">*</span></label>
                    <input type="file" name="voucher_image" class="form-control" required>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>

        </form>
    </div>
</div>

@endsection
@section('scripts')
<script>

    // Model Challan
    function openUploadModal(id) {
        $('#challan_id').val(id);
        $('#uploadModal').modal('show');
    }

    $('#student').on('change', function () {
        let studentId = $(this).val();
        // empty select
        if (!studentId) {
            $('#challan-body').html(`
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Select student to view challans
                    </td>
                </tr>
            `);
            return;
        }
        $.ajax({
            url: "{{ route('parent.fee-challans.ajax') }}",
            type: "GET",
            data: { student_id: studentId }, // 
            beforeSend: function () {
                $('#challan-body').html(`
                    <tr>
                        <td colspan="9" class="text-center py-3">
                            Loading...
                        </td>
                    </tr>
                `);
            },
            success: function (response) {
                let rows = '';
                if (response.length === 0) {
                    rows = `
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                No challans found
                            </td>
                        </tr>
                    `;
                } else {
                   
                    $.each(response, function (index, item) {
                        let viewUrl = "{{ route('parent.view.challan',':id') }}".replace(':id', item.id);
                        rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.student?.academic_session?.session_name ?? 'N/A'}</td>
                                <td>${item.student?.session_item?.class ?? 'N/A'}</td>
                                <td>${item.student?.session_item?.section ?? 'N/A'}</td>
                                <td>${item.month ?? '-'}</td>
                                <td>${item.voucher?.total_amount ?? '0'}</td>
                                <td>${item.voucher?.due_date ?? 'N/A'}</td>
                                <td>
                                    ${
                                        item.status === 'pending'
                                            ? '<span class="badge bg-warning">Pending</span>'
                                            : item.status === 'approved'
                                            ? '<span class="badge bg-success">Approved</span>'
                                            : item.status === 'review'
                                            ? '<span class="badge bg-info">Review</span>'
                                            : '<span class="badge bg-secondary">Unknown</span>'
                                    }
                                </td>
                                <td class="text-center">
                                  ${
                                        `<a href="{{ route('parent.print.challan', ['voucher' => '__VOUCHER_ID__']) }}"
                                            title="Print Voucher"
                                            class="text-primary me-3 fs-5"
                                            target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>`.replace('__VOUCHER_ID__', item.id)
                                    }
                                    <!-- PAY -->
                                    ${
                                        item.status === 'pending'
                                        ? `<a href="#" title="Pay" class="text-success me-3 fs-5">
                                                <i class="fas fa-credit-card"></i>
                                        </a>`
                                        : ``
                                    }
                                    <!-- UPLOAD -->
                                    ${
                                        item.status === 'pending'
                                        ? `<a href="#" onclick="openUploadModal(${item.id})"
                                                title="Upload Voucher"
                                                class="text-warning me-3 fs-5">
                                                <i class="fas fa-upload"></i>
                                        </a>`
                                        : item.status === 'review'
                                        ? ``
                                        : item.status === 'approved'
                                        ? `<a href="${viewUrl}" 
                                                title="View Challan"
                                                class="text-primary me-3 fs-5"
                                                >
                                                <i class="fas fa-eye"></i>
                                        </a>`
                                        : ''
                                    }

                                </td>
                            </tr>
                        `;
                    });
                }
                $('#challan-body').html(rows);
            },
            error: function () {
                $('#challan-body').html(`
                    <tr>
                        <td colspan="9" class="text-center text-danger py-4">
                            Something went wrong
                        </td>
                    </tr>
                `);
            }
        });
    });

  
</script>
@endsection