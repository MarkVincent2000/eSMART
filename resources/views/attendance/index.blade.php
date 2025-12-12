@extends('layouts.master')
@section('title')
    Attendance
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/gridjs/theme/mermaid.min.css') }}">
@endsection
@section('content')

    @livewire('attendance.manage-attendance')

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/fullcalendar/index.global.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/calendar.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>


@endsection