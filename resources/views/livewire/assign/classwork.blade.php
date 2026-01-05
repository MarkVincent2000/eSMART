<div>
    <x-toast-notification />

    @if($classroom)
        <!-- Header with Back Button -->
        <div class="row g-4 mb-3">
            <div class="col-sm-auto">
                <div>
                    <a href="{{ url('/assingments.index') }}" class="btn btn-light">
                        <i class="ri-arrow-left-line align-bottom me-1"></i> Back to Classes
                    </a>
                </div>
            </div>
            <div class="col-sm">
                <div class="d-flex justify-content-sm-end gap-2">
                    <div class="search-box ms-2">
                        <input type="text" 
                            class="form-control" 
                            placeholder="Search assignments..." 
                            wire:model.live.debounce.300ms="search">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classroom Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1">{{ $classroom->name }}</h4>
                        <p class="text-muted mb-0">
                            <i class="ri-book-open-line me-1"></i>{{ $classroom->subject->name ?? 'No subject' }}
                            <span class="mx-2">•</span>
                            <i class="ri-group-line me-1"></i>{{ $classroom->section->name ?? 'No section' }}
                            @if($classroom->section && $classroom->section->year_level)
                                <span class="badge bg-primary-subtle text-primary ms-1">
                                    {{ $classroom->section->year_level instanceof \App\Enums\YearLevel ? $classroom->section->year_level->label() : 'Grade ' . $classroom->section->year_level }}
                                </span>
                            @endif
                            @if($classroom->room)
                                <span class="mx-2">•</span>
                                <i class="ri-building-line me-1"></i>Room: {{ $classroom->room }}
                            @endif
                            @if($classroom->students->isNotEmpty())
                                <span class="mx-2">•</span>
                                <a href="#" 
                                    wire:click.prevent="openStudentsModal" 
                                    class="text-decoration-none text-muted"
                                    title="View all students">
                                    <i class="ri-user-line me-1"></i>{{ $classroom->students->count() }} 
                                    {{ $classroom->students->count() === 1 ? 'student' : 'students' }}
                                </a>
                            @endif
                        </p>
                        @if($classroom->description)
                            <p class="text-muted mt-2 mb-0">{{ $classroom->description }}</p>
                        @endif
                    </div>
                    <div class="text-end">
                        @if($classroom->class_code)
                            <div class="mb-2">
                                <span class="badge bg-info-subtle text-info">
                                    <i class="ri-code-line me-1"></i>Class Code: {{ $classroom->class_code }}
                                </span>
                            </div>
                        @endif
                        <div class="text-muted small">
                            <i class="ri-user-line me-1"></i>
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
                </div>
            </div>
        </div>

        <!-- Create Assignment Button -->
        @if(!$isStudent)
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-18">
                                <i class="ri-user-line"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="border rounded p-3 bg-light cursor-pointer" wire:click="toggleAssignmentForm" style="cursor: pointer;">
                            <p class="text-muted mb-0">
                                <i class="ri-file-edit-line me-2"></i>Create an assignment...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Create Assignment Modal -->
        <x-modal wire:model="showAssignmentForm" size="xl" title="Create Assignment" :showFooter="false">
            <form wire:submit.prevent="createAssignment" autocomplete="off">
                <div class="mb-3">
                    <label class="form-label">Assignment Title <span class="text-danger">*</span></label>
                    <input type="text" 
                        class="form-control @error('title') is-invalid @enderror"
                        wire:model="title"
                        placeholder="Assignment title (e.g., Math Homework Chapter 5)">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                        wire:model="description" 
                        rows="3" 
                        placeholder="Add description or instructions..."></textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small">Assignment Type</label>
                        <select class="form-select form-select-sm @error('assignment_type') is-invalid @enderror" wire:model="assignment_type">
                            <option value="assignment">Assignment</option>
                            <option value="project">Project</option>
                            <option value="homework">Homework</option>
                            <option value="activity">Activity</option>
                        </select>
                        @error('assignment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Points</label>
                        <input type="number" 
                            class="form-control form-control-sm @error('points_possible') is-invalid @enderror"
                            wire:model="points_possible"
                            placeholder="Points">
                        @error('points_possible') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Due Date</label>
                        <input type="date" 
                            class="form-control form-control-sm @error('due_date') is-invalid @enderror"
                            wire:model="due_date"
                            min="{{ date('Y-m-d') }}">
                        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Due Time</label>
                        <input type="time" 
                            class="form-control form-control-sm @error('due_time') is-invalid @enderror"
                            wire:model="due_time"
                            placeholder="Time">
                        @error('due_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small">Component Category</label>
                        <select class="form-select form-select-sm @error('component_category') is-invalid @enderror" wire:model="component_category">
                            <option value="">Category (Optional)</option>
                            <option value="written_works">Written Works</option>
                            <option value="performance_tasks">Performance Tasks</option>
                        </select>
                        @error('component_category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                @if(!empty($attachments))
                    <div class="mb-3">
                        <label class="form-label small">New Attachments</label>
                        @foreach($attachments as $index => $file)
                            <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                <div>
                                    <i class="ri-file-line me-2"></i>
                                    <span>{{ is_object($file) ? $file->getClientOriginalName() : $file }}</span>
                                    @if(is_object($file))
                                        <small class="text-muted">({{ number_format($file->getSize() / 1024, 2) }} KB)</small>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-light" wire:click="removeAttachment({{ $index }})">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($editingAssignment && $editingAssignment->attachments->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label small">Existing Attachments</label>
                        @foreach($editingAssignment->attachments as $attachment)
                            <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                <div>
                                    <i class="ri-file-line me-2"></i>
                                    <span>{{ $attachment->original_name }}</span>
                                    <small class="text-muted">({{ $attachment->human_readable_size }})</small>
                                </div>
                                <a href="{{ $attachment->url }}" target="_blank" class="btn btn-sm btn-light me-1" title="View">
                                    <i class="ri-eye-line"></i>
                                </a>
                            </div>
                        @endforeach
                        <small class="text-muted d-block mt-1">Existing attachments will be kept. Add new files above to attach more.</small>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="btn btn-sm btn-light mb-0" style="cursor: pointer;">
                        <i class="ri-attachment-line me-1"></i> Attach Files
                        <input type="file" class="d-none" wire:model="attachments" multiple>
                    </label>
                    <small class="text-muted d-block mt-1">You can attach multiple files (max 10MB per file)</small>
                </div>

                @error('form')
                    <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light" wire:click="toggleAssignmentForm">Cancel</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="ri-send-plane-line me-1"></i>{{ $editingAssignment ? 'Update Assignment' : 'Post Assignment' }}
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-2"></span>{{ $editingAssignment ? 'Updating...' : 'Posting...' }}
                        </span>
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- Assignments Feed -->
        <div class="assignments-feed">
            @forelse($assignments->items() as $assignment)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-shrink-0">
                                @if($assignment->creator)
                                    @php
                                        $photoPath = $assignment->creator->photo_path
                                            ? (str_starts_with($assignment->creator->photo_path, 'http') 
                                                ? $assignment->creator->photo_path 
                                                : asset('storage/' . $assignment->creator->photo_path))
                                            : ($assignment->creator->avatar 
                                                ? asset('build/images/users/' . $assignment->creator->avatar) 
                                                : asset('build/images/users/user-dummy-img.jpg'));
                                    @endphp
                                    <a href="{{ url('/profile?user_id=' . $assignment->creator->id) }}" class="text-decoration-none">
                                        <div class="avatar-sm">
                                            <img src="{{ $photoPath }}" 
                                                alt="{{ $assignment->creator->name ?? 'User' }}" 
                                                class="rounded-circle avatar-sm"
                                                style="cursor: pointer; width: 2.5rem; height: 2.5rem; object-fit: cover;"
                                                title="View Profile">
                                        </div>
                                    </a>
                                @else
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-18">
                                            <i class="ri-user-line"></i>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <h6 class="mb-0">
                                            @if($assignment->creator)
                                                <a href="{{ url('/profile?user_id=' . $assignment->creator->id) }}" class="text-decoration-none text-body" title="View Profile">
                                                    @if(!empty($assignment->creator->first_name) || !empty($assignment->creator->last_name))
                                                        {{ trim(($assignment->creator->first_name ?? '') . ' ' . ($assignment->creator->last_name ?? '')) }}
                                                    @else
                                                        {{ $assignment->creator->name ?? 'Unknown' }}
                                                    @endif
                                                </a>
                                            @else
                                                Unknown
                                            @endif
                                        </h6>
                                        <small class="text-muted">{{ $assignment->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-{{ $assignment->status === 'published' ? 'success' : 'warning' }}-subtle text-{{ $assignment->status === 'published' ? 'success' : 'warning' }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>
                                        @if(!$isStudent && $assignment->created_by === auth()->id())
                                        <div class="dropdown">
                                            <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        wire:click.prevent="editAssignment({{ $assignment->id }})"
                                                        wire:target="editAssignment({{ $assignment->id }})">
                                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                        wire:click.prevent="deleteAssignment({{ $assignment->id }})"
                                                        wire:target="deleteAssignment({{ $assignment->id }})">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <h5 class="mb-2">{{ $assignment->title }}</h5>
                                
                                @if($assignment->description)
                                    <p class="mb-2">{{ $assignment->description }}</p>
                                @endif

                                @if($assignment->instructions)
                                    <div class="mb-2">
                                        <small class="text-muted fw-semibold">Instructions:</small>
                                        <p class="mb-0 small">{{ Str::limit($assignment->instructions, 200) }}</p>
                                    </div>
                                @endif

                                <div class="d-flex flex-wrap gap-3 mb-2">
                                    @if($assignment->points_possible)
                                        <div class="text-muted small">
                                            <i class="ri-star-fill text-warning me-1"></i>{{ $assignment->points_possible }} points
                                        </div>
                                    @endif
                                    @if($assignment->due_date)
                                        <div class="text-muted small">
                                            <i class="ri-calendar-event-fill me-1"></i>
                                            Due: {{ $assignment->due_date->format('M d, Y') }}
                                            @if($assignment->due_time)
                                                @php
                                                    $dueTime = is_string($assignment->due_time) 
                                                        ? \Carbon\Carbon::parse($assignment->due_time) 
                                                        : $assignment->due_time;
                                                @endphp
                                                {{ $dueTime->format('h:i A') }}
                                            @endif
                                        </div>
                                    @endif
                                    @if($assignment->component_category)
                                        <div class="text-muted small">
                                            <i class="ri-bookmark-line me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $assignment->component_category)) }}
                                        </div>
                                    @endif
                                </div>

                                @if($assignment->attachments_count > 0)
                                    <div class="mb-2">
                                        <small class="text-muted d-block mb-1">
                                            <i class="ri-attachment-line me-1"></i>{{ $assignment->attachments_count }} 
                                            {{ $assignment->attachments_count === 1 ? 'attachment' : 'attachments' }}
                                        </small>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($assignment->attachments as $attachment)
                                                <button type="button" 
                                                    class="btn btn-sm btn-light d-flex align-items-center gap-1"
                                                    wire:click="viewAttachment({{ $attachment->id }})"
                                                    title="{{ $attachment->original_name }}">
                                                    <i class="ri-file-{{ $attachment->isImage() ? 'image' : ($attachment->isDocument() ? 'text' : 'line') }}-line"></i>
                                                    <span class="text-truncate" style="max-width: 150px;">
                                                        {{ Str::limit($attachment->original_name, 20) }}
                                                    </span>
                                                    <small class="text-muted">({{ $attachment->human_readable_size }})</small>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
                                    <div class="d-flex align-items-center gap-3">
                                        <button type="button" 
                                            class="btn btn-sm btn-link text-muted p-0 text-decoration-none"
                                            wire:click="openStudentsModal"
                                            title="View students">
                                            <i class="ri-user-line me-1"></i>{{ $assignment->students_count ?? 0 }} students
                                        </button>
                                        <button type="button" 
                                            class="btn btn-sm btn-link text-muted p-0 text-decoration-none"
                                            wire:click="toggleSubmissions({{ $assignment->id }})"
                                            title="View submissions">
                                            <i class="ri-file-upload-line me-1"></i>{{ $assignment->submissions_count ?? 0 }} submissions
                                        </button>
                                        <button type="button" 
                                            class="btn btn-sm btn-link text-muted p-0 text-decoration-none"
                                            wire:click="toggleComments({{ $assignment->id }})"
                                            title="View comments">
                                            <i class="ri-chat-3-line me-1"></i>{{ $assignment->comments_count ?? 0 }} comments
                                        </button>
                                    </div>
                                    @if($isStudent && $studentInfo)
                                        @php
                                            $submissions = is_array($assignment->submissions) ? collect($assignment->submissions) : $assignment->submissions;
                                            $studentSubmission = $submissions->firstWhere('student_info_id', $studentInfo->id) ?? null;
                                        @endphp
                                        <div class="d-flex align-items-center gap-2">
                                            @if($studentSubmission)
                                                @php
                                                    $statusColors = [
                                                        'draft' => 'secondary',
                                                        'submitted' => 'success',
                                                        'late' => 'warning',
                                                        'graded' => 'info',
                                                        'returned' => 'primary'
                                                    ];
                                                    $statusColor = $statusColors[$studentSubmission->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                                    @if($studentSubmission->status === 'graded' && $studentSubmission->points_earned !== null)
                                                        <i class="ri-check-line me-1"></i>Graded: {{ $studentSubmission->points_earned }}/{{ $assignment->points_possible ?? 'N/A' }}
                                                    @else
                                                        {{ ucfirst($studentSubmission->status) }}
                                                    @endif
                                                </span>
                                                <button type="button" 
                                                    class="btn btn-sm btn-primary"
                                                    wire:click="openSubmissionModal({{ $assignment->id }})"
                                                    title="View/Edit submission">
                                                    <i class="ri-eye-line me-1"></i>View Submission
                                                </button>
                                            @else
                                                <button type="button" 
                                                    class="btn btn-sm btn-primary"
                                                    wire:click="openSubmissionModal({{ $assignment->id }})"
                                                    title="Submit assignment">
                                                    <i class="ri-file-upload-line me-1"></i>Submit
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submissions List (shown below assignment) -->
                @if($showSubmissionsForAssignmentId === $assignment->id)
                    @php
                        $assignmentWithSubmissions = \App\Models\Grading\Assignment::with([
                            'submissions.studentInfo.user',
                            'submissions.attachments',
                            'submissions.grader'
                        ])->find($assignment->id);
                        
                        // Filter submissions for students - only show their own
                        $submissionsToShow = collect([]);
                        if ($assignmentWithSubmissions) {
                            if ($isStudent && $studentInfo) {
                                // Students only see their own submission
                                $submissionsToShow = $assignmentWithSubmissions->submissions
                                    ->where('student_info_id', $studentInfo->id);
                            } else {
                                // Teachers see all submissions
                                $submissionsToShow = $assignmentWithSubmissions->submissions;
                            }
                        }
                    @endphp
                    @if($submissionsToShow->isNotEmpty())
                        <div class="card border-top-0 rounded-top-0 mb-3" style="margin-top: -1rem;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="mb-0">
                                        <i class="ri-file-upload-line me-2"></i>
                                        @if($isStudent)
                                            My Submission
                                        @else
                                            Submissions ({{ $submissionsToShow->count() }})
                                        @endif
                                    </h6>
                                    <button type="button" 
                                        class="btn btn-sm btn-light"
                                        wire:click="toggleSubmissions({{ $assignment->id }})"
                                        title="Hide submissions">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                @if(!$isStudent)
                                                    <th>Student</th>
                                                @endif
                                                <th>Status</th>
                                                <th>Submitted At</th>
                                                <th>Grade</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($submissionsToShow->sortByDesc('submitted_at') as $submission)
                                                @php
                                                    $user = $submission->studentInfo->user ?? null;
                                                    $photoPath = $user && $user->photo_path
                                                        ? (str_starts_with($user->photo_path, 'http') 
                                                            ? $user->photo_path 
                                                            : asset('storage/' . $user->photo_path))
                                                        : ($user && $user->avatar 
                                                            ? asset('build/images/users/' . $user->avatar) 
                                                            : asset('build/images/users/user-dummy-img.jpg'));
                                                    
                                                    $statusColors = [
                                                        'draft' => 'secondary',
                                                        'submitted' => 'success',
                                                        'late' => 'warning',
                                                        'graded' => 'info',
                                                        'returned' => 'primary'
                                                    ];
                                                    $statusColor = $statusColors[$submission->status] ?? 'secondary';
                                                @endphp
                                                <tr>
                                                    @if(!$isStudent)
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <img src="{{ $photoPath }}" 
                                                                    alt="{{ $user->name ?? 'Student' }}" 
                                                                    class="rounded-circle"
                                                                    style="width: 2.5rem; height: 2.5rem; object-fit: cover;">
                                                                <div>
                                                                    <h6 class="mb-0">
                                                                        @if($user)
                                                                            @if(!empty($user->first_name) || !empty($user->last_name))
                                                                                {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) }}
                                                                            @else
                                                                                {{ $user->name ?? 'Unknown' }}
                                                                            @endif
                                                                        @else
                                                                            Unknown
                                                                        @endif
                                                                    </h6>
                                                                    @if($submission->studentInfo && $submission->studentInfo->student_number)
                                                                        <small class="text-muted">{{ $submission->studentInfo->student_number }}</small>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                    @endif
                                                    <td>
                                                        <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                                            {{ ucfirst($submission->status) }}
                                                            @if($submission->is_late)
                                                                <i class="ri-time-line ms-1" title="Late submission"></i>
                                                            @endif
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($submission->submitted_at)
                                                            <small class="text-muted">
                                                                {{ $submission->submitted_at->format('M d, Y h:i A') }}
                                                            </small>
                                                        @else
                                                            <span class="text-muted">Not submitted</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($submission->status === 'graded' && $submission->points_earned !== null)
                                                            <span class="fw-semibold">
                                                                {{ $submission->points_earned }}/{{ $assignmentWithSubmissions->points_possible ?? 'N/A' }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" 
                                                            class="btn btn-sm btn-primary"
                                                            wire:click="viewSubmission({{ $submission->id }})"
                                                            title="View submission">
                                                            <i class="ri-eye-line me-1"></i>View
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card border-top-0 rounded-top-0 mb-3" style="margin-top: -1rem;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h6 class="mb-0">
                                        <i class="ri-file-upload-line me-2"></i>Submissions
                                    </h6>
                                    <button type="button" 
                                        class="btn btn-sm btn-light"
                                        wire:click="toggleSubmissions({{ $assignment->id }})"
                                        title="Hide submissions">
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                                <div class="text-center py-4">
                                    <i class="ri-file-upload-line display-6 text-muted mb-2"></i>
                                    <h6 class="mb-2">No Submissions Yet</h6>
                                    <p class="text-muted mb-0">No students have submitted this assignment yet.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <!-- Comments Section (shown below assignment) -->
                @if($showCommentsForAssignmentId === $assignment->id)
                    @php
                        $assignmentWithComments = \App\Models\Grading\Assignment::with([
                            'comments.user',
                            'comments.attachments'
                        ])->find($assignment->id);
                    @endphp
                    <div class="card border-top-0 rounded-top-0 mb-3" style="margin-top: -1rem;">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="mb-0">
                                    <i class="ri-chat-3-line me-2"></i>Comments ({{ $assignmentWithComments->comments->count() ?? 0 }})
                                </h6>
                                <button type="button" 
                                    class="btn btn-sm btn-light"
                                    wire:click="toggleComments({{ $assignment->id }})"
                                    title="Hide comments">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>

                            <!-- Comment Form -->
                            <div class="mb-4">
                                <form wire:submit.prevent="submitComment({{ $assignment->id }})">
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <div class="flex-shrink-0">
                                            @php
                                                $user = auth()->user();
                                                $photoPath = $user && $user->photo_path
                                                    ? (str_starts_with($user->photo_path, 'http') 
                                                        ? $user->photo_path 
                                                        : asset('storage/' . $user->photo_path))
                                                    : ($user && $user->avatar 
                                                        ? asset('build/images/users/' . $user->avatar) 
                                                        : asset('build/images/users/user-dummy-img.jpg'));
                                            @endphp
                                            <img src="{{ $photoPath }}" 
                                                alt="{{ $user->name ?? 'User' }}" 
                                                class="rounded-circle"
                                                style="width: 2rem; height: 2rem; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <textarea class="form-control @error('commentBody') is-invalid @enderror" 
                                                wire:model="commentBody" 
                                                rows="2" 
                                                placeholder="Write a comment..."></textarea>
                                            @error('commentBody') 
                                                <div class="invalid-feedback">{{ $message }}</div> 
                                            @enderror
                                        </div>
                                    </div>

                                    @if(!empty($commentAttachments))
                                        <div class="mb-2 ms-5">
                                            <label class="form-label small">Attachments</label>
                                            @foreach($commentAttachments as $index => $file)
                                                <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                                    <div>
                                                        <i class="ri-file-line me-2"></i>
                                                        <span>{{ is_object($file) ? $file->getClientOriginalName() : $file }}</span>
                                                        @if(is_object($file))
                                                            <small class="text-muted">({{ number_format($file->getSize() / 1024, 2) }} KB)</small>
                                                        @endif
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light" wire:click="removeCommentAttachment({{ $index }})">
                                                        <i class="ri-close-line"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="d-flex align-items-center justify-content-between ms-5">
                                        <div>
                                            <label class="btn btn-sm btn-light mb-0" style="cursor: pointer;">
                                                <i class="ri-attachment-line me-1"></i> Attach Files
                                                <input type="file" class="d-none" wire:model="commentAttachments" multiple>
                                            </label>
                                            <small class="text-muted ms-2">You can attach multiple files (max 5MB per file)</small>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-primary" wire:loading.attr="disabled">
                                            <span wire:loading.remove>
                                                <i class="ri-send-plane-line me-1"></i>Post
                                            </span>
                                            <span wire:loading>
                                                <span class="spinner-border spinner-border-sm me-2"></span>Posting...
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Comments List -->
                            @if($assignmentWithComments && $assignmentWithComments->comments->isNotEmpty())
                                <div class="comments-list">
                                    @foreach($assignmentWithComments->comments as $comment)
                                        @php
                                            $commentUser = $comment->user;
                                            $commentPhotoPath = $commentUser && $commentUser->photo_path
                                                ? (str_starts_with($commentUser->photo_path, 'http') 
                                                    ? $commentUser->photo_path 
                                                    : asset('storage/' . $commentUser->photo_path))
                                                : ($commentUser && $commentUser->avatar 
                                                    ? asset('build/images/users/' . $commentUser->avatar) 
                                                    : asset('build/images/users/user-dummy-img.jpg'));
                                        @endphp
                                        <div class="d-flex align-items-start gap-2 mb-3 pb-3 border-bottom">
                                            <div class="flex-shrink-0">
                                                <img src="{{ $commentPhotoPath }}" 
                                                    alt="{{ $commentUser->name ?? 'User' }}" 
                                                    class="rounded-circle"
                                                    style="width: 2rem; height: 2rem; object-fit: cover;">
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <h6 class="mb-0 small">
                                                            @if($commentUser)
                                                                @if(!empty($commentUser->first_name) || !empty($commentUser->last_name))
                                                                    {{ trim(($commentUser->first_name ?? '') . ' ' . ($commentUser->last_name ?? '')) }}
                                                                @else
                                                                    {{ $commentUser->name ?? 'Unknown' }}
                                                                @endif
                                                            @else
                                                                Unknown
                                                            @endif
                                                        </h6>
                                                        <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                                    </div>
                                                    @if($comment->user_id === auth()->id())
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-link text-muted p-0" type="button"
                                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ri-more-fill"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                        wire:click.prevent="editComment({{ $comment->id }})">
                                                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item text-danger" href="#"
                                                                        wire:click.prevent="deleteComment({{ $comment->id }})">
                                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($editingCommentId === $comment->id)
                                                    <!-- Edit Comment Form -->
                                                    <form wire:submit.prevent="updateComment({{ $comment->id }})">
                                                        <div class="mb-2">
                                                            <textarea class="form-control form-control-sm @error('editingCommentBody') is-invalid @enderror" 
                                                                wire:model="editingCommentBody" 
                                                                rows="3"></textarea>
                                                            @error('editingCommentBody') 
                                                                <div class="invalid-feedback">{{ $message }}</div> 
                                                            @enderror
                                                        </div>

                                                        @if(!empty($editingCommentAttachments))
                                                            <div class="mb-2">
                                                                <label class="form-label small">New Attachments</label>
                                                                @foreach($editingCommentAttachments as $index => $file)
                                                                    <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                                                        <div>
                                                                            <i class="ri-file-line me-2"></i>
                                                                            <span>{{ is_object($file) ? $file->getClientOriginalName() : $file }}</span>
                                                                            @if(is_object($file))
                                                                                <small class="text-muted">({{ number_format($file->getSize() / 1024, 2) }} KB)</small>
                                                                            @endif
                                                                        </div>
                                                                        <button type="button" class="btn btn-sm btn-light" wire:click="removeEditingCommentAttachment({{ $index }})">
                                                                            <i class="ri-close-line"></i>
                                                                        </button>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        @php
                                                            $existingAttachments = is_object($editingCommentExistingAttachments) 
                                                                ? $editingCommentExistingAttachments 
                                                                : collect($editingCommentExistingAttachments ?? []);
                                                        @endphp
                                                        @if($existingAttachments->isNotEmpty())
                                                            <div class="mb-2">
                                                                <label class="form-label small">Existing Attachments</label>
                                                                @foreach($existingAttachments as $attachment)
                                                                    @php
                                                                        $attachmentObj = is_object($attachment) ? $attachment : (object) $attachment;
                                                                        $url = is_object($attachment) && method_exists($attachment, 'getUrlAttribute') 
                                                                            ? $attachment->url 
                                                                            : asset('storage/' . ($attachmentObj->file_path ?? ''));
                                                                    @endphp
                                                                    <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                                                        <div>
                                                                            <i class="ri-file-line me-2"></i>
                                                                            <span>{{ $attachmentObj->original_name ?? 'File' }}</span>
                                                                            @if(isset($attachmentObj->human_readable_size))
                                                                                <small class="text-muted">({{ $attachmentObj->human_readable_size }})</small>
                                                                            @endif
                                                                        </div>
                                                                        <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-light me-1" title="View">
                                                                            <i class="ri-eye-line"></i>
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                                <small class="text-muted d-block mt-1">Existing attachments will be kept. Add new files above to attach more.</small>
                                                            </div>
                                                        @endif

                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <label class="btn btn-sm btn-light mb-0" style="cursor: pointer;">
                                                                    <i class="ri-attachment-line me-1"></i> Attach Files
                                                                    <input type="file" class="d-none" wire:model="editingCommentAttachments" multiple>
                                                                </label>
                                                            </div>
                                                            <div class="d-flex gap-2">
                                                                <button type="button" class="btn btn-sm btn-light" wire:click="cancelEditComment">
                                                                    Cancel
                                                                </button>
                                                                <button type="submit" class="btn btn-sm btn-primary" wire:loading.attr="disabled">
                                                                    <span wire:loading.remove>
                                                                        <i class="ri-save-line me-1"></i>Save
                                                                    </span>
                                                                    <span wire:loading>
                                                                        <span class="spinner-border spinner-border-sm me-2"></span>Saving...
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                @else
                                                    <!-- Display Comment -->
                                                    <p class="mb-2 small" style="white-space: pre-wrap;">{{ $comment->body }}</p>
                                                    
                                                    @if($comment->attachments->isNotEmpty())
                                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                                            @foreach($comment->attachments as $attachment)
                                                                <a href="{{ $attachment->url }}" 
                                                                    target="_blank" 
                                                                    class="btn btn-sm btn-light d-flex align-items-center gap-1"
                                                                    title="{{ $attachment->original_name }}">
                                                                    <i class="ri-file-{{ $attachment->isImage() ? 'image' : ($attachment->isDocument() ? 'text' : 'line') }}-line"></i>
                                                                    <span class="text-truncate" style="max-width: 150px;">
                                                                        {{ Str::limit($attachment->original_name, 20) }}
                                                                    </span>
                                                                    <small class="text-muted">({{ $attachment->human_readable_size }})</small>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="ri-chat-3-line display-6 text-muted mb-2"></i>
                                    <p class="text-muted mb-0 small">No comments yet. Be the first to comment!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @empty
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ri-file-list-3-line display-4 text-muted mb-3"></i>
                        <h5 class="mb-2">No Assignments Yet</h5>
                        <p class="text-muted mb-4">Create your first assignment to get started.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($assignments->hasPages())
            <x-pagination :paginator="$assignments" :show-summary="true" />
        @endif

       
        <!-- Delete Assignment Modal -->
        <x-modal wire:model="showDeleteModal" title="Delete Assignment" size="md" :centered="true" :showFooter="true">
            <div class="text-center">
                <div class="mb-4">
                    <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">Are you sure?</h5>
                <p class="text-muted">
                    You are about to delete the assignment <strong>{{ $deleteAssignmentTitle ?? 'this assignment' }}</strong>.
                    This action cannot be undone.
                </p>
                <div class="alert alert-warning mt-3">
                    <i class="ri-alert-line me-2"></i>
                    <strong>Warning:</strong> This will permanently delete the assignment and all related data including attachments.
                </div>
            </div>

            <x-slot:footer>
                <button type="button" class="btn btn-light" wire:click="closeDeleteModal">Cancel</button>
                <x-button color="danger" wire:click="confirmDelete" wire:target="confirmDelete">
                    <span wire:loading.remove wire:target="confirmDelete">Delete Assignment</span>
                    <span wire:loading wire:target="confirmDelete">Deleting...</span>
                </x-button>
            </x-slot:footer>
        </x-modal>

        <!-- Delete Comment Modal -->
        <x-modal wire:model="showDeleteCommentModal" title="Delete Comment" size="md" :centered="true" :showFooter="true">
            <div class="text-center">
                <div class="mb-4">
                    <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
                </div>
                <h5 class="mb-3">Are you sure?</h5>
                <p class="text-muted">
                    You are about to delete this comment. This action cannot be undone.
                </p>
                @if($deleteCommentBody)
                    <div class="alert alert-light mt-3 text-start">
                        <small class="text-muted d-block mb-1">Comment preview:</small>
                        <small>{{ $deleteCommentBody }}</small>
                    </div>
                @endif
                <div class="alert alert-warning mt-3">
                    <i class="ri-alert-line me-2"></i>
                    <strong>Warning:</strong> This will permanently delete the comment and all its attachments.
                </div>
            </div>

            <x-slot:footer>
                <button type="button" class="btn btn-light" wire:click="closeDeleteCommentModal">Cancel</button>
                <x-button color="danger" wire:click="confirmDeleteComment" wire:target="confirmDeleteComment">
                    <span wire:loading.remove wire:target="confirmDeleteComment">Delete Comment</span>
                    <span wire:loading wire:target="confirmDeleteComment">Deleting...</span>
                </x-button>
            </x-slot:footer>
        </x-modal>

        <!-- Students Modal -->
        <x-modal wire:model="showStudentsModal" title="Class Members" size="xl" :showFooter="false">
            @if($classroom)
                <!-- Teacher Section -->
                @if($classroom->creator)
                    @php
                        $creator = $classroom->creator;
                        $creatorPhotoPath = $creator->photo_path
                            ? (str_starts_with($creator->photo_path, 'http') 
                                ? $creator->photo_path 
                                : asset('storage/' . $creator->photo_path))
                            : ($creator->avatar 
                                ? asset('build/images/users/' . $creator->avatar) 
                                : asset('build/images/users/user-dummy-img.jpg'));
                    @endphp
                    <div class="mb-4">
                        <h6 class="mb-3 text-muted">
                            <i class="ri-user-star-line me-2"></i>Teacher
                        </h6>
                        <div class="card border">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $creatorPhotoPath }}" 
                                        alt="{{ $creator->name ?? 'Teacher' }}" 
                                        class="rounded-circle"
                                        style="width: 3rem; height: 3rem; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">
                                            @if(!empty($creator->first_name) || !empty($creator->last_name))
                                                {{ trim(($creator->first_name ?? '') . ' ' . ($creator->last_name ?? '')) }}
                                            @else
                                                {{ $creator->name ?? 'Unknown' }}
                                            @endif
                                            <span class="badge bg-primary-subtle text-primary ms-2">Teacher</span>
                                        </h6>
                                        @if($creator->email)
                                            <small class="text-muted">{{ $creator->email }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Students Section -->
                @if($classroom->students->isNotEmpty())
                    <div class="mb-3">
                        <h6 class="mb-3 text-muted">
                            <i class="ri-group-line me-2"></i>Students ({{ $classroom->students->count() }})
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Student Number</th>
                                    <th>Program</th>
                                    <th>Year Level</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($classroom->students->sortBy(function($student) {
                                    $user = $student->user;
                                    return $user ? ($user->last_name ?? $user->name ?? '') : '';
                                }) as $student)
                                    @php
                                        $user = $student->user;
                                        $photoPath = $user && $user->photo_path
                                            ? (str_starts_with($user->photo_path, 'http') 
                                                ? $user->photo_path 
                                                : asset('storage/' . $user->photo_path))
                                            : ($user && $user->avatar 
                                                ? asset('build/images/users/' . $user->avatar) 
                                                : asset('build/images/users/user-dummy-img.jpg'));
                                        
                                        // Get pivot data
                                        $enrollmentStatus = $student->pivot->status ?? 'enrolled';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $photoPath }}" 
                                                    alt="{{ $user->name ?? 'Student' }}" 
                                                    class="rounded-circle"
                                                    style="width: 2.5rem; height: 2.5rem; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0">
                                                        @if($user)
                                                            @if(!empty($user->first_name) || !empty($user->last_name))
                                                                {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) }}
                                                            @else
                                                                {{ $user->name ?? 'Unknown' }}
                                                            @endif
                                                        @else
                                                            Unknown
                                                        @endif
                                                    </h6>
                                                    @if($user && $user->email)
                                                        <small class="text-muted">{{ $user->email }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $student->student_number ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($student->program)
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ $student->program->code ?? $student->program->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($student->year_level)
                                                <span class="badge bg-primary-subtle text-primary">
                                                    Grade {{ $student->year_level }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $enrollmentStatus === 'enrolled' ? 'success' : ($enrollmentStatus === 'active' ? 'primary' : 'warning') }}-subtle text-{{ $enrollmentStatus === 'enrolled' ? 'success' : ($enrollmentStatus === 'active' ? 'primary' : 'warning') }}">
                                                {{ ucfirst($enrollmentStatus) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ri-user-line display-4 text-muted mb-3"></i>
                        <h6 class="mb-2">No Students Enrolled</h6>
                        <p class="text-muted mb-0">There are no students enrolled in this class yet.</p>
                    </div>
                @endif

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-light" wire:click="closeStudentsModal">Close</button>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ri-error-warning-line display-4 text-danger mb-3"></i>
                    <h5 class="mb-2">Classroom Not Found</h5>
                    <p class="text-muted mb-4">Unable to load classroom information.</p>
                    <button type="button" class="btn btn-light" wire:click="closeStudentsModal">Close</button>
                </div>
            @endif
        </x-modal>

        <!-- View Submission Modal -->
        <x-modal wire:model="showViewSubmissionModal" title="View Submission" size="xl" :showFooter="false">
            @if($selectedSubmission)
                @php
                    $user = $selectedSubmission->studentInfo->user ?? null;
                    $photoPath = $user && $user->photo_path
                        ? (str_starts_with($user->photo_path, 'http') 
                            ? $user->photo_path 
                            : asset('storage/' . $user->photo_path))
                        : ($user && $user->avatar 
                            ? asset('build/images/users/' . $user->avatar) 
                            : asset('build/images/users/user-dummy-img.jpg'));
                    
                    $statusColors = [
                        'draft' => 'secondary',
                        'submitted' => 'success',
                        'late' => 'warning',
                        'graded' => 'info',
                        'returned' => 'primary'
                    ];
                    $statusColor = $statusColors[$selectedSubmission->status] ?? 'secondary';
                @endphp
                
                <!-- Student Info -->
                <div class="card border mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $photoPath }}" 
                                alt="{{ $user->name ?? 'Student' }}" 
                                class="rounded-circle"
                                style="width: 3rem; height: 3rem; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">
                                    @if($user)
                                        @if(!empty($user->first_name) || !empty($user->last_name))
                                            {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) }}
                                        @else
                                            {{ $user->name ?? 'Unknown' }}
                                        @endif
                                    @else
                                        Unknown
                                    @endif
                                </h6>
                                @if($selectedSubmission->studentInfo && $selectedSubmission->studentInfo->student_number)
                                    <small class="text-muted">Student #: {{ $selectedSubmission->studentInfo->student_number }}</small>
                                @endif
                                @if($user && $user->email)
                                    <br><small class="text-muted">{{ $user->email }}</small>
                                @endif
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                    {{ ucfirst($selectedSubmission->status) }}
                                    @if($selectedSubmission->is_late)
                                        <i class="ri-time-line ms-1" title="Late submission"></i>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submission Details -->
                <div class="mb-3">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Submitted At</small>
                            <strong>
                                @if($selectedSubmission->submitted_at)
                                    {{ $selectedSubmission->submitted_at->format('M d, Y h:i A') }}
                                @else
                                    <span class="text-muted">Not submitted</span>
                                @endif
                            </strong>
                        </div>
                        @if($selectedSubmission->status === 'graded')
                            <div class="col-md-6">
                                <small class="text-muted d-block">Grade</small>
                                <strong>
                                    {{ $selectedSubmission->points_earned ?? 'N/A' }}/{{ $selectedSubmission->assignment->points_possible ?? 'N/A' }} points
                                </strong>
                            </div>
                            @if($selectedSubmission->graded_at)
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Graded At</small>
                                    <strong>{{ $selectedSubmission->graded_at->format('M d, Y h:i A') }}</strong>
                                </div>
                            @endif
                            @if($selectedSubmission->grader)
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Graded By</small>
                                    <strong>
                                        @if(!empty($selectedSubmission->grader->first_name) || !empty($selectedSubmission->grader->last_name))
                                            {{ trim(($selectedSubmission->grader->first_name ?? '') . ' ' . ($selectedSubmission->grader->last_name ?? '')) }}
                                        @else
                                            {{ $selectedSubmission->grader->name ?? 'Unknown' }}
                                        @endif
                                    </strong>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Submission Content -->
                @if($selectedSubmission->content)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Submission Content</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $selectedSubmission->content }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Attachments -->
                @if($selectedSubmission->attachments->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Attachments ({{ $selectedSubmission->attachments->count() }})</label>
                        <div class="d-flex flex-column gap-2">
                            @foreach($selectedSubmission->attachments as $attachment)
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                                <i class="ri-file-{{ $attachment->isImage() ? 'image' : ($attachment->isDocument() ? 'text' : 'line') }}-line fs-4 text-primary"></i>
                                                <div>
                                                    <h6 class="mb-0">{{ $attachment->original_name }}</h6>
                                                    <small class="text-muted">{{ $attachment->human_readable_size }}</small>
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button" 
                                                    class="btn btn-sm btn-light"
                                                    wire:click="viewSubmissionAttachment({{ $attachment->id }})"
                                                    title="View attachment">
                                                    <i class="ri-eye-line"></i> View
                                                </button>
                                                <a href="{{ $attachment->url }}" 
                                                    target="_blank" 
                                                    class="btn btn-sm btn-light"
                                                    download="{{ $attachment->original_name }}"
                                                    title="Download">
                                                    <i class="ri-download-line"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Attachments</label>
                        <div class="text-center py-3 border rounded bg-light">
                            <i class="ri-file-line display-6 text-muted mb-2"></i>
                            <p class="text-muted mb-0">No attachments</p>
                        </div>
                    </div>
                @endif

                <!-- Grading Form (for teachers) -->
                @if(!$isStudent && $classroom && $classroom->created_by === auth()->id() && in_array($selectedSubmission->status, ['submitted', 'late', 'graded', 'returned']))
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary-subtle">
                            <h6 class="mb-0">
                                <i class="ri-star-line me-2"></i>
                                @if($selectedSubmission->status === 'graded')
                                    Update Grade
                                @else
                                    Grade Submission
                                @endif
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($selectedSubmission->status === 'graded')
                                <div class="alert alert-info mb-3">
                                    <i class="ri-information-line me-2"></i>
                                    This submission has already been graded. You can update the grade below.
                                </div>
                            @endif
                            
                            <form wire:submit.prevent="saveGrade">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Points Earned <span class="text-danger">*</span>
                                            <small class="text-muted">
                                                (out of {{ $selectedSubmission->assignment->points_possible ?? 'N/A' }} points)
                                            </small>
                                        </label>
                                        <input type="number" 
                                            class="form-control @error('gradingPointsEarned') is-invalid @enderror"
                                            wire:model="gradingPointsEarned"
                                            step="0.01"
                                            min="0"
                                            max="{{ $selectedSubmission->assignment->points_possible ?? 100 }}"
                                            placeholder="Enter points earned">
                                        @error('gradingPointsEarned') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Percentage</label>
                                        @if($selectedSubmission->assignment->points_possible && $gradingPointsEarned !== null)
                                            @php
                                                $percentage = round(($gradingPointsEarned / $selectedSubmission->assignment->points_possible) * 100, 2);
                                            @endphp
                                            <div class="form-control-plaintext">
                                                <span class="fw-semibold {{ $percentage >= 75 ? 'text-success' : 'text-danger' }}">
                                                    {{ $percentage }}%
                                                </span>
                                                @if($percentage >= 75)
                                                    <span class="badge bg-success-subtle text-success ms-2">Passing</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger ms-2">Failing</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="form-control-plaintext text-muted">Enter points to calculate percentage</div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Feedback <span class="text-muted small">(Optional)</span></label>
                                    <textarea class="form-control @error('gradingFeedback') is-invalid @enderror" 
                                        wire:model="gradingFeedback" 
                                        rows="4" 
                                        placeholder="Add feedback or comments for the student..."></textarea>
                                    @error('gradingFeedback') 
                                        <div class="invalid-feedback">{{ $message }}</div> 
                                    @enderror
                                    <small class="text-muted">This feedback will be visible to the student.</small>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-light" wire:click="closeViewSubmissionModal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            <i class="ri-save-line me-1"></i>
                                            @if($selectedSubmission->status === 'graded')
                                                Update Grade
                                            @else
                                                Save Grade
                                            @endif
                                        </span>
                                        <span wire:loading>
                                            <span class="spinner-border spinner-border-sm me-2"></span>Saving...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Grade & Feedback Display (for students) -->
                @if($isStudent && $selectedSubmission->status === 'graded')
                    <div class="card border-success mb-3">
                        <div class="card-header bg-success-subtle">
                            <h6 class="mb-0">
                                <i class="ri-star-fill text-warning me-2"></i>Your Grade
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Points Earned</small>
                                    <h4 class="mb-0 fw-bold">
                                        {{ $selectedSubmission->points_earned ?? 'N/A' }}/{{ $selectedSubmission->assignment->points_possible ?? 'N/A' }}
                                    </h4>
                                    <small class="text-muted">points</small>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Percentage</small>
                                    @if($selectedSubmission->assignment->points_possible && $selectedSubmission->points_earned !== null)
                                        @php
                                            $percentage = round(($selectedSubmission->points_earned / $selectedSubmission->assignment->points_possible) * 100, 2);
                                        @endphp
                                        <h4 class="mb-0 fw-bold {{ $percentage >= 75 ? 'text-success' : 'text-danger' }}">
                                            {{ $percentage }}%
                                        </h4>
                                        @if($percentage >= 75)
                                            <span class="badge bg-success-subtle text-success">Passing</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Failing</span>
                                        @endif
                                    @else
                                        <h4 class="mb-0 text-muted">N/A</h4>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Graded At</small>
                                    <strong>
                                        @if($selectedSubmission->graded_at)
                                            {{ $selectedSubmission->graded_at->format('M d, Y h:i A') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </strong>
                                    @if($selectedSubmission->grader)
                                        <div class="mt-1">
                                            <small class="text-muted">
                                                by {{ !empty($selectedSubmission->grader->first_name) || !empty($selectedSubmission->grader->last_name) 
                                                    ? trim(($selectedSubmission->grader->first_name ?? '') . ' ' . ($selectedSubmission->grader->last_name ?? '')) 
                                                    : ($selectedSubmission->grader->name ?? 'Unknown') }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($selectedSubmission->feedback)
                                <div class="border-top pt-3">
                                    <label class="form-label fw-semibold mb-2">
                                        <i class="ri-message-2-line me-2"></i>Teacher Feedback
                                    </label>
                                    <div class="card bg-info-subtle border-info">
                                        <div class="card-body">
                                            <p class="mb-0" style="white-space: pre-wrap;">{{ $selectedSubmission->feedback }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Feedback (if graded - for teachers viewing) -->
                @if(!$isStudent && $selectedSubmission->status === 'graded' && $selectedSubmission->feedback)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teacher Feedback</label>
                        <div class="card bg-info-subtle">
                            <div class="card-body">
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $selectedSubmission->feedback }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-light" wire:click="closeViewSubmissionModal">Close</button>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ri-error-warning-line display-4 text-danger mb-3"></i>
                    <h5 class="mb-2">Submission Not Found</h5>
                    <p class="text-muted mb-4">Unable to load submission information.</p>
                    <button type="button" class="btn btn-light" wire:click="closeViewSubmissionModal">Close</button>
                </div>
            @endif
        </x-modal>

        <!-- Submission Modal -->
        <x-modal wire:model="showSubmissionModal" title="Submit Assignment" size="xl" :showFooter="false">
            @if($submissionAssignmentId)
                @php
                    $assignment = collect($assignments->items())->firstWhere('id', $submissionAssignmentId);
                    if (!$assignment) {
                        $assignment = \App\Models\Grading\Assignment::with('submissions')->find($submissionAssignmentId);
                    }
                @endphp
                @if($assignment)
                    <div class="mb-3">
                        <h6 class="mb-1">{{ $assignment->title }}</h6>
                        @if($assignment->points_possible)
                            <small class="text-muted">
                                <i class="ri-star-fill text-warning me-1"></i>{{ $assignment->points_possible }} points
                            </small>
                        @endif
                        @if($assignment->due_date)
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="ri-calendar-event-fill me-1"></i>
                                    Due: {{ $assignment->due_date instanceof \Carbon\Carbon ? $assignment->due_date->format('M d, Y') : \Carbon\Carbon::parse($assignment->due_date)->format('M d, Y') }}
                                    @if($assignment->due_time)
                                        @php
                                            $dueTime = is_string($assignment->due_time) 
                                                ? \Carbon\Carbon::parse($assignment->due_time) 
                                                : ($assignment->due_time instanceof \Carbon\Carbon ? $assignment->due_time : \Carbon\Carbon::parse($assignment->due_time));
                                        @endphp
                                        {{ $dueTime->format('h:i A') }}
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>

                    @php
                        $isGraded = $existingSubmission && $existingSubmission->status === 'graded';
                    @endphp

                    <!-- Grade & Feedback Display (if graded) -->
                    @if($isGraded)
                        <div class="card border-success mb-3">
                            <div class="card-header bg-success-subtle">
                                <h6 class="mb-0">
                                    <i class="ri-star-fill text-warning me-2"></i>Your Grade
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Points Earned</small>
                                        <h4 class="mb-0 fw-bold">
                                            {{ $existingSubmission->points_earned ?? 'N/A' }}/{{ $assignment->points_possible ?? 'N/A' }}
                                        </h4>
                                        <small class="text-muted">points</small>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Percentage</small>
                                        @if($assignment->points_possible && $existingSubmission->points_earned !== null)
                                            @php
                                                $percentage = round(($existingSubmission->points_earned / $assignment->points_possible) * 100, 2);
                                            @endphp
                                            <h4 class="mb-0 fw-bold {{ $percentage >= 75 ? 'text-success' : 'text-danger' }}">
                                                {{ $percentage }}%
                                            </h4>
                                            @if($percentage >= 75)
                                                <span class="badge bg-success-subtle text-success">Passing</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Failing</span>
                                            @endif
                                        @else
                                            <h4 class="mb-0 text-muted">N/A</h4>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Graded At</small>
                                        <strong>
                                            @if($existingSubmission->graded_at)
                                                {{ $existingSubmission->graded_at->format('M d, Y h:i A') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </strong>
                                        @if($existingSubmission->grader)
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    by {{ !empty($existingSubmission->grader->first_name) || !empty($existingSubmission->grader->last_name) 
                                                        ? trim(($existingSubmission->grader->first_name ?? '') . ' ' . ($existingSubmission->grader->last_name ?? '')) 
                                                        : ($existingSubmission->grader->name ?? 'Unknown') }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($existingSubmission->feedback)
                                    <div class="border-top pt-3">
                                        <label class="form-label fw-semibold mb-2">
                                            <i class="ri-message-2-line me-2"></i>Teacher Feedback
                                        </label>
                                        <div class="card bg-info-subtle border-info">
                                            <div class="card-body">
                                                <p class="mb-0" style="white-space: pre-wrap;">{{ $existingSubmission->feedback }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="alert alert-warning mb-3">
                            <i class="ri-lock-line me-2"></i>
                            <strong>Submission Locked:</strong> This submission has been graded and cannot be updated. 
                            If you need to make changes, please contact your teacher.
                        </div>
                    @endif

                    <form wire:submit.prevent="submitAssignment" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label">Your Work <span class="text-muted small">(Optional)</span></label>
                            <textarea class="form-control @error('submissionContent') is-invalid @enderror" 
                                wire:model="submissionContent" 
                                rows="5" 
                                placeholder="Enter your submission content, notes, or comments..."
                                @if($isGraded) readonly disabled @endif></textarea>
                            @error('submissionContent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        @if(!empty($submissionAttachments) && !$isGraded)
                            <div class="mb-3">
                                <label class="form-label small">New Attachments</label>
                                @foreach($submissionAttachments as $index => $file)
                                    <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                        <div>
                                            <i class="ri-file-line me-2"></i>
                                            <span>{{ is_object($file) ? $file->getClientOriginalName() : $file }}</span>
                                            @if(is_object($file))
                                                <small class="text-muted">({{ number_format($file->getSize() / 1024, 2) }} KB)</small>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light" wire:click="removeSubmissionAttachment({{ $index }})">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($existingSubmission && $existingSubmission->attachments->isNotEmpty())
                            <div class="mb-3">
                                <label class="form-label small">Attachments</label>
                                @foreach($existingSubmission->attachments as $attachment)
                                    <div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2">
                                        <div>
                                            <i class="ri-file-line me-2"></i>
                                            <span>{{ $attachment->original_name }}</span>
                                            <small class="text-muted">({{ $attachment->human_readable_size }})</small>
                                        </div>
                                        <a href="{{ $attachment->url }}" target="_blank" class="btn btn-sm btn-light me-1" title="View">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </div>
                                @endforeach
                                @if(!$isGraded)
                                    <small class="text-muted d-block mt-1">Existing attachments will be kept. Add new files above to attach more.</small>
                                @endif
                            </div>
                        @endif

                        @if(!$isGraded)
                            <div class="mb-3">
                                <label class="btn btn-sm btn-light mb-0" style="cursor: pointer;">
                                    <i class="ri-attachment-line me-1"></i> Attach Files
                                    <input type="file" class="d-none" wire:model="submissionAttachments" multiple>
                                </label>
                                <small class="text-muted d-block mt-1">You can attach multiple files (max 10MB per file)</small>
                            </div>
                        @endif

                        @if($existingSubmission && !$isGraded)
                            <div class="alert alert-info mb-3">
                                <i class="ri-information-line me-2"></i>
                                <strong>Existing Submission:</strong> You have already submitted this assignment. 
                                Submitting again will update your submission.
                            </div>
                        @endif

                        @error('form')
                            <div class="alert alert-danger mt-3">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-light" wire:click="closeSubmissionModal">Close</button>
                            @if(!$isGraded)
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        <i class="ri-send-plane-line me-1"></i>{{ $existingSubmission ? 'Update Submission' : 'Submit Assignment' }}
                                    </span>
                                    <span wire:loading>
                                        <span class="spinner-border spinner-border-sm me-2"></span>{{ $existingSubmission ? 'Updating...' : 'Submitting...' }}
                                    </span>
                                </button>
                            @endif
                        </div>
                    </form>
                @endif
            @endif
        </x-modal>


         <!-- Attachment Viewer Modal -->
         <x-modal wire:model="showAttachmentModal" size="xl" :title="$selectedAttachment ? $selectedAttachment->original_name : 'View Attachment'" :showFooter="false">
            @if($selectedAttachment)
                <div class="attachment-viewer">
                    @if($selectedAttachment->isImage())
                        <!-- Display images directly -->
                        <div class="text-center">
                            <img src="{{ $selectedAttachmentUrl }}" 
                                alt="{{ $selectedAttachment->original_name }}" 
                                class="img-fluid rounded"
                                style="max-height: 70vh; width: auto;">
                        </div>
                    @elseif($selectedAttachment->isDocument())
                        <!-- Display documents in iframe -->
                        <div class="border rounded" style="height: 70vh;">
                            <iframe 
                                src="{{ $selectedAttachmentUrl }}" 
                                class="w-100 h-100 border-0 rounded"
                                style="min-height: 70vh;"
                                title="{{ $selectedAttachment->original_name }}">
                                <p>Your browser does not support iframes. 
                                    <a href="{{ $selectedAttachmentUrl }}" target="_blank" class="btn btn-primary">
                                        <i class="ri-download-line me-1"></i>Download File
                                    </a>
                                </p>
                            </iframe>
                        </div>
                    @else
                        <!-- For other file types, show download option -->
                        <div class="text-center py-5">
                            <i class="ri-file-line display-4 text-muted mb-3"></i>
                            <h5>{{ $selectedAttachment->original_name }}</h5>
                            <p class="text-muted mb-3">
                                File size: {{ $selectedAttachment->human_readable_size }}<br>
                                File type: {{ $selectedAttachment->file_type ?? 'Unknown' }}
                            </p>
                            <a href="{{ $selectedAttachmentUrl }}" 
                                target="_blank" 
                                class="btn btn-primary"
                                download="{{ $selectedAttachment->original_name }}">
                                <i class="ri-download-line me-1"></i>Download File
                            </a>
                        </div>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <div class="text-muted small">
                        <i class="ri-file-info-line me-1"></i>
                        Size: {{ $selectedAttachment->human_readable_size }}
                        @if($selectedAttachment->file_type)
                            <span class="mx-2">•</span>
                            Type: {{ $selectedAttachment->file_type }}
                        @endif
                    </div>
                    <div>
                        <a href="{{ $selectedAttachmentUrl }}" 
                            target="_blank" 
                            class="btn btn-sm btn-light me-2"
                            download="{{ $selectedAttachment->original_name }}">
                            <i class="ri-download-line me-1"></i>Download
                        </a>
                        <button type="button" class="btn btn-sm btn-light" wire:click="closeAttachmentModal">
                            Close
                        </button>
                    </div>
                </div>
            @endif
        </x-modal>

    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ri-error-warning-line display-4 text-danger mb-3"></i>
                <h5 class="mb-2">Classroom Not Found</h5>
                <p class="text-muted mb-4">The classroom you're looking for doesn't exist or you don't have permission to
                    view it.</p>
                <a href="{{ url('/assingments') }}" class="btn btn-primary">
                    <i class="ri-arrow-left-line align-bottom me-1"></i> Back to Classes
                </a>
            </div>
        </div>
    @endif
</div>