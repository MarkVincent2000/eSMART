<div>
    <x-toast-notification />

    {{-- Delete Multiple Subjects Modal --}}
    <x-modal id="delete-multiple-subjects-modal" wire:model="showDeleteMultipleModal" title="Delete Multiple Subjects" size="md"
        :centered="true" :show-footer="true">

        <div class="text-center">
            <div class="mb-4">
                <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
            </div>
            <h5 class="mb-3">Are you sure?</h5>
            <p class="text-muted">
                You are about to delete <strong>{{ count($selected) }}</strong>
                {{ count($selected) === 1 ? 'subject' : 'subjects' }}.
                This action cannot be undone.
            </p>
            <div class="alert alert-warning mt-3">
                <i class="ri-alert-line me-2"></i>
                <strong>Warning:</strong> All selected subjects will be permanently deleted from the system.
            </div>
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeDeleteMultipleModal">Cancel</button>
            <x-button color="danger" wire:click="confirmDeleteMultiple" wireTarget="confirmDeleteMultiple">
                <span wire:loading.remove wire:target="confirmDeleteMultiple">Delete {{ count($selected) }}
                    {{ count($selected) === 1 ? 'Subject' : 'Subjects' }}</span>
                <span wire:loading wire:target="confirmDeleteMultiple">Deleting...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- View Subject Modal --}}
    <x-modal id="view-subject-modal" wire:model="showViewSubjectModal" title="View Subject Details" size="lg"
        :centered="true" overflow="visible" :show-footer="true">
        @if($this->selectedSubject)
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted">Subject Code</label>
                    <div class="fw-semibold">{{ $this->selectedSubject->code ?? 'N/A' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Subject Name</label>
                    <div class="fw-semibold">{{ $this->selectedSubject->name }}</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label text-muted">Description</label>
                    <div>{{ $this->selectedSubject->description ?? 'No description provided' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Units</label>
                    <div class="fw-semibold">{{ $this->selectedSubject->units ?? '0.00' }}</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Year Level</label>
                    <div>
                        @if($this->selectedSubject->year_level)
                            <span class="badge bg-primary-subtle text-primary">
                                Grade {{ $this->selectedSubject->year_level }}
                            </span>
                        @else
                            <span class="text-muted">Not assigned</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Status</label>
                    <div>
                        @if($this->selectedSubject->is_active)
                            <span class="badge bg-success-subtle text-success">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Created At</label>
                    <div>
                        {{ $this->selectedSubject->created_at ? $this->selectedSubject->created_at->format('M d, Y h:i A') : '-' }}
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted">Last Updated</label>
                    <div>
                        {{ $this->selectedSubject->updated_at ? $this->selectedSubject->updated_at->format('M d, Y h:i A') : '-' }}
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
            <button type="button" class="btn btn-light" wire:click="closeViewSubjectModal">Close</button>
        </x-slot:footer>
    </x-modal>

    {{-- Delete Subject Modal --}}
    <x-modal id="delete-subject-modal" wire:model="showDeleteSubjectModal" title="Delete Subject" size="md"
        :centered="true" :show-footer="true">

        <div class="text-center">
            <div class="mb-4">
                <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
            </div>
            <h5 class="mb-3">Are you sure?</h5>
            <p class="text-muted">
                You are about to delete the subject <strong>{{ $deleteSubjectName ?? 'this subject' }}</strong>.
                This action cannot be undone.
            </p>
            <div class="alert alert-warning mt-3">
                <i class="ri-alert-line me-2"></i>
                <strong>Warning:</strong> This subject will be permanently deleted from the system.
            </div>
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeDeleteSubjectModal">Cancel</button>
            <x-button color="danger" wire:click="confirmDeleteSubject" wireTarget="confirmDeleteSubject">
                <span wire:loading.remove wire:target="confirmDeleteSubject">Delete Subject</span>
                <span wire:loading wire:target="confirmDeleteSubject">Deleting...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Create Subject Modal --}}
    <x-modal id="create-subject-modal" wire:model="showCreateModal" :title="$subjectId ? 'Edit Subject' : 'Create Subject'"
        size="lg" vertical-align="top" :show-footer="true" overflow="visible">
        <form wire:submit.prevent="saveSubject">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="code" class="form-label">Subject Code</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                        wire:model="code" placeholder="Enter subject code (e.g., MATH101, ENG201)">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Enter a unique code for the subject (optional)</small>
                </div>

                <div class="col-md-6">
                    <label for="name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        wire:model="name" placeholder="Enter subject name (e.g., Mathematics, English)">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Enter the full name of the subject</small>
                </div>

                <div class="col-md-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                        wire:model="description" rows="3" placeholder="Enter subject description"></textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Enter a brief description of the subject (optional)</small>
                </div>

                <div class="col-md-6">
                    <label for="units" class="form-label">Units</label>
                    <input type="number" step="0.01" min="0" max="999.99" class="form-control @error('units') is-invalid @enderror" id="units"
                        wire:model="units" placeholder="Enter number of units (e.g., 3.00)">
                    @error('units')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Enter the number of units for this subject (optional)</small>
                </div>

                <div class="col-md-6">
                    <label for="yearLevel" class="form-label">Year Level</label>
                    <select class="form-control @error('yearLevel') is-invalid @enderror" id="yearLevel"
                        wire:model="yearLevel">
                        <option value="">Select Year Level</option>
                        @foreach(\App\Enums\YearLevel::options() as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @error('yearLevel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Select the year level for this subject (optional)</small>
                </div>

                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="isActive" wire:model="isActive">
                        <label class="form-check-label" for="isActive">
                            Active
                        </label>
                    </div>
                    <small class="text-muted">Toggle to activate or deactivate this subject</small>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeCreateModal">Cancel</button>
            <x-button color="primary" wire:click="saveSubject" wireTarget="saveSubject">
                <span wire:loading.remove wire:target="saveSubject">
                    {{ $subjectId ? 'Update Subject' : 'Create Subject' }}
                </span>
                <span wire:loading wire:target="saveSubject">
                    {{ $subjectId ? 'Updating...' : 'Creating...' }}
                </span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="orderList">
                <div class="card-header border-0">
                    <div class="row align-items-center gy-3">
                        <div class="col-sm">
                            <h5 class="card-title mb-0">Manage Subjects</h5>
                        </div>
                        <div class="col-sm-auto">
                            <div class="d-flex gap-1 flex-wrap">
                                <x-button color="success" icon="ri-add-line" loading="true" loading-text="Adding Subject..." wireTarget="openCreateModal" wire:click="openCreateModal">Add Subject</x-button>
                                
                                @if (!empty($selected))
                                    <x-button color="danger" icon="ri-delete-bin-2-line" wire:click="deleteMultiple" wireTarget="deleteMultiple">
                                        Delete Selected ({{ count($selected) }})
                                    </x-button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <form>
                        <div class="row g-3">
                            <div class="col-xxl-5 col-sm-6">
                                <div class="search-box">
                                    <input type="text" class="form-control search" placeholder="Search for subject code, name, or description..." wire:model.live.debounce.300ms="search">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <!--end col-->
                            
                           
                            <!--end col-->
                            <div class="col-xxl-2 col-sm-4">
                                <div>
                                    <select class="form-control" wire:model.live="yearLevelFilter" id="idYearLevel">
                                        <option value="all">All Year Levels</option>
                                        @foreach(\App\Enums\YearLevel::options() as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!--end col-->
                           
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </form>
                </div>
                <div class="card-body pt-0">
                    <div>
                        <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link py-3 {{ $statusFilter === 'all' ? 'active' : '' }}" 
                                   wire:click="setStatusFilter('all')" 
                                   role="button" 
                                   style="cursor: pointer;">
                                    <i class="ri-book-open-line me-1 align-bottom"></i> All Subjects
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link py-3 {{ $statusFilter === 'active' ? 'active' : '' }}" 
                                   wire:click="setStatusFilter('active')" 
                                   role="button" 
                                   style="cursor: pointer;">
                                    <i class="ri-checkbox-circle-line me-1 align-bottom"></i> Active
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link py-3 {{ $statusFilter === 'inactive' ? 'active' : '' }}" 
                                   wire:click="setStatusFilter('inactive')" 
                                   role="button" 
                                   style="cursor: pointer;">
                                    <i class="ri-close-circle-line me-1 align-bottom"></i> Inactive
                                </a>
                            </li>
                        </ul>

                        <div class="table-responsive table-card mb-1">
                            <table class="table table-nowrap align-middle" id="subjectTable">
                                <thead class="text-muted table-light">
                                    <tr class="text-uppercase">
                                        <th scope="col" style="width: 25px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model.live="selectPage">
                                            </div>
                                        </th>
                                        <th >Code</th>
                                        <th >Subject Name</th>
                                        <th >Description</th>
                                        <th >Units</th>
                                        <th  >Year Level</th>
                                        <th >Status</th>
                                        <th >Action</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                    @forelse($subjects as $subject)
                                    <tr wire:key="subject-{{ $subject->id }}">
                                        <th scope="row">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model.live="selected" value="{{ $subject->id }}">
                                            </div>
                                        </th>
                                        <td class="code">
                                            <span class="fw-medium">{{ $subject->code ?? 'N/A' }}</span>
                                        </td>
                                        <td class="name">
                                            <span class="fw-medium">{{ $subject->name }}</span>
                                        </td>
                                        <td class="description">
                                            <span class="text-muted">{{ Str::limit($subject->description ?? 'N/A', 50) }}</span>
                                        </td>
                                        <td class="units">
                                            <span>{{ $subject->units ?? '0.00' }}</span>
                                        </td>
                                        <td class="year_level">
                                            @if($subject->year_level)
                                                <span class="badge bg-primary-subtle text-primary">Grade {{ $subject->year_level }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="status">
                                            @if($subject->is_active)
                                                <span class="badge bg-success-subtle text-success text-uppercase">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger text-uppercase">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <ul class="list-inline hstack gap-2 mb-0">
                                                <li class="list-inline-item">
                                                    <x-button color="info" icon="ri-eye-fill" icon-position="left" size="sm"
                                                        :iconOnly="true" title="View"
                                                        wire:click="viewSubject({{ $subject->id }})"
                                                        wireTarget="viewSubject({{ $subject->id }})">
                                                    </x-button>
                                                </li>
                                                <li class="list-inline-item">
                                                    <x-button color="primary" icon="ri-pencil-fill" icon-position="left" size="sm"
                                                        :iconOnly="true" title="Edit"
                                                        wire:click="editSubject({{ $subject->id }})"
                                                        wireTarget="editSubject({{ $subject->id }})">
                                                    </x-button>
                                                </li>
                                                <li class="list-inline-item">
                                                    <x-button color="danger" icon="ri-delete-bin-5-fill" icon-position="left" size="sm"
                                                        :iconOnly="true" title="Delete"
                                                        wire:click="deleteSubject({{ $subject->id }})"
                                                        wireTarget="deleteSubject({{ $subject->id }})">
                                                    </x-button>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:75px;height:75px"></lord-icon>
                                                <h5 class="mt-2">No Subjects Found</h5>
                                                <p class="text-muted">There are no subjects available. Click "Add Subject" to create one.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($selectPage && !$selectAll && $subjects->total() > $subjects->count())
                            <div class="alert alert-info py-2 mb-3">
                                You have selected <strong>{{ count($selected) }}</strong> subject(s) on this page.
                                <a href="#" wire:click.prevent="selectAllMatching" class="alert-link fw-bold">
                                    Select all <strong>{{ $subjects->total() }}</strong> subject(s)?
                                </a>
                            </div>
                        @elseif($selectAll)
                            <div class="alert alert-success py-2 mb-3">
                                You have selected all <strong>{{ $subjects->total() }}</strong> subject(s).
                            </div>
                        @endif
                        
                        {{-- Pagination --}}
                        @if($subjects->hasPages())
                            <div class="mt-4">
                                <x-pagination :paginator="$subjects" :show-summary="true" />
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
        <!--end col-->
    </div>
</div>
