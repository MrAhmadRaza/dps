@extends('admin.layouts.app')
@section('content')
<div class="ad-head">
    <div>
        <h1>Admissions</h1>
        <p>Every student registered at the school. Search by name, register no., class or session.</p>
    </div>
    <div class="ad-head-actions">
        <a href="{{ route('admin.add.admission') }}" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i>New admission</a>
    </div>
</div>

<section class="panel">
    <div class="panel-body">
        <table class="table table-hover align-middle data-table w-100" id="admissionTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th></th>
                    <th>Student</th>
                    <th>Gender</th>
                    <th>Session</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Admitted</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
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
    $('#admissionTable').DataTable({
        serverSide: true,
        ajax: @json(route('admin.admission.index')),
        scrollX: true,
        autoWidth: false,
        order: [],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, width: '32px' },
            { data: 'photo_path', name: 'photo_path', orderable: false, searchable: false, width: '44px' },
            { data: 'name', name: 'name' },
            { data: 'gender', name: 'gender' },
            { data: 'academic_session_id', name: 'academic_session_id', orderable: false },
            { data: 'class', name: 'class', orderable: false },
            { data: 'section', name: 'section', orderable: false },
            { data: 'admission_date', name: 'admission_date' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: { emptyTable: 'No admissions yet. Use “New admission” to register a student.' }
    });
});
</script>
@endsection
