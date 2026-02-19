<div>
    <x-toast-notification />

    {{-- Create / Edit Teacher Modal --}}
    <x-modal id="create-teacher-modal" wire:model="showCreateModal"
        :title="$teacherId ? 'Edit Teacher' : 'Create Teacher'" size="xl" vertical-align="center" :show-footer="true"
        overflow="visible">
        <form wire:submit.prevent="saveTeacher">
            <div class="row g-3" style="overflow: visible;">
                <div class="col-12" style="overflow: visible; position: relative; z-index: 1;" wire:key="teacher-user-select-{{ $showCreateModal ? md5(json_encode($userOptions)) : 'closed' }}">
                    <x-select label="Teacher User" wire:model="userId" :options="$userOptions"
                        placeholder="Select user..." :searchable="true" />
                    @error('userId')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Select an existing user account that will act as the teacher.</small>
                        </div>

                <div class="col-12">
                    <label for="employeeNo" class="form-label">Employee No.</label>
                    <input type="text" id="employeeNo"
                        class="form-control @error('employeeNo') is-invalid @enderror" wire:model="employeeNo"
                        placeholder="Enter employee number (optional)">
                    @error('employeeNo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                            </div>

                <div class="col-12">
                    <label for="department" class="form-label">Department</label>
                    <input type="text" id="department"
                        class="form-control @error('department') is-invalid @enderror" wire:model="department"
                        placeholder="e.g., Mathematics, Science, English">
                    @error('department')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                        </div>

                <div class="col-12">
                    <label for="position" class="form-label">Position</label>
                    <input type="text" id="position"
                        class="form-control @error('position') is-invalid @enderror" wire:model="position"
                        placeholder="e.g., Teacher I, Master Teacher, Coordinator">
                    @error('position')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    </div>

                <div class="col-12">
                    <label for="hireDate" class="form-label">Hire Date</label>
                    <input type="date" id="hireDate"
                        class="form-control @error('hireDate') is-invalid @enderror" wire:model="hireDate"
                        max="{{ date('Y-m-d') }}">
                    @error('hireDate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeCreateModal">Cancel</button>
            <x-button color="primary" wire:click="saveTeacher" wireTarget="saveTeacher">
                <span wire:loading.remove wire:target="saveTeacher">
                    {{ $teacherId ? 'Update Teacher' : 'Create Teacher' }}
                </span>
                <span wire:loading wire:target="saveTeacher">
                    {{ $teacherId ? 'Updating...' : 'Creating...' }}
                </span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Delete Teacher Modal --}}
    <x-modal id="delete-teacher-modal" wire:model="showDeleteTeacherModal" title="Delete Teacher" size="md"
        :centered="true" :show-footer="true">

        <div class="text-center">
            <div class="mb-4">
                <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
        </div>
            <h5 class="mb-3">Are you sure?</h5>
            <p class="text-muted">
                You are about to delete the teacher
                <strong>{{ $deleteTeacherName ?? 'this teacher' }}</strong>.
                This action cannot be undone.
            </p>
            <div class="alert alert-warning mt-3">
                <i class="ri-alert-line me-2"></i>
                <strong>Warning:</strong> This will remove the teacher profile from the system.
                            </div>
                        </div>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeDeleteTeacherModal">Cancel</button>
            <x-button color="danger" wire:click="confirmDeleteTeacher" wireTarget="confirmDeleteTeacher">
                <span wire:loading.remove wire:target="confirmDeleteTeacher">Delete Teacher</span>
                <span wire:loading wire:target="confirmDeleteTeacher">Deleting...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="teacherListCard">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">Manage Teachers</h5>
                                            </div>
                        <div class="col-sm-auto">
                            <div class="d-flex gap-1 flex-wrap">
                                <x-button color="success" icon="ri-add-line" loading="true"
                                    loading-text="Opening form..." wireTarget="openCreateModal"
                                    wire:click="openCreateModal">
                                    Add Teacher
                                </x-button>
                                        </div>
                                    </div>
                                        </div>
                                    </div>

                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <form class="row g-3">
                        <div class="col-xxl-5 col-sm-6">
                            <div class="search-box">
                                <input type="text" class="form-control search"
                                    placeholder="Search by name, email, employee no, or department..."
                                    wire:model.live.debounce.300ms="search">
                                <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                    </form>
                        </div>

                <div class="card-body">
                    <div class="table-responsive table-card mb-2">
                        <table class="table align-middle table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Name</th>
                                    <th>Employee No.</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Actions</th>
                                    </tr>
                                </thead>
                            <tbody>
                                @forelse ($teachers as $index => $teacher)
                                    <tr>
                                        <td>
                                            {{ ($teachers->currentPage() - 1) * $teachers->perPage() + $index + 1 }}
                                        </td>
                                        <td>
                                            <div class="fw-semibold">
                                                {{ $teacher->user?->name ?? 'N/A' }}
                                                    </div>
                                        </td>

                                        <td>
                                            {{ $teacher->employee_no ?? '—' }}
                                        </td>
                                        <td>
                                            {{ $teacher->department ?? '—' }}
                                        </td>
                                        <td>
                                            {{ $teacher->position ?? '—' }}
                                        </td>
                                        <td>
                                            <ul class="list-inline hstack gap-2 mb-0">
                                               
                                                    <x-button color="primary" variant="soft" size="sm" :iconOnly="true"
                                                        icon="ri-pencil-line" tooltip="Edit Teacher"
                                                        wire:click="editTeacher({{ $teacher->id }})"
                                                        wireTarget="editTeacher({{ $teacher->id }})">
                                                    </x-button>
                                               
                                                    <x-button color="danger" variant="soft" size="sm" :iconOnly="true"
                                                        icon="ri-delete-bin-line" tooltip="Delete Teacher"
                                                        wire:click="deleteTeacher({{ $teacher->id }})"
                                                        wireTarget="deleteTeacher({{ $teacher->id }})">
                                                    </x-button>
                                                
                                            </ul>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="text-center py-4">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                    colors="primary:#405189,secondary:#0ab39c"
                                                    style="width:72px;height:72px">
                                                </lord-icon>
                                                <h5 class="mt-3">No teachers found</h5>
                                                <p class="text-muted mb-0">Try adjusting your search or add a new teacher.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                                            </div>

                   
                        <div class="mt-2">
                            <x-pagination :paginator="$teachers" :show-summary="true" />
                </div>
            </div>
        </div>
                    </div>
    </div>
</div>