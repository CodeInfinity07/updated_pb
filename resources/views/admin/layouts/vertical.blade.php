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

    {{-- Bootstrap CSS CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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

    {{-- Global User Details Modal - Available on all admin pages --}}
    @include('admin.partials.user-details-modal')

    @yield('script')
    @stack('scripts')

    {{-- Bootstrap JS CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>