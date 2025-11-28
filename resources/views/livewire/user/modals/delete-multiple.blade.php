<x-modal id="delete-multiple-users-modal" wire:model="showDeleteMultipleModal" title="Delete Multiple Users" size="md"
    :centered="true" :show-footer="true">

    <div class="text-center">
        <div class="mb-4">
            <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
        </div>
        <h5 class="mb-3">Are you sure?</h5>
        <p class="text-muted">
            You are about to delete <strong>{{ count($selected) }}</strong>
            {{ count($selected) === 1 ? 'user' : 'users' }}.
            This action cannot be undone.
        </p>
        <div class="alert alert-warning mt-3">
            <i class="ri-alert-line me-2"></i>
            <strong>Warning:</strong> All selected users will be permanently deleted from the system.
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
        <x-button color="danger" wire:click="confirmDeleteMultiple" wire-target="confirmDeleteMultiple">
            <span wire:loading.remove wire:target="confirmDeleteMultiple">Delete {{ count($selected) }}
                {{ count($selected) === 1 ? 'User' : 'Users' }}</span>
            <span wire:loading wire:target="confirmDeleteMultiple">Deleting...</span>
        </x-button>
    </x-slot:footer>
</x-modal>