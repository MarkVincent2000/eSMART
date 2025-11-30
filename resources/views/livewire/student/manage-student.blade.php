<div>
    <x-toast-notification />
    @include('livewire.student.modals.create-section')
    @include('livewire.student.modals.delete-section')
    @include('livewire.student.modals.create-program')
    @include('livewire.student.modals.delete-program')
    @include('livewire.student.modals.view-student-enrollment')
    @include('livewire.student.modals.edit-student-enrollment')
    @include('livewire.student.modals.delete-student-enrollment')
    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex mb-3">
                        <div class="flex-grow-1">
                            <h5 class="fs-16">Filters</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="#" class="text-decoration-underline" id="clearall"
                                wire:click.prevent="clearAllFilters">Clear All</a>
                        </div>
                    </div>


                </div>

                <div class="accordion accordion-flush filter-accordion">

                    <div class="card-body border-bottom">
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <p class="text-muted text-uppercase fs-12 fw-medium mb-2">Semester</p>
                                <a href="enrollment.semester-index" type="button">
                                    <x-button color="primary" size="sm" icon="ri-settings-3-line"
                                        tooltip="Configure Semesters" icon-position="left" :iconOnly="true"></x-button>
                                </a>
                            </div>

                            <div class="search-box search-box-sm d-flex flex-column gap-2">
                                <div class="position-relative">
                                    <input type="text" class="form-control bg-light border-0"
                                        placeholder="Search Semesters..." style="padding-right: 35px;"
                                        wire:model.live.debounce.300ms="semesterSearch">
                                    <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y"
                                        style="right: 10px; pointer-events: none; z-index: 1;"></i>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-2 mt-3 filter-check">
                                @forelse($semesters as $semester)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="{{ $semester->id }}"
                                            id="semester_{{ $semester->id }}" wire:model.change="selectedSemesters">
                                        <label class="form-check-label d-flex align-items-center gap-2"
                                            for="semester_{{ $semester->id }}">
                                            <span class="small">{{ $semester->name }} <span
                                                    class="text-muted">({{ $semester->school_year }})</span></span>
                                            @if($semester->is_active)
                                                <span class="badge bg-success-subtle text-success small">Active</span>

                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <div class="text-muted text-center py-2">
                                        <small>No semesters found</small>
                                    </div>
                                @endforelse
                                @if($totalSemesters > $semesterLimit)
                                    <div class="text-center mt-2">
                                        <button type="button"
                                            class="btn btn-link text-decoration-none text-uppercase fw-medium p-0"
                                            wire:click="loadMoreSemesters" wire:target="loadMoreSemesters">
                                            <span wire:loading.remove wire:target="loadMoreSemesters">
                                                Load More ({{ $totalSemesters - $semesterLimit }} remaining)
                                            </span>
                                            <span wire:loading wire:target="loadMoreSemesters">
                                                Loading...
                                            </span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingBrands">
                            <button class="accordion-button bg-transparent shadow-none" type="button"
                                data-bs-toggle="collapse" data-bs-target="#flush-collapseBrands" aria-expanded="true"
                                aria-controls="flush-collapseBrands">
                                <span class="text-muted text-uppercase fs-12 fw-medium">Sections</span> <span
                                    class="badge bg-success rounded-pill align-middle ms-1 filter-badge"></span>
                            </button>
                        </h2>

                        <div id="flush-collapseBrands" class="accordion-collapse collapse show"
                            aria-labelledby="flush-headingBrands">
                            <div class="accordion-body text-body pt-0">
                                <div class="search-box search-box-sm d-flex flex-column gap-2">
                                    <div class="position-relative">
                                        <input type="text" class="form-control bg-light border-0"
                                            placeholder="Search Sections..." style="padding-right: 35px;"
                                            wire:model.live.debounce.300ms="sectionSearch">
                                        <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y"
                                            style="right: 10px; pointer-events: none; z-index: 1;"></i>
                                    </div>
                                    <div>
                                        <x-button color="primary" size="sm" icon="ri-add-line" icon-position="left"
                                            wire:click="addSection" wireTarget="addSection">Add Section</x-button>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2 mt-3 filter-check">
                                    @forelse($sections as $section)
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="form-check flex-grow-1">
                                                <input class="form-check-input" type="checkbox" value="{{ $section->id }}"
                                                    id="section_{{ $section->id }}" wire:model.change="selectedSections">
                                                <label class="form-check-label d-flex align-items-center gap-2 small"
                                                    for="section_{{ $section->id }}">
                                                    <span class="small">{{ $section->name }}
                                                        ({{ $section->year_level->label() }})</span>
                                                    @if($section->active)
                                                        <span class="badge bg-success-subtle text-success small">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger small">Inactive</span>
                                                    @endif
                                                </label>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <x-button color="info" icon="ri-edit-line" icon-position="left" size="sm"
                                                    :iconOnly="true" tooltip="Edit Section" tooltip-placement="top"
                                                    wire:click="editSection({{ $section->id }})"
                                                    wireTarget="editSection({{ $section->id }})">
                                                </x-button>
                                                <x-button color="danger" icon="ri-delete-bin-line" icon-position="left"
                                                    size="sm" :iconOnly="true" tooltip="Delete Section"
                                                    tooltip-placement="top" wire:click="deleteSection({{ $section->id }})"
                                                    wireTarget="deleteSection({{ $section->id }})">
                                                </x-button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted text-center py-2">
                                            <small>No sections found</small>
                                        </div>
                                    @endforelse
                                    @if($totalSections > $sectionLimit)
                                        <div class="text-center mt-2">
                                            <button type="button"
                                                class="btn btn-link text-decoration-none text-uppercase fw-medium p-0"
                                                wire:click="loadMoreSections" wire:target="loadMoreSections">
                                                <span wire:loading.remove wire:target="loadMoreSections">
                                                    Load More ({{ $totalSections - $sectionLimit }} remaining)
                                                </span>
                                                <span wire:loading wire:target="loadMoreSections">
                                                    Loading...
                                                </span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end accordion-item -->

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingDiscount">
                            <button class="accordion-button bg-transparent shadow-none" type="button"
                                data-bs-toggle="collapse" data-bs-target="#flush-collapseDiscount" aria-expanded="true"
                                aria-controls="flush-collapseDiscount">
                                <span class="text-muted text-uppercase fs-12 fw-medium">Programs</span> <span
                                    class="badge bg-success rounded-pill align-middle ms-1 filter-badge"></span>
                            </button>
                        </h2>
                        <div id="flush-collapseDiscount" class="accordion-collapse collapse show"
                            aria-labelledby="flush-headingDiscount">
                            <div class="accordion-body text-body pt-0">
                                <div class="search-box search-box-sm d-flex flex-column gap-2">
                                    <div class="position-relative">
                                        <input type="text" class="form-control bg-light border-0"
                                            placeholder="Search Programs..." style="padding-right: 35px;"
                                            wire:model.live.debounce.300ms="programSearch">
                                        <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y"
                                            style="right: 10px; pointer-events: none; z-index: 1;"></i>
                                    </div>
                                    <div>
                                        <x-button color="primary" size="sm" icon="ri-add-line" icon-position="left"
                                            wire:click="addProgram" wireTarget="addProgram">Add Program</x-button>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2 mt-3 filter-check">
                                    @forelse($programs as $program)
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="form-check flex-grow-1">
                                                <input class="form-check-input" type="checkbox" value="{{ $program->id }}"
                                                    id="program_{{ $program->id }}" wire:model.change="selectedPrograms">
                                                <label class="form-check-label d-flex align-items-center gap-2 small"
                                                    for="program_{{ $program->id }}">
                                                    <span class="small">{{ $program->code }}</span>
                                                    @if($program->active)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </label>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <x-button color="info" icon="ri-edit-line" icon-position="left" size="sm"
                                                    :iconOnly="true" tooltip="Edit Program" tooltip-placement="top"
                                                    wire:click="editProgram({{ $program->id }})"
                                                    wireTarget="editProgram({{ $program->id }})">
                                                </x-button>
                                                <x-button color="danger" icon="ri-delete-bin-line" icon-position="left"
                                                    size="sm" :iconOnly="true" tooltip="Delete Program"
                                                    tooltip-placement="top" wire:click="deleteProgram({{ $program->id }})"
                                                    wireTarget="deleteProgram({{ $program->id }})">
                                                </x-button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted text-center py-2">
                                            <small>No programs found</small>
                                        </div>
                                    @endforelse
                                    @if($totalPrograms > $programLimit)
                                        <div class="text-center mt-2">
                                            <button type="button"
                                                class="btn btn-link text-decoration-none text-uppercase fw-medium p-0"
                                                wire:click="loadMorePrograms" wire:target="loadMorePrograms">
                                                <span wire:loading.remove wire:target="loadMorePrograms">
                                                    Load More ({{ $totalPrograms - $programLimit }} remaining)
                                                </span>
                                                <span wire:loading wire:target="loadMorePrograms">
                                                    Loading...
                                                </span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end accordion-item -->

                </div>
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-9 col-lg-8">
            <div>
                <div class="card">
                    <div class="card-header border-0">
                        <div class="row g-4">
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <input type="text" class="form-control" id="searchStudentList"
                                            placeholder="Search Students..."
                                            wire:model.live.debounce.300ms="studentSearch">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link fw-semibold {{ $studentStatus === 'all' ? 'active' : '' }}"
                                            href="{{ request()->fullUrlWithQuery(['studentStatus' => null]) }}"
                                            wire:click.prevent="$set('studentStatus', 'all')" role="tab"
                                            style="cursor: pointer;">
                                            All
                                            @if($this->getStatusCount('all') > 0)
                                                <span
                                                    class="badge bg-danger-subtle text-danger align-middle rounded-pill ms-1">{{ $this->getStatusCount('all') }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link fw-semibold {{ $studentStatus === 'pending' ? 'active' : '' }}"
                                            href="{{ request()->fullUrlWithQuery(['studentStatus' => 'pending']) }}"
                                            wire:click.prevent="$set('studentStatus', 'pending')" role="tab"
                                            style="cursor: pointer;">
                                            Pending
                                            @if($this->getStatusCount('pending') > 0)
                                                <span
                                                    class="badge bg-warning-subtle text-warning align-middle rounded-pill ms-1">{{ $this->getStatusCount('pending') }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link fw-semibold {{ $studentStatus === 'enrolled' ? 'active' : '' }}"
                                            href="{{ request()->fullUrlWithQuery(['studentStatus' => 'enrolled']) }}"
                                            wire:click.prevent="$set('studentStatus', 'enrolled')" role="tab"
                                            style="cursor: pointer;">
                                            Enrolled
                                            @if($this->getStatusCount('enrolled') > 0)
                                                <span
                                                    class="badge bg-success-subtle text-success align-middle rounded-pill ms-1">{{ $this->getStatusCount('enrolled') }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link fw-semibold {{ $studentStatus === 'inactive' ? 'active' : '' }}"
                                            href="{{ request()->fullUrlWithQuery(['studentStatus' => 'inactive']) }}"
                                            wire:click.prevent="$set('studentStatus', 'inactive')" role="tab"
                                            style="cursor: pointer;">
                                            Inactive
                                            @if($this->getStatusCount('inactive') > 0)
                                                <span
                                                    class="badge bg-danger-subtle text-danger align-middle rounded-pill ms-1">{{ $this->getStatusCount('inactive') }}</span>
                                            @endif
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link fw-semibold {{ $studentStatus === 'graduated' ? 'active' : '' }}"
                                            href="{{ request()->fullUrlWithQuery(['studentStatus' => 'graduated']) }}"
                                            wire:click.prevent="$set('studentStatus', 'graduated')" role="tab"
                                            style="cursor: pointer;">
                                            Graduated
                                            @if($this->getStatusCount('graduated') > 0)
                                                <span
                                                    class="badge bg-info-subtle text-info align-middle rounded-pill ms-1">{{ $this->getStatusCount('graduated') }}</span>
                                            @endif
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- end card header -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID #</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Program</th>
                                        <th scope="col">Year Level</th>
                                        <th scope="col">Section</th>
                                        <th scope="col">Semester</th>
                                        <th scope="col">School Year</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Enrolled At</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($this->studentInfos as $studentInfo)
                                        <tr wire:key="student-{{ $studentInfo->id }}">
                                            <td>
                                                <strong>{{ $studentInfo->student_number }}</strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">{{ $studentInfo->user->name ?? 'N/A' }}</h6>
                                                        <small
                                                            class="text-muted">{{ $studentInfo->user->email ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($studentInfo->program)
                                                    <span class="badge bg-primary-subtle text-primary">
                                                        {{ $studentInfo->program->code }} - {{ $studentInfo->program->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($studentInfo->year_level)
                                                    <span class="badge bg-secondary-subtle text-secondary">
                                                        Grade {{ $studentInfo->year_level }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($studentInfo->section)
                                                    <span>{{ $studentInfo->section->name }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($studentInfo->semester)
                                                    <span>{{ $studentInfo->semester->name }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $studentInfo->school_year }}</td>
                                            <td>
                                                @if($studentInfo->status === 'pending')
                                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                                @elseif($studentInfo->status === 'enrolled')
                                                    <span class="badge bg-success-subtle text-success">Enrolled</span>
                                                @elseif($studentInfo->status === 'inactive')
                                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                @elseif($studentInfo->status === 'graduated')
                                                    <span class="badge bg-info-subtle text-info">Graduated</span>
                                                @else
                                                    <span
                                                        class="badge bg-secondary-subtle text-secondary">{{ ucfirst($studentInfo->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $studentInfo->enrolled_at ? $studentInfo->enrolled_at->format('M d, Y') : '-' }}
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <x-button color="info" icon="ri-eye-line" icon-position="left" size="sm"
                                                        :iconOnly="true" tooltip="View Details" tooltip-placement="top"
                                                        wire:click="viewStudent({{ $studentInfo->id }})"
                                                        wireTarget="viewStudent({{ $studentInfo->id }})">
                                                    </x-button>
                                                    <x-button color="primary" icon="ri-edit-line" icon-position="left"
                                                        size="sm" :iconOnly="true" tooltip="Edit Enrollment"
                                                        tooltip-placement="top"
                                                        wire:click="editStudent({{ $studentInfo->id }})"
                                                        wireTarget="editStudent({{ $studentInfo->id }})">
                                                    </x-button>
                                                    @if($studentInfo->status === 'pending')
                                                        <x-button color="danger" icon="ri-delete-bin-line" icon-position="left"
                                                            size="sm" :iconOnly="true" tooltip="Delete Enrollment"
                                                            tooltip-placement="top"
                                                            wire:click="deleteStudent({{ $studentInfo->id }})"
                                                            wireTarget="deleteStudent({{ $studentInfo->id }})">
                                                        </x-button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                    colors="primary:#405189,secondary:#0ab39c"
                                                    style="width:72px;height:72px">
                                                </lord-icon>
                                                <h5 class="mt-4">No Students Found</h5>
                                                <p class="text-muted">Try adjusting your search or filters.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($this->studentInfos->hasPages())
                            <div class="mt-4">
                                <x-pagination :paginator="$this->studentInfos" :show-summary="true" />
                            </div>
                        @endif
                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div>
        </div>
        <!-- end col -->
    </div>


</div>