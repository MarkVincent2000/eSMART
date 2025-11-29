<x-modal id="create-program-modal" wire:model="showProgramModal" :title="$programId ? 'Edit Program' : 'Create Program'"
    size="lg" :centered="true" :show-footer="true">
    <form wire:submit.prevent="saveProgram">
        <div class="row g-3">
            <div class="col-md-12">
                <label for="programCode" class="form-label">Program Code <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('programCode') is-invalid @enderror" id="programCode"
                    wire:model="programCode" placeholder="Enter program code (e.g., TVL, GAS, HUMSS, STEM)">
                @error('programCode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Enter a unique code for the program</small>
            </div>

            <div class="col-md-12">
                <label for="programName" class="form-label">Program Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('programName') is-invalid @enderror" id="programName"
                    wire:model="programName"
                    placeholder="Enter program name (e.g., Technical-Vocational Livelihood Program)">
                @error('programName')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Enter the full name of the program</small>
            </div>

            <div class="col-md-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="programActive" wire:model="programActive">
                    <label class="form-check-label" for="programActive">
                        Active
                    </label>
                </div>
                <small class="text-muted">Toggle to activate or deactivate this program</small>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
        <x-button color="primary" wire:click="saveProgram" wireTarget="saveProgram">
            <span wire:loading.remove wire:target="saveProgram">
                {{ $programId ? 'Update Program' : 'Create Program' }}
            </span>
            <span wire:loading wire:target="saveProgram">
                {{ $programId ? 'Updating...' : 'Creating...' }}
            </span>
        </x-button>
    </x-slot:footer>
</x-modal>