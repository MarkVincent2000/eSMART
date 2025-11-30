{{-- View Student Modal --}}
<x-modal id="view-student-modal" wire:model="showViewStudentModal" title="View Student Enrollment" size="lg"
    :centered="true" overflow="visible" :show-footer="true">
    @if($this->selectedStudentInfo)
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label text-muted">Student Number</label>
                <div class="fw-semibold">{{ $this->selectedStudentInfo->student_number }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Status</label>
                <div>
                    @if($this->selectedStudentInfo->status === 'pending')
                        <span class="badge bg-warning-subtle text-warning">Pending</span>
                    @elseif($this->selectedStudentInfo->status === 'enrolled')
                        <span class="badge bg-success-subtle text-success">Enrolled</span>
                    @elseif($this->selectedStudentInfo->status === 'inactive')
                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                    @elseif($this->selectedStudentInfo->status === 'graduated')
                        <span class="badge bg-info-subtle text-info">Graduated</span>
                    @else
                        <span
                            class="badge bg-secondary-subtle text-secondary">{{ ucfirst($this->selectedStudentInfo->status) }}</span>
                    @endif
                </div>
            </div>
            @if($this->selectedStudentInfo->user)
                <div class="col-md-6">
                    <label class="form-label text-muted">Student Name</label>
                    <div class="fw-semibold">{{ $this->selectedStudentInfo->user->name }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Email</label>
                    <div>{{ $this->selectedStudentInfo->user->email }}</div>
                </div>
            @endif
            <div class="col-md-6">
                <label class="form-label text-muted">Program</label>
                <div>
                    @if($this->selectedStudentInfo->program)
                        <span class="badge bg-primary-subtle text-primary">
                            {{ $this->selectedStudentInfo->program->code }} -
                            {{ $this->selectedStudentInfo->program->name }}
                        </span>
                    @else
                        <span class="text-muted">Not assigned</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Year Level</label>
                <div>
                    @if($this->selectedStudentInfo->year_level)
                        <span class="badge bg-secondary-subtle text-secondary">
                            Grade {{ $this->selectedStudentInfo->year_level }}
                        </span>
                    @else
                        <span class="text-muted">Not assigned</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Section</label>
                <div>
                    @if($this->selectedStudentInfo->section)
                        <span>{{ $this->selectedStudentInfo->section->name }}</span>
                    @else
                        <span class="text-muted">Not assigned</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Semester</label>
                <div>
                    @if($this->selectedStudentInfo->semester)
                        <span>{{ $this->selectedStudentInfo->semester->name }}</span>
                    @else
                        <span class="text-muted">Not assigned</span>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">School Year</label>
                <div class="fw-semibold">{{ $this->selectedStudentInfo->school_year }}</div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Enrolled At</label>
                <div>
                    {{ $this->selectedStudentInfo->enrolled_at ? $this->selectedStudentInfo->enrolled_at->format('M d, Y') : '-' }}
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Created At</label>
                <div>
                    {{ $this->selectedStudentInfo->created_at ? $this->selectedStudentInfo->created_at->format('M d, Y h:i A') : '-' }}
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted">Last Updated</label>
                <div>
                    {{ $this->selectedStudentInfo->updated_at ? $this->selectedStudentInfo->updated_at->format('M d, Y h:i A') : '-' }}
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-4">
            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
            </lord-icon>
            <h5 class="mt-4">Loading...</h5>
        </div>
    @endif

    <x-slot:footer>
        <button type="button" class="btn btn-light" wire:click="closeViewStudentModal">Close</button>
    </x-slot:footer>
</x-modal>