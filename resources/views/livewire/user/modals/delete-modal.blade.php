<x-modal id="delete-user-modal" wire:model="showDeleteModal" title="Delete User" size="md" :centered="true"
    :show-footer="true">

    <div class="text-center">
        <div class="mb-4">
            <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
        </div>
        <h5 class="mb-3">Are you sure?</h5>
        <p class="text-muted">
            You are about to delete <strong>{{ $deleteUserName ?? 'this user' }}</strong>.
            This action cannot be undone.
        </p>
    </div>

    <x-slot:footer>
        <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
        <x-button color="danger" wire:click="confirmDelete" wire-target="confirmDelete">
            <span wire:loading.remove wire:target="confirmDelete">Delete User</span>
            <span wire:loading wire:target="confirmDelete">Deleting...</span>
        </x-button>
    </x-slot:footer>
</x-modal>