@extends('layouts.master')
@section('title')
    @lang('translation.profile')
@endsection
@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}">
@endsection
@section('content')

    <div id="user-profile-wrapper">
        @livewire('profile.user-profile')
    </div>

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>

    <script src="{{ URL::asset('build/js/pages/profile.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function getProfileComponent() {
                const wrapper = document.getElementById('user-profile-wrapper');
                return wrapper ? wrapper.querySelector('[wire\\:id]') : null;
            }

            window.updateProfileTab = function (tab) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);

                if (typeof Livewire !== 'undefined') {
                    const componentEl = getProfileComponent();
                    if (componentEl) {
                        const componentId = componentEl.getAttribute('wire:id');
                        const component = Livewire.find(componentId);
                        if (component && typeof component.set === 'function') {
                            component.set('activeTab', tab);
                        }
                    }
                }
            };

            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab');
            if (tab) {
                const targetId = tab === 'overview' ? 'overview-tab' : tab;
                const tabLink = document.querySelector(`.profile-nav a[href="#${targetId}"]`);
                if (tabLink && typeof bootstrap !== 'undefined') {
                    const tabInstance = bootstrap.Tab.getOrCreateInstance(tabLink);
                    tabInstance.show();
                }
            }
        });
    </script>
@endsection