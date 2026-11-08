@php $contact = \App\Services\ParentPortal\ParentPortal::contact(); @endphp
<div class="pp-help">
    <p><strong>{{ $title ?? 'Questions about fees or your account?' }}</strong><br>
        <span class="pp-muted">Contact the school office{{ $contact['hours'] ? ', ' . $contact['hours'] : '' }}.</span></p>
    <div class="pp-help-actions">
        @if ($contact['tel'])
            <a class="pp-btn pp-btn-outline" href="tel:{{ $contact['tel'] }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $contact['phone'] }}</a>
        @endif
        @if ($contact['whatsapp'])
            <a class="pp-btn pp-btn-outline" href="https://wa.me/{{ $contact['whatsapp'] }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp</a>
        @endif
    </div>
</div>
