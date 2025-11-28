@extends('layouts.master')
@section('title')
    Profile Settings
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/dropzone/dropzone.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/filepond/filepond.min.css') }}" type="text/css" />
    <link rel="stylesheet"
        href="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <x-breadcrumb title="Profile Settings" li_1="User Management" />

    <div id="user-profile-settings-wrapper">
        @livewire('user.user-profile-settings')
    </div>


@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('build/js/pages/dashboard-ecommerce.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="{{ URL::asset('build/libs/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}">
    </script>
    <script
        src="{{ URL::asset('build/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}">
        </script>
    <script
        src="{{ URL::asset('build/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}">
        </script>
    <script src="{{ URL::asset('build/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Wait for Livewire to be ready
            Livewire.hook('morph.updated', () => {
                initializeTabHandlers();
            });

            // Initialize immediately if Livewire is already loaded
            if (typeof Livewire !== 'undefined') {
                initializeTabHandlers();
            }

            function getProfileComponent() {
                const wrapper = document.getElementById('user-profile-settings-wrapper');
                if (!wrapper) return null;
                return wrapper.querySelector('[data-component="user-profile-settings"][wire\\:id]');
            }

            function initializeTabHandlers() {
                const livewireElement = getProfileComponent();

                if (!livewireElement) {
                    setTimeout(initializeTabHandlers, 100);
                    return;
                }

                // Update URL when tab changes
                function updateUrlTab(tab) {
                    const url = new URL(window.location);
                    url.searchParams.set('tab', tab);
                    window.history.pushState({}, '', url);

                    // Update Livewire component if available
                    if (typeof Livewire !== 'undefined') {
                        const componentId = livewireElement.getAttribute('wire:id');
                        try {
                            const component = Livewire.find(componentId);
                            if (component && typeof component.set === 'function') {
                                component.set('activeTab', tab);
                            }
                        } catch (e) {
                            console.log('Livewire component not found yet');
                        }
                    }
                }

                // Remove existing listeners to prevent duplicates
                const tabElements = document.querySelectorAll('a[data-bs-toggle="tab"]');

                tabElements.forEach(tab => {
                    // Clone and replace to remove old listeners
                    const newTab = tab.cloneNode(true);
                    tab.parentNode.replaceChild(newTab, tab);

                    // Add fresh listener
                    newTab.addEventListener('shown.bs.tab', function (e) {
                        const tabId = e.target.getAttribute('href').replace('#', '');
                        updateUrlTab(tabId);
                    });
                });

                // Activate tab from URL on page load
                const urlParams = new URLSearchParams(window.location.search);
                const tabParam = urlParams.get('tab');

                if (tabParam && (tabParam === 'personalDetails' || tabParam === 'changePassword')) {
                    const tabElement = document.querySelector(`a[href="#${tabParam}"]`);
                    if (tabElement) {
                        const tabTrigger = new bootstrap.Tab(tabElement);
                        tabTrigger.show();
                    }
                } else {
                    // Default to personalDetails if no tab in URL
                    const defaultTab = document.querySelector('a[href="#personalDetails"]');
                    if (defaultTab && !document.querySelector('.tab-pane.active')) {
                        const tabTrigger = new bootstrap.Tab(defaultTab);
                        tabTrigger.show();
                    }
                }
            }

            // Global function for onclick handlers
            window.updateUrlTab = function (tab) {
                const livewireElement = getProfileComponent();

                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                window.history.pushState({}, '', url);

                if (livewireElement && typeof Livewire !== 'undefined') {
                    const componentId = livewireElement.getAttribute('wire:id');
                    try {
                        const component = Livewire.find(componentId);
                        if (component && typeof component.set === 'function') {
                            component.set('activeTab', tab);
                        }
                    } catch (e) {
                        console.log('Livewire component not found');
                    }
                }
            };
        });
    </script>

@endsection