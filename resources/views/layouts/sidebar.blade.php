<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                @php
                    $user = Auth::user();
                    $photoPath = $user->photo_path
                        ? (str_starts_with($user->photo_path, 'http') ? $user->photo_path : asset('storage/' . $user->photo_path))
                        : ($user->avatar ? asset('build/images/users/' . $user->avatar) : asset('build/images/users/user-dummy-img.jpg'));
                @endphp
                <img class="rounded header-profile-user" src="{{ $photoPath }}" alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">{{ Auth::user()->name }}</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i
                            class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span
                            class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <!-- item-->
            <h6 class="dropdown-header">Welcome {{ Auth::user()->name }}!</h6>
            <a class="dropdown-item" href="profile.index"><i
                    class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Profile</span></a>


            <div class="dropdown-divider"></div>

            <a class="dropdown-item" href="profile.index-profile-settings"><span
                    class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span
                        class="align-middle">Settings</span></a>


            <a class="dropdown-item " href="javascript:void();"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                    class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span
                    key="t-logout">@lang('translation.logout')</span></a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
                <ul class="navbar-nav">
                    {{-- Minimal dashboard entry for minimized / two-column sidebar --}}
                    {{-- <x-sidebar.item href="dashboard-analytics" title="Dashboard" />
                    <!-- User Management -->
                    <x-sidebar.item href="user-management" title="User Management" /> --}}
                </ul>
            </div>
            <ul class="navbar-nav" id="navbar-nav">

                @can('view-student-management')
                    <x-sidebar.title title="Student Management" />

                    @can('view-enrollment-management')
                        <x-sidebar.nav-link href="enrollment.manage-enroll-index" icon="ri-user-star-line" title="Students" />
                    @endcan
                    @can('view-semester-management')
                        <x-sidebar.nav-link href="enrollment.semester-index" icon="ri-calendar-line" title="Semesters" />
                    @endcan
                @endcan


                <!-- User Management -->
                @can('view-user-management')
                    <x-sidebar.title title="Admin Management" />
                    <x-sidebar.dropdown id="sidebarUserManagement" title="User" icon="ri-user-line"
                        :active="request()->is('user-management*')">
                        <ul class="nav nav-sm flex-column">

                            <x-sidebar.item href="user-management.index" title="Users" />

                            @can('view-role-management')
                                <x-sidebar.item href="user-management.index-role" title="Roles" />
                            @endcan
                            @can('view-permission-management')
                                <x-sidebar.item href="user-management.index-permission" title="Permissions" />
                            @endcan
                        </ul>
                    </x-sidebar.dropdown>
                @endcan

































            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>