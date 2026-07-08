@extends('backend.layout.master')
@section('title', 'Reports')

@section('content')
<div class="container">
    <div class="page-inner">

        <div class="page-header mb-4">
            <h3>Reports Module</h3>
        </div>

        <div class="card p-4">

            <div class="row mb-3">

                {{-- Report Type --}}
                <div class="col-md-4">
                    <label>Report Type</label>
                    <select id="report_type" class="form-control">
                        <option value="cashbook">Cash Book</option>
                    </select>
                </div>

                {{-- Month --}}
                <div class="col-md-4">
                    <label>Select Month</label>
                    <select id="month" class="form-control">
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>

                {{-- Year --}}
                <div class="col-md-4">
                    <label>Year</label>
                    <input type="number" id="year" value="{{ date('Y') }}" class="form-control">
                </div>

            </div>

            {{-- Print Button --}}
            <div class="mt-3">
                <button id="printReport" class="btn btn-success">
                    <i class="fa fa-print"></i> Print Cash Book
                </button>
            </div>

        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
$('#printReport').click(function(){
    let type = $('#report_type').val();
    let month = $('#month').val();
    let year = $('#year').val();
    let url = "{{ route('admin.reports.cashbook') }}?type=" + type + "&month=" + month + "&year=" + year;
    window.open(url, '_blank');
});
</script>
@endsection