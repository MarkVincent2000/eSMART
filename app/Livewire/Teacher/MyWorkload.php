<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher\Teacher;
use App\Models\Teacher\Workload;
use App\Models\Subject;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\StudentInfo;
use App\Models\Grading\Classroom;
use Livewire\Attributes\Computed;

class MyWorkload extends Component
{
    use WithPagination;

    /**
     * Search term for filtering workload entries.
     *
     * @var string
     */
    public $search = '';

    /**
     * Modal state for creating/editing workload.
     *
     * @var bool
     */
    public $showCreateModal = false;
    public $showDeleteWorkloadModal = false;
    public $showViewStudentsModal = false;
    public $selectedWorkloadId = null;

    /**
     * Print modal state and filters.
     *
     * @var bool|int|string|null
     */
    public $showPrintModal = false;
    public $printStep = 1;
    public $printUrl = '';
    public $printSectionId = null;
    public $printSemesterId = null;
    public $printSubjectId = null;

    // Form fields for workload creation
    public $workloadId = null;
    public $gradeLevel = null;
    public $subjectId = null;
    public $sectionId = null;
    public $semesterId = null;
    public $loadUnits = '';
    public $scheduleText = '';
    public $room = '';

    // Delete state
    public $deleteWorkloadId = null;
    public $deleteWorkloadLabel = null;

    /**
     * Get grade level options from YearLevel enum.
     */
    #[Computed]
    public function gradeLevelOptions(): array
    {
        return \App\Enums\YearLevel::options();
    }

    /**
     * Get section options - filtered by selected grade level.
     */
    #[Computed]
    public function sectionOptions(): array
    {
        $query = Section::orderBy('year_level')
            ->orderBy('name')
            ->select('id', 'name', 'year_level');
        
        // Filter by grade level if selected
        if ($this->gradeLevel) {
            $query->where('year_level', $this->gradeLevel);
        }
        
        return $query->get()
            ->map(function ($section) {
                $label = $section->name;
                if ($section->year_level) {
                    $gradeLevel = $section->year_level instanceof \App\Enums\YearLevel 
                        ? $section->year_level->label() 
                        : 'Grade ' . $section->year_level;
                    $label .= ' (' . $gradeLevel . ')';
                }
                return [
                    'value' => $section->id,
                    'label' => $label,
                ];
            })
            ->toArray();
    }

    /**
     * Get subject options - filtered by selected section's grade level.
     * Excludes subjects already added as workloads for the current teacher in the selected semester.
     */
    #[Computed]
    public function subjectOptions(): array
    {
        $query = Subject::orderBy('name')
            ->select('id', 'name', 'code', 'units', 'year_level');
        
        // Filter by section's grade level if section is selected
        if ($this->sectionId) {
            $section = Section::find($this->sectionId);
            if ($section && $section->year_level) {
                // Get the integer value from YearLevel enum
                $sectionYearLevel = $section->year_level instanceof \App\Enums\YearLevel
                    ? $section->year_level->value
                    : $section->year_level;
                
                // Filter subjects: include subjects with matching year_level OR null year_level (available for all levels)
                $query->where(function ($q) use ($sectionYearLevel) {
                    $q->where('year_level', $sectionYearLevel)
                      ->orWhereNull('year_level');
                });
            }
        }
        
        // Get semester ID (use selected or active semester)
        $semesterId = $this->semesterId;
        if (!$semesterId) {
            $semesterId = Semester::where('is_active', true)
                ->orderBy('start_date', 'desc')
                ->value('id');
        }
        
        // Exclude subjects already added as workloads for the current teacher in this semester
        if ($semesterId) {
            $user = Auth::user();
            if ($user) {
                $teacher = Teacher::where('user_id', $user->id)->first();
                if ($teacher) {
                    // Get subject IDs that already have workloads for this teacher and semester
                    $existingSubjectIds = Workload::where('teacher_id', $teacher->id)
                        ->where('semester_id', $semesterId)
                        ->whereNotNull('subject_id')
                        ->when($this->workloadId, function ($q) {
                            // When editing, exclude the current workload's subject from the exclusion list
                            // so it can still be selected
                            $q->where('id', '!=', $this->workloadId);
                        })
                        ->pluck('subject_id')
                        ->toArray();
                    
                    // Exclude those subjects from the query
                    if (!empty($existingSubjectIds)) {
                        $query->whereNotIn('id', $existingSubjectIds);
                    }
                }
            }
        }
        
        return $query->get()
            ->map(function ($subject) {
                $code = $subject->code ?? '';
                $name = $subject->name ?? 'Unknown Subject';
                
                // Build label: Code - Name or just Name if no code
                $label = $code ? "{$code} - {$name}" : $name;
                
                return [
                    'value' => $subject->id,
                    'label' => $label,
                    'units' => $subject->units,
                ];
            })
            ->toArray();
    }

    /**
     * Reset pagination when the search term changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Open the create workload modal.
     */
    public function openCreateModal(): void
    {
        $this->resetForm();

        // Pre-select the current active semester (if any) and make it read-only in the UI.
        $this->semesterId = Semester::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->value('id');

        $this->showCreateModal = true;
    }

    /**
     * Close the create workload modal.
     */
    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    /**
     * When grade level changes, reset section and subject selections.
     */
    public function updatedGradeLevel($value): void
    {
        $this->sectionId = null;
        $this->subjectId = null;
        $this->loadUnits = '';
    }

    /**
     * When section changes, reset subject selection.
     */
    public function updatedSectionId($value): void
    {
        $this->subjectId = null;
        $this->loadUnits = '';
    }

    /**
     * When subject selection changes, auto-populate load units.
     */
    public function updatedSubjectId($value): void
    {
        if ($value) {
            $subject = Subject::find($value);
            if ($subject && $subject->units !== null) {
                // Preload units only if user hasn't typed anything yet
                if ($this->loadUnits === '' || $this->loadUnits === null) {
                    $this->loadUnits = (string) $subject->units;
                }
            }
        } else {
            $this->loadUnits = '';
        }
    }

    /**
     * Reset workload form fields.
     */
    public function resetForm(): void
    {
        $this->workloadId = null;
        $this->gradeLevel = null;
        $this->subjectId = null;
        $this->sectionId = null;
        $this->semesterId = null;
        $this->loadUnits = '';
        $this->scheduleText = '';
        $this->room = '';
        $this->resetErrorBag();
    }

    /**
     * Open the print workloads modal.
     */
    public function printWorkloads(): void
    {
        $this->printStep = 1;
        $this->printUrl = '';
        $this->printSectionId = null;
        $this->printSemesterId = null;
        $this->printSubjectId = null;
        $this->resetErrorBag();
        $this->showPrintModal = true;
    }

    /**
     * Close the print workloads modal and reset state.
     */
    public function closePrintModal(): void
    {
        $this->showPrintModal = false;
        $this->printStep = 1;
        $this->printUrl = '';
        $this->printSectionId = null;
        $this->printSemesterId = null;
        $this->printSubjectId = null;
        $this->resetErrorBag();
    }

    /**
     * Go to next print step and generate preview URL.
     */
    public function nextPrintStep(): void
    {
        $params = [];

        if ($this->printSectionId) {
            $params['section_id'] = (int) $this->printSectionId;
        }

        if ($this->printSemesterId) {
            $params['semester_id'] = (int) $this->printSemesterId;
        }

        if ($this->printSubjectId) {
            $params['subject_id'] = (int) $this->printSubjectId;
        }

        $this->printUrl = route('teacher.workloads.print') . (!empty($params) ? '?' . http_build_query($params) : '');
        $this->printStep = 2;
    }

    /**
     * Go back to filter step in print modal.
     */
    public function previousPrintStep(): void
    {
        $this->printStep = 1;
        $this->printUrl = '';
    }

    /**
     * Get a human-readable summary of selected print filters.
     */
    #[Computed]
    public function printFilterSummary(): array
    {
        $filters = [];

        if ($this->printSectionId) {
            $section = Section::find($this->printSectionId);
            if ($section) {
                $label = $section->name;
                if ($section->year_level) {
                    $gradeLevel = $section->year_level instanceof \App\Enums\YearLevel
                        ? $section->year_level->label()
                        : 'Grade ' . $section->year_level;
                    $label .= ' (' . $gradeLevel . ')';
                }
                $filters['Section'] = $label;
            }
        }

        if ($this->printSemesterId) {
            $semester = Semester::find($this->printSemesterId);
            if ($semester) {
                $label = $semester->name;
                if ($semester->school_year) {
                    $label .= ' (' . $semester->school_year . ')';
                }
                $filters['Semester'] = $label;
            }
        }

        if ($this->printSubjectId) {
            $subject = Subject::find($this->printSubjectId);
            if ($subject) {
                $filters['Subject'] = $subject->display_name ?? ($subject->code ? $subject->code . ' - ' . $subject->name : $subject->name);
            }
        }

        return $filters;
    }

    /**
     * Load an existing workload into the form for editing.
     */
    public function editWorkload(int $workloadId): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $workload = Workload::with(['subject', 'section', 'semester', 'classroom', 'teacher'])
            ->findOrFail($workloadId);

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        if (!$isSuperAdmin) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher || $workload->teacher_id !== $teacher->id) {
                $this->dispatch('show-toast', [
                    'message' => 'You are not allowed to edit this workload.',
                    'type' => 'error',
                    'title' => 'Unauthorized',
                ]);
                return;
            }
        }

        $this->resetForm();

        $this->workloadId = $workload->id;
        
        // Set grade level from section first (this enables section dropdown)
        if ($workload->section && $workload->section->year_level) {
            $this->gradeLevel = $workload->section->year_level instanceof \App\Enums\YearLevel
                ? $workload->section->year_level->value
                : $workload->section->year_level;
        }
        
        // Set section ID (this enables subject dropdown)
        $this->sectionId = $workload->section_id;
        
        // Set subject ID
        $this->subjectId = $workload->subject_id;
        
        // Set semester ID
        $this->semesterId = $workload->semester_id;
        
        // Set load units
        $this->loadUnits = $workload->load_units !== null ? (string) $workload->load_units : '';

        $schedule = is_array($workload->schedule) ? $workload->schedule : [];
        $this->scheduleText = $schedule['text'] ?? '';
        $this->room = $schedule['room'] ?? '';

        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    /**
     * Store a new workload entry for the authenticated teacher.
     */
    public function saveWorkload(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->addError('subjectId', 'You must be logged in to add a workload.');
            return;
        }

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        $teacher = null;
        if (!$isSuperAdmin) {
            $teacher = Teacher::where('user_id', $user->id)->first();

            if (!$teacher) {
                $this->addError('subjectId', 'No teacher profile found for your account.');
                return;
            }
        }

        $rules = [
            'subjectId' => ['required', 'integer', 'exists:subjects,id'],
            'sectionId' => ['nullable', 'integer', 'exists:sections,id'],
            'semesterId' => ['nullable', 'integer', 'exists:semesters,id'],
            'loadUnits' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'scheduleText' => ['nullable', 'string', 'max:1000'],
            'room' => ['nullable', 'string', 'max:255'],
        ];

        $this->validate($rules);

        $schedule = [];
        if (!empty($this->scheduleText)) {
            $schedule['text'] = $this->scheduleText;
        }
        if (!empty($this->room)) {
            $schedule['room'] = $this->room;
        }
        if (empty($schedule)) {
            $schedule = null;
        }

        if ($this->workloadId) {
            // Update existing workload
            $workload = Workload::findOrFail($this->workloadId);

            if (!$isSuperAdmin) {
                if ($workload->teacher_id !== $teacher->id) {
                    $this->addError('subjectId', 'You are not allowed to edit this workload.');
                    return;
                }
            }

            $workload->update([
                'subject_id' => $this->subjectId,
                'subject_type' => Subject::class,
                'section_id' => $this->sectionId ?: null,
                'semester_id' => $this->semesterId ?: null,
                'classroom_id' => null,
                'load_units' => $this->loadUnits !== '' ? (float) $this->loadUnits : null,
                'schedule' => $schedule,
            ]);

            $message = 'Workload updated successfully!';
        } else {
            // Only non-super-admin teachers can create workload in this screen
            if ($isSuperAdmin) {
                $this->addError('subjectId', 'Super-admin cannot add workload from this screen.');
                return;
            }

            // Create a single workload for the selected subject
            Workload::create([
                'teacher_id' => $teacher->id,
                'subject_id' => $this->subjectId,
                'subject_type' => Subject::class,
                'section_id' => $this->sectionId ?: null,
                'semester_id' => $this->semesterId ?: null,
                'classroom_id' => null,
                'load_units' => $this->loadUnits !== '' ? (float) $this->loadUnits : null,
                'schedule' => $schedule,
            ]);

            $message = 'Workload added successfully!';
        }

        $this->closeCreateModal();
        $this->resetPage();

        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success',
        ]);
    }

    /**
     * Open delete confirmation modal for a workload.
     */
    public function deleteWorkload(int $workloadId): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $workload = Workload::with(['teacher.user', 'subject'])->find($workloadId);
        if (!$workload) {
            $this->dispatch('show-toast', [
                'message' => 'Workload not found.',
                'type' => 'error',
                'title' => 'Error',
            ]);
            return;
        }

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        if (!$isSuperAdmin) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher || $workload->teacher_id !== $teacher->id) {
                $this->dispatch('show-toast', [
                    'message' => 'You are not allowed to delete this workload.',
                    'type' => 'error',
                    'title' => 'Unauthorized',
                ]);
                return;
            }
        }

        $subjectLabel = $workload->subject->name ?? 'Subject';
        $teacherName = $workload->teacher->user->name ?? 'Teacher';

        $this->deleteWorkloadId = $workload->id;
        $this->deleteWorkloadLabel = $subjectLabel . ' - ' . $teacherName;
        $this->showDeleteWorkloadModal = true;
    }

    /**
     * Confirm and delete the workload.
     */
    public function confirmDeleteWorkload(): void
    {
        if (!$this->deleteWorkloadId) {
            return;
        }

        $user = Auth::user();
        if (!$user) {
            return;
        }

        $workload = Workload::with('teacher')->findOrFail($this->deleteWorkloadId);

        $isSuperAdmin = method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        if (!$isSuperAdmin) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher || $workload->teacher_id !== $teacher->id) {
                $this->dispatch('show-toast', [
                    'message' => 'You are not allowed to delete this workload.',
                    'type' => 'error',
                    'title' => 'Unauthorized',
                ]);
                return;
            }
        }

        $workload->delete();

        $this->closeDeleteWorkloadModal();
        $this->resetPage();

        $this->dispatch('show-toast', [
            'message' => 'Workload deleted successfully!',
            'type' => 'success',
            'title' => 'Workload Deleted',
        ]);
    }

    /**
     * Close delete confirmation modal and reset delete state.
     */
    public function closeDeleteWorkloadModal(): void
    {
        $this->showDeleteWorkloadModal = false;
        $this->deleteWorkloadId = null;
        $this->deleteWorkloadLabel = null;
    }

    /**
     * Open view students modal for a workload.
     */
    public function viewStudents(int $workloadId): void
    {
        $workload = Workload::with(['section', 'subject'])->findOrFail($workloadId);

        if (!$workload->section_id) {
            $this->dispatch('show-toast', [
                'message' => 'This workload does not have a section assigned.',
                'type' => 'warning',
                'title' => 'No Section',
            ]);
            return;
        }

        $this->selectedWorkloadId = $workloadId;
        $this->showViewStudentsModal = true;
    }

    /**
     * Close view students modal.
     */
    public function closeViewStudentsModal(): void
    {
        $this->showViewStudentsModal = false;
        $this->selectedWorkloadId = null;
    }

    /**
     * Get the selected workload for viewing students.
     */
    #[Computed]
    public function selectedWorkload()
    {
        if (!$this->selectedWorkloadId) {
            return null;
        }

        return Workload::with(['section', 'subject', 'semester'])->find($this->selectedWorkloadId);
    }

    /**
     * Get enrolled students for the selected workload's section.
     */
    #[Computed]
    public function enrolledStudents()
    {
        if (!$this->selectedWorkloadId) {
            return collect();
        }

        $workload = Workload::find($this->selectedWorkloadId);

        if (!$workload || !$workload->section_id) {
            return collect();
        }

        return StudentInfo::with(['user', 'program', 'section'])
            ->where('section_id', $workload->section_id)
            ->where('status', 'enrolled')
            ->orderBy('student_number')
            ->get();
    }

    public function render()
    {
        $user = Auth::user();
        $isSuperAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('super-admin');

        // Default: empty paginator
        $workloads = Workload::whereRaw('1 = 0')->paginate(10);
        $canAddWorkload = false;

        if ($user) {
            if ($isSuperAdmin) {
                // Super-admin: see all workloads, with teacher name
                $query = Workload::with(['teacher.user', 'subject', 'section', 'semester', 'classroom']);

                if (!empty($this->search)) {
                    $search = '%' . $this->search . '%';
                    $query->where(function ($q) use ($search) {
                        $q->whereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery->where('name', 'like', $search)
                                ->orWhere('code', 'like', $search);
                        })
                        ->orWhereHas('section', function ($sectionQuery) use ($search) {
                            $sectionQuery->where('name', 'like', $search);
                        })
                        ->orWhereHas('semester', function ($semesterQuery) use ($search) {
                            $semesterQuery->where('name', 'like', $search)
                                ->orWhere('school_year', 'like', $search);
                        })
                        ->orWhereHas('classroom', function ($classroomQuery) use ($search) {
                            $classroomQuery->where('name', 'like', $search)
                                ->orWhere('class_code', 'like', $search);
                        })
                        ->orWhereHas('teacher.user', function ($teacherQuery) use ($search) {
                            $teacherQuery->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        });
                    });
                }

                $workloads = $query->latest()->paginate(10);
                $canAddWorkload = false; // super-admin views only here
            } else {
                // Regular teacher: see and add only their own workloads
                $teacher = Teacher::where('user_id', $user->id)->first();

                if ($teacher) {
                    $query = Workload::with(['subject', 'section', 'semester', 'classroom'])
                        ->where('teacher_id', $teacher->id);

                    if (!empty($this->search)) {
                        $search = '%' . $this->search . '%';
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('subject', function ($subjectQuery) use ($search) {
                                $subjectQuery->where('name', 'like', $search)
                                    ->orWhere('code', 'like', $search);
                            })
                            ->orWhereHas('section', function ($sectionQuery) use ($search) {
                                $sectionQuery->where('name', 'like', $search);
                            })
                            ->orWhereHas('semester', function ($semesterQuery) use ($search) {
                                $semesterQuery->where('name', 'like', $search)
                                    ->orWhere('school_year', 'like', $search);
                            })
                            ->orWhereHas('classroom', function ($classroomQuery) use ($search) {
                                $classroomQuery->where('name', 'like', $search)
                                    ->orWhere('class_code', 'like', $search);
                            });
                        });
                    }

                    $workloads = $query->latest()->paginate(10);
                    $canAddWorkload = true;
                }
            }
        }

        $semesterOptions = Semester::where('is_active', true)
            ->orderBy('start_date', 'desc')
            ->select('id', 'name', 'school_year')
            ->get()
            ->map(function ($semester) {
                $label = $semester->name;
                if ($semester->school_year) {
                    $label .= ' (' . $semester->school_year . ')';
                }
                return [
                    'value' => $semester->id,
                    'label' => $label,
                ];
            })
            ->toArray();

        return view('livewire.teacher.my-workload', [
            'workloads' => $workloads,
            'isSuperAdmin' => $isSuperAdmin,
            'canAddWorkload' => $canAddWorkload,
            'semesterOptions' => $semesterOptions,
        ]);
    }
}
