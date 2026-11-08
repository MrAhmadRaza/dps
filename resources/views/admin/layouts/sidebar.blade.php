<aside class="ad-sidebar" id="adSidebar" aria-label="Main navigation">
    <a href="{{ route('admin.dashboard') }}" class="ad-brand">
        <img src="{{ asset('admin_assets/img/crest.png') }}" alt="" width="34" height="34">
        <span>
            <span class="ad-brand-name">{{ config('school.short_name', 'DPS') }}</span>
            <span class="ad-brand-sub">Staff portal</span>
        </span>
    </a>
    <nav class="ad-nav">
        @foreach ($adNav as $group => $items)
            <div class="ad-nav-group">
                <div class="ad-nav-label">{{ $group }}</div>
                @foreach ($items as $item)
                    @php $on = request()->routeIs(...$item['active']); @endphp
                    <a href="{{ route($item['route']) }}" class="{{ $on ? 'active' : '' }}" @if($on) aria-current="page" @endif>
                        <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $item['label'] }}</span>
                        @if (!empty($item['count']))
                            <span class="ad-nav-count" title="{{ $item['count'] }} payment proof(s) to review">{{ $item['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>
    <div class="ad-sidebar-foot">
        <form method="POST" action="{{ route('admin.logout') }}" class="mb-2">
            @csrf
            <button type="submit" class="ad-nav-link" style="all:unset;cursor:pointer;color:#C9D3E6;font-size:.85rem">
                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i>Sign out
            </button>
        </form>
        &copy; {{ date('Y') }} {{ config('school.name', 'Dunyapur Public School') }}
    </div>
</aside>
<div class="ad-backdrop" aria-hidden="true"></div>
