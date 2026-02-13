<div>
    <x-toast-notification />

    {{-- Create / Edit Workload Modal --}}
    <x-modal id="create-workload-modal" wire:model="showCreateModal" :title="'Add Workload'" size="xl"
        vertical-align="center" :show-footer="true" overflow="visible">
        <form wire:submit="saveWorkload">
            <div class="row g-3" style="overflow: visible;">
                {{-- Grade Level Selection --}}
                <div class="col-12">
                    <label for="gradeLevel" class="form-label fw-semibold mb-2">Grade Level</label>
                    <select 
                        id="gradeLevel" 
                        class="form-select @error('gradeLevel') is-invalid @enderror" 
                        wire:model.live="gradeLevel"
                    >
                        <option value="">Select grade level...</option>
                        @foreach($this->gradeLevelOptions as $grade)
                            <option value="{{ $grade['value'] }}" @if($grade['value'] == $gradeLevel) selected @endif>
                                {{ $grade['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('gradeLevel')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Section Selection --}}
                <div class="col-12">
                    <label for="sectionId" class="form-label fw-semibold mb-2">Section (optional)</label>
                    <select 
                        id="sectionId" 
                        class="form-select @error('sectionId') is-invalid @enderror" 
                        wire:model.live="sectionId"
                        @if(!$gradeLevel && !$workloadId) disabled @endif
                    >
                        <option value="">Select section...</option>
                        @foreach($this->sectionOptions as $section)
                            <option value="{{ $section['value'] }}" @if($section['value'] == $sectionId) selected @endif>
                                {{ $section['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @if(!$gradeLevel && !$workloadId)
                        <small class="text-muted">Please select a grade level first.</small>
                    @endif
                    @error('sectionId')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Subject Selection --}}
                <div class="col-12">
                    <label for="subjectId" class="form-label fw-semibold mb-2">Subject <span class="text-danger">*</span></label>
                    <select 
                        id="subjectId" 
                        class="form-select @error('subjectId') is-invalid @enderror" 
                        wire:model.live="subjectId"
                        @if(!$sectionId && !$workloadId) disabled @endif
                    >
                        <option value="">Select subject...</option>
                        @foreach($this->subjectOptions as $subject)
                            <option value="{{ $subject['value'] }}" @if($subject['value'] == $subjectId) selected @endif>
                                {{ $subject['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @if(!$sectionId && !$workloadId)
                        <small class="text-muted">Please select a section first.</small>
                    @endif
                    @error('subjectId')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="semesterId" class="form-label fw-semibold mb-2">Semester</label>
                    <select 
                        id="semesterId" 
                        class="form-select @error('semesterId') is-invalid @enderror" 
                        wire:model="semesterId"
                        disabled
                    >
                        <option value="">Active semester will be used</option>
                        @foreach($semesterOptions as $semester)
                            <option value="{{ $semester['value'] }}" @if($semester['value'] == $semesterId) selected @endif>
                                {{ $semester['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('semesterId')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        The current active semester is automatically applied and cannot be changed here.
                    </small>
                </div>

                <div class="col-12">
                    <label for="room" class="form-label">Room </label>
                    <input type="text" id="room" wire:model="room"
                        class="form-control @error('room') is-invalid @enderror"
                        placeholder="Enter room (e.g., Room 201)">
                    @error('room')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <label for="loadUnits" class="form-label">Load Units </label>
                    <input type="number" step="0.01" min="0" max="999.99"
                        class="form-control @error('loadUnits') is-invalid @enderror" id="loadUnits"
                        wire:model="loadUnits" placeholder="Enter teaching load units (e.g., 3.00)">
                    @error('loadUnits')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Current units: {{ $loadUnits !== '' ? $loadUnits : '0.00' }}
                    </div>
                </div>

                <div class="col-12">
                    <label for="scheduleText" class="form-label">Schedule </label>
                    <textarea id="scheduleText" rows="3" wire:model="scheduleText"
                        class="form-control @error('scheduleText') is-invalid @enderror"
                        placeholder="Enter schedule details (e.g., MWF 8:00–9:00 AM, Room 201)"></textarea>
                    @error('scheduleText')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">This will be stored as part of the workload's schedule
                        information.</small>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeCreateModal">Cancel</button>
            <x-button color="primary" wire:click="saveWorkload" wireTarget="saveWorkload">
                <span wire:loading.remove wire:target="saveWorkload">
                    {{ $workloadId ? 'Update Workload' : 'Add Workload' }}
                </span>
                <span wire:loading wire:target="saveWorkload">
                    {{ $workloadId ? 'Updating...' : 'Saving...' }}
                </span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Delete Workload Modal --}}
    <x-modal id="delete-workload-modal" wire:model="showDeleteWorkloadModal" title="Delete Workload" size="md"
        :centered="true" :show-footer="true">

        <div class="text-center">
            <div class="mb-4">
                <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
            </div>
            <h5 class="mb-3">Are you sure?</h5>
            <p class="text-muted">
                You are about to delete the workload
                <strong>{{ $deleteWorkloadLabel ?? 'this workload' }}</strong>.
                This action cannot be undone.
            </p>
            <div class="alert alert-warning mt-3">
                <i class="ri-alert-line me-2"></i>
                <strong>Warning:</strong> This will remove the workload from the system.
            </div>
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeDeleteWorkloadModal">Cancel</button>
            <x-button color="danger" wire:click="confirmDeleteWorkload" wireTarget="confirmDeleteWorkload">
                <span wire:loading.remove wire:target="confirmDeleteWorkload">Delete Workload</span>
                <span wire:loading wire:target="confirmDeleteWorkload">Deleting...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- View Students Modal --}}
    <x-modal id="view-students-modal" wire:model="showViewStudentsModal" title="View Students" size="xl"
        :centered="true" :show-footer="true" overflow="visible">
        @if($this->selectedWorkload)
            <div class="mb-3">
                <div class="alert alert-info mb-3">
                    <div class="d-flex align-items-center">
                        <i class="ri-information-line me-2 fs-5"></i>
                        <div>
                            <strong>Subject:</strong> {{ $this->selectedWorkload->subject->name ?? 'N/A' }}<br>
                            <strong>Section:</strong> {{ $this->selectedWorkload->section->name ?? 'N/A' }}
                            @if($this->selectedWorkload->section && $this->selectedWorkload->section->year_level)
                                @php
                                    $gradeLevel = $this->selectedWorkload->section->year_level instanceof \App\Enums\YearLevel
                                        ? $this->selectedWorkload->section->year_level->label()
                                        : 'Grade ' . $this->selectedWorkload->section->year_level;
                                @endphp
                                <span class="badge bg-primary-subtle text-primary ms-1">{{ $gradeLevel }}</span>
                            @endif
                            @if($this->selectedWorkload->semester)
                                <br><strong>Semester:</strong> {{ $this->selectedWorkload->semester->name ?? 'N/A' }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Student Number</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Year Level</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->enrolledStudents as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $student->student_number ?? '—' }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $student->user->name ?? 'N/A' }}
                                    </div>
                                    <small class="text-muted">{{ $student->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    {{ $student->program->name ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        Grade {{ $student->year_level ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success text-uppercase">
                                        {{ ucfirst($student->status ?? 'enrolled') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                        colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
                                    </lord-icon>
                                    <h5 class="mt-3">No enrolled students</h5>
                                    <p class="text-muted mb-0">
                                        There are no enrolled students in this section yet.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($this->enrolledStudents->isNotEmpty())
                <div class="mt-3 text-muted">
                    <small>
                        <i class="ri-information-line me-1"></i>
                        Showing {{ $this->enrolledStudents->count() }} enrolled student(s) in
                        {{ $this->selectedWorkload->section->name ?? 'this section' }}.
                    </small>
                </div>
            @endif
        @else
            <div class="text-center py-4">
                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                    colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
                </lord-icon>
                <h5 class="mt-3">Loading...</h5>
            </div>
        @endif

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeViewStudentsModal">Close</button>
        </x-slot:footer>
    </x-modal>

    {{-- Print Workloads Modal --}}
    <x-modal id="print-workloads-modal" wire:model="showPrintModal" title="Print Workloads" size="xl"
        :centered="true" :show-footer="true" overflow="visible" :contentScrollable="false">

        {{-- Wizard Steps --}}
        <div class="mb-4">
            <ul class="nav nav-pills nav-justified" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $printStep == 1 ? 'active' : ($printStep > 1 ? 'completed' : '') }}"
                        type="button" disabled>
                        <i class="ri-filter-line me-1"></i>
                        <span class="d-none d-sm-inline">Step 1:</span> Filters
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $printStep == 2 ? 'active' : '' }}" type="button" disabled>
                        <i class="ri-file-pdf-line me-1"></i>
                        <span class="d-none d-sm-inline">Step 2:</span> Preview
                    </button>
                </li>
            </ul>
        </div>

        {{-- Step 1: Filters --}}
        @if($printStep == 1)
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="printSectionId" class="form-label fw-semibold mb-2">Section (optional)</label>
                    <select 
                        id="printSectionId" 
                        class="form-select" 
                        wire:model="printSectionId"
                    >
                        <option value="">All sections</option>
                        @foreach($this->sectionOptions as $section)
                            <option value="{{ $section['value'] }}">{{ $section['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="printSemesterId" class="form-label fw-semibold mb-2">Semester (optional)</label>
                    <select 
                        id="printSemesterId" 
                        class="form-select" 
                        wire:model="printSemesterId"
                    >
                        <option value="">All semesters</option>
                        @foreach($semesterOptions as $semester)
                            <option value="{{ $semester['value'] }}">{{ $semester['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="printSubjectId" class="form-label fw-semibold mb-2">Subject (optional)</label>
                    <select 
                        id="printSubjectId" 
                        class="form-select" 
                        wire:model="printSubjectId"
                    >
                        <option value="">All subjects</option>
                        @foreach($this->subjectOptions as $subject)
                            <option value="{{ $subject['value'] }}">{{ $subject['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <h6 class="mb-2">Selected Filters Summary:</h6>
                <div class="card">
                    <div class="card-body">
                        @if(empty($this->printFilterSummary))
                            <p class="text-muted mb-0">No filters selected. All matching workloads will be included.</p>
                        @else
                            <div class="row g-3">
                                @foreach($this->printFilterSummary as $label => $value)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start">
                                            <strong class="text-muted me-2" style="min-width: 100px;">{{ $label }}:</strong>
                                            <span>{{ $value }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 2: PDF Preview --}}
        @if($printStep == 2)
            <div class="mb-4">
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>Preview:</strong> The PDF preview is shown below. You can print or download it using the
                    browser's print dialog.
                </div>

                @if($printUrl)
                    <div class="border rounded" style="height: 600px; overflow: hidden;">
                        <iframe src="{{ $printUrl }}" style="width: 100%; height: 100%; border: none;"
                            title="Workloads PDF Preview">
                        </iframe>
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">Generating PDF preview...</p>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:footer>
            @if($printStep == 1)
                <button type="button" class="btn btn-light" wire:click="closePrintModal">Cancel</button>
                <x-button color="primary" wire:click="nextPrintStep" wireTarget="nextPrintStep">
                    Next: Preview
                    <i class="ri-arrow-right-line ms-1"></i>
                </x-button>
            @elseif($printStep == 2)
                <button type="button" class="btn btn-light" wire:click="previousPrintStep">Back</button>
                <button type="button" class="btn btn-light" wire:click="closePrintModal">Close</button>
                @if($printUrl)
                    <a href="{{ $printUrl }}" target="_blank" class="btn btn-primary">
                        <i class="ri-download-line me-1"></i>
                        Download PDF
                    </a>
                @endif
            @endif
        </x-slot:footer>
    </x-modal>

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="workloadListCard">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">My Workload</h5>
                        </div>
                        @if($canAddWorkload)
                            <div class="col-sm-auto">
                                <div class="d-flex gap-1 flex-wrap">
                                    <x-button color="success" icon="ri-add-line" loading="true"
                                        loadingText="Opening form..." wireTarget="openCreateModal"
                                        wire:click="openCreateModal">
                                        Add Workload
                                    </x-button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <form class="row g-3 align-items-center">
                        <div class="col-xxl-5 col-sm-6">
                            <div class="search-box">
                                <input type="text" class="form-control search"
                                    placeholder="Search by subject, section, semester, or classroom..."
                                    wire:model.live.debounce.300ms="search">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>

                        <div class="col-sm-auto ms-auto">
                            <x-button color="secondary" variant="soft" icon="ri-printer-line"
                                wire:click="printWorkloads" wireTarget="printWorkloads">
                                Print
                            </x-button>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive table-card mb-2">
                        <table class="table align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    @if($isSuperAdmin)
                                        <th>Teacher</th>
                                    @endif
                                    <th>Subject</th>
                                    <th>Section</th>
                                    <th>Semester / SY</th>
                                    <th>Classroom</th>
                                    <th>Load Units</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($workloads as $index => $workload)
                                    <tr>
                                        <td>
                                            {{ ($workloads->currentPage() - 1) * $workloads->perPage() + $index + 1 }}
                                        </td>
                                        @if($isSuperAdmin)
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $workload->teacher->user->name ?? 'N/A' }}
                                                </div>
                                            </td>
                                        @endif
                                        <td>
                                            <div class="fw-semibold">
                                                {{ $workload->subject->display_name ?? $workload->subject->name ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($workload->section)
                                                <div class="fw-semibold">
                                                    {{ $workload->section->name }}
                                                </div>
                                                @if($workload->section->year_level)
                                                    <small class="text-muted">
                                                        @php
                                                            $gradeLevel = $workload->section->year_level instanceof \App\Enums\YearLevel
                                                                ? $workload->section->year_level->label()
                                                                : 'Grade ' . $workload->section->year_level;
                                                        @endphp
                                                        {{ $gradeLevel }}
                                                    </small>
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($workload->semester)
                                                {{ $workload->semester->name ?? 'Semester' }}
                                                <span class="text-muted">
                                                    ({{ $workload->semester->school_year ?? 'SY' }})
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($workload->classroom)
                                                {{ $workload->classroom->name ?? 'Classroom' }}
                                                <span class="text-muted">
                                                    ({{ $workload->classroom->class_code ?? '' }})
                                                </span>
                                            @elseif(is_array($workload->schedule ?? null) && !empty($workload->schedule['room'] ?? null))
                                                Room {{ $workload->schedule['room'] }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            {{ $workload->load_units !== null ? number_format($workload->load_units, 2) : '0.00' }}
                                        </td>
                                        <td>
                                            <ul class="list-inline hstack gap-1 mb-0">
                                                <li class="list-inline-item">
                                                    <x-button color="info" size="sm" icon-position="left" :iconOnly="true"
                                                        icon="ri-group-line" tooltip="View Students"
                                                        wire:click="viewStudents({{ $workload->id }})"
                                                        wireTarget="viewStudents({{ $workload->id }})">
                                                    </x-button>
                                                </li>
                                                <li class="list-inline-item">
                                                    <x-button color="primary" size="sm" icon-position="left" :iconOnly="true"
                                                        icon="ri-edit-line" tooltip="Edit Workload"
                                                        wire:click="editWorkload({{ $workload->id }})"
                                                        wireTarget="editWorkload({{ $workload->id }})">
                                                    </x-button>
                                                </li>
                                                <li class="list-inline-item">
                                                    <x-button color="danger" size="sm" :iconOnly="true"
                                                        icon="ri-delete-bin-line" tooltip="Delete Workload"
                                                        wire:click="deleteWorkload({{ $workload->id }})"
                                                        wireTarget="deleteWorkload({{ $workload->id }})">
                                                    </x-button>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isSuperAdmin ? 8 : 7 }}">
                                            <div class="text-center py-4">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                    colors="primary:#405189,secondary:#0ab39c"
                                                    style="width:72px;height:72px">
                                                </lord-icon>
                                                <h5 class="mt-3">No workload assigned</h5>
                                                <p class="text-muted mb-0">You don't have any teaching workload yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($workloads->hasPages())
                        <div class="mt-2">
                            <x-pagination :paginator="$workloads" :show-summary="true" />
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>