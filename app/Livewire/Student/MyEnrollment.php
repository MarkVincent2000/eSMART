<?php

namespace App\Livewire\Student;

use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\StudentInfo;
use App\Models\StudentDetails\Program;
use App\Models\StudentDetails\Section;
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
        $this->activeSemester = Semester::where('is_active', true)->first();
    }

    public function checkEnrollment()
    {
        $user = Auth::user();
        
        // Check if user has role 'user'
        if ($user && $user->hasRole('user')) {
            // Check if there's an active semester
            if ($this->activeSemester) {
                // Get all user's enrollments, ordered by most recent first
                $enrollments = StudentInfo::with(['semester', 'program'])
                    ->where('user_id', $user->id)
                    ->whereNotNull('semester_id')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                if ($enrollments->isEmpty()) {
                    $this->studentInfo = null;
                    $this->hasEnrollment = false;
                    return;
                }
                
                // Get the most recent enrollment to determine current grade level
                $mostRecentEnrollment = $enrollments->first();
                $currentGradeLevel = $mostRecentEnrollment->year_level;
                
                // Check enrollment based on current grade level rules
                // Grade 11-12: Can enroll per semester (check if enrolled in active semester)
                // Grade 7-10: Can enroll per school year (check if enrolled in active semester's school year)
                
                if ($currentGradeLevel >= 11) {
                    // User is currently Grade 11-12 (based on most recent enrollment)
                    // Check if they have a Grade 11-12 enrollment for the active semester
                    $activeSemesterEnrollment = $enrollments->first(function($enrollment) {
                        return $enrollment->year_level >= 11 && 
                               $enrollment->semester_id === $this->activeSemester->id;
                    });
                    
                    if ($activeSemesterEnrollment) {
                        $this->studentInfo = $activeSemesterEnrollment;
                        $this->hasEnrollment = true;
                    } else {
                        // User is Grade 11-12 but not enrolled in active semester
                        // Ignore old Grade 7-10 enrollments - they can enroll in the new semester
                        $this->studentInfo = null;
                        $this->hasEnrollment = false;
                    }
                } else {
                    // User is currently Grade 7-10 (based on most recent enrollment)
                    // Check if they have a Grade 7-10 enrollment for the active semester's school year
                    $schoolYearEnrollment = $enrollments->first(function($enrollment) {
                        return $enrollment->year_level >= 7 && 
                               $enrollment->year_level <= 10 &&
                               $enrollment->school_year === $this->activeSemester->school_year;
                    });
                    
                    if ($schoolYearEnrollment) {
                        $this->studentInfo = $schoolYearEnrollment;
                        $this->hasEnrollment = true;
                    } else {
                        // User is Grade 7-10 but not enrolled in active semester's school year
                        $this->studentInfo = null;
                        $this->hasEnrollment = false;
                    }
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

        // Get all existing enrollments for the user
        $existingEnrollments = StudentInfo::where('user_id', $user->id)
            ->whereNotNull('semester_id')
            ->get();

        if ($existingEnrollments->isEmpty()) {
            return true; // No existing enrollment, can enroll
        }

        // Grade 11-12: Can enroll again if there is another semester (different semester_id)
        // They can enroll in different semesters, but not the same semester twice
        if ($yearLevel >= 11) {
            // Check if already enrolled in this exact semester
            $alreadyEnrolledInThisSemester = $existingEnrollments->contains(function($enrollment) {
                return $enrollment->semester_id === $this->activeSemester->id;
            });

            // Can enroll if not already enrolled in this semester
            return !$alreadyEnrolledInThisSemester;
        }

        // Grade 7-10: Can only enroll if the school_year is different from existing enrollment
        // They can only enroll once per school year
        if ($yearLevel >= 7 && $yearLevel <= 10) {
            // Check if already enrolled in this school year
            $alreadyEnrolledInThisSchoolYear = $existingEnrollments->contains(function($enrollment) {
                return $enrollment->school_year === $this->activeSemester->school_year;
            });

            // Can enroll if not already enrolled in this school year
            return !$alreadyEnrolledInThisSchoolYear;
        }

        return false;
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
        
        return StudentInfo::with(['user', 'program', 'section', 'semester'])
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
            'sectionId' => 'nullable|exists:sections,id',
        ];
        
        $this->validate($rules, [
            'studentNumber.required' => 'Student number is required.',
            'yearLevel.required' => 'Year level is required.',
            'yearLevel.in' => 'Year level must be between Grade 7 and Grade 12.',
            'programId.required' => 'Program is required for Grade 11-12 students.',
            'programId.exists' => 'Selected program is invalid.',
            'sectionId.exists' => 'Selected section is invalid.',
        ]);
        
        // Check enrollment eligibility based on grade level
        if (!$this->canEnroll($this->yearLevel)) {
            $yearLevelLabel = YearLevel::from($this->yearLevel)->label();
            
            if ($this->yearLevel >= 11) {
                // Grade 11-12: Check if already enrolled in this semester
                $existingEnrollment = StudentInfo::where('user_id', $user->id)
                    ->where('semester_id', $this->activeSemester->id)
                    ->first();
                    
                if ($existingEnrollment) {
                    $this->dispatch('show-toast', [
                        'message' => 'You are already enrolled for this semester.',
                        'type' => 'warning',
                        'title' => 'Already Enrolled'
                    ]);
                    return;
                }
            } else {
                // Grade 7-10: Check if already enrolled in this school year
                $existingEnrollment = StudentInfo::where('user_id', $user->id)
                    ->where('school_year', $this->activeSemester->school_year)
                    ->first();
                    
                if ($existingEnrollment) {
                    $this->dispatch('show-toast', [
                        'message' => 'You are already enrolled for school year ' . $this->activeSemester->school_year . '. Grade ' . ($this->yearLevel <= 8 ? '7-8' : '9-10') . ' students can only enroll once per school year.',
                        'type' => 'warning',
                        'title' => 'Already Enrolled'
                    ]);
                    return;
                }
            }
        }
        
        // Create StudentInfo record
        $studentInfoData = [
            'user_id' => $user->id,
            'student_number' => $this->studentNumber,
            'year_level' => $this->yearLevel,
            'section_id' => $this->sectionId ?: null,
            'semester_id' => $this->activeSemester->id,
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
        
        $query = StudentInfo::with(['user', 'program', 'section', 'semester'])
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
                  ->orWhereHas('semester', function($semesterQuery) {
                      $semesterQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                    ->orWhere('school_year', 'like', '%' . $this->studentSearch . '%');
                  });
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
                  ->orWhereHas('semester', function($semesterQuery) {
                      $semesterQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                    ->orWhere('school_year', 'like', '%' . $this->studentSearch . '%');
                  });
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
