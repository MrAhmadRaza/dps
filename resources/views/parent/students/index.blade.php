@extends('parent.layouts.app')

@section('content')
<div class="pp-page-head">
    <h1>Children</h1>
    <p>Students enrolled at DPS under your account.</p>
</div>

@if ($students->isEmpty())
    <div class="pp-card pp-empty">
        <i class="fa-solid fa-children" aria-hidden="true"></i>
        <strong>No active students on this account</strong>
        Contact the school office if your child should appear here.
    </div>
@else
    <div class="pp-kids">
        @foreach ($students as $student)
            <a class="pp-card pp-kid" href="{{ route('parent.view.student', $student->id) }}">
                @include('parent.partials.avatar', ['student' => $student])
                <span>
                    <strong>{{ $student->name }}</strong>
                    <span class="pp-kid-meta">Class {{ $student->sessionItem->class ?? '—' }} {{ $student->sessionItem->section ?? '' }}</span>
                    <span class="pp-kid-meta">{{ $student->academicSession->session_name ?? '' }}</span>
                    @if ($student->open_challans_count)
                        <span class="pp-status pp-status--warning"><i class="fa-solid fa-clock" aria-hidden="true"></i>{{ $student->open_challans_count }} unpaid</span>
                    @else
                        <span class="pp-status pp-status--success"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>Fees up to date</span>
                    @endif
                </span>
            </a>
        @endforeach
    </div>
@endif
@endsection
