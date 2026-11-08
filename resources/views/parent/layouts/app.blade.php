@php
    $ppAsset = fn (string $path) => asset($path) . '?v=' . (@filemtime(public_path($path)) ?: '1');
    $nav = [
        ['route' => 'parent.index',         'match' => 'parent.index',                       'label' => 'Home',     'icon' => 'fa-house'],
        ['route' => 'parent.fee-challan',   'match' => ['parent.fee-challan', 'parent.view.challan', 'parent.challan.*'], 'label' => 'Fees', 'icon' => 'fa-file-invoice'],
        ['route' => 'parent.student.index', 'match' => ['parent.student.*', 'parent.view.student'], 'label' => 'Children', 'icon' => 'fa-children'],
        ['route' => 'parent.account',       'match' => 'parent.account*',                    'label' => 'Account',  'icon' => 'fa-user'],
    ];
    $school = config('school', []);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    @include('parent.layouts.head')
</head>
<body class="pp">
    <a class="pp-skip" href="#main">Skip to content</a>

    <header class="pp-header">
        <div class="pp-header-inner">
            <a href="{{ route('parent.index') }}" class="pp-brand">
                <img src="{{ asset('parent_assets/img/crest.png') }}" alt="" width="38" height="38">
                <span class="pp-brand-text">
                    <span class="pp-brand-name">{{ $school['name'] ?? config('app.name') }}</span>
                    <span class="pp-brand-sub">Parent portal</span>
                </span>
            </a>

            <nav class="pp-nav" aria-label="Main">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['match'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <form method="POST" action="{{ route('parent.logout') }}" class="pp-signout">
                @csrf
                <button type="submit" aria-label="Sign out"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span class="label" aria-hidden="true">Sign out</span></button>
            </form>
        </div>
    </header>

    <main id="main" class="pp-main" tabindex="-1">
        @include('parent.partials.flash')
        @yield('content')
    </main>

    <footer class="pp-footer">
        © {{ date('Y') }} {{ $school['name'] ?? config('app.name') }}. <a href="{{ url('/') }}">School website</a>
    </footer>

    <nav class="pp-tabs" aria-label="Main">
        @foreach ($nav as $item)
            <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['match'])) aria-current="page" @endif>
                <i class="fa-solid {{ $item['icon'] }}" aria-hidden="true"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <script src="{{ $ppAsset('parent_assets/js/portal.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
