<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DPS Parent Portal</title>
    
    @include('frontend.include.css')
</head>
<body>
    @include('frontend.include.header')
   {{-- Main Content  --}}
    @yield('content')
   {{-- End Main Content  --}}
   @include('frontend.include.footer')
</body>
<script>
    document.getElementById("year").textContent = new Date().getFullYear();
</script>
</html>