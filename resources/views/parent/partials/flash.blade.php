@foreach (['success' => 'fa-circle-check', 'error' => 'fa-circle-exclamation'] as $type => $icon)
    @if (session($type))
        <div class="pp-flash pp-flash--{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}">
            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
            <p>{{ session($type) }}</p>
            <button type="button" data-dismiss-flash aria-label="Dismiss"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
        </div>
    @endif
@endforeach
