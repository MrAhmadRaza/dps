@extends('admin.layouts.app')
@section('content')
<div class="ad-head">
    <div>
        <h1>Parent accounts</h1>
        <p>Families with at least one active student. Parents sign in to the parent portal with the father's CNIC and a password you set here.</p>
    </div>
</div>

<section class="panel">
    <div class="panel-body">
        <table class="table table-hover align-middle data-table w-100" id="parentTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Father</th>
                    <th>CNIC (sign-in ID)</th>
                    <th>Contact</th>
                    <th>Children at DPS</th>
                    <th class="text-center">Reset password</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</section>
@endsection

@section('scripts')
<script>
$(function () {
    $('#parentTable').DataTable({
        serverSide: true,
        ajax: @json(route('admin.parent.show')),
        scrollX: true,
        autoWidth: false,
        order: [],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '32px' },
            { data: 'father_name', name: 'father_name' },
            { data: 'father_nic', name: 'father_nic' },
            { data: 'contact_no', name: 'contact_no' },
            { data: 'children', name: 'children', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: { emptyTable: 'No parent accounts yet.' }
    });
});
</script>
@endsection
