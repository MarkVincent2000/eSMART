<div>
    <x-toast-notification />
    <div class="row">
        <div class="col-xxl-3">
            {{-- Active Semester Card --}}
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3 flex-grow-1 text-start">Active Semester</h6>
                    @if($activeSemester)
                        <div class="text-center mb-3">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#405189,secondary:#0ab39c" style="width:90px;height:90px">
                            </lord-icon>
                        </div>
                        <div class="text-center mb-3">
                            <h4 class="mb-1">{{ $activeSemester->name }}</h4>
                            <p class="text-muted mb-2">{{ $activeSemester->school_year }}</p>
                            @if($activeSemester->start_date && $activeSemester->end_date)
                                <p class="text-muted small mb-0">
                                    {{ $activeSemester->start_date->format('M d, Y') }} -
                                    {{ $activeSemester->end_date->format('M d, Y') }}
                                </p>
                            @endif
                        </div>
                        <div class="text-center">
                            <span class="badge bg-success-subtle text-success">Active</span>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
                            </lord-icon>
                            <h5 class="mt-3 mb-1">No Active Semester</h5>
                            <p class="text-muted mb-0">There is currently no active semester.</p>
                        </div>
                    @endif
                </div>
            </div>
            <!--end card-->

            {{-- Enrollment Status Card --}}
            @if(auth()->user() && auth()->user()->hasRole('user'))
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Enrollment Status</h6>
                        @if($hasEnrollment && $studentInfo)
                            <div class="text-center py-2">
                                <lord-icon src="https://cdn.lordicon.com/lupuorrc.json" trigger="loop"
                                    colors="primary:#0ab39c,secondary:#405189" style="width:72px;height:72px">
                                </lord-icon>
                                <h5 class="mt-3 mb-2">Enrolled</h5>
                                <p class="text-muted mb-2">You are enrolled for this semester.</p>
                                @if($studentInfo->semester)
                                    <p class="text-muted small mb-0">
                                        <strong>Semester:</strong> {{ $studentInfo->semester->name }}
                                    </p>
                                @endif
                                @if($studentInfo->program)
                                    <p class="text-muted small mb-0">
                                        <strong>Program:</strong> {{ $studentInfo->program->code }}
                                    </p>
                                @endif
                                <span class="badge bg-success-subtle text-success mt-2">
                                    {{ ucfirst($studentInfo->status) }}
                                </span>
                            </div>
                        @elseif($activeSemester)
                            <div class="text-center py-2">
                                <lord-icon src="https://cdn.lordicon.com/wxnxiano.json" trigger="loop"
                                    colors="primary:#f06548,secondary:#f7b84b" style="width:72px;height:72px">
                                </lord-icon>
                                <h5 class="mt-3 mb-2">Not Enrolled</h5>
                                <p class="text-muted mb-3">You are not enrolled for the active semester.</p>
                                <x-button color="primary" icon="ri-user-add-line" icon-position="left" wire:click="enrollNow"
                                    wireTarget="enrollNow">
                                    Enroll Now
                                </x-button>
                            </div>
                        @else
                            <div class="text-center py-2">
                                <p class="text-muted mb-0">No active semester available for enrollment.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <!--end card-->
            @endif

            <!--end card-->



        </div>
        <!---end col-->
        <div class="col-xxl-9">

            <!--end card-->
            <div>
                <div class="card">
                    <div class="card-header border-0">
                        <div class="row g-4">
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <input type="text" class="form-control" id="searchStudentList"
                                            placeholder="Search Enrollments..."
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
                                            href="{{ request()->url() }}"
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
                                                        wire:click="viewEnrollment({{ $studentInfo->id }})"
                                                        wireTarget="viewEnrollment({{ $studentInfo->id }})">
                                                    </x-button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                    colors="primary:#405189,secondary:#0ab39c"
                                                    style="width:72px;height:72px">
                                                </lord-icon>
                                                <h5 class="mt-4">No Enrollments Found</h5>
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
            <!--end card-->
        </div>
        <!--end col-->
    </div>

    <x-modal id="enroll-modal" wire:model="showEnrollModal" title="Enroll Now" size="lg" :centered="false"
        vertical-align="top" overflow="visible" :show-footer="true">
        <form wire:submit.prevent="saveEnrollment">
            @if($activeSemester)
                <div class="mb-4 p-3 bg-light rounded">
                    <h6 class="mb-2">Active Semester</h6>
                    <p class="mb-1"><strong>{{ $activeSemester->name }}</strong> - {{ $activeSemester->school_year }}</p>
                    @if($activeSemester->start_date && $activeSemester->end_date)
                        <small class="text-muted">
                            {{ $activeSemester->start_date->format('M d, Y') }} -
                            {{ $activeSemester->end_date->format('M d, Y') }}
                        </small>
                    @endif
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-12">
                    <label for="studentNumber" class="form-label">Student Number <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('studentNumber') is-invalid @enderror"
                        id="studentNumber" wire:model="studentNumber" placeholder="Enter your student number">
                    @error('studentNumber')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Enter your student number</small>
                </div>

                <div class="col-md-6">
                    <label for="yearLevel" class="form-label">Year Level <span class="text-danger">*</span></label>
                    <select class="form-select @error('yearLevel') is-invalid @enderror" id="yearLevel"
                        wire:model.change="yearLevel">
                        <option value="">Select Year Level</option>
                        @foreach(\App\Enums\YearLevel::options() as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @error('yearLevel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="programId" class="form-label">Program</label>
                    <div class="@error('programId') is-invalid @enderror"
                        wire:key="program-select-{{ $yearLevel ?? 'none' }}">
                        <x-select wire:model="programId" :options="$this->programOptions" placeholder="Select Program"
                            :searchable="true" :disabled="!$yearLevel" id="programId" />
                    </div>
                    @error('programId')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @if(!$yearLevel)
                        <small class="text-muted">Please select a year level first</small>
                    @endif
                </div>



                <div class="col-md-12">
                    <label for="sectionId" class="form-label">Section</label>
                    <div class="@error('sectionId') is-invalid @enderror"
                        wire:key="section-select-{{ $yearLevel ?? 'none' }}">
                        <x-select wire:model="sectionId" :options="$this->sectionOptions"
                            placeholder="Select Section (Optional)" :searchable="true" :disabled="!$yearLevel"
                            id="sectionId" />
                    </div>
                    @error('sectionId')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @if(!$yearLevel)
                        <small class="text-muted">Please select a year level first</small>
                    @endif
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeEnrollModal">Cancel</button>
            <x-button color="primary" wire:click="saveEnrollment" wireTarget="saveEnrollment">
                <span wire:loading.remove wire:target="saveEnrollment">
                    Submit Enrollment
                </span>
                <span wire:loading wire:target="saveEnrollment">
                    Submitting...
                </span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal id="view-enrollment-modal" wire:model="showViewEnrollmentModal" title="View Enrollment Details" size="lg"
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
                @if($this->selectedStudentInfo->user)
                    <div class="col-md-12">
                        <hr>
                        <h6 class="mb-3">Student Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Name</label>
                                <div class="fw-semibold">{{ $this->selectedStudentInfo->user->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Email</label>
                                <div>{{ $this->selectedStudentInfo->user->email }}</div>
                            </div>
                        </div>
                    </div>
                @endif
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
            <button type="button" class="btn btn-light" wire:click="closeViewEnrollmentModal">Close</button>
        </x-slot:footer>
    </x-modal>
</div>