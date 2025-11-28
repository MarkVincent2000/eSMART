<div>
    @php
        $coverPhoto = data_get($user, 'cover_photo_path');
        $coverPhotoPath = $coverPhoto
            ? (str_starts_with($coverPhoto, 'http')
                ? $coverPhoto
                : asset('storage/' . ltrim($coverPhoto, '/')))
            : asset('build/images/profile-bg.jpg');

        $photo = data_get($user, 'photo_path');
        $photoPath = $photo
            ? (str_starts_with($photo, 'http')
                ? $photo
                : asset('storage/' . ltrim($photo, '/')))
            : asset('build/images/users/user-dummy-img.jpg');
    @endphp
    <div class="profile-foreground position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg">
            <img src="{{ $coverPhotoPath }}" alt="profile cover" class="profile-wid-img" />
        </div>
    </div>
    <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
        <div class="row g-4">
            <div class="col-auto">
                <div class="avatar-lg">
                    <img src="{{ $photoPath }}" alt="user-img" class="img-thumbnail rounded-circle" />
                </div>
            </div>
            <!--end col-->
            <div class="col">
                <div class="p-2">
                    <h3 class="text-white mb-1">{{ $user?->name ?? 'User' }}</h3>
                    <p class="text-white text-opacity-75">{{ data_get($cpu = $user, 'roles.0.name', 'Role not set') }}
                    </p>
                    <div class="hstack text-white-50 gap-1">
                        <div class="me-2"><i
                                class="ri-map-pin-user-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>{{ $user->personalDetails->address ?? 'No address found' }}
                        </div>
                        {{-- <div>
                            <i
                                class="ri-building-line me-1 text-white text-opacity-75 fs-16 align-middle"></i>Themesbrand
                        </div> --}}
                    </div>
                </div>
            </div>
            <!--end col-->



        </div>
        <!--end row-->
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div>
                <div class="d-flex profile-wrapper">
                    <!-- Nav tabs -->
                    <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link fs-14 {{ $activeTab === 'overview' ? 'active' : '' }}"
                                data-bs-toggle="tab" href="#overview-tab" role="tab"
                                onclick="updateProfileTab('overview')">
                                <i class="ri-airplay-fill d-inline-block d-md-none"></i> <span
                                    class="d-none d-md-inline-block">Overview</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fs-14 {{ $activeTab === 'activities' ? 'active' : '' }}"
                                data-bs-toggle="tab" href="#activities" role="tab"
                                onclick="updateProfileTab('activities')">
                                <i class="ri-list-unordered d-inline-block d-md-none"></i> <span
                                    class="d-none d-md-inline-block">Activity Logs</span>
                            </a>
                        </li>
                    </ul>
                    <div class="flex-shrink-0">
                        <a href="profile.index-profile-settings" class="btn btn-success"><i
                                class="ri-edit-box-line align-bottom"></i> Edit Profile</a>
                    </div>
                </div>
                <!-- Tab panes -->
                <div class="tab-content pt-4 text-muted">
                    <div class="tab-pane fade {{ $activeTab === 'overview' ? 'show active' : '' }}" id="overview-tab"
                        role="tabpanel">
                        <div class="row">
                            <div class="col-xxl-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-5">Complete Your Profile</h5>
                                        <div class="progress animated-progress custom-progress progress-label">
                                            @php
                                                $completionPercentage = $this->profileCompletionPercentage;
                                                $progressColor = $completionPercentage >= 80 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger');
                                            @endphp
                                            <div class="progress-bar bg-{{ $progressColor }}" role="progressbar"
                                                style="width: {{ $completionPercentage }}%"
                                                aria-valuenow="{{ $completionPercentage }}" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <div class="label">{{ $completionPercentage }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Info</h5>
                                        <div class="table-responsive">
                                            <table class="table table-borderless mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Full Name :</th>
                                                        <td class="text-muted">{{ $userProfile->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Mobile :</th>
                                                        <td class="text-muted">
                                                            {{ $userProfile->personalDetails->contact_no ?? 'No phone found' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">E-mail :</th>
                                                        <td class="text-muted">{{ $userProfile->email }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Location :</th>
                                                        <td class="text-muted">
                                                            {{ $userProfile->personalDetails->address ?? 'No address found' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="ps-0" scope="row">Joining Date</th>
                                                        <td class="text-muted">
                                                            {{ $userProfile->created_at->format('d M Y') }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->







                                <!--end card-->
                            </div>
                            <!--end col-->
                            <div class="col-xxl-9">


                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card">
                                            <div class="card-header align-items-center d-flex">
                                                <h4 class="card-title mb-0 me-2">Personal Information</h4>
                                            </div>
                                            <div class="card-body">
                                                @if($personalDetails)
                                                    <div class="row">
                                                        <!-- Personal Details Section -->
                                                        <div class="col-lg-6">
                                                            <h5 class="mb-3">Other Information</h5>
                                                            <div class="table-responsive">
                                                                <table class="table table-borderless mb-0">
                                                                    <tbody>
                                                                        @if($personalDetails->sex)
                                                                            <tr>
                                                                                <th class="ps-0" scope="row" style="width: 40%;">Sex :</th>
                                                                                <td class="text-muted">{{ ucfirst($personalDetails->sex) }}</td>
                                                                            </tr>
                                                                        @endif
                                                                        @if($personalDetails->date_of_birth)
                                                                            <tr>
                                                                                <th class="ps-0" scope="row">Date of Birth :</th>
                                                                                <td class="text-muted">{{ \Carbon\Carbon::parse($personalDetails->date_of_birth)->format('d M Y') }}</td>
                                                                            </tr>
                                                                        @endif
                                                                        @if($personalDetails->religion)
                                                                            <tr>
                                                                                <th class="ps-0" scope="row">Religion :</th>
                                                                                <td class="text-muted">{{ ucfirst($personalDetails->religion) }}</td>
                                                                            </tr>
                                                                        @endif
                                                                        
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <!--end col-->
                                                        
                                                        <!-- Guardian Information Section -->
                                                        <div class="col-lg-6">
                                                            <h5 class="mb-3">Guardian Information</h5>
                                                            @if($personalDetails->guardian_first_name || $personalDetails->guardian_last_name || $personalDetails->guardian_relationship || $personalDetails->guardian_contact_no)
                                                                <div class="table-responsive">
                                                                    <table class="table table-borderless mb-0">
                                                                        <tbody>
                                                                            @if($personalDetails->guardian_first_name || $personalDetails->guardian_last_name)
                                                                                <tr>
                                                                                    <th class="ps-0" scope="row" style="width: 40%;">Guardian Name :</th>
                                                                                    <td class="text-muted">
                                                                                        @php
                                                                                            $guardianNameParts = array_filter([
                                                                                                $personalDetails->guardian_first_name,
                                                                                                $personalDetails->guardian_middle_name,
                                                                                                $personalDetails->guardian_last_name
                                                                                            ]);
                                                                                            $guardianName = implode(' ', $guardianNameParts);
                                                                                            if ($personalDetails->guardian_suffix) {
                                                                                                $guardianName .= ', ' . $personalDetails->guardian_suffix;
                                                                                            }
                                                                                        @endphp
                                                                                        {{ $guardianName ?: 'N/A' }}
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                            @if($personalDetails->guardian_relationship)
                                                                                <tr>
                                                                                    <th class="ps-0" scope="row">Relationship :</th>
                                                                                    <td class="text-muted">{{ ucfirst($personalDetails->guardian_relationship) }}</td>
                                                                                </tr>
                                                                            @endif
                                                                            @if($personalDetails->guardian_contact_no)
                                                                                <tr>
                                                                                    <th class="ps-0" scope="row">Contact Number :</th>
                                                                                    <td class="text-muted">{{ $personalDetails->guardian_contact_no }}</td>
                                                                                </tr>
                                                                            @endif
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <p class="text-muted mb-0">No guardian information available.</p>
                                                            @endif
                                                        </div>
                                                        <!--end col-->
                                                    </div>
                                                    <!--end row-->
                                                @else
                                                    <div class="text-center py-4">
                                                        <p class="text-muted mb-0">No personal information available. Please update your profile.</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <!--end card body-->
                                        </div>
                                        <!--end card-->
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                    <div class="tab-pane fade {{ $activeTab === 'activities' ? 'show active' : '' }}" id="activities"
                        role="tabpanel">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Activity Logs</h5>
                                <div class="acitivity-timeline">
                                    @forelse ($activityLogs as $activityLog)
                                        @php
                                            $changes = collect($activityLog->properties['changes_summary'] ?? [])->take(3);
                                        @endphp
                                        <div class="acitivity-item d-flex mb-3">
                                            <div class="flex-shrink-0">
                                                <img src="{{ $photoPath }}" alt=""
                                                    class="avatar-xs rounded-circle acitivity-avatar material-shadow" />
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">{{ $activityLog->causer->name ?? 'System' }} <span
                                                        class="badge bg-primary-subtle text-primary align-middle text-uppercase">
                                                        {{ $activityLog->event ?? 'activity' }}
                                                    </span>
                                                </h6>
                                                <p class="text-muted mb-2">
                                                    {{ $activityLog->description ?? 'No description provided' }}
                                                </p>
                                                <div class="text-muted small mb-2">
                                                    <div>Module: <span
                                                            class="fw-semibold">{{ $activityLog->log_name ?? 'General' }}</span>
                                                    </div>
                                                    <div>IP: <span
                                                            class="fw-semibold">{{ $activityLog->ip_address ?? 'N/A' }}</span>
                                                    </div>
                                                    @if($activityLog->address)
                                                        <div>Device Name: <span
                                                                class="fw-semibold">{{ $activityLog->address }}</span></div>
                                                    @endif
                                                </div>
                                                @if($changes->isNotEmpty())
                                                    <div class="border border-dashed rounded p-2 bg-light-subtle mb-2">
                                                        @foreach($changes as $change)
                                                            <div class="d-flex justify-content-between small text-muted">
                                                                <span>{{ \Illuminate\Support\Str::headline($change['field'] ?? 'Field') }}</span>
                                                                <span>{{ $change['from'] ?? '—' }} →
                                                                    {{ $change['to'] ?? '—' }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <small
                                                    class="mb-0 text-muted">{{ $activityLog->created_at->format('d M Y H:i a') }}</small>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center">
                                            <p class="text-muted">No activity logs found</p>
                                        </div>
                                    @endforelse

                                    @if($hasMoreActivityLogs)
                                        <div class="text-center mt-3">
                                            <x-button size="sm" variant="soft" color="secondary"
                                                icon="ri-arrow-down-double-line" wire:click="loadMoreActivities"
                                                wire:loading.attr="disabled">
                                                <span wire:loading.remove wire:target="loadMoreActivities">Load More</span>
                                                <span wire:loading wire:target="loadMoreActivities">Loading...</span>
                                            </x-button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <!--end card-body-->
                        </div>
                        <!--end card-->
                    </div>
                    <!--end tab-pane-->


                    <!--end tab-pane-->
                </div>
                <!--end tab-content-->
            </div>
        </div>
        <!--end col-->
    </div>
    <!--end row-->
</div>