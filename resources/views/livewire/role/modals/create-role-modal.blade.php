<x-modal id="create-role-modal" wire:model="showRoleModal" :title="$roleId ? 'Edit Role' : 'Create Role'" size="lg"
    :centered="true" :show-footer="true">
    <form wire:submit.prevent="saveRole">
        <div class="row g-3">
            <div class="col-md-12">
                <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name"
                    placeholder="Enter role name (e.g., admin, manager, user)">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Use lowercase letters and hyphens (e.g., super-admin, content-manager)</small>
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
                <small class="text-muted">Select the guard for this role (usually 'web' for web applications)</small>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
        <x-button color="primary" wire:click="saveRole" wire-target="saveRole">
            <span wire:loading.remove wire:target="saveRole">
                {{ $roleId ? 'Update Role' : 'Create Role' }}
            </span>
            <span wire:loading wire:target="saveRole">
                {{ $roleId ? 'Updating...' : 'Creating...' }}
            </span>
        </x-button>
    </x-slot:footer>
</x-modal>