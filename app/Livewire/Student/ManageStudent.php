<?php

namespace App\Livewire\Student;

use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\Program;
use App\Models\StudentDetails\StudentInfo;
use App\Models\Notification;
use App\Enums\YearLevel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Log;

class ManageStudent extends Component
{
    use WithPagination;
    
    public $semesters;
    public $sections;
    public $programs;
    public $totalSemesters = 0;
    public $totalSections = 0;
    public $totalPrograms = 0;
    
    // Student Info properties
    public $studentSearch = '';
    public $studentStatus = 'all'; // 'all', 'pending', 'enrolled', 'inactive', 'graduated'
    
    // Filter properties
    public $selectedSchoolYears = []; // Changed from selectedSemesters to selectedSchoolYears
    public $selectedSections = [];
    public $selectedPrograms = [];
    
    // Selection properties for checkboxes
    public $selected = [];
    public $selectAll = false;
    public $selectPage = false;
    
    /**
     * Sync properties with URL query string
     */
    protected $queryString = [
        'studentStatus' => ['except' => 'all'],
        'studentSearch' => ['except' => ''],
    ];
    
    // Section modal state
    public $showSectionModal = false;
    public $sectionId = null;
    
    // Delete modal state
    public $showDeleteSectionModal = false;
    public $deleteSectionId = null;
    public $deleteSectionName = null;
    
    // Program modal state
    public $showProgramModal = false;
    public $programId = null;
    
    // Delete program modal state
    public $showDeleteProgramModal = false;
    public $deleteProgramId = null;
    public $deleteProgramName = null;
    
    // View student modal state
    public $showViewStudentModal = false;
    public $selectedStudentInfoId = null;
    
    // Edit student enrollment modal state
    public $showEditStudentModal = false;
    public $editStudentInfoId = null;
    
    // Delete student enrollment modal state
    public $showDeleteStudentModal = false;
    public $deleteStudentInfoId = null;
    public $deleteStudentNumber = null;
    
    // Bulk status update modal state
    public $showBulkStatusModal = false;
    public $bulkStatus = 'pending';
    
    // Print modal state
    public $showPrintModal = false;
    public $printStep = 1; // 1 = filters, 2 = preview
    public $printUrl = '';
    
    // Edit enrollment form fields
    public $editStudentNumber = '';
    public $editProgramId = null;
    public $editYearLevel = null;
    public $editSectionId = null;
    public $editStatus = 'pending';
    public $editEnrolledAt = null;
    
    // Section form fields
    public $sectionName = '';
    public $yearLevel = 7; // Default to Grade 7
    public $sectionActive = true;
    
    // Program form fields
    public $programCode = '';
    public $programName = '';
    public $programYearLevels = [];
    public $programActive = true;
    
    // Search
    public $sectionSearch = '';
    public $semesterSearch = '';
    public $programSearch = '';
    
    // Load More
    public $sectionLimit = 10;
    public $semesterLimit = 10;
    public $programLimit = 10;

    public function mount()
    {
        $this->loadSemesters();
        $this->loadSections();
        $this->loadPrograms();
    }

    public function loadSemesters()
    {
        // Get unique school years with their semesters
        $baseQuery = Semester::query();
        
        if ($this->semesterSearch) {
            $baseQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->semesterSearch . '%')
                  ->orWhere('school_year', 'like', '%' . $this->semesterSearch . '%');
            });
        }
        
        // Get all semesters grouped by school year
        $allSemesters = $baseQuery->orderBy('school_year', 'desc')
            ->orderBy('name')
            ->get();
        
        // Group by school year
        $groupedByYear = $allSemesters->groupBy('school_year');
        
        // Get total count of unique school years
        $this->totalSemesters = $groupedByYear->count();
        
        // Limit the results by school year groups and convert to array for Livewire serialization
        $limitedGroups = $groupedByYear->take($this->semesterLimit);
        
        // Convert to array format that Livewire can handle
        $this->semesters = $limitedGroups->map(function($semesters, $schoolYear) {
            return [
                'school_year' => $schoolYear,
                'semesters' => $semesters->values()->all()
            ];
        })->values();
    }

    public function updatedSemesterSearch()
    {
        $this->semesterLimit = 10; // Reset limit when searching
        $this->loadSemesters();
    }

    public function loadMoreSemesters()
    {
        $this->semesterLimit += 10;
        $this->loadSemesters();
    }

    public function loadSections()
    {
        $baseQuery = Section::query();
        
        if ($this->sectionSearch) {
            $baseQuery->where('name', 'like', '%' . $this->sectionSearch . '%');
        }
        
        // Get total count before limiting
        $this->totalSections = $baseQuery->count();
        
        $this->sections = $baseQuery->orderBy('active', 'desc') // Active sections first
            ->orderBy('year_level')
            ->orderBy('name')
            ->limit($this->sectionLimit)
            ->get();
    }

    public function updatedSectionSearch()
    {
        $this->sectionLimit = 10; // Reset limit when searching
        $this->loadSections();
    }

    public function loadMoreSections()
    {
        $this->sectionLimit += 10;
        $this->loadSections();
    }

    public function addSection()
    {
        $this->sectionId = null;
        $this->resetSectionForm();
        $this->showSectionModal = true;
    }

    public function editSection($sectionId)
    {
        $section = Section::findOrFail($sectionId);
        
        $this->sectionId = $section->id;
        $this->sectionName = $section->name;
        $this->yearLevel = $section->year_level->value;
        $this->sectionActive = $section->active;
        
        $this->resetErrorBag();
        $this->showSectionModal = true;
    }

    public function closeSectionModal()
    {
        $this->showSectionModal = false;
        $this->resetSectionForm();
    }

    public function resetSectionForm()
    {
        $this->sectionId = null;
        $this->sectionName = '';
        $this->yearLevel = 7; // Default to Grade 7
        $this->sectionActive = true;
        $this->resetErrorBag();
    }

    public function saveSection()
    {
        $isEditing = !is_null($this->sectionId);
        
        $rules = [
            'sectionName' => 'required|string|max:255',
            'yearLevel' => 'required|integer|in:' . implode(',', YearLevel::values()),
            'sectionActive' => 'boolean',
        ];
        
        if ($isEditing) {
            $rules['sectionName'] .= '|unique:sections,name,' . $this->sectionId . ',id';
        } else {
            $rules['sectionName'] .= '|unique:sections,name';
        }
        
        $this->validate($rules, [
            'sectionName.required' => 'Section name is required.',
            'sectionName.unique' => 'This section name already exists.',
            'yearLevel.required' => 'Year level is required.',
            'yearLevel.integer' => 'Year level must be a number.',
            'yearLevel.in' => 'Year level must be between Grade 7 and Grade 12.',
        ]);
        
        $sectionData = [
            'name' => $this->sectionName,
            'year_level' => YearLevel::from($this->yearLevel),
            'active' => $this->sectionActive,
        ];
        
        if ($isEditing) {
            $section = Section::findOrFail($this->sectionId);
            $section->update($sectionData);
            $message = 'Section updated successfully!';
        } else {
            Section::create($sectionData);
            $message = 'Section created successfully!';
        }
        
        $this->loadSections();
        $this->closeSectionModal();
        
        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success',
            'title' => $isEditing ? 'Section Updated' : 'Section Created'
        ]);
    }

    public function deleteSection($sectionId)
    {
        $section = Section::findOrFail($sectionId);
        
        $this->deleteSectionId = $section->id;
        $this->deleteSectionName = $section->name;
        $this->showDeleteSectionModal = true;
    }

    public function confirmDeleteSection()
    {
        if ($this->deleteSectionId) {
            $section = Section::findOrFail($this->deleteSectionId);
            $sectionName = $section->name;
            
            // Check if section has students
            if ($section->studentInfos()->count() > 0) {
                $this->closeDeleteSectionModal();
                $this->dispatch('show-toast', [
                    'message' => 'Cannot delete section. It has students assigned.',
                    'type' => 'error',
                    'title' => 'Delete Failed'
                ]);
                return;
            }
            
            $section->delete();
            $this->loadSections();
            $this->closeDeleteSectionModal();
            
            $this->dispatch('show-toast', [
                'message' => 'Section "' . $sectionName . '" deleted successfully!',
                'type' => 'success',
                'title' => 'Section Deleted'
            ]);
        }
    }

    public function closeDeleteSectionModal()
    {
        $this->showDeleteSectionModal = false;
        $this->deleteSectionId = null;
        $this->deleteSectionName = null;
    }

    // Program Methods
    public function loadPrograms()
    {
        $baseQuery = Program::query();
        
        if ($this->programSearch) {
            $baseQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->programSearch . '%')
                  ->orWhere('code', 'like', '%' . $this->programSearch . '%');
            });
        }
        
        // Get total count before limiting
        $this->totalPrograms = $baseQuery->count();
        
        $this->programs = $baseQuery->orderBy('active', 'desc') // Active programs first
            ->orderBy('code')
            ->orderBy('name')
            ->limit($this->programLimit)
            ->get();
    }

    public function updatedProgramSearch()
    {
        $this->programLimit = 10; // Reset limit when searching
        $this->loadPrograms();
    }

    public function loadMorePrograms()
    {
        $this->programLimit += 10;
        $this->loadPrograms();
    }

    public function addProgram()
    {
        $this->programId = null;
        $this->resetProgramForm();
        $this->showProgramModal = true;
    }

    public function editProgram($programId)
    {
        $program = Program::findOrFail($programId);
        
        $this->programId = $program->id;
        $this->programCode = $program->code;
        $this->programName = $program->name;
        $this->programYearLevels = $program->getYearLevelValues();
        $this->programActive = $program->active;
        
        $this->resetErrorBag();
        $this->showProgramModal = true;
    }

    public function closeProgramModal()
    {
        $this->showProgramModal = false;
        $this->resetProgramForm();
    }

    public function resetProgramForm()
    {
        $this->programId = null;
        $this->programCode = '';
        $this->programName = '';
        $this->programYearLevels = [];
        $this->programActive = true;
        $this->resetErrorBag();
    }

    public function saveProgram()
    {
        $isEditing = !is_null($this->programId);
        
        $rules = [
            'programCode' => 'required|string|max:255',
            'programName' => 'required|string|max:255',
            'programYearLevels' => 'required|array|min:1',
            'programYearLevels.*' => 'required|integer|in:' . implode(',', YearLevel::values()),
            'programActive' => 'boolean',
        ];
        
        if ($isEditing) {
            $rules['programCode'] .= '|unique:programs,code,' . $this->programId . ',id';
            $rules['programName'] .= '|unique:programs,name,' . $this->programId . ',id';
        } else {
            $rules['programCode'] .= '|unique:programs,code';
            $rules['programName'] .= '|unique:programs,name';
        }
        
        $this->validate($rules, [
            'programCode.required' => 'Program code is required.',
            'programCode.unique' => 'This program code already exists.',
            'programName.required' => 'Program name is required.',
            'programName.unique' => 'This program name already exists.',
            'programYearLevels.required' => 'At least one year level is required.',
            'programYearLevels.array' => 'Year levels must be an array.',
            'programYearLevels.min' => 'At least one year level must be selected.',
            'programYearLevels.*.required' => 'Each year level is required.',
            'programYearLevels.*.integer' => 'Year level must be a number.',
            'programYearLevels.*.in' => 'Year level must be between Grade 7 and Grade 12.',
        ]);
        
        $programData = [
            'code' => $this->programCode,
            'name' => $this->programName,
            'active' => $this->programActive,
        ];
        
        if ($isEditing) {
            $program = Program::findOrFail($this->programId);
            $program->update($programData);
            $program->syncYearLevels($this->programYearLevels);
            $message = 'Program updated successfully!';
        } else {
            $program = Program::create($programData);
            $program->syncYearLevels($this->programYearLevels);
            $message = 'Program created successfully!';
        }
        
        $this->loadPrograms();
        $this->closeProgramModal();
        
        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success',
            'title' => $isEditing ? 'Program Updated' : 'Program Created'
        ]);
    }

    public function deleteProgram($programId)
    {
        $program = Program::findOrFail($programId);
        
        $this->deleteProgramId = $program->id;
        $this->deleteProgramName = $program->name;
        $this->showDeleteProgramModal = true;
    }

    public function confirmDeleteProgram()
    {
        if ($this->deleteProgramId) {
            $program = Program::findOrFail($this->deleteProgramId);
            $programName = $program->name;
            
            // Check if program has students
            if ($program->studentInfos()->count() > 0) {
                $this->closeDeleteProgramModal();
                $this->dispatch('show-toast', [
                    'message' => 'Cannot delete program. It has students assigned.',
                    'type' => 'error',
                    'title' => 'Delete Failed'
                ]);
                return;
            }
            
            $program->delete();
            $this->loadPrograms();
            $this->closeDeleteProgramModal();
            
            $this->dispatch('show-toast', [
                'message' => 'Program "' . $programName . '" deleted successfully!',
                'type' => 'success',
                'title' => 'Program Deleted'
            ]);
        }
    }

    public function closeDeleteProgramModal()
    {
        $this->showDeleteProgramModal = false;
        $this->deleteProgramId = null;
        $this->deleteProgramName = null;
    }

    // Student Info Methods
    #[Computed]
    public function studentInfos()
    {
        $query = StudentInfo::with(['user', 'program', 'section']);
        
        // Search filter
        if ($this->studentSearch) {
            $query->where(function($q) {
                $q->where('student_number', 'like', '%' . $this->studentSearch . '%')
                  ->orWhere('school_year', 'like', '%' . $this->studentSearch . '%')
                  ->orWhereHas('user', function($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                ->orWhere('email', 'like', '%' . $this->studentSearch . '%');
                  })
                  ->orWhereHas('program', function($programQuery) {
                      $programQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                   ->orWhere('code', 'like', '%' . $this->studentSearch . '%');
                  })
                  ->orWhereHas('section', function($sectionQuery) {
                      $sectionQuery->where('name', 'like', '%' . $this->studentSearch . '%');
                  });
            });
        }
        
        // Status filter
        if ($this->studentStatus !== 'all') {
            $query->where('status', $this->studentStatus);
        }
        
        // School year filter (since semester is stored as JSON in student_info)
        if (!empty($this->selectedSchoolYears)) {
            $schoolYears = array_filter($this->selectedSchoolYears);
            if (!empty($schoolYears)) {
                $query->whereIn('school_year', $schoolYears);
            }
        }
        
        // Section filter
        if (!empty($this->selectedSections)) {
            $sectionIds = array_filter(array_map('intval', $this->selectedSections));
            if (!empty($sectionIds)) {
                $query->whereIn('section_id', $sectionIds);
            }
        }
        
        // Program filter
        if (!empty($this->selectedPrograms)) {
            $programIds = array_filter(array_map('intval', $this->selectedPrograms));
            if (!empty($programIds)) {
                $query->whereIn('program_id', $programIds);
            }
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

    public function updatedSelectedSchoolYears()
    {
        $this->resetPage();
    }

    public function updatedSelectedSections()
    {
        $this->resetPage();
    }

    public function updatedSelectedPrograms()
    {
        $this->resetPage();
    }

    public function updatedSelectPage($value)
    {
        $pageIds = $this->studentInfos->pluck('id')->map(fn($id) => (string) $id)->toArray();

        if ($value) {
            $this->selected = array_unique(array_merge($this->selected, $pageIds));
        } else {
            $this->selected = array_diff($this->selected, $pageIds);
            $this->selectAll = false;
        }
    }

    public function updatedSelected()
    {
        $this->selectAll = false;
        
        $pageIds = $this->studentInfos->pluck('id')->map(fn($id) => (string) $id)->toArray();
        
        if (empty($pageIds)) {
            $this->selectPage = false;
            return;
        }

        $this->selectPage = count(array_intersect($pageIds, $this->selected)) === count($pageIds);
    }

    public function selectAllMatching()
    {
        $this->selectAll = true;
        $this->selectPage = true;
        
        // Apply same filters as studentInfos() method
        $query = StudentInfo::with(['user', 'program', 'section']);
        
        // Search filter
        if ($this->studentSearch) {
            $query->where(function($q) {
                $q->where('student_number', 'like', '%' . $this->studentSearch . '%')
                  ->orWhere('school_year', 'like', '%' . $this->studentSearch . '%')
                  ->orWhereHas('user', function($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                ->orWhere('email', 'like', '%' . $this->studentSearch . '%');
                  })
                  ->orWhereHas('program', function($programQuery) {
                      $programQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                   ->orWhere('code', 'like', '%' . $this->studentSearch . '%');
                  })
                  ->orWhereHas('section', function($sectionQuery) {
                      $sectionQuery->where('name', 'like', '%' . $this->studentSearch . '%');
                  });
            });
        }
        
        // Status filter
        if ($this->studentStatus !== 'all') {
            $query->where('status', $this->studentStatus);
        }
        
        // School year filter (since semester is stored as JSON in student_info)
        if (!empty($this->selectedSchoolYears)) {
            $schoolYears = array_filter($this->selectedSchoolYears);
            if (!empty($schoolYears)) {
                $query->whereIn('school_year', $schoolYears);
            }
        }
        
        // Section filter
        if (!empty($this->selectedSections)) {
            $sectionIds = array_filter(array_map('intval', $this->selectedSections));
            if (!empty($sectionIds)) {
                $query->whereIn('section_id', $sectionIds);
            }
        }
        
        // Program filter
        if (!empty($this->selectedPrograms)) {
            $programIds = array_filter(array_map('intval', $this->selectedPrograms));
            if (!empty($programIds)) {
                $query->whereIn('program_id', $programIds);
            }
        }
        
        $this->selected = $query->orderBy('created_at', 'desc')
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function clearAllFilters()
    {
        $this->selectedSchoolYears = [];
        $this->selectedSections = [];
        $this->selectedPrograms = [];
        $this->studentSearch = '';
        $this->studentStatus = 'all';
        $this->selected = [];
        $this->selectAll = false;
        $this->selectPage = false;
        $this->resetPage();
    }

    public function openBulkStatusModal()
    {
        if (empty($this->selected)) {
            $this->dispatch('show-toast', [
                'message' => 'Please select at least one student.',
                'type' => 'warning',
                'title' => 'No Selection'
            ]);
            return;
        }
        
        $this->bulkStatus = 'pending';
        $this->resetErrorBag();
        $this->showBulkStatusModal = true;
    }

    public function closeBulkStatusModal()
    {
        $this->showBulkStatusModal = false;
        $this->bulkStatus = 'pending';
        $this->resetErrorBag();
    }

    public function saveBulkStatusUpdate()
    {
        if (empty($this->selected)) {
            $this->dispatch('show-toast', [
                'message' => 'Please select at least one student.',
                'type' => 'warning',
                'title' => 'No Selection'
            ]);
            return;
        }

        $this->validate([
            'bulkStatus' => 'required|in:pending,enrolled,inactive,graduated',
        ], [
            'bulkStatus.required' => 'Status is required.',
            'bulkStatus.in' => 'Status must be one of: pending, enrolled, inactive, graduated.',
        ]);

        $selectedIds = array_filter(array_map('intval', $this->selected));
        
        if (empty($selectedIds)) {
            $this->dispatch('show-toast', [
                'message' => 'No valid students selected.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }

        $updatedCount = StudentInfo::whereIn('id', $selectedIds)
            ->update(['status' => $this->bulkStatus]);

        // Send notifications to updated students
        try {
            $updatedStudents = StudentInfo::whereIn('id', $selectedIds)
                ->with('user')
                ->get();
            
            foreach ($updatedStudents as $student) {
                try {
                    $this->notifyStudentEnrollmentUpdated($student);
                } catch (\Exception $e) {
                    Log::warning('Failed to send notification to student: ' . $e->getMessage(), [
                        'student_id' => $student->id
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending bulk status update notifications: ' . $e->getMessage());
        }

        $this->closeBulkStatusModal();
        $this->selected = [];
        $this->selectAll = false;
        $this->selectPage = false;
        $this->resetPage();

        $this->dispatch('show-toast', [
            'message' => "Successfully updated {$updatedCount} student(s) status to " . ucfirst($this->bulkStatus) . ".",
            'type' => 'success',
            'title' => 'Status Updated'
        ]);
    }

    public function getStatusCount($status)
    {
        $query = StudentInfo::query();
        
        // Apply search filter
        if ($this->studentSearch) {
            $query->where(function($q) {
                $q->where('student_number', 'like', '%' . $this->studentSearch . '%')
                  ->orWhere('school_year', 'like', '%' . $this->studentSearch . '%')
                  ->orWhereHas('user', function($userQuery) {
                      $userQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                ->orWhere('email', 'like', '%' . $this->studentSearch . '%');
                  })
                  ->orWhereHas('program', function($programQuery) {
                      $programQuery->where('name', 'like', '%' . $this->studentSearch . '%')
                                   ->orWhere('code', 'like', '%' . $this->studentSearch . '%');
                  })
                  ->orWhereHas('section', function($sectionQuery) {
                      $sectionQuery->where('name', 'like', '%' . $this->studentSearch . '%');
                  });
            });
        }
        
        // Apply status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Apply school year filter (since semester is stored as JSON in student_info)
        if (!empty($this->selectedSchoolYears)) {
            $schoolYears = array_filter($this->selectedSchoolYears);
            if (!empty($schoolYears)) {
                $query->whereIn('school_year', $schoolYears);
            }
        }
        
        // Apply section filter
        if (!empty($this->selectedSections)) {
            $sectionIds = array_filter(array_map('intval', $this->selectedSections));
            if (!empty($sectionIds)) {
                $query->whereIn('section_id', $sectionIds);
            }
        }
        
        // Apply program filter
        if (!empty($this->selectedPrograms)) {
            $programIds = array_filter(array_map('intval', $this->selectedPrograms));
            if (!empty($programIds)) {
                $query->whereIn('program_id', $programIds);
            }
        }
        
        return $query->count();
    }

    public function viewStudent($studentInfoId)
    {
        $studentInfo = StudentInfo::with(['user', 'program', 'section'])
            ->find($studentInfoId);
            
        if (!$studentInfo) {
            $this->dispatch('show-toast', [
                'message' => 'Student enrollment not found.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }
        
        $this->selectedStudentInfoId = $studentInfoId;
        $this->showViewStudentModal = true;
    }

    public function closeViewStudentModal()
    {
        $this->showViewStudentModal = false;
        $this->selectedStudentInfoId = null;
    }

    #[Computed]
    public function selectedStudentInfo()
    {
        if (!$this->selectedStudentInfoId) {
            return null;
        }
        
        return StudentInfo::with(['user', 'program', 'section'])
            ->find($this->selectedStudentInfoId);
    }

    public function editStudent($studentInfoId)
    {
        $studentInfo = StudentInfo::with(['program', 'section'])
            ->find($studentInfoId);
            
        if (!$studentInfo) {
            $this->dispatch('show-toast', [
                'message' => 'Student enrollment not found.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }
        
        $this->editStudentInfoId = $studentInfoId;
        $this->editStudentNumber = $studentInfo->student_number;
        $this->editProgramId = $studentInfo->program_id;
        $this->editYearLevel = $studentInfo->year_level;
        $this->editSectionId = $studentInfo->section_id;
        $this->editStatus = $studentInfo->status;
        $this->editEnrolledAt = $studentInfo->enrolled_at ? $studentInfo->enrolled_at->format('Y-m-d') : null;
        
        $this->resetErrorBag();
        $this->showEditStudentModal = true;
    }

    public function closeEditStudentModal()
    {
        $this->showEditStudentModal = false;
        $this->resetEditStudentForm();
    }

    public function resetEditStudentForm()
    {
        $this->editStudentInfoId = null;
        $this->editStudentNumber = '';
        $this->editProgramId = null;
        $this->editYearLevel = null;
        $this->editSectionId = null;
        $this->editStatus = 'pending';
        $this->editEnrolledAt = null;
        $this->resetErrorBag();
    }

    public function updatedEditYearLevel()
    {
        // Reset section when year level changes
        $this->editSectionId = null;
        
        // Reset program if it's not available for the new year level
        if ($this->editProgramId && $this->editYearLevel) {
            $program = Program::find($this->editProgramId);
            if ($program) {
                $availableYearLevels = $program->getYearLevelValues();
                if (!in_array($this->editYearLevel, $availableYearLevels)) {
                    $this->editProgramId = null;
                }
            }
        }
    }

    #[Computed]
    public function editProgramOptions()
    {
        $query = Program::where('active', true);
        
        // Filter by year level if selected
        if ($this->editYearLevel) {
            // Query the pivot table directly using the year_level integer value
            $query->whereHas('yearLevelPivots', function($q) {
                $q->where('year_level', $this->editYearLevel);
            });
        }
        
        return $query->orderBy('code')
            ->orderBy('name')
            ->get()
            ->map(function($program) {
                return [
                    'value' => $program->id,
                    'label' => $program->code . ' - ' . $program->name
                ];
            })->toArray();
    }

    #[Computed]
    public function editSectionOptions()
    {
        if (!$this->editYearLevel) {
            return [];
        }
        
        return Section::where('active', true)
            ->where('year_level', $this->editYearLevel)
            ->orderBy('name')
            ->get()
            ->map(function($section) {
                return [
                    'value' => $section->id,
                    'label' => $section->name
                ];
            })->toArray();
    }

    #[Computed]
    public function semesterOptions()
    {
        return Semester::orderBy('is_active', 'desc')
            ->orderBy('school_year', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function($semester) {
                return [
                    'value' => $semester->id,
                    'label' => $semester->name . ' (' . $semester->school_year . ')'
                ];
            })->toArray();
    }

    public function saveEditStudent()
    {
        if (!$this->editStudentInfoId) {
            return;
        }
        
        $studentInfo = StudentInfo::findOrFail($this->editStudentInfoId);
        
        // Validation - Program is required for Grade 11-12, optional for Grade 7-10
        $rules = [
            'editStudentNumber' => 'required|string|max:255',
            'editYearLevel' => 'required|integer|in:' . implode(',', YearLevel::values()),
            'editProgramId' => $this->editYearLevel >= 11 ? 'required|exists:programs,id' : 'nullable|exists:programs,id',
            'editSectionId' => 'nullable|exists:sections,id',
            'editStatus' => 'required|in:pending,enrolled,inactive,graduated',
            'editEnrolledAt' => 'nullable|date',
        ];
        
        $this->validate($rules, [
            'editStudentNumber.required' => 'Student number is required.',
            'editYearLevel.required' => 'Year level is required.',
            'editYearLevel.in' => 'Year level must be between Grade 7 and Grade 12.',
            'editProgramId.required' => 'Program is required for Grade 11-12 students.',
            'editProgramId.exists' => 'Selected program is invalid.',
            'editSectionId.exists' => 'Selected section is invalid.',
            'editStatus.required' => 'Status is required.',
            'editStatus.in' => 'Status must be one of: pending, enrolled, inactive, graduated.',
            'editEnrolledAt.date' => 'Enrolled at must be a valid date.',
        ]);
        
        // Additional validation: Check if the selected program is available for the selected year level
        if ($this->editProgramId && $this->editYearLevel) {
            $program = Program::find($this->editProgramId);
            if ($program) {
                $availableYearLevels = $program->getYearLevelValues();
                if (!in_array($this->editYearLevel, $availableYearLevels)) {
                    $this->addError('editProgramId', 'The selected program is not available for the selected year level.');
                    return;
                }
            }
        }
        
        // Update StudentInfo record
        $updateData = [
            'student_number' => $this->editStudentNumber,
            'year_level' => $this->editYearLevel,
            'section_id' => $this->editSectionId ?: null,
            'status' => $this->editStatus,
            'enrolled_at' => $this->editEnrolledAt ? $this->editEnrolledAt : null,
        ];
        
        // Only include program_id if it's provided
        if ($this->editProgramId) {
            $updateData['program_id'] = $this->editProgramId;
        } else {
            $updateData['program_id'] = null;
        }
        
        $studentInfo->update($updateData);
        
        // Reload the student info with relationships
        $studentInfo->refresh();
        $studentInfo->load(['user', 'program', 'section']);
        
        // Send notification to the student
        try {
            $this->notifyStudentEnrollmentUpdated($studentInfo);
        } catch (\Exception $notifyError) {
            // Log error but don't fail the update
            Log::error('Failed to send notification for student enrollment update: ' . $notifyError->getMessage());
        }
        
        $this->closeEditStudentModal();
        $this->resetPage(); // Reset pagination
        
        $this->dispatch('show-toast', [
            'message' => 'Student enrollment updated successfully!',
            'type' => 'success',
            'title' => 'Enrollment Updated'
        ]);
    }

    public function deleteStudent($studentInfoId)
    {
        $studentInfo = StudentInfo::with(['user'])
            ->find($studentInfoId);
            
        if (!$studentInfo) {
            $this->dispatch('show-toast', [
                'message' => 'Student enrollment not found.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }
        
        // Only allow deletion of pending enrollments
        if ($studentInfo->status !== 'pending') {
            $this->dispatch('show-toast', [
                'message' => 'Only pending enrollments can be deleted.',
                'type' => 'error',
                'title' => 'Delete Failed'
            ]);
            return;
        }
        
        $this->deleteStudentInfoId = $studentInfo->id;
        $this->deleteStudentNumber = $studentInfo->student_number;
        $this->showDeleteStudentModal = true;
    }

    public function confirmDeleteStudent()
    {
        if (!$this->deleteStudentInfoId) {
            return;
        }
        
        $studentInfo = StudentInfo::findOrFail($this->deleteStudentInfoId);
        
        // Double-check status before deletion
        if ($studentInfo->status !== 'pending') {
            $this->closeDeleteStudentModal();
            $this->dispatch('show-toast', [
                'message' => 'Only pending enrollments can be deleted.',
                'type' => 'error',
                'title' => 'Delete Failed'
            ]);
            return;
        }
        
        $studentNumber = $studentInfo->student_number;
        $studentInfo->delete();
        
        $this->closeDeleteStudentModal();
        $this->resetPage(); // Reset pagination
        
        $this->dispatch('show-toast', [
            'message' => 'Student enrollment "' . $studentNumber . '" deleted successfully!',
            'type' => 'success',
            'title' => 'Enrollment Deleted'
        ]);
    }

    public function closeDeleteStudentModal()
    {
        $this->showDeleteStudentModal = false;
        $this->deleteStudentInfoId = null;
        $this->deleteStudentNumber = null;
    }

    /**
     * Notify student about enrollment update
     * 
     * @param StudentInfo $studentInfo
     * @return void
     */
    private function notifyStudentEnrollmentUpdated(StudentInfo $studentInfo)
    {
        try {
            // Skip if student doesn't have a user account
            if (!$studentInfo->user || !$studentInfo->user_id) {
                Log::info('Skipping notification - student has no user account', ['student_info_id' => $studentInfo->id]);
                return;
            }

            // Build notification title and body
            $title = "📝 Enrollment Updated";
            
            $body = "Your enrollment information has been updated. ";
            
            // Add details about what was updated
            $details = [];
            
            if ($studentInfo->program) {
                $details[] = "Program: {$studentInfo->program->name}";
            }
            
            if ($studentInfo->section) {
                $details[] = "Section: {$studentInfo->section->name}";
            }
            
            if ($studentInfo->year_level) {
                $yearLevelLabel = YearLevel::from($studentInfo->year_level)->label();
                $details[] = "Year Level: {$yearLevelLabel}";
            }
            
            if ($studentInfo->status) {
                $statusLabel = ucfirst($studentInfo->status);
                $details[] = "Status: {$statusLabel}";
            }
            
            if (!empty($details)) {
                $body .= implode(', ', $details) . ". ";
            }
            
            $body .= "Please review your updated enrollment details.";

            // Build notification data
            $notificationData = [
                'student_info_id' => $studentInfo->id,
                'student_number' => $studentInfo->student_number,
                'program_id' => $studentInfo->program_id,
                'section_id' => $studentInfo->section_id,
                'year_level' => $studentInfo->year_level,
                'status' => $studentInfo->status,
            ];

            // URL for the notification
            $notificationUrl = "/enrollment.my-info-index";

            // Create notification
            Notification::create([
                'user_id' => $studentInfo->user_id,
                'type' => 'enrollment_updated',
                'title' => $title,
                'body' => $body,
                'url' => $notificationUrl,
                'data' => $notificationData,
                'notifiable_id' => $studentInfo->id,
                'notifiable_type' => StudentInfo::class,
                'read_at' => null,
            ]);

            Log::info("Sent enrollment update notification to student {$studentInfo->user_id} for enrollment: {$studentInfo->student_number} (ID: {$studentInfo->id})");

        } catch (\Exception $e) {
            // Log error but don't fail the enrollment update
            Log::error("Failed to send notification for student enrollment {$studentInfo->id}: " . $e->getMessage());
        }
    }

    public function printStudents()
    {
        $this->printStep = 1;
        $this->printUrl = '';
        $this->resetErrorBag();
        $this->showPrintModal = true;
    }

    public function closePrintModal()
    {
        $this->showPrintModal = false;
        $this->printStep = 1;
        $this->printUrl = '';
        $this->resetErrorBag();
    }

    public function nextPrintStep()
    {
        // Generate print URL with current filters
        $params = [];
        
        if ($this->studentStatus !== 'all') {
            $params['status'] = $this->studentStatus;
        }
        
        if (!empty($this->selectedSchoolYears)) {
            $params['school_years'] = implode(',', $this->selectedSchoolYears);
        }
        
        if (!empty($this->selectedSections)) {
            $params['sections'] = implode(',', $this->selectedSections);
        }
        
        if (!empty($this->selectedPrograms)) {
            $params['programs'] = implode(',', $this->selectedPrograms);
        }
        
        if ($this->studentSearch) {
            $params['search'] = $this->studentSearch;
        }
        
        // Build URL with query parameters
        $this->printUrl = route('students.print') . '?' . http_build_query($params);
        $this->printStep = 2;
    }

    public function previousPrintStep()
    {
        $this->printStep = 1;
        $this->printUrl = '';
    }

    #[Computed]
    public function printFilterSummary()
    {
        $filters = [];
        
        // Status
        if ($this->studentStatus !== 'all') {
            $filters['Status'] = ucfirst($this->studentStatus);
        }
        
        // School Years
        if (!empty($this->selectedSchoolYears)) {
            $schoolYears = array_filter($this->selectedSchoolYears);
            if (!empty($schoolYears)) {
                $filters['School Years'] = implode(', ', $schoolYears);
            }
        }
        
        // Sections
        if (!empty($this->selectedSections)) {
            $sectionIds = array_filter(array_map('intval', $this->selectedSections));
            if (!empty($sectionIds)) {
                $sections = Section::whereIn('id', $sectionIds)->get();
                $filters['Sections'] = $sections->map(fn($s) => $s->name)->join(', ');
            }
        }
        
        // Programs
        if (!empty($this->selectedPrograms)) {
            $programIds = array_filter(array_map('intval', $this->selectedPrograms));
            if (!empty($programIds)) {
                $programs = Program::whereIn('id', $programIds)->get();
                $filters['Programs'] = $programs->map(fn($p) => $p->code . ' - ' . $p->name)->join(', ');
            }
        }
        
        // Search
        if ($this->studentSearch) {
            $filters['Search'] = $this->studentSearch;
        }
        
        return $filters;
    }

    public function render()
    {
        return view('livewire.student.manage-student');
    }
}
