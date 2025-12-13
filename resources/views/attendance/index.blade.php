@extends('layouts.master')
@section('title')
    Manage Attendance
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/dragula/dragula.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/flatpickr/flatpickr.min.css') }}">
@endsection
@section('content')

    <x-breadcrumb title="Manage Attendance" li_1="Activities" />
    @include('attendance.attendance')
@endsection
@section('script')
    <script>
        // Pass permission status to JavaScript
        window.canManageAttendance = @json(auth()->user()->can('manage-attendance'));
    </script>
    <script src="{{ URL::asset('build/libs/dragula/dragula.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/dom-autoscroller/dom-autoscroller.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/attendance-categories.js') }}"></script>

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection