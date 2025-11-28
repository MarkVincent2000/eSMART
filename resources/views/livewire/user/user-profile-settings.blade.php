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
                                    : ($user->avatar ? asset('build/images/users/user-dummy-img.jpg') : asset('build/images/users/user-dummy-img.jpg')));
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
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="sexInput" class="form-label">Sex</label>
                                            <select class="form-select @error('sex') is-invalid @enderror" id="sexInput"
                                                wire:model="sex">
                                                <option value="" disabled {{ empty($sex) ? 'selected' : '' }}>Select Sex
                                                </option>
                                                @foreach($this->sexOptions as $sexOption)
                                                    <option value="{{ $sexOption->value }}">{{ ucfirst($sexOption->value) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('sex')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="contactNoInput" class="form-label">Contact Number</label>
                                            <input type="text"
                                                class="form-control @error('contact_no') is-invalid @enderror"
                                                id="contactNoInput" placeholder="Enter your contact number"
                                                wire:model="contact_no">
                                            @error('contact_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="dateOfBirthInput" class="form-label">Date of Birth</label>
                                            <input type="text"
                                                class="form-control @error('date_of_birth') is-invalid @enderror"
                                                id="dateOfBirthInput" placeholder="Select date of birth" x-data="{
                                                    init() {
                                                        const fp = flatpickr(this.$el, {
                                                            dateFormat: 'Y-m-d',
                                                            altInput: true,
                                                            altFormat: 'd M, Y',
                                                            maxDate: 'today',
                                                            defaultDate: @js($date_of_birth ?: null),
                                                            onChange: (selectedDates, dateStr, instance) => {
                                                                if (selectedDates.length > 0) {
                                                                    @this.set('date_of_birth', dateStr);
                                                                } else {
                                                                    @this.set('date_of_birth', '');
                                                                }
                                                            }
                                                        });
                                                        // Sync with Livewire updates
                                                        Livewire.hook('message.processed', (message, component) => {
                                                            if (@this.date_of_birth) {
                                                                fp.setDate(@this.date_of_birth, false);
                                                            } else {
                                                                fp.clear();
                                                            }
                                                        });
                                                    }
                                                }">
                                            @error('date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="religionInput" class="form-label">Religion</label>
                                            <select class="form-select @error('religion') is-invalid @enderror"
                                                id="religionInput" wire:model="religion">
                                                <option value="" disabled {{ empty($religion) ? 'selected' : '' }}>
                                                    Select Religion</option>
                                                @foreach($this->religionOptions as $religionOption)
                                                    <option value="{{ $religionOption->value }}">
                                                        {{ ucfirst($religionOption->value) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('religion')
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

                                    <div class="border border-dashed border-end-0 border-start-0 my-4"></div>
                                    <!--end col-->

                                    <!-- Guardian Information Section -->
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <h4 class="mb-3 ">Guardian Information</h4>

                                        </div>

                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="guardianFirstNameInput" class="form-label">Guardian First
                                                Name</label>
                                            <input type="text"
                                                class="form-control @error('guardian_first_name') is-invalid @enderror"
                                                id="guardianFirstNameInput" placeholder="Enter guardian first name"
                                                wire:model="guardian_first_name">
                                            @error('guardian_first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="guardianLastNameInput" class="form-label">Guardian Last
                                                Name</label>
                                            <input type="text"
                                                class="form-control @error('guardian_last_name') is-invalid @enderror"
                                                id="guardianLastNameInput" placeholder="Enter guardian last name"
                                                wire:model="guardian_last_name">
                                            @error('guardian_last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="guardianMiddleNameInput" class="form-label">Guardian Middle
                                                Name</label>
                                            <input type="text"
                                                class="form-control @error('guardian_middle_name') is-invalid @enderror"
                                                id="guardianMiddleNameInput" placeholder="Enter guardian middle name"
                                                wire:model="guardian_middle_name">
                                            @error('guardian_middle_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="guardianSuffixInput" class="form-label">Guardian Suffix</label>
                                            <input type="text"
                                                class="form-control @error('guardian_suffix') is-invalid @enderror"
                                                id="guardianSuffixInput" placeholder="e.g., Jr., Sr., III"
                                                wire:model="guardian_suffix">
                                            @error('guardian_suffix')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="guardianRelationshipInput" class="form-label">Guardian
                                                Relationship</label>
                                            <select
                                                class="form-select @error('guardian_relationship') is-invalid @enderror"
                                                id="guardianRelationshipInput" wire:model="guardian_relationship">
                                                <option value="" disabled {{ empty($guardian_relationship) ? 'selected' : '' }}>Select Relationship</option>
                                                @foreach($this->guardianRelationshipOptions as $relationship)
                                                    <option value="{{ $relationship->value }}">
                                                        {{ ucfirst($relationship->value) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('guardian_relationship')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="guardianContactNoInput" class="form-label">Guardian Contact
                                                Number</label>
                                            <input type="text"
                                                class="form-control @error('guardian_contact_no') is-invalid @enderror"
                                                id="guardianContactNoInput" placeholder="Enter guardian contact number"
                                                wire:model="guardian_contact_no">
                                            @error('guardian_contact_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->

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