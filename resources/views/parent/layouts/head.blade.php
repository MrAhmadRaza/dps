@php
    $ppAsset = fn (string $path) => asset($path) . '?v=' . (@filemtime(public_path($path)) ?: '1');
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#061A3A">
<title>{{ $pageTitle ?? 'Parent portal' }} | {{ config('school.short_name', config('app.name')) }} parent portal</title>
<link rel="icon" href="{{ asset('parent_assets/img/crest.png') }}" type="image/png">
<link rel="apple-touch-icon" href="{{ asset('parent_assets/img/crest.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ $ppAsset('parent_assets/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ $ppAsset('parent_assets/font-awesome/css/all.min.css') }}">
<link rel="stylesheet" href="{{ $ppAsset('parent_assets/css/portal.css') }}">
