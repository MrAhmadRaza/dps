{{-- @include('admin.partials.page-head', ['title' => '…', 'sub' => '…']) with optional @slot-like $actions html --}}
<div class="ad-head">
    <div>
        <h1>{{ $title }}</h1>
        @if (!empty($sub))<p>{{ $sub }}</p>@endif
    </div>
    @if (!empty($actions))
        <div class="ad-head-actions">{!! $actions !!}</div>
    @endif
</div>
