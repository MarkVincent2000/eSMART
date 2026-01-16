@extends('layouts.master')
@section('title')
    Subjects
@endsection
@section('content')

<x-breadcrumb title="Subjects" li_1="Academic" />

@can('view-subject-management')
    @livewire('student.manage-subject')
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
    <!-- apexcharts -->
    
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection