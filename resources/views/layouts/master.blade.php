<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-layout="{{ \App\Models\SystemSetting::getValue('theme.layout', 'vertical') }}"
    data-topbar="{{ \App\Models\SystemSetting::getValue('theme.topbar', 'light') }}"
    data-sidebar="{{ \App\Models\SystemSetting::getValue('theme.sidebar', 'dark') }}"
    data-sidebar-size="{{ \App\Models\SystemSetting::getValue('theme.sidebar_size', 'lg') }}"
    data-sidebar-image="{{ \App\Models\SystemSetting::getValue('theme.sidebar_image', 'none') }}"
    data-preloader="{{ \App\Models\SystemSetting::getValue('theme.preloader', 'disable') }}"
    data-theme="{{ \App\Models\SystemSetting::getValue('theme.theme', 'default') }}"
    data-theme-colors="{{ \App\Models\SystemSetting::getValue('theme.theme_colors', 'default') }}">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | {{ \App\Models\SystemSetting::getValue('site.short_name', 'smart') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta content="eSMART Campus - Student Management System" name="description" />
    <meta content="eSMART Campus Team" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon"
        href="{{ \App\Models\SystemSetting::getAsset('site.favicon', URL::asset('build/images/favicon.ico')) }}">
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

        /* Custom Modal Overlay */
        .custom-modal-overlay {
            overflow: hidden;
        }

        /* Allow scrolling only on the modal content container */
        .custom-modal-overlay>div[style*="overflow-y: auto"] {
            overflow-y: auto !important;
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

        /* Ensure modal content remains sharp and clear */
        .modal-card-dark {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            transform: translate3d(0, 0, 0);
            -webkit-transform: translate3d(0, 0, 0);
        }

        /* Prevent blur on modal content */
        .modal-card-dark * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        /* Mobile Viewport Fixes */
        .custom-modal-overlay {
            height: 100vh;
            height: 100dvh;
        }

        .custom-modal-overlay > div[style*="min-height: 100vh"] {
            min-height: 100vh !important;
            min-height: 100dvh !important;
        }

        .modal-card-dark {
            max-height: calc(100vh - 2rem) !important;
            max-height: calc(100dvh - 2rem) !important;
        }

        @media (max-width: 576px) {
            .custom-modal-overlay > div {
                padding: 0.5rem !important;
            }
            .modal-card-dark {
                max-height: calc(100vh - 1rem) !important;
                max-height: calc(100dvh - 1rem) !important;
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