@extends('layouts.master')
@section('title')
    Manage Students
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/gridjs/theme/mermaid.min.css') }}">
@endsection
@section('content')
    <x-breadcrumb title="Manage Students" li_1="Student Management" />


    @livewire('student.manage-student')

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/nouislider/nouislider.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/wnumb/wNumb.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/gridjs/gridjs.umd.js') }}"></script>
    <script src="https://unpkg.com/gridjs/plugins/selection/dist/selection.umd.js"></script>


    <script src="{{ URL::asset('build/js/pages/ecommerce-product-list.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>


@endsection