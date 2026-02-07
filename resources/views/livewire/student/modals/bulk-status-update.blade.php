<x-modal id="bulk-status-modal" wire:model="showBulkStatusModal" title="Update Student Status" size="md"
    :centered="true" :show-footer="true">
    <form wire:submit.prevent="saveBulkStatusUpdate">
        <div class="mb-3">
            <p class="text-muted">
                You are updating the status of <strong>{{ count($this->selected) }}</strong> selected student(s).
            </p>
        </div>

        <div class="mb-3">
            <label for="bulkStatus" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select @error('bulkStatus') is-invalid @enderror" id="bulkStatus"
                wire:model="bulkStatus">
                <option value="pending">Pending</option>
                <option value="enrolled">Enrolled</option>
                <option value="inactive">Inactive</option>
                <option value="graduated">Graduated</option>
            </select>
            @error('bulkStatus')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="bulkStatusSmsNotification"
                    wire:model="bulkStatusSmsNotification">
                <label class="form-check-label" for="bulkStatusSmsNotification">
                    Send SMS to students and guardians when updating status
                </label>
            </div>
            <small class="text-muted d-block mt-1">Students and guardians will receive SMS about the status change</small>
        </div>

        <div class="alert alert-warning">
            <i class="ri-alert-line me-2"></i>
            <strong>Warning:</strong> This action will update the status of all selected students. This cannot be
            undone.
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light" wire:click="closeBulkStatusModal">Cancel</button>
        <x-button color="primary" wire:click="saveBulkStatusUpdate" wireTarget="saveBulkStatusUpdate">
            <span wire:loading.remove wire:target="saveBulkStatusUpdate">
                Update Status
            </span>
            <span wire:loading wire:target="saveBulkStatusUpdate">
                Updating...
            </span>
        </x-button>
    </x-slot:footer>
</x-modal>