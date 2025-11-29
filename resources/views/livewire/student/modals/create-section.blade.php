<x-modal id="create-section-modal" wire:model="showSectionModal" :title="$sectionId ? 'Edit Section' : 'Create Section'"
    size="lg" :centered="true" :show-footer="true">
    <form wire:submit.prevent="saveSection">
        <div class="row g-3">
            <div class="col-md-12">
                <label for="sectionName" class="form-label">Section Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('sectionName') is-invalid @enderror" id="sectionName"
                    wire:model="sectionName" placeholder="Enter section name (e.g., Section A, Section B)">
                @error('sectionName')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Enter a unique name for the section</small>
            </div>

            <div class="col-md-12">
                <label for="yearLevel" class="form-label">Year Level <span class="text-danger">*</span></label>
                <select class="form-control @error('yearLevel') is-invalid @enderror" id="yearLevel"
                    wire:model="yearLevel">
                    @foreach(\App\Enums\YearLevel::options() as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
                @error('yearLevel')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Select the year level for this section</small>
            </div>

            <div class="col-md-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="sectionActive" wire:model="sectionActive">
                    <label class="form-check-label" for="sectionActive">
                        Active
                    </label>
                </div>
                <small class="text-muted">Toggle to activate or deactivate this section</small>
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
        <x-button color="primary" wire:click="saveSection" wireTarget="saveSection">
            <span wire:loading.remove wire:target="saveSection">
                {{ $sectionId ? 'Update Section' : 'Create Section' }}
            </span>
            <span wire:loading wire:target="saveSection">
                {{ $sectionId ? 'Updating...' : 'Creating...' }}
            </span>
        </x-button>
    </x-slot:footer>
</x-modal>