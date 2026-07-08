<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
     <title>{{ config('app.name') }} | {{ $pageTitle ?? 'N/A' }} </title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport"/>
    {{-- CSS Links --}}
    @include('backend.include.css')
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      @include('backend.include.sidebar')
      <!-- End Sidebar -->

      <div class="main-panel">
      @include('backend.include.header')

        @yield('content')

       @include('backend.include.footer')
      </div>
      
    </div>
    {{-- JS Links --}}
 @include('backend.include.js')
  </body>
</html>
