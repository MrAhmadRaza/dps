{{-- Row action buttons for list tables.
     $actions: list of ['type' => 'link'|'delete', 'href', 'icon', 'label', 'blank' => bool, 'confirm' => text] --}}
<div class="row-actions">
    @foreach ($actions as $a)
        @continue(empty($a['href']))
        @if (($a['type'] ?? 'link') === 'delete')
            <form method="POST" action="{{ $a['href'] }}" data-confirm="{{ $a['confirm'] ?? 'Delete this record? This cannot be undone.' }}" data-confirm-title="{{ $a['label'] }}" data-confirm-ok="Delete" data-confirm-tone="danger">
                @csrf @method('DELETE')
                <button type="submit" class="btn-icon is-danger" title="{{ $a['label'] }}" aria-label="{{ $a['label'] }}"><i class="{{ $a['icon'] ?? 'fa-regular fa-trash-can' }}"></i></button>
            </form>
        @else
            <a href="{{ $a['href'] }}" class="btn-icon" title="{{ $a['label'] }}" aria-label="{{ $a['label'] }}" @if (!empty($a['blank'])) target="_blank" rel="noopener" @endif><i class="{{ $a['icon'] }}"></i></a>
        @endif
    @endforeach
</div>
