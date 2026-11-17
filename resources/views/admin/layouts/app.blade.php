@php
    $adAsset   = fn (string $path) => asset($path) . '?v=' . (@filemtime(public_path($path)) ?: '1');
    $adNav     = \App\Services\AdminPortal\Navigation::groups();
    $adCurrent = \App\Services\AdminPortal\Navigation::current($adNav, request());
@endphp
<!doctype html>
<html lang="en">
<head>
    @include('admin.layouts.head')
    @stack('styles')
</head>
<body class="ad">
    <a class="visually-hidden-focusable" href="#main">Skip to content</a>
    <div class="ad-shell">
        @include('admin.layouts.sidebar')
        <div class="ad-main">
            @include('admin.layouts.topbar')
            @include('admin.partials.flash')
            <main class="ad-content" id="main">
                @yield('content')
            </main>
            <footer class="ad-footer">{{ config('school.name', 'Dunyapur Public School') }} · Staff portal</footer>
        </div>
    </div>

    {{-- Shared confirm step (admin.js) and image zoom --}}
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="confirmModalTitle" data-confirm-title>Please confirm</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"><p class="mb-0" data-confirm-text></p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-confirm-accept>Confirm</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="zoomModal" tabindex="-1" aria-label="Image preview" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent shadow-none">
                <div class="text-end mb-2"><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <img src="" alt="Preview" class="img-fluid rounded mx-auto d-block" style="max-height:85vh">
            </div>
        </div>
    </div>

    <script src="{{ $adAsset('admin_assets/js/jquery.min.js') }}"></script>
    <script src="{{ $adAsset('admin_assets/js/popper.min.js') }}"></script>
    <script src="{{ $adAsset('admin_assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ $adAsset('admin_assets/js/datatables.min.js') }}"></script>
    <script src="{{ $adAsset('admin_assets/js/datatables.bootstrap5.min.js') }}"></script>
    <script src="{{ $adAsset('admin_assets/js/tom-select.js') }}"></script>
    <script src="{{ $adAsset('admin_assets/js/admin.js') }}"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
    </script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
