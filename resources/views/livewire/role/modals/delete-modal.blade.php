<x-modal id="delete-role-modal" wire:model="showDeleteModal" title="Delete Role" size="md" :centered="true"
    :show-footer="true">

    <div class="text-center">
        <div class="mb-4">
            <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
        </div>
        <h5 class="mb-3">Are you sure?</h5>
        <p class="text-muted">
            You are about to delete the role <strong>{{ $deleteRoleName ?? 'this role' }}</strong>.
            This action cannot be undone.
        </p>
        <div class="alert alert-warning mt-3">
            <i class="ri-alert-line me-2"></i>
            <strong>Warning:</strong> All users with this role will lose this role assignment.
        </div>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
        <x-button color="danger" wire:click="confirmDelete" wire-target="confirmDelete">
            <span wire:loading.remove wire:target="confirmDelete">Delete Role</span>
            <span wire:loading wire:target="confirmDelete">Deleting...</span>
        </x-button>
    </x-slot:footer>
</x-modal>