<div>
    <x-toast-notification />

    <!-- Header with Create Button -->
    <div class="row g-4 mb-3">
        @if(!$isStudent)
            @can('manage-assignments')
            <div class="col-sm-auto">
                <div>
                    <button type="button" wire:click="openCreateModal" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Create Class
                    </button>
                </div>
            </div>
            @endcan
        @endif
        <div class="col-sm">
            <div class="d-flex justify-content-sm-end gap-2">
                <div class="search-box ms-2">
                    <input type="text" class="form-control" placeholder="Search classes..."
                        wire:model.live.debounce.300ms="search">
                    <i class="ri-search-line search-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Classrooms List -->
    <div class="row">
        @forelse($classrooms->items() as $classroom)
            <div class="col-xxl-3 col-sm-6 project-card">
                <div class="card card-height-100">
                    <div class="card-body">
                        <div class="d-flex flex-column h-100">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <p class="text-muted mb-4">
                                        {{ $classroom->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                @if(!$isStudent)
                                    <div class="flex-shrink-0">
                                        <div class="dropdown">
                                            <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ url('/assingments.index?id=' . $classroom->id) }}">
                                                        <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        wire:click.prevent="editClassroom({{ $classroom->id }})"
                                                        wire:target="editClassroom({{ $classroom->id }})">
                                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                        wire:click.prevent="deleteClassroom({{ $classroom->id }})"
                                                        wire:target="deleteClassroom({{ $classroom->id }})">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Remove
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex-shrink-0">
                                        <a href="{{ url('/assingments.index?id=' . $classroom->id) }}"
                                            class="btn btn-soft-primary btn-sm">
                                            <i class="ri-eye-fill align-middle me-1"></i> View
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="d-flex mb-2">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 fs-15">
                                        <a href="#" class="text-body">{{ $classroom->name }}</a>
                                    </h5>
                                    <p class="text-muted text-truncate-two-lines mb-3">
                                        {{ Str::limit($classroom->description ?? 'No description', 100) }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span
                                        class="badge bg-{{ $classroom->status === 'active' ? 'success' : ($classroom->status === 'archived' ? 'secondary' : 'warning') }}-subtle text-{{ $classroom->status === 'active' ? 'success' : ($classroom->status === 'archived' ? 'secondary' : 'warning') }}">
                                        {{ ucfirst($classroom->status) }}
                                    </span>
                                    @if($classroom->class_code)
                                        <span class="badge bg-info-subtle text-info">
                                            <i class="ri-code-line me-1"></i>{{ $classroom->class_code }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted small">
                                    <div><i
                                            class="ri-book-open-line me-1"></i>{{ $classroom->subject->name ?? 'No subject' }}
                                    </div>
                                    <div>
                                        <i class="ri-group-line me-1"></i>{{ $classroom->section->name ?? 'No section' }}
                                        @if($classroom->section && $classroom->section->year_level)
                                            <span class="badge bg-primary-subtle text-primary ms-1">
                                                {{ $classroom->section->year_level instanceof \App\Enums\YearLevel ? $classroom->section->year_level->label() : 'Grade ' . $classroom->section->year_level }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($classroom->room)
                                        <div><i class="ri-building-line me-1"></i>Room: {{ $classroom->room }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-dashed py-2">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="text-muted small">
                                    <i class="ri-user-line me-1 align-bottom"></i>
                                    @if($classroom->creator)
                                        @if(!empty($classroom->creator->first_name) || !empty($classroom->creator->last_name))
                                            {{ trim(($classroom->creator->first_name ?? '') . ' ' . ($classroom->creator->last_name ?? '')) }}
                                        @else
                                            {{ $classroom->creator->name ?? 'Unknown' }}
                                        @endif
                                    @else
                                        Unknown
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="text-muted small">
                                    <i class="ri-calendar-line me-1 align-bottom"></i>
                                    {{ $classroom->semester->name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ri-book-open-line display-4 text-muted mb-3"></i>
                        <h5 class="mb-2">No Classes Yet</h5>
                        <p class="text-muted mb-4">
                            @if($isStudent)
                                You are not enrolled in any classes yet.
                            @else
                                Create your first class to get started.
                            @endif
                        </p>
                        @if(!$isStudent)
                            @can('manage-assignments')
                                <button type="button" wire:click="openCreateModal" class="btn btn-success">
                                    <i class="ri-add-line align-bottom me-1"></i> Create Class
                                </button>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <x-pagination :paginator="$classrooms" :show-summary="true" />

    <!-- Create/Edit Class Modal -->
    <x-modal wire:model="showCreateModal" size="lg" :title="$editingClassroom ? 'Edit Class' : 'Create Class'"
        :showFooter="false">
        <form wire:submit.prevent="saveClassroom" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Class name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name"
                    placeholder="Enter class name">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <small class="text-muted">*Required</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Section</label>
                <select class="form-select @error('section_id') is-invalid @enderror" wire:model.live="section_id">
                    <option value="">Select Section</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">
                            {{ $section->name }}
                            @if($section->year_level)
                                -
                                {{ $section->year_level instanceof \App\Enums\YearLevel ? $section->year_level->label() : 'Grade ' . $section->year_level }}
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if($section_id)
                    @php
                        $selectedSection = collect($sections)->firstWhere('id', $section_id);
                    @endphp
                    @if($selectedSection && $selectedSection->year_level)
                        <small class="text-success d-block mt-1">
                            <i class="ri-graduation-cap-line me-1"></i>
                            Grade:
                            <strong>{{ $selectedSection->year_level instanceof \App\Enums\YearLevel ? $selectedSection->year_level->label() : 'Grade ' . $selectedSection->year_level }}</strong>
                        </small>
                    @endif
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <input type="text" class="form-control @error('subject_name') is-invalid @enderror"
                    wire:model="subject_name" placeholder="Search or enter subject name" list="subjects-list">
                <datalist id="subjects-list">
                    @foreach($availableSubjects as $subject)
                        <option value="{{ $subject->name }}" data-id="{{ $subject->id }}">
                            {{ $subject->display_name }}
                        </option>
                    @endforeach
                </datalist>
                <input type="hidden" wire:model="subject_id">
                <input type="hidden" wire:model="subject_type" value="{{ \App\Models\Subject::class }}">
                @error('subject_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Room</label>
                <input type="text" class="form-control @error('room') is-invalid @enderror" wire:model="room"
                    placeholder="Enter room number">
                @error('room') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" wire:model="description"
                    rows="3" placeholder="Optional description"></textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Semester <span class="text-danger">*</span></label>
                <select class="form-select @error('semester_id') is-invalid @enderror" wire:model.live="semester_id">
                    <option value="">Select Semester</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}">
                            {{ $semester->name }} - {{ $semester->school_year }}
                            @if($semester->is_active) (Active) @endif
                        </option>
                    @endforeach
                </select>
                @error('semester_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @error('form')
                <div class="alert alert-danger mt-3">{{ $message }}</div>
            @enderror

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-light" wire:click="closeCreateModal">Cancel</button>
                <button type="submit" class="btn btn-success" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        <i class="ri-save-line me-1"></i>{{ $editingClassroom ? 'Update' : 'Create' }}
                    </span>
                    <span wire:loading>
                        <span
                            class="spinner-border spinner-border-sm me-2"></span>{{ $editingClassroom ? 'Updating...' : 'Creating...' }}
                    </span>
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Delete Class Modal -->
    <x-modal id="delete-classroom-modal" wire:model="showDeleteModal" title="Delete Class" size="md" :centered="true"
        :show-footer="true">

        <div class="text-center">
            <div class="mb-4">
                <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
            </div>
            <h5 class="mb-3">Are you sure?</h5>
            <p class="text-muted">
                You are about to delete the class <strong>{{ $deleteClassroomName ?? 'this class' }}</strong>.
                This action cannot be undone.
            </p>
            <div class="alert alert-warning mt-3">
                <i class="ri-alert-line me-2"></i>
                <strong>Warning:</strong> This will permanently delete the class and all related data.
            </div>
        </div>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeDeleteModal">Cancel</button>
            <x-button color="danger" wire:click="confirmDelete" wire:target="confirmDelete">
                <span wire:loading.remove wire:target="confirmDelete">Delete Class</span>
                <span wire:loading wire:target="confirmDelete">Deleting...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>
</div>