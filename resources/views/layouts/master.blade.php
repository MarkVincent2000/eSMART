<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light"
    data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default"
    data-theme-colors="default">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | eSMART Campus</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="eSMART Campus - Student Management System" name="description" />
    <meta content="eSMART Campus Team" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
    @include('layouts.head-css')




    <style>
        /* Dark mode styling for modal card - using galaxy theme colors */
        [data-bs-theme="dark"] .modal-card-dark,
        html[data-bs-theme="dark"] .modal-card-dark {
            background-color: var(--vz-secondary-bg, #05192f) !important;
            /* Galaxy theme dark mode secondary background color */
        }

        /* Galaxy theme specific dark mode */
        [data-theme="galaxy"][data-bs-theme="dark"] .modal-card-dark,
        html[data-theme="galaxy"][data-bs-theme="dark"] .modal-card-dark {
            background-color: var(--vz-secondary-bg, #05192f) !important;
            /* Galaxy theme dark mode secondary background color */
        }

        /* Light mode - white background */
        [data-bs-theme="light"] .modal-card-dark,
        :not([data-bs-theme]) .modal-card-dark,
        html:not([data-bs-theme="dark"]) .modal-card-dark {
            background-color: #fff !important;
        }

        /* Dark mode styling for select dropdown - using galaxy theme colors */
        [data-bs-theme="dark"] .select-dropdown-dark,
        html[data-bs-theme="dark"] .select-dropdown-dark {
            background-color: var(--vz-secondary-bg, #05192f) !important;
            /* Galaxy theme dark mode secondary background color */
        }

        /* Galaxy theme specific dark mode for select dropdown */
        [data-theme="galaxy"][data-bs-theme="dark"] .select-dropdown-dark,
        html[data-theme="galaxy"][data-bs-theme="dark"] .select-dropdown-dark {
            background-color: var(--vz-secondary-bg, #05192f) !important;
            /* Galaxy theme dark mode secondary background color */
        }

        /* Light mode - white background for select dropdown */
        [data-bs-theme="light"] .select-dropdown-dark,
        :not([data-bs-theme]) .select-dropdown-dark,
        html:not([data-bs-theme="dark"]) .select-dropdown-dark {
            background-color: #fff !important;
        }

        /* Alpine.js Cloak */
        [x-cloak] {
            display: none !important;
        }

        /* Prevent animation on cloaked elements */
        [x-cloak] .modal-bounce,
        [x-cloak].modal-bounce {
            animation: none !important;
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

        .duration-300 {
            transition-duration: 300ms;
        }

        .opacity-0 {
            opacity: 0;
        }

        .opacity-100 {
            opacity: 1;
        }

        /* Transform utilities for modal animations */
        .transform {
            transform: translateZ(0);
        }

        /* Individual transform utilities */
        .-translate-y-12 {
            transform: translateY(-3rem);
        }

        .translate-y-0 {
            transform: translateY(0);
        }

        .scale-95 {
            transform: scale(0.95);
        }

        .scale-100 {
            transform: scale(1);
        }

        /* Combined transforms for modal animations - higher specificity */
        .transform.-translate-y-12.scale-95,
        .-translate-y-12.scale-95 {
            transform: translateY(-3rem) scale(0.95) !important;
        }

        .transform.translate-y-0.scale-100,
        .translate-y-0.scale-100 {
            transform: translateY(0) scale(1) !important;
        }

        /* Gap utility */
        .gap-2 {
            gap: 0.5rem;
        }

        /* Shadow for modal */
        .shadow-lg {
            box-shadow: 0 20px 25px -5px rgba(177, 177, 177, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Modal Bounce Animation */
        .card.modal-bounce {
            /* Ensure the animation is clean and not affected by x-cloak */
            animation: modalBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
            animation-fill-mode: both !important;
            transform-origin: center center !important;
        }

        /* Ensure animation works even when element is visible */
        .custom-modal-overlay:not([x-cloak]) .card.modal-bounce,
        .custom-modal-overlay[x-show="true"] .card.modal-bounce {
            animation: modalBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) !important;
        }

        @keyframes modalBounce {

            0%,
            100% {
                transform: scale(1);
            }

            20% {
                transform: scale(0.96);
                /* Slightly smaller */
            }

            50% {
                transform: scale(1.03);
                /* Slightly larger for the 'bounce' effect */
            }

            80% {
                transform: scale(0.99);
            }
        }
    </style>

    @livewireStyles

    {{--
    <script>
        // Force dark mode always
        (function () {
            // Set dark mode in sessionStorage
            if (typeof (Storage) !== "undefined") {
                sessionStorage.setItem("data-bs-theme", "dark");
            }
            // Set dark mode attribute on document element
            document.documentElement.setAttribute("data-bs-theme", "dark");
        })();
    </script> --}}
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