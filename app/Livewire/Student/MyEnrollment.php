<?php

namespace App\Livewire\Student;

use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\StudentInfo;
use App\Models\StudentDetails\Program;
use App\Models\StudentDetails\Section;
use App\Models\Notification;
use App\Models\User;
use App\Enums\YearLevel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class MyEnrollment extends Component
{
    use WithPagination;
    
    public $activeSemester;
    public $hasEnrollment = false;
    public $studentInfo = null;
    
    // Student Info properties for tabs
    public $studentSearch = '';
    public $studentStatus = 'all'; // 'all', 'pending', 'enrolled', 'inactive', 'graduated'
    
    /**
     * Sync properties with URL query string
     */
    protected $queryString = [
        'studentStatus' => ['except' => 'all'],
        'studentSearch' => ['except' => ''],
    ];
    
    // Enrollment modal state
    public $showEnrollModal = false;
    
    // View enrollment modal state
    public $showViewEnrollmentModal = false;
    public $selectedStudentInfoId = null;
    
    // Enrollment form fields
    public $studentNumber = '';
    public $programId = null;
    public $yearLevel = null;
    public $sectionId = null;

    public function mount()
    {
        $this->loadActiveSemester();
        $this->checkEnrollment();
    }

    public function loadActiveSemester()
    {
        // Load the active semester together with its quarters
        $this->activeSemester = Semester::with(['quarters' => function ($query) {
            $query->orderBy('name');
        }])->where('is_active', true)->first();
    }

    public function checkEnrollment()
    {
        $user = Auth::user();
        
        // Check if user has role 'user'
        if ($user && $user->hasRole('user')) {
            // Check if there's an active semester
            if ($this->activeSemester) {
                // Rule: every grade level can enroll only once per school year.
                // Find the most recent enrollment for the active semester's school year.
                $enrollment = StudentInfo::with(['program'])
                    ->where('user_id', $user->id)
                    ->where('school_year', $this->activeSemester->school_year)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($enrollment) {
                    $this->studentInfo = $enrollment;
                    $this->hasEnrollment = true;
                } else {
                    $this->studentInfo = null;
                    $this->hasEnrollment = false;
                }
            } else {
                // No active semester, so no enrollment possible
                $this->studentInfo = null;
                $this->hasEnrollment = false;
            }
        }
    }

    public function canEnroll($yearLevel)
    {
        if (!$this->activeSemester) {
            return false;
        }

        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Rule: every grade level can enroll only once per school year.
        // Check if there's already an enrollment for the active semester's school year.
        $alreadyEnrolledInThisSchoolYear = StudentInfo::where('user_id', $user->id)
            ->where('school_year', $this->activeSemester->school_year)
            ->exists();

        return !$alreadyEnrolledInThisSchoolYear;
    }

    public function enrollNow()
    {
        if (!$this->activeSemester) {
            $this->dispatch('show-toast', [
                'message' => 'No active semester available for enrollment.',
                'type' => 'error',
                'title' => 'Enrollment Failed'
            ]);
            return;
        }
        
        $this->resetEnrollmentForm();
        $this->showEnrollModal = true;
    }

    public function closeEnrollModal()
    {
        $this->showEnrollModal = false;
        $this->resetEnrollmentForm();
    }

    public function viewEnrollment($studentInfoId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return;
        }
        
        // Verify the enrollment belongs to the current user
        $studentInfo = StudentInfo::with(['user', 'program', 'section', 'semester'])
            ->where('id', $studentInfoId)
            ->where('user_id', $user->id)
            ->first();
            
        if (!$studentInfo) {
            $this->dispatch('show-toast', [
                'message' => 'Enrollment not found.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }
        
        $this->selectedStudentInfoId = $studentInfoId;
        $this->showViewEnrollmentModal = true;
    }

    public function closeViewEnrollmentModal()
    {
        $this->showViewEnrollmentModal = false;
        $this->selectedStudentInfoId = null;
    }

    #[Computed]
    public function selectedStudentInfo()
    {
        if (!$this->selectedStudentInfoId) {
            return null;
        }
        
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        
        return StudentInfo::with(['user', 'program', 'section'])
            ->where('id', $this->selectedStudentInfoId)
            ->where('user_id', $user->id)
            ->first();
    }

    public function resetEnrollmentForm()
    {
        $this->studentNumber = '';
        $this->programId = null;
        $this->yearLevel = null;
        $this->sectionId = null;
        $this->resetErrorBag();
    }


    public function updatedYearLevel()
    {
        // Reset section when year level changes
        $this->sectionId = null;
    }

    #[Computed]
    public function programs()
    {
        return Program::where('active', true)
            ->orderBy('code')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function programOptions()
    {
        return $this->programs->map(function($program) {
            return [
                'value' => $program->id,
                'label' => $program->code . ' - ' . $program->name
            ];
        })->toArray();
    }

    #[Computed]
    public function sections()
    {
        if (!$this->yearLevel) {
            return collect([]);
        }
        
        return Section::where('active', true)
            ->where('year_level', $this->yearLevel)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function sectionOptions()
    {
        return $this->sections->map(function($section) {
            return [
                'value' => $section->id,
                'label' => $section->name
            ];
        })->toArray();
    }

    public function saveEnrollment()
    {
        $user = Auth::user();
        
        if (!$user) {
            $this->dispatch('show-toast', [
                'message' => 'You must be logged in to enroll.',
                'type' => 'error',
                'title' => 'Enrollment Failed'
            ]);
            return;
        }
        
        if (!$this->activeSemester) {
            $this->dispatch('show-toast', [
                'message' => 'No active semester available for enrollment.',
                'type' => 'error',
                'title' => 'Enrollment Failed'
            ]);
            return;
        }
        
        // Validation - Program is required for Grade 11-12, optional for Grade 7-10
        $rules = [
            'studentNumber' => 'required|string|max:255',
            'yearLevel' => 'required|integer|in:' . implode(',', YearLevel::values()),
            'programId' => $this->yearLevel >= 11 ? 'required|exists:programs,id' : 'nullable|exists:programs,id',
            // Section is required and must belong to the selected year level
            'sectionId' => 'required|exists:sections,id,year_level,' . $this->yearLevel,
        ];
        
        $this->validate($rules, [
            'studentNumber.required' => 'Student number is required.',
            'yearLevel.required' => 'Year level is required.',
            'yearLevel.in' => 'Year level must be between Grade 7 and Grade 12.',
            'programId.required' => 'Program is required for Grade 11-12 students.',
            'programId.exists' => 'Selected program is invalid.',
            'sectionId.exists' => 'Selected section is invalid.',
        ]);
        
        // Check enrollment eligibility based on school year rule
        if (!$this->canEnroll($this->yearLevel)) {
            // User is already enrolled in this school year for some semester
            $this->dispatch('show-toast', [
                'message' => 'You are already enrolled for school year ' . $this->activeSemester->school_year . '. Students can only enroll once per school year.',
                'type' => 'warning',
                'title' => 'Already Enrolled'
            ]);
            return;
        }
        
        // Build semester JSON: include all semesters for this school year (e.g. 1st and 2nd)
        $semestersForYear = Semester::where('school_year', $this->activeSemester->school_year)
            ->orderBy('id')
            ->get()
            ->map(function ($semester) {
                return [
                    'id' => $semester->id,
                    'name' => $semester->name,
                    'school_year' => $semester->school_year,
                ];
            })
            ->toArray();

        // Create StudentInfo record
        $studentInfoData = [
            'user_id' => $user->id,
            'student_number' => $this->studentNumber,
            'year_level' => $this->yearLevel,
            'section_id' => $this->sectionId ?: null,
            'semester' => $semestersForYear,
            'school_year' => $this->activeSemester->school_year,
            'status' => 'pending',
            'enrolled_at' => now(),
        ];
        
        // Only include program_id if it's provided
        if ($this->programId) {
            $studentInfoData['program_id'] = $this->programId;
        } else {
            $studentInfoData['program_id'] = null;
        }
        
        $studentInfo = StudentInfo::create($studentInfoData);

        // Notify all admins and super-admins about the new enrollment
        try {
            $adminUsers = User::role(['super-admin', 'admin'])->get();

            if ($adminUsers->isNotEmpty()) {
                $title = 'New Enrollment Submitted';
                $body = "A new enrollment has been submitted by {$user->name} "
                      . "for school year {$this->activeSemester->school_year}.";

                foreach ($adminUsers as $admin) {
                    Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'enrollment_submitted',
                        'title' => $title,
                        'body' => $body,
                        'url' => '/enrollment.manage-enroll-index',
                        'data' => [
                            'student_info_id' => $studentInfo->id,
                            'student_number' => $studentInfo->student_number,
                            'student_user_id' => $studentInfo->user_id,
                            'program_id' => $studentInfo->program_id,
                            'section_id' => $studentInfo->section_id,
                            'year_level' => $studentInfo->year_level,
                            'status' => $studentInfo->status,
                            'school_year' => $studentInfo->school_year,
                        ],
                        'notifiable_id' => $studentInfo->id,
                        'notifiable_type' => StudentInfo::class,
                        'read_at' => null,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Swallow notification errors to not break enrollment flow
        }
        
        $this->closeEnrollModal();
        $this->checkEnrollment();
        $this->resetPage(); // Reset pagination for studentInfos
        
        $this->dispatch('show-toast', [
            'message' => 'Enrollment submitted successfully! Your enrollment is pending approval.',
            'type' => 'success',
            'title' => 'Enrollment Submitted'
        ]);
    }

    #[Computed]
    public function studentInfos()
    {
        $user = Auth::user();
        
        if (!$user) {
            return collect([])->paginate(10);
        }
        
        $query = StudentInfo::with(['user', 'program', 'section'])
            ->where('user_id', $user->id);
        
        // Search filter
        if ($this->studentSearch) {
            $query->where(function($q) {
                $q->where('student_number', 'like', '%' . $this->studentSearch . '%')
                  ->orWhere('school_year', 'like', '%' . $this->studentSearch . '%')
                  ->orWhereHas('program', function($programQuery) {
                      $programQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                   ->orWhere('code', 'like', '%' . $this->studentSearch . '%');
                  })
                  ->orWhereHas('section', function($sectionQuery) {
                      $sectionQuery->where('name', 'like', '%' . $this->studentSearch . '%');
                  })
                  // Semester is stored as JSON; search by school_year only
                  ;
            });
        }
        
        // Status filter
        if ($this->studentStatus !== 'all') {
            $query->where('status', $this->studentStatus);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function updatedStudentSearch()
    {
        $this->resetPage();
    }

    public function updatedStudentStatus()
    {
        $this->resetPage();
    }

    public function getStatusCount($status)
    {
        $user = Auth::user();
        
        if (!$user) {
            return 0;
        }
        
        $query = StudentInfo::where('user_id', $user->id);
        
        // Apply search filter
        if ($this->studentSearch) {
            $query->where(function($q) {
                $q->where('student_number', 'like', '%' . $this->studentSearch . '%')
                  ->orWhere('school_year', 'like', '%' . $this->studentSearch . '%')
                  ->orWhereHas('program', function($programQuery) {
                      $programQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                   ->orWhere('code', 'like', '%' . $this->studentSearch . '%');
                  })
                  ->orWhereHas('section', function($sectionQuery) {
                      $sectionQuery->where('name', 'like', '%' . $this->studentSearch . '%');
                  })
                  // Semester is stored as JSON; search by school_year only
                  ;
            });
        }
        
        // Apply status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        return $query->count();
    }

    public function render()
    {
        return view('livewire.student.my-enrollment');
    }
}
