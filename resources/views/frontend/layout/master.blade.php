<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dunyapur Public School')</title>
    <meta name="description" content="@yield('description', 'Dunyapur Public School — a trusted institution in Dunyapur, Punjab, offering a rigorous academic programme, modern science and computer labs, and a secure, green campus.')">
    <meta name="theme-color" content="#061A3A">

    {{-- Social sharing --}}
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', config('school.name'))">
    <meta property="og:description" content="@yield('description', 'Knowledge · Discipline · Success')">
    <meta property="og:image" content="{{ asset('frontend_assets/img/hero-main.jpg') }}">

    @include('frontend.include.css')
    @yield('head')
</head>
<body>
    <a class="dps-skip" href="#main">Skip to content</a>

    @include('frontend.include.header')

    <main id="main">
        @yield('content')
    </main>

    @include('frontend.include.footer')

    @include('frontend.include.js')
    @yield('js')
</body>
</html>
