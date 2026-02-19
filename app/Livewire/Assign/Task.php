<?php

namespace App\Livewire\Assign;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Grading\Classroom;
use App\Models\Grading\ClassroomStudent;
use App\Models\Subject;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\StudentInfo;
use App\Models\Teacher\Teacher;
use App\Models\Teacher\Workload;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Task extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    // Modal state
    public $showCreateModal = false;
    public $editingClassroom = null;
    
    // Delete modal state
    public $showDeleteModal = false;
    public $deleteClassroomId = null;
    public $deleteClassroomName = null;

    // Form fields - Class Information
    public $name = ''; // Class name (required)
    public $description = '';
    public $subject_id = null;
    public $subject_type = null;
    public $subject_name = ''; // For display/search
    public $section_id = null;
    public $semester_id = null;
    public $room = '';

    // Enrollment Settings
    public $allow_student_posts = true;
    public $allow_student_comments = true;
    public $students_can_see_each_other = true;
    public $guardians_can_see_updates = false;

    // Data for dropdowns
    public $semesters = [];
    public $sections = [];
    public $availableSubjects = []; // Subjects from Subject model

    // Loading states
    public $isLoading = false;
    
    // Search
    public $search = '';
    
    // User type
    public $isStudent = false;
    public $studentInfo = null;

    public function mount()
    {
        // Check if user is a student
        $this->checkUserType();
        
        // Only load these for teachers
        if (!$this->isStudent) {
            $this->loadActiveSemester();
            $this->loadSemesters();
            $this->loadSubjects();
        }
    }

    public function checkUserType()
    {
        $user = Auth::user();
        if ($user) {
            $this->studentInfo = StudentInfo::where('user_id', $user->id)->first();
            $this->isStudent = $this->studentInfo !== null;
        }
    }

    public function loadSubjects()
    {
        $query = Subject::active()->orderBy('code')->orderBy('name');

        // When user is a teacher, filter subjects by workload (semester + section)
        $user = Auth::user();
        if ($user) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                if (!$this->semester_id || !$this->section_id) {
                    $query->whereRaw('1 = 0'); // Require semester and section to show subjects
                } else {
                    $workloadSubjectIds = Workload::where('teacher_id', $teacher->id)
                        ->where('semester_id', $this->semester_id)
                        ->where('section_id', $this->section_id)
                        ->whereNotNull('subject_id')
                        ->whereHas('semester', function ($q) {
                            $q->where('is_active', true);
                        })
                        ->pluck('subject_id')
                        ->unique()
                        ->values()
                        ->toArray();

                    if (!empty($workloadSubjectIds)) {
                        $query->whereIn('id', $workloadSubjectIds);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                }
            }
        }

        $this->availableSubjects = $query->get();
    }

    public function loadActiveSemester()
    {
        $activeSemester = Semester::where('is_active', true)
            ->where('is_display', true)
            ->first();

        if ($activeSemester) {
            $this->semester_id = $activeSemester->id;
            $this->loadSections();
        }
    }

    public function loadSemesters()
    {
        // Only load semesters with active status for section/workload filtering
        $this->semesters = Semester::where('is_display', true)
            ->where('is_active', true)
            ->orderBy('school_year', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function updatedSemesterId()
    {
        $this->loadSections();
        $this->loadSubjects();
    }

    public function updatedSectionId()
    {
        $this->subject_id = null;
        $this->loadSubjects();
    }

    public function loadSections()
    {
        if (!$this->semester_id) {
            $this->sections = [];
            $this->section_id = null;
            return;
        }

        $query = Section::where('active', true)->orderBy('name', 'asc');

        // When user is a teacher, only load sections from their workload for the selected (active) semester
        $user = Auth::user();
        if ($user) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                // Only consider workloads for semesters with active status
                $workloadSectionIds = Workload::where('teacher_id', $teacher->id)
                    ->where('semester_id', $this->semester_id)
                    ->whereNotNull('section_id')
                    ->whereHas('semester', function ($q) {
                        $q->where('is_active', true);
                    })
                    ->pluck('section_id')
                    ->unique()
                    ->values()
                    ->toArray();

                if (!empty($workloadSectionIds)) {
                    $query->whereIn('id', $workloadSectionIds);
                } else {
                    $query->whereRaw('1 = 0'); // No workload sections = no sections to show
                }
            }
        }

        $this->sections = $query->get();

        // Clear section and subject if section is no longer in the filtered list
        $sectionIds = collect($this->sections)->pluck('id')->toArray();
        if ($this->section_id && !in_array($this->section_id, $sectionIds)) {
            $this->section_id = null;
            $this->subject_id = null;
            $this->subject_name = '';
        }

        $this->loadSubjects();
    }

    public function updatedSubjectId()
    {
        if ($this->subject_id) {
            $subject = Subject::find($this->subject_id);
            if ($subject) {
                $this->subject_name = $subject->name;
                $this->subject_type = Subject::class;
            }
        } else {
            $this->subject_name = '';
            $this->subject_type = null;
        }
    }

    public function openCreateModal()
    {
        $this->editingClassroom = null;
        $this->resetForm();
        $this->loadActiveSemester();
        $this->showCreateModal = true;
    }

    public function editClassroom($classroomId)
    {
        $classroom = Classroom::with(['section', 'semester', 'subject'])->find($classroomId);
        
        if (!$classroom) {
            $this->dispatch('show-toast', [
                'message' => 'Classroom not found.',
                'type' => 'error'
            ]);
            return;
        }

        // Check if user owns this classroom
        if ($classroom->created_by !== Auth::id()) {
            $this->dispatch('show-toast', [
                'message' => 'You do not have permission to edit this classroom.',
                'type' => 'error'
            ]);
            return;
        }

        $this->editingClassroom = $classroom;
        
        // Load classroom data into form
        $this->name = $classroom->name;
        $this->description = $classroom->description ?? '';
        $this->subject_id = $classroom->subject_id;
        $this->subject_type = $classroom->subject_type;
        
        // Load subject name if subject exists
        if ($classroom->subject_id && $classroom->subject_type === Subject::class) {
            $subject = Subject::find($classroom->subject_id);
            $this->subject_name = $subject ? $subject->name : '';
        } else {
            $this->subject_name = '';
        }
        
        $this->semester_id = $classroom->semester_id;
        $this->section_id = $classroom->section_id;
        $this->room = $classroom->room ?? '';
        $this->allow_student_posts = $classroom->allow_student_posts;
        $this->allow_student_comments = $classroom->allow_student_comments;
        $this->students_can_see_each_other = $classroom->students_can_see_each_other;
        $this->guardians_can_see_updates = $classroom->guardians_can_see_updates;

        // Load related data
        $this->loadSemesters();
        $this->loadSections();
        $this->loadSubjects();

        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->editingClassroom = null;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingClassroom = null;
        $this->name = '';
        $this->description = '';
        $this->subject_id = null;
        $this->subject_type = null;
        $this->subject_name = '';
        $this->semester_id = null;
        $this->section_id = null;
        $this->room = '';
        $this->allow_student_posts = true;
        $this->allow_student_comments = true;
        $this->students_can_see_each_other = true;
        $this->guardians_can_see_updates = false;
    }

    public function saveClassroom()
    {
        // Validate form
        $this->validate([
            'name' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'semester_id' => 'required|exists:semesters,id',
            'room' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'Class name is required.',
            'subject_id.required' => 'Please select a subject.',
            'section_id.required' => 'Please select a section.',
            'semester_id.required' => 'Please select a semester.',
        ]);

        // Sync subject_name and subject_type for storage
        if ($this->subject_id) {
            $subject = Subject::find($this->subject_id);
            if ($subject) {
                $this->subject_name = $subject->name;
                $this->subject_type = Subject::class;
            }
        }

        $this->isLoading = true;

        try {
            DB::beginTransaction();

            $isEditing = !is_null($this->editingClassroom);
            $classroomData = [
                'name' => $this->name,
                'description' => $this->description,
                'subject_id' => $this->subject_id,
                'subject_type' => $this->subject_type,
                'section_id' => $this->section_id,
                'semester_id' => $this->semester_id,
                'room' => $this->room,
                'allow_student_posts' => $this->allow_student_posts,
                'allow_student_comments' => $this->allow_student_comments,
                'students_can_see_each_other' => $this->students_can_see_each_other,
                'guardians_can_see_updates' => $this->guardians_can_see_updates,
            ];

            if ($isEditing) {
                // Update existing classroom
                $classroom = $this->editingClassroom;
                $classroom->update($classroomData);
                $message = 'Classroom updated successfully!';
            } else {
                // Create new classroom
                $classroomData['status'] = Classroom::STATUS_ACTIVE;
                $classroomData['created_by'] = Auth::id();
                $classroom = Classroom::create($classroomData);
                $message = 'Classroom created successfully!';
            }

            // Auto-enroll students from the selected section and semester
            $enrolledStudents = $this->enrollStudentsToClassroom($classroom);

            // Create notifications for enrolled students (only for new classrooms)
            if (!$isEditing && $enrolledStudents->isNotEmpty()) {
                $this->createClassroomNotifications($classroom, $enrolledStudents);
            }

            DB::commit();

            $this->dispatch('show-toast', [
                'message' => $message,
                'type' => 'success'
            ]);

            $this->closeCreateModal();
            $this->dispatch('classroom-created');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('form', 'An error occurred while saving the classroom: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function deleteClassroom($classroomId)
    {
        $classroom = Classroom::find($classroomId);
        
        if (!$classroom) {
            $this->dispatch('show-toast', [
                'message' => 'Classroom not found.',
                'type' => 'error'
            ]);
            return;
        }

        // Check if user owns this classroom
        if ($classroom->created_by !== Auth::id()) {
            $this->dispatch('show-toast', [
                'message' => 'You do not have permission to delete this classroom.',
                'type' => 'error'
            ]);
            return;
        }

        $this->deleteClassroomId = $classroom->id;
        $this->deleteClassroomName = $classroom->name;
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        if ($this->deleteClassroomId) {
            try {
                DB::beginTransaction();
                
                $classroom = Classroom::findOrFail($this->deleteClassroomId);
                
                // Check if user owns this classroom
                if ($classroom->created_by !== Auth::id()) {
                    throw new \Exception('You do not have permission to delete this classroom.');
                }
                
                $classroomName = $classroom->name;
                
                // Delete the classroom (cascade will handle pivot records)
                $classroom->delete();
                
                DB::commit();
                
                $this->closeDeleteModal();
                
                $this->dispatch('show-toast', [
                    'message' => 'Classroom "' . $classroomName . '" deleted successfully!',
                    'type' => 'success'
                ]);
                
                // Reset pagination if needed
                $this->resetPage();
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->dispatch('show-toast', [
                    'message' => 'An error occurred while deleting the classroom: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
            }
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteClassroomId = null;
        $this->deleteClassroomName = null;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Enroll students to classroom based on section and semester.
     * 
     * @param Classroom $classroom
     * @return \Illuminate\Support\Collection
     */
    protected function enrollStudentsToClassroom(Classroom $classroom)
    {
        if (!$classroom->section_id || !$classroom->semester_id) {
            return collect([]);
        }

        // Get all students from the selected section
        $allStudents = StudentInfo::where('section_id', $classroom->section_id)
            ->whereIn('status', ['enrolled', 'enroll'])
            ->get();

        // Filter students whose semester JSON array contains the matching semester ID
        $eligibleStudents = $allStudents->filter(function($student) use ($classroom) {
            if (empty($student->semester) || !is_array($student->semester)) {
                return false;
            }
            // Check if any semester in the array has the matching id
            foreach ($student->semester as $sem) {
                if (isset($sem['id']) && (int)$sem['id'] === (int)$classroom->semester_id) {
                    return true;
                }
            }
            return false;
        });

        // Sync students to classroom_student pivot table
        if ($eligibleStudents->isNotEmpty()) {
            $studentIds = $eligibleStudents->pluck('id')->toArray();
            
            // Prepare pivot data with enrollment details
            $pivotData = [];
            foreach ($studentIds as $studentId) {
                $pivotData[$studentId] = [
                    'role' => 'student',
                    'status' => 'enrolled',
                    'enrolled_at' => now(),
                ];
            }
            
            // Check if classroom already has students (for edit case)
            // Use student_info_id from pivot to avoid ambiguous column error
            $existingStudents = ClassroomStudent::where('classroom_id', $classroom->id)
                ->pluck('student_info_id')
                ->toArray();
            
            if (empty($existingStudents)) {
                // New classroom - use sync to set initial students
                $classroom->students()->sync($pivotData);
            } else {
                // Existing classroom - use syncWithoutDetaching to add new students without removing existing
                $classroom->students()->syncWithoutDetaching($pivotData);
            }
        }

        return $eligibleStudents;
    }

    /**
     * Create notifications for students when a new classroom is created.
     * 
     * @param Classroom $classroom
     * @param \Illuminate\Support\Collection $students
     * @return void
     */
    protected function createClassroomNotifications(Classroom $classroom, $students)
    {
        $creator = Auth::user();
        $creatorName = !empty($creator->first_name) || !empty($creator->last_name)
            ? trim(($creator->first_name ?? '') . ' ' . ($creator->last_name ?? ''))
            : ($creator->name ?? 'Teacher');

        foreach ($students as $student) {
            if ($student->user_id) {
                Notification::create([
                    'user_id' => $student->user_id,
                    'type' => 'classroom_created',
                    'title' => 'New Class Created',
                    'body' => "You have been enrolled in a new class: {$classroom->name} by {$creatorName}",
                    'url' => url('/assingments.index?id=' . $classroom->id),
                    'notifiable_type' => Classroom::class,
                    'notifiable_id' => $classroom->id,
                    'data' => [
                        'classroom_id' => $classroom->id,
                        'classroom_name' => $classroom->name,
                        'class_code' => $classroom->class_code,
                        'creator_id' => $creator->id,
                        'creator_name' => $creatorName,
                    ],
                ]);
            }
        }
    }

    public function render()
    {
        if ($this->isStudent && $this->studentInfo) {
            // For students: show classrooms they are enrolled in
            $query = Classroom::with([
                    'section', 
                    'semester', 
                    'subject',
                    'creator'
                ])
                ->whereHas('students', function($q) {
                    $q->where('student_infos.id', $this->studentInfo->id)
                      ->where('classroom_student.status', 'enrolled');
                });
        } else {
            // For teachers: show classrooms they created
            $query = Classroom::with([
                    'section', 
                    'semester', 
                    'subject',
                    'creator'
                ])
                ->where('created_by', Auth::id());
        }

        // Apply search filter
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('class_code', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('room', 'like', $searchTerm)
                  ->orWhereHas('subject', function($subjectQuery) use ($searchTerm) {
                      $subjectQuery->where('name', 'like', $searchTerm)
                                   ->orWhere('code', 'like', $searchTerm);
                  })
                  ->orWhereHas('section', function($sectionQuery) use ($searchTerm) {
                      $sectionQuery->where('name', 'like', $searchTerm);
                  })
                  ->orWhereHas('creator', function($creatorQuery) use ($searchTerm) {
                      $creatorQuery->where('name', 'like', $searchTerm)
                                    ->orWhere('first_name', 'like', $searchTerm)
                                    ->orWhere('last_name', 'like', $searchTerm);
                  });
            });
        }

        $classrooms = $query->latest()->paginate(12);

        return view('livewire.assign.task', [
            'classrooms' => $classrooms,
            'isStudent' => $this->isStudent,
        ]);
    }
}
