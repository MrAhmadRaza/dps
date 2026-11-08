@php
    $ppAsset = fn (string $path) => asset($path) . '?v=' . (@filemtime(public_path($path)) ?: '1');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    @include('parent.layouts.head')
</head>
<body class="pp">
    @yield('content')
    <script src="{{ $ppAsset('parent_assets/js/portal.js') }}" defer></script>
</body>
</html>
