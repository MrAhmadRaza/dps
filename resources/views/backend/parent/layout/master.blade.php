<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
     <title>{{ config('app.name') }} | {{ $pageTitle ?? 'N/A' }} </title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport"/>
    {{-- CSS Links --}}
    @include('backend.parent.include.css')
  </head>
  <body>
    <div class="wrapper">
      <!-- Sidebar -->
      @include('backend.parent.include.sidebar')
      <!-- End Sidebar -->

      <div class="main-panel">
      @include('backend.parent.include.header')

        @yield('content')

       @include('backend.parent.include.footer')
      </div>
      
    </div>
    {{-- JS Links --}}
 @include('backend.parent.include.js')
  </body>
</html>
