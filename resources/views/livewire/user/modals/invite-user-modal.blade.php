<x-modal id="invite-user-modal" wire:model="showInviteModal" overflow="visible" :title="$userId ? 'Edit User' : 'Invite User'" size="xl" :centered="false" vertical-align="top" :show-footer="true">
    <form wire:submit.prevent="saveUser">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name"
                    wire:model="first_name" placeholder="Enter first name">
                @error('first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name"
                    wire:model="last_name" placeholder="Enter last name">
                @error('last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control @error('middle_name') is-invalid @enderror" id="middle_name"
                    wire:model="middle_name" placeholder="Enter middle name">
                @error('middle_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="name_extension" class="form-label">Name Extension</label>
                <input type="text" class="form-control @error('name_extension') is-invalid @enderror"
                    id="name_extension" wire:model="name_extension" placeholder="e.g., Jr., Sr., III">
                @error('name_extension')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">e.g., Jr., Sr., III, IV</small>
            </div>

            <div class="col-md-12">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                    wire:model="email" placeholder="Enter email address">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <x-select label="Roles" wire:model="selectedRoles" :options="$roleOptions" placeholder="Select roles"
                    multiple :searchable="true" />
                @error('selectedRoles')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('selectedRoles.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="password" class="form-label">
                    Password
                    @if (!$userId)
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                    wire:model="password"
                    placeholder="{{ $userId ? 'Leave blank to keep current password (min. 8 characters)' : 'Enter password (min. 8 characters)' }}">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if ($userId)
                    <small class="text-muted">Leave blank to keep the current password</small>
                @endif
            </div>

            <div class="col-md-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="active_status" wire:model="active_status"
                        wire:key="active-status-{{ $userId ?? 'new' }}-{{ $active_status ? '1' : '0' }}">
                    <label class="form-check-label" for="active_status">
                        Active Status
                    </label>
                </div>
                <small class="text-muted">
                    @if($userId)
                        {{ $active_status ? 'User is currently active' : 'User is currently inactive' }}
                    @else
                        Uncheck to create user as inactive
                    @endif
                </small>
            </div>

            <!-- Personal Details Section -->
            <div class="col-md-12">
                <div class="border border-dashed border-end-0 border-start-0 my-4"></div>
                <h5 class="mb-3">Personal Details</h5>
            </div>

            <div class="col-md-6">
                <label for="sex" class="form-label">Sex</label>
                <select class="form-select border @error('sex') is-invalid @enderror" id="sex" wire:model="sex">
                    <option value="" disabled {{ empty($sex) ? 'selected' : '' }}>Select Sex</option>
                    @foreach($this->sexOptions as $sexOption)
                        <option value="{{ $sexOption->value }}">{{ ucfirst($sexOption->value) }}</option>
                    @endforeach
                </select>
                @error('sex')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="date_of_birth" class="form-label">Date of Birth</label>
                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth"
                    wire:model="date_of_birth" max="{{ date('Y-m-d') }}">
                @error('date_of_birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="religion" class="form-label">Religion</label>
                <select class="form-select border @error('religion') is-invalid @enderror" id="religion"
                    wire:model="religion">
                    <option value="" disabled {{ empty($religion) ? 'selected' : '' }}>Select Religion</option>
                    @foreach($this->religionOptions as $religionOption)
                        <option value="{{ $religionOption->value }}">{{ ucfirst($religionOption->value) }}</option>
                    @endforeach
                </select>
                @error('religion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="contact_no" class="form-label">Contact Number</label>
                <input type="text" class="form-control @error('contact_no') is-invalid @enderror" id="contact_no"
                    wire:model="contact_no" placeholder="Enter contact number">
                @error('contact_no')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror" id="address" wire:model="address"
                    placeholder="Enter address" rows="3"></textarea>
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Guardian Information Section -->
            <div class="col-md-12">
                <div class="border border-dashed border-end-0 border-start-0 my-4"></div>
                <h5 class="mb-3">Guardian Information</h5>
            </div>

            <div class="col-md-6">
                <label for="guardian_first_name" class="form-label">Guardian First Name</label>
                <input type="text" class="form-control @error('guardian_first_name') is-invalid @enderror"
                    id="guardian_first_name" wire:model="guardian_first_name" placeholder="Enter guardian first name">
                @error('guardian_first_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="guardian_last_name" class="form-label">Guardian Last Name</label>
                <input type="text" class="form-control @error('guardian_last_name') is-invalid @enderror"
                    id="guardian_last_name" wire:model="guardian_last_name" placeholder="Enter guardian last name">
                @error('guardian_last_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="guardian_middle_name" class="form-label">Guardian Middle Name</label>
                <input type="text" class="form-control @error('guardian_middle_name') is-invalid @enderror"
                    id="guardian_middle_name" wire:model="guardian_middle_name"
                    placeholder="Enter guardian middle name">
                @error('guardian_middle_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="guardian_suffix" class="form-label">Guardian Suffix</label>
                <input type="text" class="form-control @error('guardian_suffix') is-invalid @enderror"
                    id="guardian_suffix" wire:model="guardian_suffix" placeholder="e.g., Jr., Sr., III">
                @error('guardian_suffix')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">e.g., Jr., Sr., III, IV</small>
            </div>

            <div class="col-md-6">
                <label for="guardian_relationship" class="form-label">Guardian Relationship</label>
                <select class="form-select border @error('guardian_relationship') is-invalid @enderror"
                    id="guardian_relationship" wire:model="guardian_relationship">
                    <option value="" disabled {{ empty($guardian_relationship) ? 'selected' : '' }}>Select Relationship
                    </option>
                    @foreach($this->guardianRelationshipOptions as $relationship)
                        <option value="{{ $relationship->value }}">{{ ucfirst($relationship->value) }}</option>
                    @endforeach
                </select>
                @error('guardian_relationship')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="guardian_contact_no" class="form-label">Guardian Contact Number</label>
                <input type="text" class="form-control @error('guardian_contact_no') is-invalid @enderror"
                    id="guardian_contact_no" wire:model="guardian_contact_no"
                    placeholder="Enter guardian contact number">
                @error('guardian_contact_no')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </form>



    <x-slot:footer>
        <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
        <x-button color="primary" wire:click="saveUser" wire-target="saveUser">
            <span wire:loading.remove wire:target="saveUser">
                {{ $userId ? 'Update User' : 'Invite User' }}
            </span>
            <span wire:loading wire:target="saveUser">
                {{ $userId ? 'Updating...' : 'Inviting...' }}
            </span>
        </x-button>
    </x-slot:footer>
</x-modal>