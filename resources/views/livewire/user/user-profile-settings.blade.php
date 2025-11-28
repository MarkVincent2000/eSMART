<div data-component="user-profile-settings">
    <x-toast-notification />

    <div class="position-relative mx-n4 mt-n4">
        <div class="profile-wid-bg profile-setting-img">
            @php
                $coverPhotoPath = $cover_photo_preview ?? ($user->cover_photo_path
                    ? (str_starts_with($user->cover_photo_path, 'http') ? $user->cover_photo_path : asset('storage/' . $user->cover_photo_path))
                    : asset('build/images/profile-bg.jpg'));
            @endphp
            <img src="{{ $coverPhotoPath }}" class="profile-wid-img" alt="">
            <div class="overlay-content">
                <div class="text-end p-3">
                    <div class="p-0 ms-auto rounded-circle profile-photo-edit">
                        <input id="profile-foreground-img-file-input" type="file"
                            class="profile-foreground-img-file-input" wire:model="cover_photo" accept="image/*"
                            wire:loading.attr="disabled">
                        <label for="profile-foreground-img-file-input" class="profile-photo-edit btn btn-light"
                            wire:loading.class="disabled">
                            <i class="ri-image-edit-line align-bottom me-1"></i> Change Cover
                        </label>
                    </div>
                    @if($cover_photo)
                        <div class="mt-2 text-end">
                            <x-button color="primary" size="sm" wire:click="updateCoverPhoto" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="updateCoverPhoto">Save Cover</span>
                                <span wire:loading wire:target="updateCoverPhoto">Uploading...</span>
                            </x-button>
                            <button type="button" class="btn btn-light btn-sm ms-2" wire:click="$set('cover_photo', null)"
                                wire:loading.attr="disabled">Cancel</button>
                        </div>
                    @endif
                    @error('cover_photo')
                        <div class="text-danger small mt-1 text-end">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-3">
            <div class="card mt-n5">
                <div class="card-body p-4">
                    <div class="text-center">
                        <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                            @php
                                $photoPath = $profile_photo_preview ?? ($user->photo_path
                                    ? (str_starts_with($user->photo_path, 'http') ? $user->photo_path : asset('storage/' . $user->photo_path))
                                    : ($user->avatar ? asset('build/images/users/' . $user->avatar) : asset('build/images/users/avatar-1.jpg')));
                            @endphp
                            <img src="{{ $photoPath }}"
                                class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow"
                                alt="user-profile-image">
                            <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                <input id="profile-img-file-input" type="file" class="profile-img-file-input"
                                    wire:model="profile_photo" accept="image/*" wire:loading.attr="disabled">
                                <label for="profile-img-file-input" class="profile-photo-edit avatar-xs"
                                    wire:loading.class="disabled">
                                    <span class="avatar-title rounded-circle bg-light text-body material-shadow">
                                        <i class="ri-camera-fill"></i>
                                    </span>
                                </label>
                            </div>

                        </div>

                        @if($profile_photo)
                            <div class="my-3">
                                <x-button color="primary" size="sm" wire:click="updateProfilePhoto"
                                    wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="updateProfilePhoto">Save Photo</span>
                                    <span wire:loading wire:target="updateProfilePhoto">Uploading...</span>
                                </x-button>
                                <button type="button" class="btn btn-light btn-sm ms-2"
                                    wire:click="$set('profile_photo', null)" wire:loading.attr="disabled">Cancel</button>
                            </div>
                        @endif
                        @error('profile_photo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        @php
                            $nameParts = array_filter([$user->first_name, $user->middle_name, $user->last_name]);
                            $fullName = implode(' ', $nameParts);
                            if (!empty($user->name_extension)) {
                                $fullName .= ', ' . $user->name_extension;
                            }
                        @endphp
                        <h5 class="fs-16 mb-1">{{ $fullName ?: $user->name }}</h5>
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                    </div>
                </div>
            </div>
            <!--end card-->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-5">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Complete Your Profile</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="javascript:void(0);" class="badge bg-light text-primary fs-12"><i
                                    class="ri-edit-box-line align-bottom me-1"></i> Edit</a>
                        </div>
                    </div>
                    <div class="progress animated-progress custom-progress progress-label">
                        @php
                            $completionPercentage = $this->profileCompletionPercentage;
                            $progressColor = $completionPercentage >= 80 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger');
                        @endphp
                        <div class="progress-bar bg-{{ $progressColor }}" role="progressbar"
                            style="width: {{ $completionPercentage }}%" aria-valuenow="{{ $completionPercentage }}"
                            aria-valuemin="0" aria-valuemax="100">
                            <div class="label">{{ $completionPercentage }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!--end card-->
        </div>
        <!--end col-->
        <div class="col-xxl-9">
            <div class="card mt-xxl-n5">
                <div class="card-header">
                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'personalDetails' ? 'active' : '' }}"
                                data-bs-toggle="tab" href="#personalDetails" role="tab"
                                onclick="updateUrlTab('personalDetails')">
                                <i class="fas fa-home"></i> Personal Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'changePassword' ? 'active' : '' }}"
                                data-bs-toggle="tab" href="#changePassword" role="tab"
                                onclick="updateUrlTab('changePassword')">
                                <i class="far fa-user"></i> Change Password
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content">
                        <div class="tab-pane {{ $activeTab === 'personalDetails' ? 'show active' : '' }}"
                            id="personalDetails" role="tabpanel">
                            <form wire:submit.prevent="updateProfile">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="firstnameInput" class="form-label">First Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('first_name') is-invalid @enderror"
                                                id="firstnameInput" placeholder="Enter your firstname"
                                                wire:model="first_name">
                                            @error('first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="lastnameInput" class="form-label">Last Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('last_name') is-invalid @enderror"
                                                id="lastnameInput" placeholder="Enter your lastname"
                                                wire:model="last_name">
                                            @error('last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="middlenameInput" class="form-label">Middle Name</label>
                                            <input type="text"
                                                class="form-control @error('middle_name') is-invalid @enderror"
                                                id="middlenameInput" placeholder="Enter your middle name"
                                                wire:model="middle_name">
                                            @error('middle_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="nameExtensionInput" class="form-label">Name Extension</label>
                                            <input type="text"
                                                class="form-control @error('name_extension') is-invalid @enderror"
                                                id="nameExtensionInput" placeholder="e.g., Jr., Sr., III"
                                                wire:model="name_extension">
                                            @error('name_extension')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="text-muted">e.g., Jr., Sr., III, IV</small>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="phonenumberInput" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                id="phonenumberInput" placeholder="Enter your phone number"
                                                wire:model="phone">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="emailInput" class="form-label">Email Address <span
                                                    class="text-danger">*</span></label>
                                            <input type="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                id="emailInput" placeholder="Enter your email" wire:model="email">
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="addressInput" class="form-label">Address</label>
                                            <textarea class="form-control @error('address') is-invalid @enderror"
                                                id="addressInput" placeholder="Enter your address" rows="3"
                                                wire:model="address"></textarea>
                                            @error('address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <x-button color="primary" type="submit" wire-target="updateProfile">
                                                <span wire:loading.remove wire:target="updateProfile">Update</span>
                                                <span wire:loading wire:target="updateProfile">Updating...</span>
                                            </x-button>

                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                        </div>
                        <!--end tab-pane-->
                        <div class="tab-pane {{ $activeTab === 'changePassword' ? 'show active' : '' }}"
                            id="changePassword" role="tabpanel">
                            <form wire:submit.prevent="updatePassword">
                                <div class="row g-2">
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="oldpasswordInput" class="form-label">Current Password <span
                                                    class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control @error('current_password') is-invalid @enderror"
                                                id="oldpasswordInput" placeholder="Enter current password"
                                                wire:model="current_password">
                                            @error('current_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="newpasswordInput" class="form-label">New Password <span
                                                    class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control @error('new_password') is-invalid @enderror"
                                                id="newpasswordInput" placeholder="Enter new password"
                                                wire:model="new_password">
                                            @error('new_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-4">
                                        <div>
                                            <label for="confirmpasswordInput" class="form-label">Confirm Password <span
                                                    class="text-danger">*</span></label>
                                            <input type="password"
                                                class="form-control @error('confirm_password') is-invalid @enderror"
                                                id="confirmpasswordInput" placeholder="Confirm password"
                                                wire:model="confirm_password">
                                            @error('confirm_password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <a href="javascript:void(0);"
                                                class="link-primary text-decoration-underline">Forgot Password ?</a>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="text-end">
                                            <x-button color="success" icon="ri-key-2-fill" icon-position="left"
                                                wire:click="updatePassword" wire-target="updatePassword">
                                                Change Password
                                            </x-button>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                            </form>
                            <div class="mt-4 mb-3 border-bottom pb-2">
                                <div class="float-end">
                                    @if($loginHistories->count() > 1)
                                        <a href="javascript:void(0);" wire:click="logoutAllDevices"
                                            wire:confirm="Are you sure you want to logout from all other devices?"
                                            class="link-primary">All Logout</a>
                                    @endif
                                </div>
                                <h5 class="card-title">Login History</h5>
                            </div>
                            @forelse($loginHistories as $history)
                                @php
                                    $currentSessionId = session()->getId();
                                    $isCurrentSession = $history->session_id && $history->session_id === $currentSessionId;
                                    // Determine icon based on device type
                                    $deviceIcon = match ($history->device_type) {
                                        'mobile' => 'ri-smartphone-line',
                                        'tablet' => 'ri-tablet-line',
                                        'desktop' => 'ri-macbook-line',
                                        default => 'ri-computer-line'
                                    };
                                    // Format location
                                    $locationParts = array_filter([$history->city, $history->country]);
                                    $location = !empty($locationParts) ? implode(', ', $locationParts) : 'Unknown Location';
                                @endphp
                                <div class="d-flex align-items-center {{ !$loop->last ? 'mb-3' : '' }}">
                                    <div class="flex-shrink-0 avatar-sm">
                                        <div class="avatar-title bg-light text-primary rounded-3 fs-18 material-shadow">
                                            <i class="{{ $deviceIcon }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6>
                                            {{ $history->device_name ?? 'Unknown Device' }}
                                            @if($isCurrentSession)
                                                <span class="badge bg-success ms-2">Current</span>
                                            @endif
                                        </h6>
                                        <p class="text-muted mb-0">
                                            {{ $location }} - {{ $history->login_at->format('F d \a\t g:i A') }}
                                        </p>
                                    </div>
                                    <div>
                                        @if(!$isCurrentSession)
                                            <a href="javascript:void(0);" wire:click="logoutDevice({{ $history->id }})"
                                                wire:confirm="Are you sure you want to logout from this device?"
                                                class="text-danger">Logout</a>
                                        @else
                                            <span class="text-muted">Current Session</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <p class="text-muted mb-0">No login history available.</p>
                                </div>
                            @endforelse


                            <div class="mt-4">

                                <x-pagination :paginator="$loginHistories" :show-summary="true" />
                            </div>

                        </div>
                        <!--end tab-pane-->
                    </div>
                </div>
            </div>
        </div>
        <!--end col-->
    </div>



</div>