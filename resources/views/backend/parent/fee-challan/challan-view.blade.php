@extends('backend.parent.layout.master')
@section('title', $pagetitle ?? 'View Challan')

@section('content')

<div class="container py-4">
    <div class="card shadow border-0 rounded-3">
        
        <!-- Header -->
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-file-invoice me-2"></i> Challan Voucher Details
            </h5>
            <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="card-body">

            <div class="row align-items-center">

                <!-- LEFT: Image -->
                <div class="col-md-5 text-center mb-3 mb-md-0">
                    @if($challan->voucher_image)
                        <img src="{{ asset($challan->voucher_image) }}"
                             alt="Voucher Image"
                             class="img-fluid rounded shadow-sm border"
                             style="max-height: 300px; object-fit: contain; cursor:pointer;"
                             onclick="showImage(this.src)">
                    @else
                        <p class="text-muted">No Voucher Uploaded</p>
                    @endif
                </div>

                <!-- RIGHT: Details -->
                <div class="col-md-7">

                    <table class="table table-striped table-bordered align-middle">
                       

                        <tr>
                            <th>Bank Name</th>
                            <td>{{ $challan->bank_name ?? 'N/A' }}</td>
                        </tr>

                         <tr>
                            <th>Month</th>
                            <td>{{ $challan->challan->month ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Paid Date</th>
                            <td>{{ $challan->paid_date ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Transaction ID</th>
                            <td>{{ $challan->transaction_id ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                @if($challan->challan->status == 'pending')
                                    <span class="badge bg-warning px-3 py-2">Pending</span>
                                @elseif($challan->challan->status == 'review')
                                    <span class="badge bg-info px-3 py-2">Under Review</span>
                                @elseif($challan->challan->status == 'approved')
                                    <span class="badge bg-success px-3 py-2">Approved</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2">Unknown</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <!-- Download Button -->
                    @if($challan->voucher_image)
                        <a href="{{ asset( $challan->voucher_image) }}" 
                           download 
                           class="btn btn-success mt-2">
                           <i class="fas fa-download me-1"></i> Download Challan
                        </a>
                    @endif

                </div>

            </div>

        </div>
    </div>
</div>

<!-- IMAGE PREVIEW MODAL -->
<div class="modal fade" id="imgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-body text-center">
                <img id="previewImg" src="" class="img-fluid rounded">
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function showImage(src) {
    document.getElementById('previewImg').src = src;
    new bootstrap.Modal(document.getElementById('imgModal')).show();
}
</script>
@endsection