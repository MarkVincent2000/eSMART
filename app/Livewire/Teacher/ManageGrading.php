<?php

namespace App\Livewire\Teacher;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Models\Grading\StudentInfoGrade;
use App\Models\Grading\SubjectGrade;
use App\Models\Teacher\Teacher;
use App\Models\Teacher\Workload;
use App\Models\StudentDetails\StudentInfo;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;

class ManageGrading extends Component
{
    use WithPagination;

    /**
     * Search term for filtering grade records.
     *
     * @var string
     */
    public $search = '';

    /**
     * Modal state for creating grade.
     *
     * @var bool
     */
    public $showAddGradeModal = false;

    /**
     * Modal state for viewing grade details.
     *
     * @var bool
     */
    public $showViewDetailsModal = false;

    /**
     * The grade record being viewed.
     *
     * @var StudentInfoGrade|null
     */
    public $viewingGrade = null;

    /**
     * Delete confirmation modal state.
     *
     * @var bool
     */
    public $showDeleteGradeModal = false;

    /**
     * Grade record ID and label for delete confirmation.
     *
     * @var int|null
     */
    public $deleteGradeId = null;

    /**
     * @var string|null
     */
    public $deleteGradeLabel = null;

    /**
     * Print PDF modal: show state and grade ID for iframe src.
     *
     * @var bool
     */
    public $showPrintModal = false;

    /**
     * @var int|null
     */
    public $printGradeId = null;

    /**
     * Grade level selected for filtering students (displayed first in form).
     */
    public $selectedGradeLevel = null;

    /**
     * Form fields for StudentInfoGrade
     */
    public $studentInfoId = null;
    public $name = '';
    public $schoolYear = '';
    public $age = null;
    public $sex = '';
    public $lrn = '';
    public $grade = null;
    public $section = '';
    public $dateOfBirth = '';
    public $teacherId = null;
    public $teacherName = '';
    public $dateIssued = '';
    public $eligibleToAdvanceGrade = false;
    public $hasAdvanceUnitIn = false;
    public $hasLackingUnitIn = false;

    /**
     * SubjectGrade entries (array of arrays)
     */
    public $subjectGrades = [];

    /**
     * General average inputs (array with keys like 'overall' or semester IDs)
     */
    public $generalAverageInputs = [];

    /**
     * When editing, the ID of the StudentInfoGrade being edited.
     */
    public $editingGradeId = null;

    /**
     * Teacher workloads/subjects for the table
     */
    public $teacherWorkloads = [];

    /**
     * First semester ID for Grade 11-12
     */
    public $firstSemesterId = null;

    /**
     * Group workloads by semester for Grade 11-12
     */
    #[Computed]
    public function workloadsBySemester(): array
    {
        if ($this->grade < 11 || $this->grade > 12) {
            return [];
        }

        $grouped = [];
        foreach ($this->teacherWorkloads as $workload) {
            $semesterId = $workload['semester_id'] ?? null;
            if ($semesterId) {
                if (!isset($grouped[$semesterId])) {
                    $grouped[$semesterId] = [
                        'semester_id' => $semesterId,
                        'semester_name' => $workload['semester_name'] ?? 'Semester',
                        'workloads' => [],
                    ];
                }
                $grouped[$semesterId]['workloads'][] = $workload;
            }
        }

        // Sort by semester_id to ensure consistent order
        ksort($grouped);
        
        return array_values($grouped);
    }

    /**
     * Get first semester only for the main table (Grade 11-12)
     */
    #[Computed]
    public function firstSemesterOnly(): array
    {
        $allSemesters = $this->workloadsBySemester;
        return array_slice($allSemesters, 0, 1);
    }

    /**
     * Get second semester only for the second table (Grade 11-12)
     */
    #[Computed]
    public function secondSemesterOnly(): ?array
    {
        $allSemesters = $this->workloadsBySemester;
        $second = array_slice($allSemesters, 1, 1);
        return !empty($second) ? $second[0] : null;
    }

    /**
     * Get remaining semesters after the first 2 (Grade 11-12) - for 3rd, 4th, etc.
     */
    #[Computed]
    public function remainingSemesters(): array
    {
        $allSemesters = $this->workloadsBySemester;
        return array_slice($allSemesters, 2);
    }

    /**
     * Compute live general average for the modal based on form data.
     * Returns array for Grade 11-12: ['1' => 85.5, '2' => 88.0]
     * Returns array for Grade 7-10: ['overall' => 85.5]
     */
    #[Computed]
    public function modalGeneralAverage(): ?array
    {
        if (empty($this->subjectGrades) || !is_array($this->subjectGrades)) {
            return null;
        }

        if ($this->grade >= 11 && $this->grade <= 12) {
            // Group by semester_id and compute average per semester
            // Derive semester_id from key (subject_X_sem_Y) when missing from data (Livewire may not send it)
            $semesterGrades = [];
            foreach ($this->subjectGrades as $subjectKey => $subjectGradeData) {
                $semesterId = $subjectGradeData['semester_id'] ?? null;
                if ($semesterId === null && is_string($subjectKey) && preg_match('/_sem_(\d+)$/', $subjectKey, $m)) {
                    $semesterId = (int) $m[1];
                }
                $fg = $subjectGradeData['final_grade'] ?? null;
                if ($semesterId && $fg !== null && $fg !== '' && is_numeric($fg)) {
                    if (!isset($semesterGrades[$semesterId])) {
                        $semesterGrades[$semesterId] = [];
                    }
                    $semesterGrades[$semesterId][] = (float) $fg;
                }
            }
            
            $averages = [];
            foreach ($semesterGrades as $semesterId => $grades) {
                if (!empty($grades)) {
                    $averages[(string)$semesterId] = round(array_sum($grades) / count($grades), 2);
                }
            }
            
            return !empty($averages) ? $averages : null;
        } else {
            // Grade 7-10: compute overall average
            $finalGrades = [];
            foreach ($this->subjectGrades as $subjectGradeData) {
                $fg = $subjectGradeData['final_grade'] ?? null;
                if ($fg !== null && $fg !== '' && is_numeric($fg)) {
                    $finalGrades[] = (float) $fg;
                }
            }
            
            if (empty($finalGrades)) {
                return null;
            }
            
            return ['overall' => round(array_sum($finalGrades) / count($finalGrades), 2)];
        }
    }

    /**
     * Remark for general average in the modal (Passed >= 75, Failed < 75).
     * Returns array matching modalGeneralAverage structure.
     */
    #[Computed]
    public function modalGeneralAverageRemark(): ?array
    {
        $averages = $this->modalGeneralAverage;
        if ($averages === null) {
            return null;
        }
        
        $remarks = [];
        foreach ($averages as $key => $avg) {
            $remarks[$key] = $avg >= 75 ? 'Passed' : 'Failed';
        }
        
        return !empty($remarks) ? $remarks : null;
    }

    /**
     * Sync computed general average to input fields
     */
    public function updatedSubjectGrades()
    {
        $this->syncGeneralAverageInputs();
    }

    /**
     * Sync computed general average to input fields
     */
    public function syncGeneralAverageInputs()
    {
        // Sync computed values to input fields (only if input is empty or not set)
        $computed = $this->modalGeneralAverage;
        if ($computed !== null && is_array($computed)) {
            foreach ($computed as $key => $value) {
                // Only set if not already manually entered
                if (!isset($this->generalAverageInputs[$key]) || $this->generalAverageInputs[$key] === '') {
                    $this->generalAverageInputs[$key] = (string) number_format($value, 2);
                }
            }
        }
    }

    /**
     * Get grade level options for the filter dropdown.
     */
    #[Computed]
    public function gradeLevelOptions(): array
    {
        return \App\Enums\YearLevel::options();
    }

    /**
     * Get student options with section information, filtered by selected grade level.
     */
    #[Computed]
    public function studentOptions(): array
    {
        $query = StudentInfo::with(['user', 'section'])
            ->where('status', 'enrolled')
            ->orderBy('student_number');

        // Filter by selected grade level when set
        if ($this->selectedGradeLevel !== null && $this->selectedGradeLevel !== '') {
            $query->where('year_level', (int) $this->selectedGradeLevel);
        }

        return $query->get()
            ->map(function ($student) {
                $name = $student->user->name ?? 'Unknown Student';
                $studentNumber = $student->student_number ?? '';
                $sectionName = $student->section->name ?? '';
                
                // Build label: Name (Student Number) - Section
                $label = $name;
                if ($studentNumber) {
                    $label .= ' (' . $studentNumber . ')';
                }
                if ($sectionName) {
                    $label .= ' - ' . $sectionName;
                }
                
                return [
                    'value' => $student->id,
                    'label' => $label,
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
     * When grade level filter changes, clear student selection.
     */
    public function updatedSelectedGradeLevel($value): void
    {
        $this->studentInfoId = null;
        $this->resetStudentFields();
        $this->teacherWorkloads = [];
        $this->firstSemesterId = null;
        $this->subjectGrades = [];
    }

    /**
     * When student is selected, auto-populate student details and load teacher workloads.
     */
    public function updatedStudentInfoId($value): void
    {
        if (!$value) {
            $this->resetStudentFields();
            $this->teacherWorkloads = [];
            $this->firstSemesterId = null;
            return;
        }

        $studentInfo = StudentInfo::with(['user.personalDetails', 'section', 'program'])->find($value);
        if ($studentInfo) {
            // When adding (not editing), check if student already has a grade for this school year + grade level
            if (empty($this->editingGradeId)) {
                $schoolYearToCheck = $studentInfo->school_year ?? $this->schoolYear;
                $gradeLevelToCheck = $studentInfo->year_level;
                
                if ($schoolYearToCheck && $gradeLevelToCheck) {
                    $existingGrade = StudentInfoGrade::where('student_info_id', $value)
                        ->where('school_year', $schoolYearToCheck)
                        ->where('grade', $gradeLevelToCheck)
                        ->first();
                    
                    if ($existingGrade) {
                        $this->studentInfoId = null;
                        $this->resetStudentFields();
                        $this->addError('studentInfoId', 'This student already has a grade record for ' . $schoolYearToCheck . ' (Grade ' . $gradeLevelToCheck . '). Please select a different student.');
                        return;
                    }
                }
            }
            
            // Populate from StudentInfo
            $this->name = $studentInfo->user->name ?? '';
            $this->lrn = $studentInfo->student_number ?? '';
            $this->schoolYear = $studentInfo->school_year ?? $this->schoolYear;
            $this->grade = $studentInfo->year_level;
            $this->section = $studentInfo->section->name ?? '';

            // Populate from UserPersonalDetails
            $personalDetails = $studentInfo->user->personalDetails ?? null;
            if ($personalDetails) {
                $this->sex = $personalDetails->sex ?? '';
                $this->dateOfBirth = $personalDetails->date_of_birth 
                    ? Carbon::parse($personalDetails->date_of_birth)->format('Y-m-d')
                    : '';

                // Calculate age
                if ($this->dateOfBirth) {
                    $this->age = Carbon::parse($this->dateOfBirth)->age;
                }
            }

            // Load teacher workloads if teacherId is set
            $this->loadTeacherWorkloads();
        }
    }

    /**
     * Load teacher workloads/subjects based on teacherId.
     */
    private function loadTeacherWorkloads(): void
    {
        if (!$this->teacherId) {
            $this->teacherWorkloads = [];
            $this->firstSemesterId = null;
            $this->subjectGrades = [];
            return;
        }

        $workloadsQuery = Workload::with(['subject', 'semester'])
            ->where('teacher_id', $this->teacherId);

        // Filter subjects by student's grade level (only load subjects for this grade or all-level subjects)
        if ($this->grade !== null && $this->grade !== '') {
            $workloadsQuery->whereHas('subject', function ($q) {
                $q->where('year_level', (int) $this->grade)
                  ->orWhereNull('year_level');
            });
        }

        $workloads = $workloadsQuery->orderBy('subject_id')->get();

        $this->teacherWorkloads = $workloads->map(function ($workload) {
            return [
                'workload_id' => $workload->id,
                'subject_id' => $workload->subject_id,
                'subject_name' => $workload->subject->name ?? 'N/A',
                'subject_code' => $workload->subject->code ?? '',
                'semester_id' => $workload->semester_id,
                'semester_name' => $workload->semester->name ?? '',
            ];
        })->toArray();

        // For Grade 11-12, get the 1st semester_id from workload
        if ($this->grade >= 11 && $this->grade <= 12) {
            $firstSemester = Semester::where('name', 'like', '%1st%')
                ->orWhere('name', 'like', '%First%')
                ->orderBy('start_date')
                ->first();
            
            if ($firstSemester) {
                $this->firstSemesterId = $firstSemester->id;
            } else {
                // Fallback: get first semester from workloads
                $firstWorkload = $workloads->first();
                if ($firstWorkload && $firstWorkload->semester_id) {
                    $this->firstSemesterId = $firstWorkload->semester_id;
                }
            }
        }

        // Initialize subjectGrades array for each workload
        $this->subjectGrades = [];
        foreach ($this->teacherWorkloads as $workload) {
            // For Grade 11-12, include semester_id in the key to handle subjects in multiple semesters
            if ($this->grade >= 11 && $this->grade <= 12) {
                $subjectKey = 'subject_' . $workload['subject_id'] . '_sem_' . $workload['semester_id'];
            } else {
                $subjectKey = 'subject_' . $workload['subject_id'];
            }
            
            $this->subjectGrades[$subjectKey] = [
                'subject_id' => $workload['subject_id'],
                'subject_name' => $workload['subject_name'],
                'semester_id' => ($this->grade >= 11 && $this->grade <= 12) ? $workload['semester_id'] : ($this->firstSemesterId ?? $workload['semester_id']),
                'semester_type' => $workload['semester_name'] ?? '',
                'grade_type' => ($this->grade >= 7 && $this->grade <= 10) ? 'quarter' : 'term',
                'is_quarter' => ($this->grade >= 7 && $this->grade <= 10),
                'quarter_1' => '',
                'quarter_2' => '',
                'quarter_3' => '',
                'quarter_4' => '',
                'midterm' => '',
                'final_term' => '',
                'final_grade' => '',
                'remarks' => '',
            ];
        }
    }

    /**
     * Reset student-related fields.
     */
    private function resetStudentFields(): void
    {
        $this->name = '';
        $this->lrn = '';
        $this->age = null;
        $this->sex = '';
        $this->grade = null;
        $this->section = '';
        $this->dateOfBirth = '';
        $this->teacherWorkloads = [];
        $this->firstSemesterId = null;
    }

    /**
     * Reset all form fields.
     */
    private function resetForm(): void
    {
        $this->editingGradeId = null;
        $this->selectedGradeLevel = null;
        $this->studentInfoId = null;
        $this->name = '';
        $this->schoolYear = '';
        $this->age = null;
        $this->sex = '';
        $this->lrn = '';
        $this->grade = null;
        $this->section = '';
        $this->dateOfBirth = '';
        $this->teacherId = null;
        $this->teacherName = '';
        $this->dateIssued = '';
        $this->eligibleToAdvanceGrade = false;
        $this->hasAdvanceUnitIn = false;
        $this->hasLackingUnitIn = false;
        $this->subjectGrades = [];
        $this->generalAverageInputs = [];
        $this->teacherWorkloads = [];
        $this->firstSemesterId = null;
        $this->resetErrorBag();
    }

    /**
     * Open the add grade modal.
     */
    public function openAddGradeModal(): void
    {
        $this->resetForm();
        
        // Pre-populate teacher info if user is a teacher
        $user = Auth::user();
        if ($user) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                $this->teacherId = $teacher->id;
                $this->teacherName = $user->name;
                // Load workloads for the teacher
                $this->loadTeacherWorkloads();
            }
        }

        $this->dateIssued = now()->format('Y-m-d');
        $this->showAddGradeModal = true;
    }

    /**
     * Close the add grade modal.
     */
    public function closeAddGradeModal(): void
    {
        $this->showAddGradeModal = false;
        $this->resetForm();
    }

    /**
     * Open the view details modal for a grade record.
     */
    public function viewGradeDetails(int $gradeId): void
    {
        $this->viewingGrade = StudentInfoGrade::with([
            'studentInfo.user.personalDetails',
            'studentInfo.section',
            'subjectGrades.subject',
            'subjectGrades.semester',
            'teacher.user'
        ])->find($gradeId);

        if ($this->viewingGrade) {
            $this->showViewDetailsModal = true;
        } else {
            $this->dispatch('show-toast', [
                'message' => 'Grade record not found.',
                'type' => 'error',
                'title' => 'Error',
            ]);
        }
    }

    /**
     * Close the view details modal.
     */
    public function closeViewDetailsModal(): void
    {
        $this->showViewDetailsModal = false;
        $this->viewingGrade = null;
    }

    /**
     * Open the delete confirmation modal for a grade record.
     */
    public function deleteGrade(int $gradeId): void
    {
        $gradeRecord = StudentInfoGrade::find($gradeId);
        if (!$gradeRecord) {
            $this->dispatch('show-toast', [
                'message' => 'Grade record not found.',
                'type' => 'error',
                'title' => 'Error',
            ]);
            return;
        }

        $user = Auth::user();
        if (!$user) {
            return;
        }

        $isSuperAdmin = $user instanceof User && $user->hasRole('super-admin');
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$isSuperAdmin && (!$teacher || (int) $gradeRecord->teacher_id !== (int) $teacher->id)) {
            $this->dispatch('show-toast', [
                'message' => 'You are not allowed to delete this grade.',
                'type' => 'error',
                'title' => 'Unauthorized',
            ]);
            return;
        }

        $this->deleteGradeId = $gradeRecord->id;
        $this->deleteGradeLabel = $gradeRecord->name . ' (' . ($gradeRecord->school_year ?? 'N/A') . ')';
        $this->showDeleteGradeModal = true;
    }

    /**
     * Close the delete grade modal.
     */
    public function closeDeleteGradeModal(): void
    {
        $this->showDeleteGradeModal = false;
        $this->deleteGradeId = null;
        $this->deleteGradeLabel = null;
    }

    /**
     * Open the print PDF modal (iframe will load PDF for this grade).
     */
    public function openPrintModal(int $gradeId): void
    {
        $this->printGradeId = $gradeId;
        $this->showPrintModal = true;
    }

    /**
     * Close the print PDF modal.
     */
    public function closePrintModal(): void
    {
        $this->showPrintModal = false;
        $this->printGradeId = null;
    }

    /**
     * Confirm and perform the grade record deletion.
     */
    public function confirmDeleteGrade(): void
    {
        if (!$this->deleteGradeId) {
            return;
        }

        $user = Auth::user();
        if (!$user) {
            return;
        }

        $gradeRecord = StudentInfoGrade::find($this->deleteGradeId);
        if (!$gradeRecord) {
            $this->closeDeleteGradeModal();
            $this->dispatch('show-toast', [
                'message' => 'Grade record not found.',
                'type' => 'error',
                'title' => 'Error',
            ]);
            return;
        }

        $isSuperAdmin = $user instanceof User && $user->hasRole('super-admin');
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$isSuperAdmin && (!$teacher || (int) $gradeRecord->teacher_id !== (int) $teacher->id)) {
            $this->closeDeleteGradeModal();
            $this->dispatch('show-toast', [
                'message' => 'You are not allowed to delete this grade.',
                'type' => 'error',
                'title' => 'Unauthorized',
            ]);
            return;
        }

        $deletedId = $this->deleteGradeId;
        $gradeRecord->subjectGrades()->delete();
        $gradeRecord->delete();
        $this->closeDeleteGradeModal();
        $this->resetPage();

        if ($this->showViewDetailsModal && $this->viewingGrade && (int) $this->viewingGrade->id === (int) $deletedId) {
            $this->closeViewDetailsModal();
        }

        $this->dispatch('show-toast', [
            'message' => 'Grade record deleted successfully.',
            'type' => 'success',
            'title' => 'Success',
        ]);
    }

    /**
     * Open the modal and preload form with an existing grade record for editing.
     */
    public function editGrade(int $gradeId): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $gradeRecord = StudentInfoGrade::with(['studentInfo.user.personalDetails', 'studentInfo.section', 'subjectGrades'])
            ->find($gradeId);

        if (!$gradeRecord) {
            $this->dispatch('show-toast', [
                'message' => 'Grade record not found.',
                'type' => 'error',
                'title' => 'Error',
            ]);
            return;
        }

        // Authorization: only own teacher or super-admin
        $isSuperAdmin = $user instanceof User && $user->hasRole('super-admin');
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$isSuperAdmin && (!$teacher || (int) $gradeRecord->teacher_id !== (int) $teacher->id)) {
            $this->dispatch('show-toast', [
                'message' => 'You are not allowed to edit this grade.',
                'type' => 'error',
                'title' => 'Unauthorized',
            ]);
            return;
        }

        $this->resetForm();
        $this->editingGradeId = $gradeId;

        $studentInfo = $gradeRecord->studentInfo;
        if (!$studentInfo) {
            $this->dispatch('show-toast', ['message' => 'Student info not found.', 'type' => 'error', 'title' => 'Error']);
            return;
        }

        // Student and grade filter
        $this->selectedGradeLevel = $gradeRecord->grade ? (string) $gradeRecord->grade : null;
        $this->studentInfoId = $gradeRecord->student_info_id;
        $this->name = $gradeRecord->name ?? '';
        $this->schoolYear = $gradeRecord->school_year ?? '';
        $this->age = $gradeRecord->age;
        $this->sex = $gradeRecord->sex ?? '';
        $this->lrn = $gradeRecord->lrn ?? '';
        $this->grade = $gradeRecord->grade;
        $this->section = $gradeRecord->section ?? '';
        $this->dateOfBirth = $gradeRecord->date_of_birth ? $gradeRecord->date_of_birth->format('Y-m-d') : '';
        $this->teacherId = $gradeRecord->teacher_id;
        $this->teacherName = $gradeRecord->teacher_name ?? '';
        $this->dateIssued = $gradeRecord->date_issued ? $gradeRecord->date_issued->format('Y-m-d') : '';
        $this->eligibleToAdvanceGrade = (bool) $gradeRecord->eligible_to_advance_grade;
        $this->hasAdvanceUnitIn = (bool) $gradeRecord->has_advance_unit_in;
        $this->hasLackingUnitIn = (bool) $gradeRecord->has_lacking_unit_in;

        // Load teacher workloads (builds teacherWorkloads and subjectGrades structure)
        $this->loadTeacherWorkloads();

        // Overlay existing subject grades from DB (one row per subject, grade_type is JSON)
        foreach ($gradeRecord->subjectGrades as $sg) {
            if ($this->grade >= 11 && $this->grade <= 12 && $sg->semester_id) {
                $subjectKey = 'subject_' . $sg->subject_id . '_sem_' . $sg->semester_id;
            } else {
                $subjectKey = 'subject_' . $sg->subject_id;
            }

            if (!isset($this->subjectGrades[$subjectKey])) {
                continue;
            }

            $data = is_array($sg->grade_type) ? $sg->grade_type : (array) $sg->grade_type;

            foreach (['quarter_1', 'quarter_2', 'quarter_3', 'quarter_4', 'midterm', 'final_term', 'final_grade', 'remarks'] as $key) {
                if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                    $this->subjectGrades[$subjectKey][$key] = $data[$key];
                }
            }
        }

        // Load existing general average inputs from database
        $generalAverage = $gradeRecord->general_average;
        if ($generalAverage !== null && is_array($generalAverage)) {
            foreach ($generalAverage as $key => $value) {
                $this->generalAverageInputs[$key] = (string) number_format($value, 2);
            }
        } else {
            // If no existing values, sync from computed
            $this->updatedSubjectGrades();
        }

        $this->showAddGradeModal = true;
    }

    /**
     * Save the grade record.
     */
    public function saveGrade(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->addError('studentInfoId', 'You must be logged in to add a grade.');
            return;
        }

        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            $this->addError('studentInfoId', 'No teacher profile found for your account.');
            return;
        }

        $rules = [
            'studentInfoId' => ['required', 'integer', 'exists:student_infos,id'],
            'name' => ['required', 'string', 'max:255'],
            'schoolYear' => ['required', 'string', 'max:50'],
            'age' => ['nullable', 'integer', 'min:1', 'max:150'],
            'sex' => ['nullable', 'string', 'max:20'],
            'lrn' => ['nullable', 'string', 'max:50'],
            'grade' => ['nullable', 'integer', 'min:1', 'max:12'],
            'section' => ['nullable', 'string', 'max:100'],
            'dateOfBirth' => ['nullable', 'date'],
            'dateIssued' => ['nullable', 'date'],
            'eligibleToAdvanceGrade' => ['boolean'],
            'hasAdvanceUnitIn' => ['boolean'],
            'hasLackingUnitIn' => ['boolean'],
        ];

        $this->validate($rules);

        $isEditing = !empty($this->editingGradeId);

        if ($isEditing) {
            $studentInfoGrade = StudentInfoGrade::findOrFail($this->editingGradeId);
            $studentInfoGrade->update([
                'student_info_id' => $this->studentInfoId,
                'name' => $this->name,
                'school_year' => $this->schoolYear,
                'age' => $this->age,
                'sex' => $this->sex,
                'lrn' => $this->lrn,
                'grade' => $this->grade,
                'section' => $this->section,
                'date_of_birth' => $this->dateOfBirth ? Carbon::parse($this->dateOfBirth) : null,
                'teacher_id' => $this->teacherId,
                'teacher_name' => $this->teacherName,
                'date_issued' => $this->dateIssued ? Carbon::parse($this->dateIssued) : null,
                'eligible_to_advance_grade' => $this->eligibleToAdvanceGrade,
                'has_advance_unit_in' => $this->hasAdvanceUnitIn,
                'has_lacking_unit_in' => $this->hasLackingUnitIn,
            ]);
            // Remove old subject grades so we can recreate from form
            $studentInfoGrade->subjectGrades()->delete();
        } else {
            $studentInfoGrade = StudentInfoGrade::create([
                'student_info_id' => $this->studentInfoId,
                'name' => $this->name,
                'school_year' => $this->schoolYear,
                'age' => $this->age,
                'sex' => $this->sex,
                'lrn' => $this->lrn,
                'grade' => $this->grade,
                'section' => $this->section,
                'date_of_birth' => $this->dateOfBirth ? Carbon::parse($this->dateOfBirth) : null,
                'teacher_id' => $this->teacherId,
                'teacher_name' => $this->teacherName,
                'date_issued' => $this->dateIssued ? Carbon::parse($this->dateIssued) : null,
                'eligible_to_advance_grade' => $this->eligibleToAdvanceGrade,
                'has_advance_unit_in' => $this->hasAdvanceUnitIn,
                'has_lacking_unit_in' => $this->hasLackingUnitIn,
            ]);
        }

        // Create one SubjectGrade per subject with grade_type as JSON
        if (!empty($this->subjectGrades) && is_array($this->subjectGrades)) {
            foreach ($this->subjectGrades as $subjectKey => $subjectGradeData) {
                if (empty($subjectGradeData['subject_id'])) {
                    continue;
                }

                $isQuarter = $subjectGradeData['is_quarter'] ?? false;
                $fg = $subjectGradeData['final_grade'] ?? null;
                $remarks = (isset($fg) && $fg !== '' && is_numeric($fg))
                    ? ((float) $fg >= 75 ? 'Passed' : 'Failed')
                    : null;
                $gradeTypeJson = [];

                if ($isQuarter && ($this->grade >= 7 && $this->grade <= 10)) {
                    $gradeTypeJson = [
                        'quarter_1' => $subjectGradeData['quarter_1'] ?? null,
                        'quarter_2' => $subjectGradeData['quarter_2'] ?? null,
                        'quarter_3' => $subjectGradeData['quarter_3'] ?? null,
                        'quarter_4' => $subjectGradeData['quarter_4'] ?? null,
                        'final_grade' => $subjectGradeData['final_grade'] ?? null,
                        'remarks' => $remarks,
                    ];
                } else {
                    $gradeTypeJson = [
                        'midterm' => $subjectGradeData['midterm'] ?? null,
                        'final_term' => $subjectGradeData['final_term'] ?? null,
                        'final_grade' => $subjectGradeData['final_grade'] ?? null,
                        'remarks' => $remarks,
                    ];
                }

                SubjectGrade::create([
                    'student_info_grade_id' => $studentInfoGrade->id,
                    'subject_id' => $subjectGradeData['subject_id'],
                    'subject_name' => $subjectGradeData['subject_name'] ?? null,
                    'semester_id' => $subjectGradeData['semester_id'] ?? $this->firstSemesterId,
                    'semester_type' => $subjectGradeData['semester_type'] ?? null,
                    'grade_type' => $gradeTypeJson,
                    'is_quarter' => $isQuarter,
                ]);
            }
        }

        // Compute and save general average as JSON
        // Use input values if provided, otherwise compute from subject grades
        // For Grade 11-12: compute per semester {"1": 85.5, "2": 88.0}
        // For Grade 7-10: compute overall {"overall": 85.5}
        $generalAverageJson = null;
        $generalAverageRemarkJson = null;

        // Check if manual inputs are provided
        if (!empty($this->generalAverageInputs) && is_array($this->generalAverageInputs)) {
            $averages = [];
            $remarks = [];
            foreach ($this->generalAverageInputs as $key => $value) {
                if ($value !== null && $value !== '' && is_numeric($value)) {
                    $avg = (float) $value;
                    $averages[$key] = round($avg, 2);
                    $remarks[$key] = $avg >= 75 ? 'Passed' : 'Failed';
                }
            }
            if (!empty($averages)) {
                $generalAverageJson = $averages;
                $generalAverageRemarkJson = $remarks;
            }
        }

        // Fall back to computed values if no manual inputs
        if ($generalAverageJson === null && !empty($this->subjectGrades) && is_array($this->subjectGrades)) {
            if ($this->grade >= 11 && $this->grade <= 12) {
                // Group by semester_id and compute average per semester (derive semester from key if missing)
                $semesterGrades = [];
                foreach ($this->subjectGrades as $subjectKey => $subjectGradeData) {
                    $semesterId = $subjectGradeData['semester_id'] ?? null;
                    if ($semesterId === null && is_string($subjectKey) && preg_match('/_sem_(\d+)$/', $subjectKey, $m)) {
                        $semesterId = (int) $m[1];
                    }
                    $fg = $subjectGradeData['final_grade'] ?? null;
                    if ($semesterId && $fg !== null && $fg !== '' && is_numeric($fg)) {
                        if (!isset($semesterGrades[$semesterId])) {
                            $semesterGrades[$semesterId] = [];
                        }
                        $semesterGrades[$semesterId][] = (float) $fg;
                    }
                }
                
                $averages = [];
                $remarks = [];
                foreach ($semesterGrades as $semesterId => $grades) {
                    if (!empty($grades)) {
                        $avg = round(array_sum($grades) / count($grades), 2);
                        $averages[(string)$semesterId] = $avg;
                        $remarks[(string)$semesterId] = $avg >= 75 ? 'Passed' : 'Failed';
                    }
                }
                
                $generalAverageJson = !empty($averages) ? $averages : null;
                $generalAverageRemarkJson = !empty($remarks) ? $remarks : null;
            } else {
                // Grade 7-10: compute overall average
                $finalGrades = [];
                foreach ($this->subjectGrades as $subjectGradeData) {
                    $fg = $subjectGradeData['final_grade'] ?? null;
                    if ($fg !== null && $fg !== '' && is_numeric($fg)) {
                        $finalGrades[] = (float) $fg;
                    }
                }
                
                if (!empty($finalGrades)) {
                    $avg = round(array_sum($finalGrades) / count($finalGrades), 2);
                    $generalAverageJson = ['overall' => $avg];
                    $generalAverageRemarkJson = ['overall' => $avg >= 75 ? 'Passed' : 'Failed'];
                }
            }
        }

        $studentInfoGrade->update([
            'general_average' => $generalAverageJson,
            'general_average_remark' => $generalAverageRemarkJson,
        ]);

        // Notify the student when a new grade record is created (not on edit)
        if (!$isEditing) {
            $studentInfo = StudentInfo::find($this->studentInfoId);
            if ($studentInfo && $studentInfo->user_id) {
                Notification::notifyStudentGradeSaved(
                    (int) $studentInfo->user_id,
                    $studentInfoGrade,
                    $this->teacherName
                );
            }
        }

        $this->closeAddGradeModal();
        $this->resetPage();

        $this->dispatch('show-toast', [
            'message' => $isEditing ? 'Grade record updated successfully!' : 'Grade record added successfully!',
            'type' => 'success',
            'title' => 'Success',
        ]);
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        // Check roles using Spatie Permission package (hasRole method added via HasRoles trait)
        $isSuperAdmin = $user instanceof User && $user->hasRole('super-admin');
        $isAdmin = $user instanceof User && $user->hasRole('admin');
        $isStudent = $user instanceof User && $user->hasRole('user');

        // Default: empty paginator
        $studentInfoGrades = StudentInfoGrade::whereRaw('1 = 0')->paginate(12);

        if ($user) {
            $query = StudentInfoGrade::with(['studentInfo.user', 'teacher.user', 'subjectGrades']);

            // Role-based filtering
            if ($isSuperAdmin) {
                // Super-admin: see all grades
                // No additional filtering needed
            } elseif ($isAdmin) {
                // Admin: see grades where teacher_id matches their teacher_id
                $teacher = Teacher::where('user_id', $user->id)->first();
                if ($teacher) {
                    $query->where('teacher_id', $teacher->id);
                } else {
                    // If admin doesn't have a teacher profile, show nothing
                    $query->whereRaw('1 = 0');
                }
            } elseif ($isStudent) {
                // Student: see only their own grades
                $studentInfo = StudentInfo::where('user_id', $user->id)->first();
                if ($studentInfo) {
                    $query->where('student_info_id', $studentInfo->id);
                } else {
                    // If student doesn't have a student_info record, show nothing
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Teacher (not admin): see grades where teacher_id matches their teacher_id
                $teacher = Teacher::where('user_id', $user->id)->first();
                if ($teacher) {
                    $query->where('teacher_id', $teacher->id);
                } else {
                    // If teacher doesn't have a teacher profile, show nothing
                    $query->whereRaw('1 = 0');
                }
            }

            // Search filter
            if (!empty($this->search)) {
                $search = '%' . $this->search . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', $search)
                        ->orWhere('school_year', 'like', $search)
                        ->orWhere('lrn', 'like', $search)
                        ->orWhere('section', 'like', $search)
                        ->orWhere('teacher_name', 'like', $search)
                        ->orWhereHas('studentInfo.user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        })
                        ->orWhereHas('teacher.user', function ($teacherQuery) use ($search) {
                            $teacherQuery->where('name', 'like', $search)
                                ->orWhere('email', 'like', $search);
                        });
                });
            }

            $studentInfoGrades = $query->latest('date_issued')->latest()->paginate(12);
        }

        // Check if current user is a teacher with profile and workloads
        $canAddGrade = false;
        if ($user) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if ($teacher) {
                // Check if teacher has at least one workload
                $hasWorkloads = Workload::where('teacher_id', $teacher->id)->exists();
                $canAddGrade = $hasWorkloads;
            }
        }

        // Build options for selects
        $subjectOptions = [];
        $semesterOptions = [];

        // Subject and semester options (for SubjectGrade entries if needed)
        $subjectOptions = Subject::orderBy('name')
            ->select('id', 'name', 'code')
            ->get()
            ->map(function ($subject) {
                $code = $subject->code ?? '';
                $name = $subject->name ?? 'Unknown Subject';
                
                // Build label: Code - Name or just Name if no code
                $label = $code ? "{$code} - {$name}" : $name;
                
                return [
                    'value' => $subject->id,
                    'label' => $label,
                ];
            })
            ->toArray();

        $semesterOptions = Semester::orderBy('start_date', 'desc')
            ->select('id', 'name', 'school_year')
            ->get()
            ->map(function ($semester) {
                $name = $semester->name ?? 'Unknown Semester';
                $schoolYear = $semester->school_year ?? '';
                
                // Build label: Name (School Year) or just Name if no school year
                $label = $name;
                if ($schoolYear) {
                    $label .= ' (' . $schoolYear . ')';
                }
                
                return [
                    'value' => $semester->id,
                    'label' => $label,
                ];
            })
            ->toArray();

        return view('livewire.teacher.manage-grading', [
            'studentInfoGrades' => $studentInfoGrades,
            'isSuperAdmin' => $isSuperAdmin,
            'canAddGrade' => $canAddGrade,
            'subjectOptions' => $subjectOptions,
            'semesterOptions' => $semesterOptions,
        ]);
    }
}
