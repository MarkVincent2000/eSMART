<x-modal id="update-roles-modal" wire:model="showUpdateRolesModal" overflow="visible" title="Update Roles" size="lg"
    :centered="false" vertical-align="top" :show-footer="true">
    <div class="row g-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                <strong>{{ count($selected) }}</strong> {{ count($selected) === 1 ? 'user' : 'users' }}
                {{ count($selected) === 1 ? 'is' : 'are' }} selected.
                The selected roles will replace all existing roles for
                {{ count($selected) === 1 ? 'this user' : 'these users' }}.
            </div>
        </div>

        <div class="col-md-12">
            <x-select label="Roles" wire:model="bulkUpdateRoles" :options="$roleOptions"
                placeholder="Select roles to assign" multiple :searchable="true" />
            @error('bulkUpdateRoles')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('bulkUpdateRoles.*')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted">Select one or more roles to assign to the selected users. All existing roles will
                be replaced.</small>
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
        <x-button color="primary" wire:click="updateBulkRoles" wire-target="updateBulkRoles">
            <span wire:loading.remove wire:target="updateBulkRoles">
                Update Roles
            </span>
            <span wire:loading wire:target="updateBulkRoles">
                Updating...
            </span>
        </x-button>
    </x-slot:footer>
</x-modal>