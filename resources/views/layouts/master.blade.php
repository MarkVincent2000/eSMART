<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light"
    data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default"
    data-theme-colors="default">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Velzon - Admin & Dashboard Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
    @include('layouts.head-css')

    <style>
        /* Alpine.js Cloak */
        [x-cloak] {
            display: none !important;
        }

        /* Custom Modal Overlay */
        .custom-modal-overlay {
            overflow-y: auto;
        }

        /* Transition utilities for Alpine.js */
        .transition {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        .ease-out {
            transition-timing-function: cubic-bezier(0, 0, 0.2, 1);
        }

        .ease-in {
            transition-timing-function: cubic-bezier(0.4, 0, 1, 1);
        }

        .duration-150 {
            transition-duration: 150ms;
        }

        .duration-200 {
            transition-duration: 200ms;
        }

        .opacity-0 {
            opacity: 0;
        }

        .opacity-100 {
            opacity: 1;
        }

        /* Gap utility */
        .gap-2 {
            gap: 0.5rem;
        }

        /* Shadow for modal */
        .shadow-lg {
            box-shadow: 0 20px 25px -5px rgba(177, 177, 177, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>

    @livewireStyles
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
        @include('layouts.sidebar')
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    @include('layouts.customizer')

    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')
    @livewireScripts
    @stack('scripts')
</body>

</html>