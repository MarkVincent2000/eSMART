@extends('layouts.master')
@section('title')
    System Settings
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/dragula/dragula.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/filepond/filepond.min.css') }}" type="text/css" />
    <link rel="stylesheet"
        href="{{ URL::asset('build/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">
@endsection
@section('content')
    <x-breadcrumb title="System Settings" li_1="Management" />

    @can('view-system-settings')

        @livewire('management.system-settings')

    @else
        <div class="alert alert-danger alert-dismissible alert-additional fade show mb-xl-0 material-shadow" role="alert">
            <div class="alert-body">

                <div class="d-flex">
                    <div class="flex-shrink-0 me-3">
                        <i class="ri-alert-line fs-16 align-middle"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="alert-heading">Permission Denied</h5>
                        <p class="mb-0">You are not authorized to access this page.</p>
                    </div>
                </div>
            </div>
            <div class="alert-content">
                <p class="mb-0">You are not authorized to access this page.</p>
            </div>
        </div>
    @endcan


@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/dragula/dragula.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/dom-autoscroller/dom-autoscroller.min.js') }}"></script>
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

    {{-- FilePond initialization is handled by Alpine.js in the Livewire component --}}
    {{--
    <script src="{{ URL::asset('build/js/pages/form-file-upload.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/tasks-kanban.init.js') }}"></script> --}}
    <script src="{{ URL::asset('build/js/app.js') }}"></script>

@endsection