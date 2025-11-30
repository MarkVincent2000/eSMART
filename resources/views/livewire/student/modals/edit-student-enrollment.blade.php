<x-modal id="edit-student-modal" wire:model="showEditStudentModal" title="Edit Student Enrollment" size="lg"
    :centered="true" overflow="visible" :show-footer="true">
    <form wire:submit.prevent="saveEditStudent">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="editStudentNumber" class="form-label">Student Number <span
                        class="text-danger">*</span></label>
                <input type="text" class="form-control @error('editStudentNumber') is-invalid @enderror"
                    id="editStudentNumber" wire:model="editStudentNumber" placeholder="Enter student number">
                @error('editStudentNumber')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="editStatus" class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select @error('editStatus') is-invalid @enderror" id="editStatus"
                    wire:model="editStatus">
                    <option value="pending">Pending</option>
                    <option value="enrolled">Enrolled</option>
                    <option value="inactive">Inactive</option>
                    <option value="graduated">Graduated</option>
                </select>
                @error('editStatus')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="editYearLevel" class="form-label">Year Level <span class="text-danger">*</span></label>
                <select class="form-select @error('editYearLevel') is-invalid @enderror" id="editYearLevel"
                    wire:model.change="editYearLevel">
                    <option value="" disabled>Select Year Level</option>
                    @foreach(\App\Enums\YearLevel::options() as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
                @error('editYearLevel')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="editProgramId" class="form-label">Program</label>
                <div class="@error('editProgramId') is-invalid @enderror"
                    wire:key="edit-program-select-{{ $editYearLevel ?? 'none' }}">
                    <x-select wire:model="editProgramId" :options="$this->editProgramOptions"
                        placeholder="Select Program" :searchable="true" :disabled="!$editYearLevel"
                        id="editProgramId" />
                </div>
                @error('editProgramId')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @if(!$editYearLevel)
                    <small class="text-muted">Please select a year level first</small>
                @endif
            </div>
            <div class="col-md-6">
                <label for="editSectionId" class="form-label">Section</label>
                <div class="@error('editSectionId') is-invalid @enderror"
                    wire:key="edit-section-select-{{ $editYearLevel ?? 'none' }}">
                    <x-select wire:model="editSectionId" :options="$this->editSectionOptions"
                        placeholder="Select Section (Optional)" :searchable="true" :disabled="!$editYearLevel"
                        id="editSectionId" />
                </div>
                @error('editSectionId')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @if(!$editYearLevel)
                    <small class="text-muted">Please select a year level first</small>
                @endif
            </div>
            <div class="col-md-6">
                <label for="editEnrolledAt" class="form-label">Enrolled At</label>
                <input type="date" class="form-control @error('editEnrolledAt') is-invalid @enderror"
                    id="editEnrolledAt" wire:model="editEnrolledAt">
                @error('editEnrolledAt')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-light" wire:click="closeEditStudentModal">Cancel</button>
        <x-button color="primary" wire:click="saveEditStudent" wireTarget="saveEditStudent">
            <span wire:loading.remove wire:target="saveEditStudent">
                Update Enrollment
            </span>
            <span wire:loading wire:target="saveEditStudent">
                Updating...
            </span>
        </x-button>
    </x-slot:footer>
</x-modal>