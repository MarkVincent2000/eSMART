@extends('layouts.master-without-nav')
@section('title')
    Home - eSMART Campus
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('body')

    <body data-bs-spy="scroll" data-bs-target="#navbar-example">
@endsection
    @section('content')
        {{-- @component('components.breadcrumb')
        @slot('li_1') Icons @endslot
        @slot('title') Landing @endslot
        @endcomponent --}}


        <body data-bs-spy="scroll" data-bs-target="#navbar-example">

            <!-- Begin page -->
            <div class="layout-wrapper landing">
                <nav class="navbar navbar-expand-lg navbar-landing fixed-top" id="navbar">
                    <div class="container">
                        <a class="navbar-brand" href="dashboard">
                            <img src="{{ \App\Models\SystemSetting::getAsset('site.landing_logo_dark', URL::asset('build/images/smart-logo-dark.png')) }}"
                                class="card-logo card-logo-dark" alt="logo dark" height="17">
                            <img src="{{ \App\Models\SystemSetting::getAsset('site.landing_logo_light', URL::asset('build/images/smart-logo-light.png')) }}"
                                class="card-logo card-logo-light" alt="logo light" height="17">
                        </a>
                        <button class="navbar-toggler py-0 fs-20 text-body" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <i class="mdi mdi-menu"></i>
                        </button>

                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav mx-auto mt-2 mt-lg-0" id="navbar-example">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#hero">Home</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#services">Services</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#features">Features</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#plans">Plans</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#reviews">Reviews</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#team">Team</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#contact">Contact</a>
                                </li>
                            </ul>

                            <div class="">
                                <a href="{{ route('login') }}"
                                    class="btn btn-link fw-medium text-decoration-none text-dark">Sign
                                    in</a>
                                <a href="{{ route('register') }}" class="btn btn-primary">Sign Up</a>
                            </div>
                        </div>

                    </div>
                </nav>
                <!-- end navbar -->
                <div class="vertical-overlay" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent.show"></div>

                <!-- start hero section -->
                <section class="section pb-0 hero-section" id="hero">
                    <div class="bg-overlay bg-overlay-pattern"></div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8 col-sm-10">
                                <div class="text-center mt-lg-5 pt-5">
                                    <h1 class="display-6 fw-semibold mb-3 lh-base">
                                        {{ \App\Models\SystemSetting::getValue('site.tagline', 'The better way to manage your campus') }}
                                        with <span
                                            class="text-success">{{ \App\Models\SystemSetting::getValue('site.name', 'eSMART Campus') }}
                                        </span>
                                    </h1>
                                    <p class="lead text-muted lh-base">
                                        {{ \App\Models\SystemSetting::getValue('site.description', 'eSMART Campus is a comprehensive student management system designed to streamline campus operations, student enrollment, attendance tracking, and academic management.') }}
                                    </p>

                                    <div class="d-flex gap-2 justify-content-center mt-4">
                                        <a href="{{ route('register') }}" class="btn btn-primary">Get Started <i
                                                class="ri-arrow-right-line align-middle ms-1"></i></a>
                                        {{-- <a href="pages-pricing" class="btn btn-danger">View Plans <i
                                                class="ri-eye-line align-middle ms-1"></i></a> --}}
                                    </div>
                                </div>

                                <div class="mt-4 mt-sm-5 pt-sm-5 mb-sm-n5 demo-carousel">
                                    <div class="demo-img-patten-top d-none d-sm-block">
                                        <img src="{{ URL::asset('build/images/landing/img-pattern.png') }}"
                                            class="d-block img-fluid" alt="...">
                                    </div>
                                    <div class="demo-img-patten-bottom d-none d-sm-block">
                                        <img src="{{ URL::asset('build/images/landing/img-pattern.png') }}"
                                            class="d-block img-fluid" alt="...">
                                    </div>
                                    <div class="carousel slide carousel-fade" data-bs-ride="carousel">
                                        <div class="carousel-inner shadow-lg p-2 bg-white rounded">
                                            <div class="carousel-item active" data-bs-interval="2000">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.carousel_image_1', URL::asset('build/images/demos/default.png')) }}"
                                                    class="d-block w-100" alt="...">
                                            </div>
                                            <div class="carousel-item" data-bs-interval="2000">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.carousel_image_2', URL::asset('build/images/demos/saas.png')) }}"
                                                    class="d-block w-100" alt="...">
                                            </div>
                                            <div class="carousel-item" data-bs-interval="2000">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.carousel_image_3', URL::asset('build/images/demos/material.png')) }}"
                                                    class="d-block w-100" alt="...">
                                            </div>
                                            <div class="carousel-item" data-bs-interval="2000">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.carousel_image_4', URL::asset('build/images/demos/minimal.png')) }}"
                                                    class="d-block w-100" alt="...">
                                            </div>
                                            <div class="carousel-item" data-bs-interval="2000">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.carousel_image_5', URL::asset('build/images/demos/creative.png')) }}"
                                                    class="d-block w-100" alt="...">
                                            </div>
                                            <div class="carousel-item" data-bs-interval="2000">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.carousel_image_6', URL::asset('build/images/demos/modern.png')) }}"
                                                    class="d-block w-100" alt="...">
                                            </div>
                                            <div class="carousel-item" data-bs-interval="2000">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.carousel_image_7', URL::asset('build/images/demos/interactive.png')) }}"
                                                    class="d-block w-100" alt="...">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                    <div class="position-absolute start-0 end-0 bottom-0 hero-shape-svg">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink"
                            viewBox="0 0 1440 120">
                            <g mask="url(&quot;#SvgjsMask1003&quot;)" fill="none">
                                <path d="M 0,118 C 288,98.6 1152,40.4 1440,21L1440 140L0 140z">
                                </path>
                            </g>
                        </svg>
                    </div>
                    <!-- end shape -->
                </section>
                <!-- end hero section -->

                <!-- start client section -->
                <div class="pt-5 mt-5">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">

                                <div class="text-center mt-5">
                                    <h5 class="fs-20">Trusted <span class="text-primary text-decoration-underline">by</span>
                                        the world's best</h5>

                                    <!-- Swiper -->
                                    <div class="swiper trusted-client-slider mt-sm-5 mt-4 mb-sm-5 mb-4" dir="ltr">
                                        <div class="swiper-wrapper">
                                            <div class="swiper-slide">
                                                <div class="client-images">
                                                    <img src="{{ \App\Models\SystemSetting::getAsset('landing.client_logo_1', URL::asset('build/images/clients/amazon.svg')) }}"
                                                        alt="client-img" class="mx-auto img-fluid d-block">
                                                </div>
                                            </div>
                                            <div class="swiper-slide">
                                                <div class="client-images">
                                                    <img src="{{ \App\Models\SystemSetting::getAsset('landing.client_logo_2', URL::asset('build/images/clients/walmart.svg')) }}"
                                                        alt="client-img" class="mx-auto img-fluid d-block">
                                                </div>
                                            </div>
                                            <div class="swiper-slide">
                                                <div class="client-images">
                                                    <img src="{{ \App\Models\SystemSetting::getAsset('landing.client_logo_3', URL::asset('build/images/clients/lenovo.svg')) }}"
                                                        alt="client-img" class="mx-auto img-fluid d-block">
                                                </div>
                                            </div>
                                            <div class="swiper-slide">
                                                <div class="client-images">
                                                    <img src="{{ \App\Models\SystemSetting::getAsset('landing.client_logo_4', URL::asset('build/images/clients/paypal.svg')) }}"
                                                        alt="client-img" class="mx-auto img-fluid d-block">
                                                </div>
                                            </div>
                                            <div class="swiper-slide">
                                                <div class="client-images">
                                                    <img src="{{ \App\Models\SystemSetting::getAsset('landing.client_logo_5', URL::asset('build/images/clients/shopify.svg')) }}"
                                                        alt="client-img" class="mx-auto img-fluid d-block">
                                                </div>
                                            </div>
                                            <div class="swiper-slide">
                                                <div class="client-images">
                                                    <img src="{{ \App\Models\SystemSetting::getAsset('landing.client_logo_6', URL::asset('build/images/clients/verizon.svg')) }}"
                                                        alt="client-img" class="mx-auto img-fluid d-block">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </div>
                <!-- end client section -->

                <!-- start services -->
                <section class="section" id="services">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="text-center mb-5">
                                    <h1 class="mb-3 ff-secondary fw-semibold lh-base">
                                        {{ \App\Models\SystemSetting::getValue('landing.services_title', 'A Digital web design studio creating modern & engaging online') }}
                                    </h1>
                                    <p class="text-muted">
                                        {{ \App\Models\SystemSetting::getValue('landing.services_description', 'To achieve this, it would be necessary to have uniform grammar, pronunciation and more common words. If several languages coalesce the grammar') }}
                                    </p>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                        <div class="row g-3">
                            <div class="col-lg-4">
                                <div class="d-flex p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle">
                                                <i
                                                    class="{{ \App\Models\SystemSetting::getValue('landing.service_1_icon', 'ri-pencil-ruler-2-line') }} fs-36"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-18">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_1_title', 'Creative Design') }}
                                        </h5>
                                        <p class="text-muted my-3 ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_1_description', 'The creative design includes designs that are unique, effective and memorable.') }}
                                        </p>
                                        <div>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.service_1_link', '#') }}"
                                                class="fs-13 fw-medium">Learn More <i
                                                    class="ri-arrow-right-s-line align-bottom"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-4">
                                <div class="d-flex p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle">
                                                <i
                                                    class="{{ \App\Models\SystemSetting::getValue('landing.service_2_icon', 'ri-palette-line') }} fs-36"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-18">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_2_title', 'Unlimited Colors') }}
                                        </h5>
                                        <p class="text-muted my-3 ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_2_description', 'The collection of rules and guidelines which designers use to communicate with users through appealing.') }}
                                        </p>
                                        <div>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.service_2_link', '#') }}"
                                                class="fs-13 fw-medium">Learn More <i
                                                    class="ri-arrow-right-s-line align-bottom"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-4">
                                <div class="d-flex p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle">
                                                <i
                                                    class="{{ \App\Models\SystemSetting::getValue('landing.service_3_icon', 'ri-lightbulb-flash-line') }} fs-36"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-18">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_3_title', 'Strategy Solutions') }}
                                        </h5>
                                        <p class="text-muted my-3 ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_3_description', 'Business development firm that provides strategic planning, market research services and project.') }}
                                        </p>
                                        <div>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.service_3_link', '#') }}"
                                                class="fs-13 fw-medium">Learn More <i
                                                    class="ri-arrow-right-s-line align-bottom"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-4">
                                <div class="d-flex p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle">
                                                <i
                                                    class="{{ \App\Models\SystemSetting::getValue('landing.service_4_icon', 'ri-customer-service-line') }} fs-36"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-18">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_4_title', 'Awesome Support') }}
                                        </h5>
                                        <p class="text-muted my-3 ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_4_description', 'Awesome Support is the most versatile and feature-rich support plugin for all version.') }}
                                        </p>
                                        <div>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.service_4_link', '#') }}"
                                                class="fs-13 fw-medium">Learn More <i
                                                    class="ri-arrow-right-s-line align-bottom"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-4">
                                <div class="d-flex p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle">
                                                <i
                                                    class="{{ \App\Models\SystemSetting::getValue('landing.service_5_icon', 'ri-stack-line') }} fs-36"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-18">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_5_title', 'Truly Multipurpose') }}
                                        </h5>
                                        <p class="text-muted my-3 ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_5_description', 'You usually get a broad range of options to play with. This enables you to use a single theme across multiple.') }}
                                        </p>
                                        <div>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.service_5_link', '#') }}"
                                                class="fs-13 fw-medium">Learn More <i
                                                    class="ri-arrow-right-s-line align-bottom"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-4">
                                <div class="d-flex p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle">
                                                <i
                                                    class="{{ \App\Models\SystemSetting::getValue('landing.service_6_icon', 'ri-settings-2-line') }} fs-36"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-18">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_6_title', 'Easy to customize') }}
                                        </h5>
                                        <p class="text-muted my-3 ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_6_description', 'Personalise your own website, no matter what theme and what customization options.') }}
                                        </p>
                                        <div>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.service_6_link', '#') }}"
                                                class="fs-13 fw-medium">Learn More <i
                                                    class="ri-arrow-right-s-line align-bottom"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-lg-4">
                                <div class="d-flex p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle">
                                                <i
                                                    class="{{ \App\Models\SystemSetting::getValue('landing.service_7_icon', 'ri-slideshow-line') }} fs-36"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-18">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_7_title', 'Responsive & Clean Design') }}
                                        </h5>
                                        <p class="text-muted my-3 ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_7_description', 'Responsive design is a graphic user interface (GUI) design approach used to create content.') }}
                                        </p>
                                        <div>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.service_7_link', '#') }}"
                                                class="fs-13 fw-medium">Learn More <i
                                                    class="ri-arrow-right-s-line align-bottom"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-4">
                                <div class="d-flex p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle">
                                                <i
                                                    class="{{ \App\Models\SystemSetting::getValue('landing.service_8_icon', 'ri-google-fill') }} fs-36"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-18">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_8_title', 'Google Font Collection') }}
                                        </h5>
                                        <p class="text-muted my-3 ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_8_description', 'Google Fonts is a collection of 915 fonts, all available to use for free on your website.') }}
                                        </p>
                                        <div>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.service_8_link', '#') }}"
                                                class="fs-13 fw-medium">Learn More <i
                                                    class="ri-arrow-right-s-line align-bottom"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-4">
                                <div class="d-flex p-3">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm icon-effect">
                                            <div class="avatar-title bg-transparent text-success rounded-circle">
                                                <i
                                                    class="{{ \App\Models\SystemSetting::getValue('landing.service_9_icon', 'ri-briefcase-5-line') }} fs-36"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fs-18">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_9_title', 'Top Industry Specialists') }}
                                        </h5>
                                        <p class="text-muted my-3 ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('landing.service_9_description', 'An industrial specialist works with industrial operations to ensure that manufacturing facilities work.') }}
                                        </p>
                                        <div>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.service_9_link', '#') }}"
                                                class="fs-13 fw-medium">Learn More <i
                                                    class="ri-arrow-right-s-line align-bottom"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end services -->

                <!-- start features -->
                <section class="section bg-light py-5" id="features">
                    <div class="container">
                        <div class="row align-items-center gy-4">
                            <div class="col-lg-6 col-sm-7 mx-auto">
                                <div>
                                    <img src="{{ \App\Models\SystemSetting::getAsset('landing.feature_image', URL::asset('build/images/landing/features/img-1.png')) }}"
                                        alt="" class="img-fluid mx-auto">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="text-muted">
                                    <div class="avatar-sm icon-effect mb-4">
                                        <div class="avatar-title bg-transparent rounded-circle text-success h1">
                                            <i
                                                class="{{ \App\Models\SystemSetting::getValue('landing.feature_icon', 'ri-collage-line') }} fs-36"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-3 fs-20">
                                        {{ \App\Models\SystemSetting::getValue('landing.feature_title', 'Huge collection of widgets') }}
                                    </h3>
                                    <p class="mb-4 ff-secondary fs-16">
                                        {{ \App\Models\SystemSetting::getValue('landing.feature_description', 'Collection widgets specialize in displaying many elements of the same type, such as a collection of pictures from a collection of articles from a news app or a collection of messages from a communication app.') }}
                                    </p>

                                    <div class="row pt-3">
                                        <div class="col-3">
                                            <div class="text-center">
                                                <h4>{{ \App\Models\SystemSetting::getValue('landing.feature_stat_1_value', '5') }}
                                                </h4>
                                                <p>{{ \App\Models\SystemSetting::getValue('landing.feature_stat_1_label', 'Dashboards') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-center">
                                                <h4>{{ \App\Models\SystemSetting::getValue('landing.feature_stat_2_value', '150+') }}
                                                </h4>
                                                <p>{{ \App\Models\SystemSetting::getValue('landing.feature_stat_2_label', 'Pages') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-center">
                                                <h4>{{ \App\Models\SystemSetting::getValue('landing.feature_stat_3_value', '7+') }}
                                                </h4>
                                                <p>{{ \App\Models\SystemSetting::getValue('landing.feature_stat_3_label', 'Functional Apps') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end features -->

                <!-- start cta -->
                <section class="py-5 bg-primary position-relative">
                    <div class="bg-overlay bg-overlay-pattern opacity-50"></div>
                    <div class="container">
                        <div class="row align-items-center gy-4">
                            <div class="col-sm">
                                <div>
                                    <h4 class="text-white mb-0 fw-semibold">
                                        {{ \App\Models\SystemSetting::getValue('landing.cta_title', 'Build your campus management system with eSMART Campus') }}
                                    </h4>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-sm-auto">
                                <div>
                                    <a href="{{ \App\Models\SystemSetting::getValue('landing.cta_button_link', route('register')) }}"
                                        class="btn bg-gradient btn-danger"><i
                                            class="{{ \App\Models\SystemSetting::getValue('landing.cta_button_icon', 'ri-user-add-line') }} align-middle me-1"></i>
                                        {{ \App\Models\SystemSetting::getValue('landing.cta_button_text', 'Get Started') }}</a>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end cta -->

                <!-- start features -->
                <section class="section">
                    <div class="container">
                        <div class="row align-items-center gy-4">
                            <div class="col-lg-6 order-2 order-lg-1">
                                <div class="text-muted">
                                    <h5 class="fs-12 text-uppercase text-success">
                                        {{ \App\Models\SystemSetting::getValue('landing.feature_block_1_badge', 'Design') }}
                                    </h5>
                                    <h4 class="mb-3">
                                        {{ \App\Models\SystemSetting::getValue('landing.feature_block_1_title', 'Well Designed Dashboards') }}
                                    </h4>
                                    <p class="mb-4 ff-secondary">
                                        {{ \App\Models\SystemSetting::getValue('landing.feature_block_1_description', 'Quality Dashboards (QD) is a condition-specific, actionable web-based application for quality reporting and population management that is integrated into the Electronic Health Record (EHR).') }}
                                    </p>

                                    <div class="row">
                                        <div class="col-sm-5">
                                            <div class="vstack gap-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-2">
                                                        <div class="avatar-xs icon-effect">
                                                            <div
                                                                class="avatar-title bg-transparent text-success rounded-circle h2">
                                                                <i class="ri-check-fill"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-0">
                                                            {{ \App\Models\SystemSetting::getValue('landing.feature_block_1_item_1', 'Ecommerce') }}
                                                        </h5>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-2">
                                                        <div class="avatar-xs icon-effect">
                                                            <div
                                                                class="avatar-title bg-transparent text-success rounded-circle h2">
                                                                <i class="ri-check-fill"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-0">
                                                            {{ \App\Models\SystemSetting::getValue('landing.feature_block_1_item_2', 'Analytics') }}
                                                        </h5>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-2">
                                                        <div class="avatar-xs icon-effect">
                                                            <div
                                                                class="avatar-title bg-transparent text-success rounded-circle h2">
                                                                <i class="ri-check-fill"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-0">
                                                            {{ \App\Models\SystemSetting::getValue('landing.feature_block_1_item_3', 'CRM') }}
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-5">
                                            <div class="vstack gap-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-2">
                                                        <div class="avatar-xs icon-effect">
                                                            <div
                                                                class="avatar-title bg-transparent text-success rounded-circle h2">
                                                                <i class="ri-check-fill"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-0">
                                                            {{ \App\Models\SystemSetting::getValue('landing.feature_block_1_item_4', 'Crypto') }}
                                                        </h5>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 me-2">
                                                        <div class="avatar-xs icon-effect">
                                                            <div
                                                                class="avatar-title bg-transparent text-success rounded-circle h2">
                                                                <i class="ri-check-fill"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h5 class="fs-14 mb-0">
                                                            {{ \App\Models\SystemSetting::getValue('landing.feature_block_1_item_5', 'Projects') }}
                                                        </h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <a href="{{ \App\Models\SystemSetting::getValue('landing.feature_block_1_button_link', '/dashboard') }}"
                                            class="btn btn-primary">{{ \App\Models\SystemSetting::getValue('landing.feature_block_1_button_text', 'Learn More') }}
                                            <i class="ri-arrow-right-line align-middle ms-1"></i></a>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-6 col-sm-7 col-10 ms-auto order-1 order-lg-2">
                                <div>
                                    <img src="{{ \App\Models\SystemSetting::getAsset('landing.feature_block_1_image', URL::asset('build/images/landing/features/img-2.png')) }}"
                                        alt="" class="img-fluid">
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row align-items-center mt-5 pt-lg-5 gy-4">
                            <div class="col-lg-6 col-sm-7 col-10 mx-auto">
                                <div>
                                    <img src="{{ \App\Models\SystemSetting::getAsset('landing.feature_block_2_image', URL::asset('build/images/landing/features/img-3.png')) }}"
                                        alt="" class="img-fluid">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="text-muted ps-lg-5">
                                    <h5 class="fs-12 text-uppercase text-success">
                                        {{ \App\Models\SystemSetting::getValue('landing.feature_block_2_badge', 'structure') }}
                                    </h5>
                                    <h4 class="mb-3">
                                        {{ \App\Models\SystemSetting::getValue('landing.feature_block_2_title', 'Well Documented') }}
                                    </h4>
                                    <p class="mb-4">
                                        {{ \App\Models\SystemSetting::getValue('landing.feature_block_2_description', 'used to describe something that is known about or known to be true because there are many documents that describe it, prove it, etc.') }}
                                    </p>

                                    <div class="vstack gap-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="avatar-xs icon-effect">
                                                    <div class="avatar-title bg-transparent text-success rounded-circle h2">
                                                        <i class="ri-check-fill"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-0">
                                                    {{ \App\Models\SystemSetting::getValue('landing.feature_block_2_item_1', 'Dynamic Conetnt') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="avatar-xs icon-effect">
                                                    <div class="avatar-title bg-transparent text-success rounded-circle h2">
                                                        <i class="ri-check-fill"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-0">
                                                    {{ \App\Models\SystemSetting::getValue('landing.feature_block_2_item_2', 'Setup plugin\'s information.') }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-2">
                                                <div class="avatar-xs icon-effect">
                                                    <div class="avatar-title bg-transparent text-success rounded-circle h2">
                                                        <i class="ri-check-fill"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-0">
                                                    {{ \App\Models\SystemSetting::getValue('landing.feature_block_2_item_3', 'Themes customization information') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end features -->

                <!-- start plan -->
                <section class="section bg-light" id="plans">
                    <div class="bg-overlay bg-overlay-pattern"></div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="text-center mb-5">
                                    <h3 class="mb-3 fw-semibold">
                                        {{ \App\Models\SystemSetting::getValue('landing.pricing_title', 'Choose the plan that\'s right for you') }}
                                    </h3>
                                    <p class="text-muted mb-4">
                                        {{ \App\Models\SystemSetting::getValue('landing.pricing_description', 'Simple pricing. No hidden fees. Advanced features for you business.') }}
                                    </p>

                                    <div class="d-flex justify-content-center align-items-center">
                                        <div>
                                            <h5 class="fs-14 mb-0">
                                                {{ \App\Models\SystemSetting::getValue('landing.pricing_monthly_label', 'Month') }}
                                            </h5>
                                        </div>
                                        <div class="form-check form-switch fs-20 ms-3 " onclick="check()">
                                            <input class="form-check-input" type="checkbox" id="plan-switch">
                                            <label class="form-check-label" for="plan-switch"></label>
                                        </div>
                                        <div>
                                            <h5 class="fs-14 mb-0">
                                                {{ \App\Models\SystemSetting::getValue('landing.pricing_annual_label', 'Annual') }}
                                                <span class="badge bg-success-subtle text-success">Save
                                                    {{ \App\Models\SystemSetting::getValue('landing.pricing_save_percentage', '20%') }}</span>
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                        <div class="row gy-4">
                            <div class="col-lg-4">
                                <div class="card plan-box mb-0">
                                    <div class="card-body p-4 m-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1 fw-semibold">
                                                    {{ \App\Models\SystemSetting::getValue('landing.plan_1_name', 'Basic Plan') }}
                                                </h5>
                                                <p class="text-muted mb-0">
                                                    {{ \App\Models\SystemSetting::getValue('landing.plan_1_subtitle', 'For Startup') }}
                                                </p>
                                            </div>
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-light rounded-circle text-primary">
                                                    <i
                                                        class="{{ \App\Models\SystemSetting::getValue('landing.plan_1_icon', 'ri-book-mark-line') }} fs-20"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="py-4 text-center">
                                            <h1 class="month"><sup><small>₱</small></sup><span
                                                    class="ff-secondary fw-bold">{{ \App\Models\SystemSetting::getValue('landing.plan_1_price_monthly', '19') }}</span>
                                                <span class="fs-13 text-muted">/Month</span>
                                            </h1>
                                            <h1 class="annual"><sup><small>₱</small></sup><span
                                                    class="ff-secondary fw-bold">{{ \App\Models\SystemSetting::getValue('landing.plan_1_price_annual', '171') }}</span>
                                                <span class="fs-13 text-muted">/Year</span>
                                            </h1>
                                        </div>

                                        <div>
                                            <ul class="list-unstyled text-muted vstack gap-3 ff-secondary">
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_1_feature_1', 'Upto <b>3</b> Projects') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_1_feature_2', 'Upto <b>299</b> Customers') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_1_feature_3', 'Scalable Bandwidth') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_1_feature_4', '<b>5</b> FTP Login') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div
                                                            class="flex-shrink-0 {{ \App\Models\SystemSetting::getValue('landing.plan_1_feature_5_enabled', 'false') === 'true' ? 'text-success' : 'text-danger' }} me-1">
                                                            <i
                                                                class="{{ \App\Models\SystemSetting::getValue('landing.plan_1_feature_5_enabled', 'false') === 'true' ? 'ri-checkbox-circle-fill' : 'ri-close-circle-fill' }} fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_1_feature_5', '<b>24/7</b> Support') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div
                                                            class="flex-shrink-0 {{ \App\Models\SystemSetting::getValue('landing.plan_1_feature_6_enabled', 'false') === 'true' ? 'text-success' : 'text-danger' }} me-1">
                                                            <i
                                                                class="{{ \App\Models\SystemSetting::getValue('landing.plan_1_feature_6_enabled', 'false') === 'true' ? 'ri-checkbox-circle-fill' : 'ri-close-circle-fill' }} fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_1_feature_6', '<b>Unlimited</b> Storage') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div
                                                            class="flex-shrink-0 {{ \App\Models\SystemSetting::getValue('landing.plan_1_feature_7_enabled', 'false') === 'true' ? 'text-success' : 'text-danger' }} me-1">
                                                            <i
                                                                class="{{ \App\Models\SystemSetting::getValue('landing.plan_1_feature_7_enabled', 'false') === 'true' ? 'ri-checkbox-circle-fill' : 'ri-close-circle-fill' }} fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_1_feature_7', 'Domain') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="mt-4">
                                                <a href="{{ \App\Models\SystemSetting::getValue('landing.plan_1_button_link', 'javascript:void(0);') }}"
                                                    class="btn btn-soft-success w-100">{{ \App\Models\SystemSetting::getValue('landing.plan_1_button_text', 'Get Started') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-lg-4">
                                <div class="card plan-box mb-0 ribbon-box right">
                                    <div class="card-body p-4 m-2">
                                        @if(\App\Models\SystemSetting::getValue('landing.plan_2_badge_text', 'Popular'))
                                            <div class="ribbon-two ribbon-two-danger">
                                                <span>{{ \App\Models\SystemSetting::getValue('landing.plan_2_badge_text', 'Popular') }}</span>
                                            </div>
                                        @endif
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1 fw-semibold">
                                                    {{ \App\Models\SystemSetting::getValue('landing.plan_2_name', 'Pro Business') }}
                                                </h5>
                                                <p class="text-muted mb-0">
                                                    {{ \App\Models\SystemSetting::getValue('landing.plan_2_subtitle', 'Professional plans') }}
                                                </p>
                                            </div>
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-light rounded-circle text-primary">
                                                    <i
                                                        class="{{ \App\Models\SystemSetting::getValue('landing.plan_2_icon', 'ri-medal-fill') }} fs-20"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="py-4 text-center">
                                            <h1 class="month"><sup><small>₱</small></sup><span
                                                    class="ff-secondary fw-bold">{{ \App\Models\SystemSetting::getValue('landing.plan_2_price_monthly', '29') }}</span>
                                                <span class="fs-13 text-muted">/Month</span>
                                            </h1>
                                            <h1 class="annual"><sup><small>₱</small></sup><span
                                                    class="ff-secondary fw-bold">{{ \App\Models\SystemSetting::getValue('landing.plan_2_price_annual', '261') }}</span>
                                                <span class="fs-13 text-muted">/Year</span>
                                            </h1>
                                        </div>

                                        <div>
                                            <ul class="list-unstyled text-muted vstack gap-3 ff-secondary">
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_2_feature_1', 'Upto <b>15</b> Projects') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_2_feature_2', '<b>Unlimited</b> Customers') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_2_feature_3', 'Scalable Bandwidth') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_2_feature_4', '<b>12</b> FTP Login') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_2_feature_5', '<b>24/7</b> Support') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div
                                                            class="flex-shrink-0 {{ \App\Models\SystemSetting::getValue('landing.plan_2_feature_6_enabled', 'false') === 'true' ? 'text-success' : 'text-danger' }} me-1">
                                                            <i
                                                                class="{{ \App\Models\SystemSetting::getValue('landing.plan_2_feature_6_enabled', 'false') === 'true' ? 'ri-checkbox-circle-fill' : 'ri-close-circle-fill' }} fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_2_feature_6', '<b>Unlimited</b> Storage') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div
                                                            class="flex-shrink-0 {{ \App\Models\SystemSetting::getValue('landing.plan_2_feature_7_enabled', 'false') === 'true' ? 'text-success' : 'text-danger' }} me-1">
                                                            <i
                                                                class="{{ \App\Models\SystemSetting::getValue('landing.plan_2_feature_7_enabled', 'false') === 'true' ? 'ri-checkbox-circle-fill' : 'ri-close-circle-fill' }} fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_2_feature_7', 'Domain') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="mt-4">
                                                <a href="{{ \App\Models\SystemSetting::getValue('landing.plan_2_button_link', 'javascript:void(0);') }}"
                                                    class="btn btn-soft-success w-100">{{ \App\Models\SystemSetting::getValue('landing.plan_2_button_text', 'Get Started') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-lg-4">
                                <div class="card plan-box mb-0">
                                    <div class="card-body p-4 m-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1 fw-semibold">
                                                    {{ \App\Models\SystemSetting::getValue('landing.plan_3_name', 'Platinum Plan') }}
                                                </h5>
                                                <p class="text-muted mb-0">
                                                    {{ \App\Models\SystemSetting::getValue('landing.plan_3_subtitle', 'Enterprise Businesses') }}
                                                </p>
                                            </div>
                                            <div class="avatar-sm">
                                                <div class="avatar-title bg-light rounded-circle text-primary">
                                                    <i
                                                        class="{{ \App\Models\SystemSetting::getValue('landing.plan_3_icon', 'ri-stack-fill') }} fs-20"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="py-4 text-center">
                                            <h1 class="month"><sup><small>₱</small></sup><span
                                                    class="ff-secondary fw-bold">{{ \App\Models\SystemSetting::getValue('landing.plan_3_price_monthly', '39') }}</span>
                                                <span class="fs-13 text-muted">/Month</span>
                                            </h1>
                                            <h1 class="annual"><sup><small>₱</small></sup><span
                                                    class="ff-secondary fw-bold">{{ \App\Models\SystemSetting::getValue('landing.plan_3_price_annual', '351') }}</span>
                                                <span class="fs-13 text-muted">/Year</span>
                                            </h1>
                                        </div>

                                        <div>
                                            <ul class="list-unstyled text-muted vstack gap-3 ff-secondary">
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_3_feature_1', '<b>Unlimited</b> Projects') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_3_feature_2', '<b>Unlimited</b> Customers') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_3_feature_3', 'Scalable Bandwidth') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_3_feature_4', '<b>Unlimited</b> FTP Login') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_3_feature_5', '<b>24/7</b> Support') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_3_feature_6', '<b>Unlimited</b> Storage') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="d-flex">
                                                        <div class="flex-shrink-0 text-success me-1">
                                                            <i class="ri-checkbox-circle-fill fs-15 align-middle"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            {!! \App\Models\SystemSetting::getValue('landing.plan_3_feature_7', 'Domain') !!}
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                            <div class="mt-4">
                                                <a href="{{ \App\Models\SystemSetting::getValue('landing.plan_3_button_link', 'javascript:void(0);') }}"
                                                    class="btn btn-soft-success w-100">{{ \App\Models\SystemSetting::getValue('landing.plan_3_button_text', 'Get Started') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end plan -->

                <!-- start faqs -->
                <section class="section">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="text-center mb-5">
                                    <h3 class="mb-3 fw-semibold">
                                        {{ \App\Models\SystemSetting::getValue('landing.faq_title', 'Frequently Asked Questions') }}
                                    </h3>
                                    <p class="text-muted mb-4 ff-secondary">
                                        {{ \App\Models\SystemSetting::getValue('landing.faq_description', 'If you can not find answer to your question in our FAQ, you can always contact us or email us. We will answer you shortly!') }}
                                    </p>

                                    <div class="hstack gap-2 justify-content-center">
                                        <a href="{{ \App\Models\SystemSetting::getValue('landing.faq_email_button_link', 'mailto:support@example.com') }}"
                                            class="btn btn-primary btn-label rounded-pill"><i
                                                class="{{ \App\Models\SystemSetting::getValue('landing.faq_email_button_icon', 'ri-mail-line') }} label-icon align-middle rounded-pill fs-16 me-2"></i>
                                            {{ \App\Models\SystemSetting::getValue('landing.faq_email_button_text', 'Email Us') }}</a>
                                        <a href="{{ \App\Models\SystemSetting::getValue('landing.faq_tweet_button_link', 'https://twitter.com/example') }}"
                                            class="btn btn-info btn-label rounded-pill"><i
                                                class="{{ \App\Models\SystemSetting::getValue('landing.faq_tweet_button_icon', 'ri-twitter-line') }} label-icon align-middle rounded-pill fs-16 me-2"></i>
                                            {{ \App\Models\SystemSetting::getValue('landing.faq_tweet_button_text', 'Send Us Tweet') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row g-lg-5 g-4">
                            <div class="col-lg-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="flex-shrink-0 me-1">
                                        <i
                                            class="{{ \App\Models\SystemSetting::getValue('landing.faq_general_icon', 'ri-question-line') }} fs-24 align-middle text-success me-1"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-0 fw-semibold">
                                            {{ \App\Models\SystemSetting::getValue('landing.faq_general_title', 'General Questions') }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box"
                                    id="genques-accordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="genques-headingOne">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#genques-collapseOne" aria-expanded="true"
                                                aria-controls="genques-collapseOne">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_general_1_question', 'What is the purpose of using themes ?') }}
                                            </button>
                                        </h2>
                                        <div id="genques-collapseOne" class="accordion-collapse collapse show"
                                            aria-labelledby="genques-headingOne" data-bs-parent="#genques-accordion">
                                            <div class="accordion-body ff-secondary">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_general_1_answer', 'A theme is a set of colors, fonts, effects, and more that can be applied to your entire presentation to give it a consistent, professional look. You\'ve already been using a theme, even if you didn\'t know it: the default Office theme, which consists.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="genques-headingTwo">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#genques-collapseTwo"
                                                aria-expanded="false" aria-controls="genques-collapseTwo">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_general_2_question', 'Can a theme have more than one theme?') }}
                                            </button>
                                        </h2>
                                        <div id="genques-collapseTwo" class="accordion-collapse collapse"
                                            aria-labelledby="genques-headingTwo" data-bs-parent="#genques-accordion">
                                            <div class="accordion-body ff-secondary">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_general_2_answer', 'A story can have as many themes as the reader can identify based on recurring patterns and parallels within the story itself. In looking at ways to separate themes into a hierarchy, we might find it useful to follow the example of a single book.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="genques-headingThree">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#genques-collapseThree"
                                                aria-expanded="false" aria-controls="genques-collapseThree">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_general_3_question', 'What are theme features?') }}
                                            </button>
                                        </h2>
                                        <div id="genques-collapseThree" class="accordion-collapse collapse"
                                            aria-labelledby="genques-headingThree" data-bs-parent="#genques-accordion">
                                            <div class="accordion-body ff-secondary">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_general_3_answer', 'Theme features is a set of specific functionality that may be enabled by theme authors. Themes must register each individual Theme Feature that the author wishes to support. Theme support functions should be called in the theme\'s functions.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="genques-headingFour">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#genques-collapseFour"
                                                aria-expanded="false" aria-controls="genques-collapseFour">
                                                What is simple theme?
                                            </button>
                                        </h2>
                                        <div id="genques-collapseFour" class="accordion-collapse collapse"
                                            aria-labelledby="genques-headingFour" data-bs-parent="#genques-accordion">
                                            <div class="accordion-body ff-secondary">
                                                Simple is a free WordPress theme, by Themify, built exactly what it is named
                                                for: simplicity. Immediately upgrade the
                                                quality of your WordPress site with the simple theme To use the built-in
                                                Chrome theme editor.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end accordion-->

                            </div>
                            <!-- end col -->
                            <div class="col-lg-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="flex-shrink-0 me-1">
                                        <i
                                            class="{{ \App\Models\SystemSetting::getValue('landing.faq_privacy_icon', 'ri-shield-keyhole-line') }} fs-24 align-middle text-success me-1"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-0 fw-semibold">
                                            {{ \App\Models\SystemSetting::getValue('landing.faq_privacy_title', 'Privacy & Security') }}
                                        </h5>
                                    </div>
                                </div>

                                <div class="accordion custom-accordionwithicon custom-accordion-border accordion-border-box"
                                    id="privacy-accordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="privacy-headingOne">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#privacy-collapseOne"
                                                aria-expanded="false" aria-controls="privacy-collapseOne">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_privacy_1_question', 'Does Word have night mode?') }}
                                            </button>
                                        </h2>
                                        <div id="privacy-collapseOne" class="accordion-collapse collapse"
                                            aria-labelledby="privacy-headingOne" data-bs-parent="#privacy-accordion">
                                            <div class="accordion-body ff-secondary">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_privacy_1_answer', 'You can run Microsoft Word in dark mode, which uses a dark color palette to help reduce eye strain in low light settings. You can choose to make the document white or black using the Switch Modes button in the ribbon\'s View tab.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="privacy-headingTwo">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#privacy-collapseTwo" aria-expanded="true"
                                                aria-controls="privacy-collapseTwo">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_privacy_2_question', 'Is theme an opinion?') }}
                                            </button>
                                        </h2>
                                        <div id="privacy-collapseTwo" class="accordion-collapse collapse show"
                                            aria-labelledby="privacy-headingTwo" data-bs-parent="#privacy-accordion">
                                            <div class="accordion-body ff-secondary">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_privacy_2_answer', 'A theme is an opinion the author expresses on the subject, for instance, the author\'s dissatisfaction with the narrow confines of French bourgeois marriage during that period theme is an idea that a writer repeats.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="privacy-headingThree">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#privacy-collapseThree"
                                                aria-expanded="false" aria-controls="privacy-collapseThree">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_privacy_3_question', 'How do you develop a theme?') }}
                                            </button>
                                        </h2>
                                        <div id="privacy-collapseThree" class="accordion-collapse collapse"
                                            aria-labelledby="privacy-headingThree" data-bs-parent="#privacy-accordion">
                                            <div class="accordion-body ff-secondary">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_privacy_3_answer', 'A short story, novella, or novel presents a narrative to its reader. Perhaps that narrative involves mystery, terror, romance, comedy, or all of the above. These works of fiction may also contain memorable characters, vivid world-building, literary devices.') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="privacy-headingFour">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#privacy-collapseFour"
                                                aria-expanded="false" aria-controls="privacy-collapseFour">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_privacy_4_question', 'Do stories need themes?') }}
                                            </button>
                                        </h2>
                                        <div id="privacy-collapseFour" class="accordion-collapse collapse"
                                            aria-labelledby="privacy-headingFour" data-bs-parent="#privacy-accordion">
                                            <div class="accordion-body ff-secondary">
                                                {{ \App\Models\SystemSetting::getValue('landing.faq_privacy_4_answer', 'A story can have as many themes as the reader can identify based on recurring patterns and parallels within the story itself. In looking at ways to separate themes into a hierarchy, we might find it useful to follow the example of a single book.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end accordion-->
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end faqs -->

                <!-- start review -->
                <section class="section bg-primary" id="reviews">
                    <div class="bg-overlay bg-overlay-pattern"></div>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-10">
                                <div class="text-center">
                                    <div>
                                        <i
                                            class="{{ \App\Models\SystemSetting::getValue('landing.reviews_icon', 'ri-double-quotes-l') }} text-success display-3"></i>
                                    </div>
                                    <h4 class="text-white mb-5"><span
                                            class="text-success">{{ \App\Models\SystemSetting::getValue('landing.reviews_number', '19k') }}</span>+
                                        {{ \App\Models\SystemSetting::getValue('landing.reviews_heading', 'Satisfied clients') }}
                                    </h4>

                                    <!-- Swiper -->
                                    <div class="swiper client-review-swiper rounded" dir="ltr">
                                        <div class="swiper-wrapper">
                                            <div class="swiper-slide">
                                                <div class="row justify-content-center">
                                                    <div class="col-10">
                                                        <div class="text-white-50">
                                                            <p class="fs-20 ff-secondary mb-4">"
                                                                {{ \App\Models\SystemSetting::getValue('landing.testimonial_1_quote', 'I am givng 5 stars. Theme is great and everyone one stuff everything in theme. Future request should not affect current state of theme.') }}
                                                                "
                                                            </p>

                                                            <div>
                                                                <h5 class="text-white">
                                                                    {{ \App\Models\SystemSetting::getValue('landing.testimonial_1_name', 'gregoriusus') }}
                                                                </h5>
                                                                <p>- {{ \App\Models\SystemSetting::getValue('landing.testimonial_1_role', 'Skote User') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end slide -->
                                            <div class="swiper-slide">
                                                <div class="row justify-content-center">
                                                    <div class="col-10">
                                                        <div class="text-white-50">
                                                            <p class="fs-20 ff-secondary mb-4">"
                                                                {{ \App\Models\SystemSetting::getValue('landing.testimonial_2_quote', 'Awesome support. Had few issues while setting up because of my device, the support team helped me fix them up in a day. Everything looks clean and good. Highly recommended!') }}
                                                                "
                                                            </p>

                                                            <div>
                                                                <h5 class="text-white">
                                                                    {{ \App\Models\SystemSetting::getValue('landing.testimonial_2_name', 'GeekyGreenOwl') }}
                                                                </h5>
                                                                <p>- {{ \App\Models\SystemSetting::getValue('landing.testimonial_2_role', 'Skote User') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end slide -->
                                            <div class="swiper-slide">
                                                <div class="row justify-content-center">
                                                    <div class="col-10">
                                                        <div class="text-white-50">
                                                            <p class="fs-20 ff-secondary mb-4">"
                                                                {{ \App\Models\SystemSetting::getValue('landing.testimonial_3_quote', 'Amazing template, Redux store and components is nicely designed. It\'s a great start point for an admin based project. Clean Code and good documentation. Template is completely in React and absolutely no usage of jQuery') }}
                                                                "
                                                            </p>

                                                            <div>
                                                                <h5 class="text-white">
                                                                    {{ \App\Models\SystemSetting::getValue('landing.testimonial_3_name', 'sreeks456') }}
                                                                </h5>
                                                                <p>- {{ \App\Models\SystemSetting::getValue('landing.testimonial_3_role', 'Veltrix User') }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- end slide -->
                                        </div>
                                        <div class="swiper-button-next bg-white rounded-circle"></div>
                                        <div class="swiper-button-prev bg-white rounded-circle"></div>
                                        <div class="swiper-pagination position-relative mt-2"></div>
                                    </div>
                                    <!-- end slider -->
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end review -->

                <!-- start counter -->
                <section class="py-5 position-relative bg-light">
                    <div class="container">
                        <div class="row text-center gy-4">
                            <div class="col-lg-3 col-6">
                                <div>
                                    <h2 class="mb-2"><span class="counter-value"
                                            data-target="{{ \App\Models\SystemSetting::getValue('landing.stat_1_value', '100') }}">0</span>{{ \App\Models\SystemSetting::getValue('landing.stat_1_suffix', '+') }}
                                    </h2>
                                    <div class="text-muted">
                                        {{ \App\Models\SystemSetting::getValue('landing.stat_1_label', 'Projects Completed') }}
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-lg-3 col-6">
                                <div>
                                    <h2 class="mb-2"><span class="counter-value"
                                            data-target="{{ \App\Models\SystemSetting::getValue('landing.stat_2_value', '24') }}">0</span>{{ \App\Models\SystemSetting::getValue('landing.stat_2_suffix', '') }}
                                    </h2>
                                    <div class="text-muted">
                                        {{ \App\Models\SystemSetting::getValue('landing.stat_2_label', 'Win Awards') }}
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->

                            <div class="col-lg-3 col-6">
                                <div>
                                    <h2 class="mb-2"><span class="counter-value"
                                            data-target="{{ \App\Models\SystemSetting::getValue('landing.stat_3_value', '20.3') }}">0</span>{{ \App\Models\SystemSetting::getValue('landing.stat_3_suffix', 'k') }}
                                    </h2>
                                    <div class="text-muted">
                                        {{ \App\Models\SystemSetting::getValue('landing.stat_3_label', 'Satisfied Clients') }}
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-3 col-6">
                                <div>
                                    <h2 class="mb-2"><span class="counter-value"
                                            data-target="{{ \App\Models\SystemSetting::getValue('landing.stat_4_value', '50') }}">0</span>{{ \App\Models\SystemSetting::getValue('landing.stat_4_suffix', '') }}
                                    </h2>
                                    <div class="text-muted">
                                        {{ \App\Models\SystemSetting::getValue('landing.stat_4_label', 'Employees') }}
                                    </div>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end counter -->

                <!-- start Work Process -->
                <section class="section">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="text-center mb-5">
                                    <h3 class="mb-3 fw-semibold">How Our System Works</h3>
                                    <p class="text-muted mb-4 ff-secondary">
                                        Our enrollment and information management system streamlines your educational
                                        journey from start to finish. Here’s a quick look at our simple, user-friendly
                                        process that ensures accuracy, transparency, and support at every stage.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row text-center">
                            <div class="col-lg-4">
                                <div class="process-card mt-4">
                                    <div class="process-arrow-img d-none d-lg-block">
                                        <img src="{{ \App\Models\SystemSetting::getAsset('landing.process_arrow_image', URL::asset('build/images/landing/process-arrow-img.png')) }}" alt=""
                                            class="img-fluid">
                                    </div>
                                    <div class="avatar-sm icon-effect mx-auto mb-4">
                                        <div class="avatar-title bg-transparent text-success rounded-circle h1">
                                            <i class="{{ \App\Models\SystemSetting::getValue('landing.process_1_icon', 'ri-quill-pen-line') }}"></i>
                                        </div>
                                    </div>

                                    <h5>{{ \App\Models\SystemSetting::getValue('landing.process_1_title', 'Submit Your Information') }}</h5>
                                    <p class="text-muted ff-secondary">
                                        {{ \App\Models\SystemSetting::getValue('landing.process_1_description', 'Register and provide your personal and academic details through our secure portal. Our intuitive forms guide you to ensure your data is complete and accurate.') }}
                                    </p>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-4">
                                <div class="process-card mt-4">
                                    <div class="process-arrow-img d-none d-lg-block">
                                        <img src="{{ \App\Models\SystemSetting::getAsset('landing.process_arrow_image', URL::asset('build/images/landing/process-arrow-img.png')) }}" alt=""
                                            class="img-fluid">
                                    </div>
                                    <div class="avatar-sm icon-effect mx-auto mb-4">
                                        <div class="avatar-title bg-transparent text-success rounded-circle h1">
                                            <i class="{{ \App\Models\SystemSetting::getValue('landing.process_2_icon', 'ri-user-follow-line') }}"></i>
                                        </div>
                                    </div>

                                    <h5>{{ \App\Models\SystemSetting::getValue('landing.process_2_title', 'Admin Review & Verification') }}</h5>
                                    <p class="text-muted ff-secondary">
                                        {{ \App\Models\SystemSetting::getValue('landing.process_2_description', 'Our administrators review your submission, verify the information, and assist if corrections are needed. Notifications keep you updated on the progress of your enrollment.') }}
                                    </p>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-lg-4">
                                <div class="process-card mt-4">
                                    <div class="avatar-sm icon-effect mx-auto mb-4">
                                        <div class="avatar-title bg-transparent text-success rounded-circle h1">
                                            <i class="{{ \App\Models\SystemSetting::getValue('landing.process_3_icon', 'ri-book-mark-line') }}"></i>
                                        </div>
                                    </div>

                                    <h5>{{ \App\Models\SystemSetting::getValue('landing.process_3_title', 'Confirmation & Ongoing Access') }}</h5>
                                    <p class="text-muted ff-secondary">
                                        Once approved, you’ll receive confirmation and can access your enrollment
                                        information anytime. Manage updates, receive important notifications, and track your
                                        academic journey seamlessly!
                                    </p>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end Work Process -->

                <!-- start team -->
                <section class="section bg-light" id="team">
                    <div class="container text-center">


                           <!-- start researcher sub-section -->
                        <div class="row justify-content-center mt-5">
                            <div class="col-lg-8">
                                <div class="text-center mb-5 mt-4">
                                    <h4 class="mb-3 fw-semibold">{{ \App\Models\SystemSetting::getValue('landing.researcher_title', 'Our') }} <span class="text-danger">{{ \App\Models\SystemSetting::getValue('landing.researcher_title_highlight', 'Researchers') }}</span></h4>
                                    <p class="text-muted mb-4 ff-secondary">
                                        {{ \App\Models\SystemSetting::getValue('landing.researcher_description', 'Our dedicated researchers drive innovation and explore new possibilities. They are passionate about uncovering insights that shape the future.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-lg-3 col-sm-6">
                                <div class="card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl mx-auto mb-4 position-relative">
                                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageInModal('{{ \App\Models\SystemSetting::getAsset('landing.researcher_1_image', URL::asset('build/images/users/avatar-2.jpg')) }}')">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.researcher_1_image', URL::asset('build/images/users/avatar-2.jpg')) }}" alt=""
                                                    class="img-fluid rounded-circle" style="aspect-ratio: 1/1; object-fit: cover;">
                                            </a>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.researcher_1_email', 'mailto:jane@example.com') }}"
                                                class="btn btn-success btn-sm position-absolute bottom-0 end-0 rounded-circle avatar-xs">
                                                <div class="avatar-title bg-transparent">
                                                    <i class="ri-mail-fill align-bottom"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <!-- end card body -->
                                        <h5 class="mb-1"><a href="{{ \App\Models\SystemSetting::getValue('landing.researcher_1_email', 'mailto:jane@example.com') }}" class="text-body">{{ \App\Models\SystemSetting::getValue('landing.researcher_1_name', 'Jane Doe') }}</a>
                                        </h5>
                                        <p class="text-muted mb-0 ff-secondary">{{ \App\Models\SystemSetting::getValue('landing.researcher_1_role', 'Lead Researcher') }}</p>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                            <div class="col-lg-3 col-sm-6">
                                <div class="card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl mx-auto mb-4 position-relative">
                                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageInModal('{{ \App\Models\SystemSetting::getAsset('landing.researcher_2_image', URL::asset('build/images/users/mark.png')) }}')">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.researcher_2_image', URL::asset('build/images/users/mark.png')) }}" alt=""
                                                    class="img-fluid rounded-circle" style="aspect-ratio: 1/1; object-fit: cover;">
                                            </a>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.researcher_2_email', 'mailto:john@example.com') }}"
                                                class="btn btn-success btn-sm position-absolute bottom-0 end-0 rounded-circle avatar-xs">
                                                <div class="avatar-title bg-transparent">
                                                    <i class="ri-mail-fill align-bottom"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <!-- end card body -->
                                        <h5 class="mb-1"><a href="{{ \App\Models\SystemSetting::getValue('landing.researcher_2_email', 'mailto:john@example.com') }}" class="text-body">{{ \App\Models\SystemSetting::getValue('landing.researcher_2_name', 'John Smith') }}</a></h5>
                                        <p class="text-muted mb-0 ff-secondary">{{ \App\Models\SystemSetting::getValue('landing.researcher_2_role', 'Data Scientist') }}</p>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                            <div class="col-lg-3 col-sm-6">
                                <div class="card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl mx-auto mb-4 position-relative">
                                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageInModal('{{ \App\Models\SystemSetting::getAsset('landing.researcher_3_image', URL::asset('build/images/users/kit.png')) }}')">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.researcher_3_image', URL::asset('build/images/users/kit.png')) }}" alt=""
                                                    class="img-fluid rounded-circle" style="aspect-ratio: 1/1; object-fit: cover;">
                                            </a>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.researcher_3_email', 'mailto:alice@example.com') }}"
                                                class="btn btn-success btn-sm position-absolute bottom-0 end-0 rounded-circle avatar-xs">
                                                <div class="avatar-title bg-transparent">
                                                    <i class="ri-mail-fill align-bottom"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <!-- end card body -->
                                        <h5 class="mb-1"><a href="{{ \App\Models\SystemSetting::getValue('landing.researcher_3_email', 'mailto:alice@example.com') }}" class="text-body">{{ \App\Models\SystemSetting::getValue('landing.researcher_3_name', 'Alice Johnson') }}</a></h5>
                                        <p class="text-muted mb-0 ff-secondary">{{ \App\Models\SystemSetting::getValue('landing.researcher_3_role', 'Research Assistant') }}</p>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->

                            <div class="col-lg-3 col-sm-6">
                                <div class="card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl mx-auto mb-4 position-relative">
                                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageInModal('{{ \App\Models\SystemSetting::getAsset('landing.researcher_4_image', URL::asset('build/images/users/cuyag.png')) }}')">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.researcher_4_image', URL::asset('build/images/users/cuyag.png')) }}" alt=""
                                                    class="img-fluid rounded-circle" style="aspect-ratio: 1/1; object-fit: cover;">
                                            </a>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.researcher_4_email', 'mailto:bob@example.com') }}"
                                                class="btn btn-success btn-sm position-absolute bottom-0 end-0 rounded-circle avatar-xs">
                                                <div class="avatar-title bg-transparent">
                                                    <i class="ri-mail-fill align-bottom"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <!-- end card body -->
                                        <h5 class="mb-1"><a href="{{ \App\Models\SystemSetting::getValue('landing.researcher_4_email', 'mailto:bob@example.com') }}" class="text-body">{{ \App\Models\SystemSetting::getValue('landing.researcher_4_name', 'Bob Williams') }}</a></h5>
                                        <p class="text-muted mb-0 ff-secondary">{{ \App\Models\SystemSetting::getValue('landing.researcher_4_role', 'Analyst') }}</p>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>

                        </div>
                        <!-- end row -->
                        <!-- end researcher sub-section -->

                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="text-center mb-5">
                                    <h3 class="mb-3 fw-semibold">{{ \App\Models\SystemSetting::getValue('landing.team_title', 'Our') }} <span class="text-danger">{{ \App\Models\SystemSetting::getValue('landing.team_title_highlight', 'Team') }}</span></h3>
                                    <p class="text-muted mb-4 ff-secondary">
                                        {{ \App\Models\SystemSetting::getValue('landing.team_description', 'Our talented team combines expertise in education and technology to build a smart, efficient system that empowers users. We are dedicated to continuous innovation, ensuring our platform makes enrollment and information management seamless, reliable, and easy for everyone.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-lg-3 col-sm-6">
                                <div class="card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl mx-auto mb-4 position-relative">
                                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageInModal('{{ \App\Models\SystemSetting::getAsset('landing.team_member_1_image', URL::asset('build/images/users/avatar-2.jpg')) }}')">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.team_member_1_image', URL::asset('build/images/users/avatar-2.jpg')) }}" alt=""
                                                    class="img-fluid rounded-circle" style="aspect-ratio: 1/1; object-fit: cover;">
                                            </a>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.team_member_1_email', 'mailto:nancy@example.com') }}"
                                                class="btn btn-success btn-sm position-absolute bottom-0 end-0 rounded-circle avatar-xs">
                                                <div class="avatar-title bg-transparent">
                                                    <i class="ri-mail-fill align-bottom"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <!-- end card body -->
                                        <h5 class="mb-1"><a href="{{ \App\Models\SystemSetting::getValue('landing.team_member_1_email', 'mailto:nancy@example.com') }}" class="text-body">{{ \App\Models\SystemSetting::getValue('landing.team_member_1_name', 'Nancy Martino') }}</a>
                                        </h5>
                                        <p class="text-muted mb-0 ff-secondary">{{ \App\Models\SystemSetting::getValue('landing.team_member_1_role', 'Team Leader') }}</p>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                            <div class="col-lg-3 col-sm-6">
                                <div class="card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl mx-auto mb-4 position-relative">
                                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageInModal('{{ \App\Models\SystemSetting::getAsset('landing.team_member_2_image', URL::asset('build/images/users/mark.png')) }}')">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.team_member_2_image', URL::asset('build/images/users/mark.png')) }}" alt=""
                                                    class="img-fluid rounded-circle" style="aspect-ratio: 1/1; object-fit: cover;">
                                            </a>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.team_member_2_email', 'mailto:mark@example.com') }}"
                                                class="btn btn-success btn-sm position-absolute bottom-0 end-0 rounded-circle avatar-xs">
                                                <div class="avatar-title bg-transparent">
                                                    <i class="ri-mail-fill align-bottom"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <!-- end card body -->
                                        <h5 class="mb-1"><a href="{{ \App\Models\SystemSetting::getValue('landing.team_member_2_email', 'mailto:mark@example.com') }}" class="text-body">{{ \App\Models\SystemSetting::getValue('landing.team_member_2_name', 'Mark Vincent Quiao') }}</a></h5>
                                        <p class="text-muted mb-0 ff-secondary">{{ \App\Models\SystemSetting::getValue('landing.team_member_2_role', 'Full Stack Developer') }}</p>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                            <div class="col-lg-3 col-sm-6">
                                <div class="card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl mx-auto mb-4 position-relative">
                                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageInModal('{{ \App\Models\SystemSetting::getAsset('landing.team_member_3_image', URL::asset('build/images/users/kit.png')) }}')">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.team_member_3_image', URL::asset('build/images/users/kit.png')) }}" alt=""
                                                    class="img-fluid rounded-circle" style="aspect-ratio: 1/1; object-fit: cover;">
                                            </a>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.team_member_3_email', 'mailto:kit@example.com') }}"
                                                class="btn btn-success btn-sm position-absolute bottom-0 end-0 rounded-circle avatar-xs">
                                                <div class="avatar-title bg-transparent">
                                                    <i class="ri-mail-fill align-bottom"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <!-- end card body -->
                                        <h5 class="mb-1"><a href="{{ \App\Models\SystemSetting::getValue('landing.team_member_3_email', 'mailto:kit@example.com') }}" class="text-body">{{ \App\Models\SystemSetting::getValue('landing.team_member_3_name', 'Kit Benedic Aguing') }}</a></h5>
                                        <p class="text-muted mb-0 ff-secondary">{{ \App\Models\SystemSetting::getValue('landing.team_member_3_role', 'Project Manager') }}</p>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->

                            <div class="col-lg-3 col-sm-6">
                                <div class="card">
                                    <div class="card-body text-center p-4">
                                        <div class="avatar-xl mx-auto mb-4 position-relative">
                                            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="showImageInModal('{{ \App\Models\SystemSetting::getAsset('landing.team_member_4_image', URL::asset('build/images/users/cuyag.png')) }}')">
                                                <img src="{{ \App\Models\SystemSetting::getAsset('landing.team_member_4_image', URL::asset('build/images/users/cuyag.png')) }}" alt=""
                                                    class="img-fluid rounded-circle" style="aspect-ratio: 1/1; object-fit: cover;">
                                            </a>
                                            <a href="{{ \App\Models\SystemSetting::getValue('landing.team_member_4_email', 'mailto:saturnino@example.com') }}"
                                                class="btn btn-success btn-sm position-absolute bottom-0 end-0 rounded-circle avatar-xs">
                                                <div class="avatar-title bg-transparent">
                                                    <i class="ri-mail-fill align-bottom"></i>
                                                </div>
                                            </a>
                                        </div>
                                        <!-- end card body -->
                                        <h5 class="mb-1"><a href="{{ \App\Models\SystemSetting::getValue('landing.team_member_4_email', 'mailto:saturnino@example.com') }}" class="text-body">{{ \App\Models\SystemSetting::getValue('landing.team_member_4_name', 'Saturnino JR E Cuyag') }}</a></h5>
                                        <p class="text-muted mb-0 ff-secondary">{{ \App\Models\SystemSetting::getValue('landing.team_member_4_role', 'System Analyst') }}</p>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div>

                        </div>
                        <!-- end row -->

                     

                    </div>
                    <!-- end container -->
                </section>
                <!-- end team -->

                <!-- start contact -->
                <section class="section" id="contact">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <div class="text-center mb-5">
                                    <h3 class="mb-3 fw-semibold">{{ \App\Models\SystemSetting::getValue('landing.contact_title', 'Get In Touch') }}</h3>
                                    <p class="text-muted mb-4 ff-secondary">{{ \App\Models\SystemSetting::getValue('landing.contact_description', 'We thrive when coming up with innovative ideas but also understand that a smart concept should be supported with faucibus sapien odio measurable results.') }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row text-center">
                            <div class="col-md-4 mb-4">
                                <h5 class="fs-13 text-muted text-uppercase">{{ \App\Models\SystemSetting::getValue('landing.contact_address_1_label', 'Office Address 1:') }}</h5>
                                <div class="ff-secondary fw-semibold">{!! \App\Models\SystemSetting::getValue('landing.contact_address_1', 'Kidapawan City,<br />North Cotabato') !!}</div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <h5 class="fs-13 text-muted text-uppercase">{{ \App\Models\SystemSetting::getValue('landing.contact_address_2_label', 'Office Address 2:') }}</h5>
                                <div class="ff-secondary fw-semibold">{!! \App\Models\SystemSetting::getValue('landing.contact_address_2', 'Bansalan,<br />Davao del Sur') !!}</div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <h5 class="fs-13 text-muted text-uppercase">{{ \App\Models\SystemSetting::getValue('landing.contact_hours_label', 'Working Hours:') }}</h5>
                                <div class="ff-secondary fw-semibold">{{ \App\Models\SystemSetting::getValue('landing.contact_hours', '9:00am to 6:00pm') }}</div>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="row mt-5">
                            <div class="col-lg-12">
                                <div class="rounded-3 overflow-hidden shadow-lg">
                                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3959.8733279900575!2d125.12097357570651!3d7.024172617141272!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32f8f95479b3541d%3A0xe4d5fc216849e674!2sManongol%20National%20High%20School%20Basketball%20Court!5e0!3m2!1sen!2sph!4v1771512211867!5m2!1sen!2sph" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end container -->
                </section>
                <!-- end contact -->

                <!-- start cta -->
                <section class="py-5 bg-primary position-relative">
                    <div class="bg-overlay bg-overlay-pattern opacity-50"></div>
                    <div class="container">
                        <div class="row align-items-center gy-4">
                            <div class="col-sm">
                                <div>
                                    <h4 class="text-white mb-0 fw-semibold">Build your campus management system with eSMART
                                        Campus
                                    </h4>
                                </div>
                            </div>
                            <!-- end col -->
                            <div class="col-sm-auto">
                                <div>
                                    <a href="{{ route('register') }}" class="btn bg-gradient btn-danger"><i
                                            class="ri-user-add-line align-middle me-1"></i> Get Started</a>
                                </div>
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->
                    </div>
                    <!-- end container -->
                </section>
                <!-- end cta -->

                <!-- Start footer -->
                <footer class="custom-footer bg-dark py-5 position-relative">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-4 mt-4">
                                <div>
                                    <div>
                                        <img src="{{ \App\Models\SystemSetting::getAsset('site.landing_logo_light', URL::asset('build/images/smart-logo-light.png')) }}"
                                            alt="logo light" height="17">
                                    </div>
                                    <div class="mt-4 fs-13">
                                        <p>{{ \App\Models\SystemSetting::getValue('site.name', 'eSMART Campus') }} - Student
                                            Management System</p>
                                        <p class="ff-secondary">
                                            {{ \App\Models\SystemSetting::getValue('site.description', 'A comprehensive platform for managing student enrollment, attendance, grades, assignments, and all campus operations in one unified system.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="col-lg-7 ms-lg-auto">
                                <div class="row">
                                    <div class="col-sm-4 mt-4">
                                        <h5 class="text-white mb-0">Company</h5>
                                        <div class="text-muted mt-3">
                                            <ul class="list-unstyled ff-secondary footer-list">
                                                <li><a href="pages-profile">About Us</a></li>
                                                <li><a href="pages-gallery">Gallery</a></li>
                                                <li><a href="apps-projects-overview">Projects</a></li>
                                                <li><a href="pages-timeline">Timeline</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 mt-4">
                                        <h5 class="text-white mb-0">Apps Pages</h5>
                                        <div class="text-muted mt-3">
                                            <ul class="list-unstyled ff-secondary footer-list">
                                                <li><a href="pages-pricing">Calendar</a></li>
                                                <li><a href="apps-mailbox">Mailbox</a></li>
                                                <li><a href="apps-chat">Chat</a></li>
                                                <li><a href="apps-crm-deals">Deals</a></li>
                                                <li><a href="apps-tasks-kanban">Kanban Board</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 mt-4">
                                        <h5 class="text-white mb-0">Support</h5>
                                        <div class="text-muted mt-3">
                                            <ul class="list-unstyled ff-secondary footer-list">
                                                <li><a href="pages-faqs">FAQ</a></li>
                                                <li><a href="pages-faqs">Contact</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                        </div>

                        <div class="row text-center text-sm-start align-items-center mt-5">
                            <div class="col-sm-6">

                                <div>
                                    <p class="copy-rights mb-0">
                                        <script>
                                            document.write(new Date().getFullYear())
                                        </script>
                                        {{ \App\Models\SystemSetting::getValue('site.footer_text', '© eSMART Campus. Crafted with ❤️ by eSMART Campus Team') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-sm-end mt-3 mt-sm-0">
                                    <ul class="list-inline mb-0 footer-social-link">
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="avatar-xs d-block">
                                                <div class="avatar-title rounded-circle">
                                                    <i class="ri-facebook-fill"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="avatar-xs d-block">
                                                <div class="avatar-title rounded-circle">
                                                    <i class="ri-github-fill"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="avatar-xs d-block">
                                                <div class="avatar-title rounded-circle">
                                                    <i class="ri-linkedin-fill"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="avatar-xs d-block">
                                                <div class="avatar-title rounded-circle">
                                                    <i class="ri-google-fill"></i>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="list-inline-item">
                                            <a href="javascript: void(0);" class="avatar-xs d-block">
                                                <div class="avatar-title rounded-circle">
                                                    <i class="ri-dribbble-line"></i>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end footer -->

                <!-- Image Modal -->
                <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center pt-0 pb-4">
                                <img id="modalImage" src="" alt="Profile Image" class="img-fluid rounded shadow-lg" style="max-height: 80vh; object-fit: contain;">
                            </div>
                        </div>
                    </div>
                </div>

                <!--start back-to-top-->
                <button onclick="topFunction()" class="btn btn-danger btn-icon landing-back-top" id="back-to-top">
                    <i class="ri-arrow-up-line"></i>
                </button>
                <!--end back-to-top-->

            </div>
            <!-- end layout wrapper -->

        </body>
    @endsection
    @section('script')
        <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
        <script src="{{ URL::asset('build/js/pages/landing.init.js') }}"></script>
        <script>
            function showImageInModal(imageSrc) {
                document.getElementById('modalImage').src = imageSrc;
            }
        </script>
    @endsection