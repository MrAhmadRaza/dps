<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DPS Dunyapur - Parent Portal')</title>
    <meta name="description" content="@yield('description', 'The official Portal of Dunyapur Public School — track attendance, fees, results and circulars in one secure place.')">
    @include('frontend.include.css')
</head>
<body>

    @include('frontend.include.header')

    @yield('content')

    @include('frontend.include.footer')

    @include('frontend.include.js')
    @yield('js')
</body>
</html>