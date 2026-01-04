@extends('layouts.master')
@section('title')
    Dashboard
@endsection
@section('content')

<x-breadcrumb title="Dashboard" li_1="Menu" />

@livewire('menu.dashboard')

@endsection
@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/dashboard-crm.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection