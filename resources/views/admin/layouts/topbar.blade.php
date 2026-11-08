@php
    $user = auth()->user();
    $initials = collect(preg_split('/\s+/', trim($user->name ?? 'Staff')))->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: 'S';
    $role = $user?->isSuperAdmin() ? 'Super admin' : 'Admin';
@endphp
<header class="ad-topbar">
    <button type="button" class="ad-burger" data-nav-toggle aria-controls="adSidebar" aria-label="Open menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <nav class="ad-crumbs" aria-label="Breadcrumb">
        @if ($adCurrent && $adCurrent['route'] !== 'admin.dashboard')
            <span class="d-none d-sm-inline">{{ $adCurrent['group'] }}</span>
            <span class="sep d-none d-sm-inline">/</span>
            @if (!request()->routeIs($adCurrent['route']))
                <a href="{{ route($adCurrent['route']) }}">{{ $adCurrent['label'] }}</a>
                <span class="sep">/</span>
            @endif
        @endif
        <span class="current">{{ $pageTitle ?? ($adCurrent['label'] ?? 'Staff portal') }}</span>
    </nav>
    <div class="ad-topbar-right">
        <div class="dropdown">
            <button class="ad-user" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="ad-avatar" aria-hidden="true">{{ $initials }}</span>
                <span class="ad-user-text">
                    <span class="ad-user-name">{{ $user->name ?? 'Staff' }}</span>
                    <span class="ad-user-role">{{ $role }}</span>
                </span>
                <i class="fa-solid fa-chevron-down small text-muted ms-1"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="px-2 pt-1 pb-2 small text-muted">Signed in as<br><strong class="text-ink">{{ $user->email ?? '' }}</strong></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('admin.general-settings.index') }}"><i class="fa-solid fa-gear me-2 text-muted"></i>Settings</a></li>
                <li><a class="dropdown-item" href="{{ url('/') }}" target="_blank" rel="noopener"><i class="fa-solid fa-globe me-2 text-muted"></i>Open public website</a></li>
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="fa-solid fa-arrow-right-from-bracket me-2 text-muted"></i>Sign out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
