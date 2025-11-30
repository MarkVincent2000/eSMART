<div>
    <x-toast-notification />
    <div class="chat-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
        <div class="file-manager-sidebar minimal-border">
            <div class="p-1 d-flex flex-column h-100">
                <div class="mb-4 p-2">
                    <h6 class="text-muted text-uppercase mb-3">Active Semester</h6>
                    <div class="rounded p-1"
                        style="min-height: 150px; display: flex; align-items: center; justify-content: center;">

                        @if($activeSemester)
                            <div class="card border card-border-success w-100">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">{{ $activeSemester->name }}</h6>
                                </div>
                                <div class="card-body">
                                    @if($activeSemester->start_date && $activeSemester->end_date)
                                        <p class="card-text">{{ $activeSemester->start_date->format('M d, Y') }} -
                                            {{ $activeSemester->end_date->format('M d, Y') }}
                                        </p>
                                    @else
                                        <p class="card-text text-muted">No dates set</p>
                                    @endif
                                    <div class="text-end">
                                        <span class="badge bg-success-subtle text-success mt-2">Active</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">
                                <i class="ri-calendar-line fs-24 mb-2 d-block"></i>
                                <p class="mb-0 small">No active semester</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="mt-auto text-center">
                    <img src="{{ URL::asset('build/images/task.png') }}" alt="Task" class="img-fluid" />
                </div>
            </div>
        </div><!--end side content-->
        <div class="file-manager-content minimal-border w-100 p-4 pb-0">
            <div class="row mb-4">
                <div class="col-auto order-1 d-block d-lg-none">
                    <button type="button" class="btn btn-soft-success btn-icon btn-sm fs-16 file-menu-btn">
                        <i class="ri-menu-2-fill align-bottom"></i>
                    </button>
                </div>
                <div class="col-sm order-3 order-sm-2 mt-3 mt-sm-0">
                    <h5 class="fw-semibold mb-0">Manage Semesters</h5>
                </div>
            </div>
            <div class="p-3 bg-light rounded mb-4">
                <div class="row g-2">
                    <div class="col-lg-auto">
                        <select class="form-control" wire:model.live="sortBy">
                            <option value="name">Sort by Name</option>
                            <option value="school_year">Sort by School Year</option>
                            <option value="created_at">Sort by Date Created</option>
                        </select>
                    </div>
                    <div class="col-lg-auto">
                        <select class="form-control" wire:model.live="status">
                            <option value="all">All Semesters</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>
                    </div>
                    <div class="col-lg">
                        <div class="search-box">
                            <input type="text" class="form-control search"
                                placeholder="Search semester name or school year"
                                wire:model.live.debounce.300ms="search">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                    <div class="col-lg-auto">
                        <button class="btn btn-primary" type="button" wire:click="addSemester">
                            <i class="ri-add-fill align-bottom"></i> Add Semester
                        </button>
                    </div>
                </div>
            </div>

            <div class="todo-content position-relative px-4 mx-n4">
                <div class="todo-task">
                    <div class="table-responsive">
                        <table class="table align-middle position-relative table-nowrap">
                            <thead class="table-active">
                                <tr>
                                    <th scope="col">Semester Name</th>
                                    <th scope="col">School Year</th>
                                    <th scope="col">Start Date</th>
                                    <th scope="col">End Date</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Created At</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($this->semesters as $semester)
                                    <tr wire:key="semester-{{ $semester->id }}"
                                        class="semester-row {{ $semester->is_active ? 'table-success' : '' }}">
                                        <td>
                                            <strong>{{ $semester->name }}</strong>
                                        </td>
                                        <td>{{ $semester->school_year }}</td>
                                        <td>
                                            {{ $semester->start_date ? $semester->start_date->format('M d, Y') : '-' }}
                                        </td>
                                        <td>
                                            {{ $semester->end_date ? $semester->end_date->format('M d, Y') : '-' }}
                                        </td>
                                        <td>
                                            @if($semester->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $semester->created_at?->format('d M, Y') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @if(!$semester->is_active)
                                                    <x-button color="success" icon="ri-check-line" icon-position="left"
                                                        size="sm" :iconOnly="true" tooltip="Set as Active"
                                                        tooltip-placement="top"
                                                        wire:click="setActiveSemester({{ $semester->id }})"
                                                        wireTarget="setActiveSemester({{ $semester->id }})">
                                                    </x-button>
                                                @endif
                                                <x-button color="info" icon="ri-edit-line" icon-position="left" size="sm"
                                                    :iconOnly="true" tooltip="Edit Semester" tooltip-placement="top"
                                                    wire:click="editSemester({{ $semester->id }})"
                                                    wireTarget="editSemester({{ $semester->id }})">
                                                </x-button>
                                                <x-button color="danger" icon="ri-delete-bin-line" icon-position="left"
                                                    size="sm" :iconOnly="true" tooltip="Delete Semester"
                                                    tooltip-placement="top" wire:click="deleteSemester({{ $semester->id }})"
                                                    wireTarget="deleteSemester({{ $semester->id }})">
                                                </x-button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
                                            </lord-icon>
                                            <h5 class="mt-4">No Semesters Found</h5>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Pagination --}}

            <div class="mt-1">
                <x-pagination :paginator="$this->semesters" :show-summary="true" />
            </div>


        </div>
    </div>

    <x-modal id="create-semester-modal" wire:model="showSemesterModal" :title="$semesterId ? 'Edit Semester' : 'Create Semester'" size="lg" :centered="true" :show-footer="true" overflow="visible">
        <form wire:submit.prevent="saveSemester">
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="semesterName" class="form-label">Semester Name <span
                            class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('semesterName') is-invalid @enderror"
                        id="semesterName" wire:model="semesterName"
                        placeholder="Enter semester name (e.g., 1st Semester, 2nd Semester)">
                    @error('semesterName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Enter a unique name for the semester</small>
                </div>

                <div class="col-md-12">
                    <label for="schoolYear" class="form-label">School Year <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('schoolYear') is-invalid @enderror" id="schoolYear"
                        wire:model.blur="schoolYear" placeholder="Enter school year (e.g., 2025-2026)">
                    @error('schoolYear')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Enter the school year in format YYYY-YYYY</small>
                </div>

                <div class="col-md-6" wire:key="start-date-div-{{ $semesterId ?? 'new' }}">
                    <label for="startDate" class="form-label">Start Date</label>
                    <input type="date" class="form-control @error('startDate') is-invalid @enderror" id="startDate"
                        wire:model.blur="startDate">
                    @error('startDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Select the start date of the semester</small>
                </div>

                <div class="col-md-6" wire:key="end-date-div-{{ $semesterId ?? 'new' }}">
                    <label for="endDate" class="form-label">End Date</label>
                    <input type="date" class="form-control @error('endDate') is-invalid @enderror" id="endDate"
                        wire:model="endDate" @if($startDate) min="{{ $startDate }}" @endif>
                    @error('endDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Select the end date of the semester</small>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeSemesterModal">Cancel</button>
            <x-button color="primary" wire:click="saveSemester" wireTarget="saveSemester">
                <span wire:loading.remove wire:target="saveSemester">
                    {{ $semesterId ? 'Update Semester' : 'Create Semester' }}
                </span>
                <span wire:loading wire:target="saveSemester">
                    {{ $semesterId ? 'Updating...' : 'Creating...' }}
                </span>
            </x-button>
        </x-slot:footer>
    </x-modal>


    <x-modal id="set-active-semester-modal" wire:model="showSetActiveModal" title="Set as Active Semester" size="md"
        :centered="true" :show-footer="true">

        <div class="text-center">
            <div class="mb-4">
                <i class="ri-checkbox-circle-line text-success" style="font-size: 4rem;"></i>
            </div>
            <h5 class="mb-3">Set as Active Semester?</h5>
            <p class="text-muted">
                You are about to set <strong>{{ $setActiveSemesterName ?? 'this semester' }}</strong>
                @if($setActiveSchoolYear)
                    ({{ $setActiveSchoolYear }})
                @endif
                as the active semester.
            </p>
            <div class="alert alert-info mt-3">
                <i class="ri-information-line me-2"></i>
                <strong>Note:</strong> This will deactivate the current active semester and activate this one instead.
            </div>
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeSetActiveModal">Cancel</button>
            <x-button color="success" wire:click="confirmSetActiveSemester" wireTarget="confirmSetActiveSemester">
                <span wire:loading.remove wire:target="confirmSetActiveSemester">Set as Active</span>
                <span wire:loading wire:target="confirmSetActiveSemester">Setting...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    <x-modal id="delete-semester-modal" wire:model="showDeleteSemesterModal" title="Delete Semester" size="md"
        :centered="true" :show-footer="true">

        <div class="text-center">
            <div class="mb-4">
                <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
            </div>
            <h5 class="mb-3">Are you sure?</h5>
            <p class="text-muted">
                You are about to delete the semester <strong>{{ $deleteSemesterName ?? 'this semester' }}</strong>.
                This action cannot be undone.
            </p>
            <div class="alert alert-warning mt-3">
                <i class="ri-alert-line me-2"></i>
                <strong>Warning:</strong> This semester will be permanently deleted from the system.
            </div>
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeDeleteSemesterModal">Cancel</button>
            <x-button color="danger" wire:click="confirmDeleteSemester" wireTarget="confirmDeleteSemester">
                <span wire:loading.remove wire:target="confirmDeleteSemester">Delete Semester</span>
                <span wire:loading wire:target="confirmDeleteSemester">Deleting...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>






</div>