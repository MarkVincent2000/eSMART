<x-modal id="invite-user-modal" wire:model="showInviteModal" :title="$userId ? 'Edit User' : 'Invite User'" size="lg"
    :centered="true" :show-footer="true">
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
                    <input class="form-check-input" type="checkbox" id="active_status" wire:model="active_status">
                    <label class="form-check-label" for="active_status">
                        Active Status
                    </label>
                </div>
                <small class="text-muted">Uncheck to create user as inactive</small>
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