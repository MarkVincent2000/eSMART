@extends('layouts.master')
@section('title')
    Manage Attendance
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/dragula/dragula.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ URL::asset('build/libs/flatpickr/flatpickr.min.css') }}">
@endsection
@section('content')

    <x-breadcrumb title="Manage Attendance" li_1="Activities" />

    @if($attendance)
        {{-- Show student attendance view when viewing a specific attendance --}}
        @include('attendance.student-attendance', ['attendance' => $attendance, 'stats' => $stats, 'currentUserStudentAttendance' => $currentUserStudentAttendance])
    @else
        {{-- Show main attendance board --}}
        @include('attendance.attendance')
    @endif
@endsection
@section('script')
    <script>
        // Pass permission status to JavaScript
        window.canManageAttendance = @json(auth()->user()->can('manage-attendance'));
        // Pass route URL to JavaScript (folder only - ID will be passed as query parameter)
        window.studentAttendanceRoute = '{{ route("attendance.index") }}';
    </script>
    <script src="{{ URL::asset('build/libs/dragula/dragula.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/dom-autoscroller/dom-autoscroller.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    @if(!$attendance)
        <script src="{{ URL::asset('build/js/pages/attendance-categories.js') }}"></script>
    @else
        <script src="{{ URL::asset('build/js/pages/student-attendance.js') }}"></script>
    @endif

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection