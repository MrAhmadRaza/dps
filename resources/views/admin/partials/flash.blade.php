@php
    $flashes = [];
    if (session('success')) $flashes[] = ['success', 'fa-circle-check', session('success'), true];
    if (session('error'))   $flashes[] = ['error', 'fa-circle-exclamation', session('error'), false];
    if (session('info'))    $flashes[] = ['info', 'fa-circle-info', session('info'), true];
    $admissionLinks = session('admission_student_id') || session('admission_challan_id');
@endphp
@if ($flashes)
    <div class="flash-stack" role="status" aria-live="polite">
        @foreach ($flashes as [$type, $icon, $message, $autohide])
            <div class="flash flash-{{ $type }}" data-autohide="{{ ($autohide && !$admissionLinks) ? 1 : 0 }}">
                <i class="fa-solid {{ $icon }}"></i>
                <div class="flash-body">
                    {{ $message }}
                    @if ($type === 'success' && $admissionLinks)
                        <div class="flash-actions">
                            @if (session('admission_student_id'))
                                <a href="{{ route('admin.print.admission', ['id' => session('admission_student_id')]) }}" target="_blank" class="btn btn-sm btn-light"><i class="fa-solid fa-print"></i>Print application</a>
                            @endif
                            @if (session('admission_challan_id'))
                                <a href="{{ route('admin.challan.print', session('admission_challan_id')) }}" target="_blank" class="btn btn-sm btn-primary"><i class="fa-solid fa-file-invoice"></i>Print challan</a>
                            @endif
                        </div>
                    @endif
                </div>
                <button type="button" class="btn-close" aria-label="Dismiss"></button>
            </div>
        @endforeach
    </div>
@endif
