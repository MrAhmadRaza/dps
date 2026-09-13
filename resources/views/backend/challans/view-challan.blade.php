@extends('backend.layout.master')
@section('title', $pageTitle ?? 'View Challan')

@section('content')

<div class="container py-4">
    <div class="card shadow border-0 rounded-3">
        
        <!-- Header -->
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-file-invoice me-2"></i> Challan Voucher Details
            </h5>
            <a href="{{ url()->previous() }}" class="btn btn-light btn-sm fw-bold">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <div class="card-body p-4">

            <div class="row align-items-center">

                <!-- LEFT: Voucher Image -->
                <div class="col-md-5 text-center mb-4 mb-md-0">
                    @php
                        $voucherImg = $challan->payment?->voucher_image ?? $challan->voucher_image;
                    @endphp

                    @if($voucherImg)
                        <img src="{{ asset($voucherImg) }}"
                             alt="Voucher Image"
                             class="img-fluid rounded shadow-sm border"
                             style="max-height: 320px; object-fit: contain; cursor: pointer;"
                             onclick="showImage(this.src)">
                        <div class="mt-2 text-muted small">
                            <i class="fas fa-search-plus me-1"></i> Click image to preview
                        </div>
                    @else
                        <div class="p-4 border rounded bg-light text-muted">
                            <i class="fas fa-image fa-3x mb-2 d-block text-secondary"></i>
                            No Voucher Uploaded
                        </div>
                    @endif
                </div>

                <!-- RIGHT: Details Table -->
                <div class="col-md-7">

                    <table class="table table-striped table-bordered align-middle mb-3">
                        <tr>
                            <th style="width: 35%;">Student Name</th>
                            <td class="fw-bold">{{ $challan->student->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Bank Name</th>
                            <td>{{ $challan->payment->bank_name ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Month</th>
                            <td class="fw-bold text-primary">
                                @php
                                    $month = $challan->month ?? 'N/A';

                                    if (str_contains($month, ' to ')) {
                                        try {
                                            [$start, $end] = explode(' to ', $month);
                                            $startFormatted = \Carbon\Carbon::createFromFormat('Y-m', trim($start))->format('M Y');
                                            $endFormatted   = \Carbon\Carbon::createFromFormat('Y-m', trim($end))->format('M Y');
                                            echo $startFormatted . ' to ' . $endFormatted;
                                        } catch (\Exception $e) {
                                            echo $month;
                                        }
                                    } else {
                                        try {
                                            echo \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y');
                                        } catch (\Exception $e) {
                                            echo $month;
                                        }
                                    }
                                @endphp
                            </td>
                        </tr>

                        <tr>
                            <th>Paid Date</th>
                            <td>
                                @if($challan->payment?->paid_date)
                                    {{ \Carbon\Carbon::parse($challan->payment->paid_date)->format('d M Y') }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Transaction ID</th>
                            <td>{{ $challan->payment->transaction_id ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                @if($challan->status == 'pending')
                                    <span class="badge bg-warning text-dark px-3 py-2">Pending</span>
                                @elseif($challan->status == 'review')
                                    <span class="badge bg-info text-dark px-3 py-2">Review</span>
                                @elseif($challan->status == 'approved' || $challan->status == 'paid')
                                    <span class="badge bg-success px-3 py-2">Approved</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2">{{ ucfirst($challan->status ?? 'Unknown') }}</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    <!-- Download Button -->
                    @if($voucherImg)
                        <a href="{{ asset($voucherImg) }}" 
                           download 
                           class="btn btn-success mt-2">
                           <i class="fas fa-download me-1"></i> Download Challan Voucher
                        </a>
                    @endif

                </div>

            </div>

        </div>
    </div>
</div>

<!-- IMAGE PREVIEW MODAL -->
<div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img id="previewImg" src="" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function showImage(src) {
    document.getElementById('previewImg').src = src;
    var myModal = new bootstrap.Modal(document.getElementById('imgModal'));
    myModal.show();
}
</script>
@endsection