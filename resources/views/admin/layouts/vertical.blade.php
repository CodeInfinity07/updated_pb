<!doctype html>
<html lang="en">

<head>
    @include('admin.layouts.partials/title-meta', ['title' => $title])
    @yield('css')
    @include('admin.layouts.partials/head-css')

    <!-- ADD THIS: CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Add in the head section or before closing body --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body>

    <div class="wrapper">
        @include('admin.layouts.partials/topbar')
        @include('admin.layouts.partials/left-sidebar')

        <div class="page-content">

            <div class="container-xxl">
                @include("admin.layouts.partials/page-title", ['title' => $title, 'subTitle' => $subTitle])
                @yield('content')
            </div>

            @include("admin.layouts.partials/footer")
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @include("admin.layouts.partials/right-sidebar")
    @include("admin.layouts.partials/footer-scripts")

    <!-- ADD THIS: Notifications JavaScript -->
    @yield('vite_scripts')
    @vite(['resources/js/notifications.js'])
    @vite(['resources/js/app.js'])

    {{-- Bootstrap Ready Utility - ensures bootstrap is available before running inline scripts --}}
    <script>
        (function() {
            var callbacks = [];
            var bootstrapReady = false;

            function checkBootstrap() {
                if (window.bootstrap) {
                    bootstrapReady = true;
                    callbacks.forEach(function(cb) { cb(); });
                    callbacks = [];
                } else {
                    setTimeout(checkBootstrap, 10);
                }
            }

            window.onBootstrapReady = function(callback) {
                if (bootstrapReady && window.bootstrap) {
                    callback();
                } else {
                    callbacks.push(callback);
                }
            };

            checkBootstrap();
        })();
    </script>

    {{-- Global User Details Modal - Available on all admin pages --}}
    @include('admin.partials.user-details-modal')

    @yield('script')
    @stack('scripts')

</body>

</html>