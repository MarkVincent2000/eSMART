<x-modal id="create-permission-modal" wire:model="showPermissionModal" overflow="visible" :title="$permissionId ? 'Edit Permission' : 'Create Permission'" size="lg" :centered="true" :show-footer="true">
    <form wire:submit.prevent="savePermission">
        <div class="row g-3">
            <div class="col-md-12">
                <label for="name" class="form-label">Permission Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name"
                    placeholder="Enter permission name (e.g., create-user, edit-post, delete-comment)">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Use lowercase letters and hyphens (e.g., create-user, edit-post,
                    delete-comment)</small>
            </div>

            <div class="col-md-12">
                <label for="guard_name" class="form-label">Guard Name <span class="text-danger">*</span></label>
                <select class="form-control @error('guard_name') is-invalid @enderror" id="guard_name"
                    wire:model="guard_name">
                    <option value="web">Web</option>
                    <option value="api">API</option>
                </select>
                @error('guard_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Select the guard for this permission (usually 'web' for web
                    applications)</small>
            </div>

            <div class="col-md-12">
                <x-select label="Assign to Roles" wire:model="selectedRoles" :options="$roleOptions"
                    placeholder="Select roles (optional)" multiple :searchable="true" />
                @error('selectedRoles')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('selectedRoles.*')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="text-muted">Select which roles should have this permission. You can leave this empty and
                    assign later.</small>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
        <x-button color="primary" wire:click="savePermission" wire-target="savePermission">
            <span wire:loading.remove wire:target="savePermission">
                {{ $permissionId ? 'Update Permission' : 'Create Permission' }}
            </span>
            <span wire:loading wire:target="savePermission">
                {{ $permissionId ? 'Updating...' : 'Creating...' }}
            </span>
        </x-button>
    </x-slot:footer>
</x-modal>