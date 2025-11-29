<?php

namespace App\Livewire\Student;

use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\Program;
use App\Enums\YearLevel;
use Livewire\Component;

class ManageStudent extends Component
{
    public $semesters;
    public $sections;
    public $programs;
    public $totalSemesters = 0;
    public $totalSections = 0;
    public $totalPrograms = 0;
    
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
    
    // Section form fields
    public $sectionName = '';
    public $yearLevel = 7; // Default to Grade 7
    public $sectionActive = true;
    
    // Program form fields
    public $programCode = '';
    public $programName = '';
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
        $baseQuery = Semester::query();
        
        if ($this->semesterSearch) {
            $baseQuery->where(function($q) {
                $q->where('name', 'like', '%' . $this->semesterSearch . '%')
                  ->orWhere('school_year', 'like', '%' . $this->semesterSearch . '%');
            });
        }
        
        // Get total count before limiting
        $this->totalSemesters = $baseQuery->count();
        
        $this->semesters = $baseQuery->orderBy('is_active', 'desc') // Active semesters first
            ->orderBy('name')
            ->limit($this->semesterLimit)
            ->get();
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
        
        session()->flash('message', $message);
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
                session()->flash('error', 'Cannot delete section. It has students assigned.');
                return;
            }
            
            $section->delete();
            $this->loadSections();
            $this->closeDeleteSectionModal();
            
            session()->flash('message', 'Section "' . $sectionName . '" deleted successfully!');
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
        $this->programActive = true;
        $this->resetErrorBag();
    }

    public function saveProgram()
    {
        $isEditing = !is_null($this->programId);
        
        $rules = [
            'programCode' => 'required|string|max:255',
            'programName' => 'required|string|max:255',
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
        ]);
        
        $programData = [
            'code' => $this->programCode,
            'name' => $this->programName,
            'active' => $this->programActive,
        ];
        
        if ($isEditing) {
            $program = Program::findOrFail($this->programId);
            $program->update($programData);
            $message = 'Program updated successfully!';
        } else {
            Program::create($programData);
            $message = 'Program created successfully!';
        }
        
        $this->loadPrograms();
        $this->closeProgramModal();
        
        session()->flash('message', $message);
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
                session()->flash('error', 'Cannot delete program. It has students assigned.');
                return;
            }
            
            $program->delete();
            $this->loadPrograms();
            $this->closeDeleteProgramModal();
            
            session()->flash('message', 'Program "' . $programName . '" deleted successfully!');
        }
    }

    public function closeDeleteProgramModal()
    {
        $this->showDeleteProgramModal = false;
        $this->deleteProgramId = null;
        $this->deleteProgramName = null;
    }

    public function render()
    {
        return view('livewire.student.manage-student');
    }
}
