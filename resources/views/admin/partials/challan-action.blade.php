@php
    $who = $challan->student->name ?? 'this student';
    $month = \App\Helpers\FeeChallanHelper::formatChallanMonth($challan->month);
@endphp
<div class="row-actions">
    <a href="{{ $viewRoute }}" class="btn-icon" title="Open challan" aria-label="Open challan"><i class="fa-regular fa-eye"></i></a>

    @if ($status === 'review')
        <form method="POST" action="{{ $approvedRoute }}" data-confirm="Mark {{ $who }}'s {{ $month }} challan as paid?" data-confirm-title="Approve payment" data-confirm-ok="Approve">
            @csrf
            <button type="submit" class="btn-icon is-ok" title="Approve payment" aria-label="Approve payment"><i class="fa-solid fa-check"></i></button>
        </form>
    @elseif ($status === 'approved' && empty($paidViaOneLink))
        <form method="POST" action="{{ $pendingRoute }}" data-confirm="Set {{ $who }}'s {{ $month }} challan back to unpaid?" data-confirm-title="Set back to unpaid" data-confirm-ok="Set to unpaid">
            @csrf
            <button type="submit" class="btn-icon" title="Set back to unpaid" aria-label="Set back to unpaid"><i class="fa-solid fa-rotate-left"></i></button>
        </form>
    @endif

    @if (empty($paidViaOneLink))
        <form method="POST" action="{{ $deleteRoute }}" data-confirm="Delete {{ $who }}'s {{ $month }} challan? Any uploaded proof is removed with it." data-confirm-title="Delete challan" data-confirm-ok="Delete" data-confirm-tone="danger">
            @csrf @method('DELETE')
            <button type="submit" class="btn-icon is-danger" title="Delete challan" aria-label="Delete challan"><i class="fa-regular fa-trash-can"></i></button>
        </form>
    @endif
</div>
