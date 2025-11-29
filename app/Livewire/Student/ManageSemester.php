<?php

namespace App\Livewire\Student;

use App\Models\StudentDetails\Semester;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class ManageSemester extends Component
{
    use WithPagination;
    
    public $activeSemester;
    public $search = '';
    public $status = 'all'; // 'all', 'active', 'inactive'
    public $sortBy = 'name'; // 'name', 'school_year', 'created_at'
    
    // Semester modal state
    public $showSemesterModal = false;
    public $semesterId = null;
    
    // Delete modal state
    public $showDeleteSemesterModal = false;
    public $deleteSemesterId = null;
    public $deleteSemesterName = null;
    
    // Semester form fields
    public $semesterName = '';
    public $schoolYear = '';
    public $startDate = null;
    public $endDate = null;
    public $semesterActive = false;

    public function mount()
    {
        $this->loadActiveSemester();
    }

    #[Computed]
    public function semesters()
    {
        $query = Semester::query();
        
        // Search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('school_year', 'like', '%' . $this->search . '%');
            });
        }
        
        // Status filter
        if ($this->status !== 'all') {
            $query->where('is_active', $this->status === 'active');
        }
        
        // Sorting - Always prioritize active semesters first (unless filtering by inactive only)
        if ($this->status !== 'inactive') {
            $query->orderBy('is_active', 'desc');
        }
        
        if ($this->sortBy === 'school_year') {
            $query->orderBy('school_year', 'desc')->orderBy('name');
        } elseif ($this->sortBy === 'created_at') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('name');
        }
        
        return $query->paginate(10);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function loadActiveSemester()
    {
        $this->activeSemester = Semester::where('is_active', true)->first();
    }

    public function setActiveSemester($semesterId)
    {
        // Deactivate all semesters
        Semester::where('is_active', true)->update(['is_active' => false]);
        
        // Activate the selected semester
        $semester = Semester::findOrFail($semesterId);
        $semester->update(['is_active' => true]);
        
        // Reload data
        $this->loadActiveSemester();
        
        // Show success notification
        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => $semester->name . ' (' . $semester->school_year . ') is now active'
        ]);
    }

    public function addSemester()
    {
        $this->semesterId = null;
        $this->resetSemesterForm();
        $this->showSemesterModal = true;
    }

    public function editSemester($semesterId)
    {
        $semester = Semester::findOrFail($semesterId);
        
        $this->semesterId = $semester->id;
        $this->semesterName = $semester->name;
        $this->schoolYear = $semester->school_year;
        
        // Handle date formatting - dates are cast to Carbon instances in the model
        if ($semester->start_date) {
            /** @var \Carbon\Carbon $startDate */
            $startDate = $semester->start_date;
            $this->startDate = $startDate->format('Y-m-d');
        } else {
            $this->startDate = null;
        }
        
        if ($semester->end_date) {
            /** @var \Carbon\Carbon $endDate */
            $endDate = $semester->end_date;
            $this->endDate = $endDate->format('Y-m-d');
        } else {
            $this->endDate = null;
        }
        
        $this->resetErrorBag();
        $this->showSemesterModal = true;
    }

    public function closeSemesterModal()
    {
        $this->showSemesterModal = false;
        $this->resetSemesterForm();
    }

    public function resetSemesterForm()
    {
        $this->semesterId = null;
        $this->semesterName = '';
        $this->schoolYear = '';
        $this->startDate = null;
        $this->endDate = null;
        $this->semesterActive = false;
        $this->resetErrorBag();
    }

    public function updatedStartDate()
    {
        // If end date is before the new start date, clear it
        if ($this->endDate && $this->startDate && $this->endDate < $this->startDate) {
            $this->endDate = null;
            $this->addError('endDate', 'End date must be after or equal to start date.');
        }
    }

    public function updatedEndDate()
    {
        // Validate that end date is not before start date
        if ($this->endDate && $this->startDate && $this->endDate < $this->startDate) {
            $this->addError('endDate', 'End date must be after or equal to start date.');
        } else {
            $this->resetErrorBag('endDate');
        }
    }

    public function saveSemester()
    {
        $isEditing = !is_null($this->semesterId);
        
        $rules = [
            'semesterName' => 'required|string|max:255',
            'schoolYear' => 'required|string|max:255',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
        ];
        
        $this->validate($rules, [
            'semesterName.required' => 'Semester name is required.',
            'schoolYear.required' => 'School year is required.',
            'endDate.after_or_equal' => 'End date must be after or equal to start date.',
        ]);

        // Additional validation: Check if end date is before start date (in case validation rule doesn't catch it)
        if ($this->endDate && $this->startDate && $this->endDate < $this->startDate) {
            $this->addError('endDate', 'End date must be after or equal to start date.');
            return;
        }
        
        $data = [
            'name' => $this->semesterName,
            'school_year' => $this->schoolYear,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'is_active' => false, // Always set to false when creating/editing, use Set as Active button instead
        ];
        
        if ($isEditing) {
            $semester = Semester::findOrFail($this->semesterId);
            $semester->update($data);
            $message = 'Semester "' . $this->semesterName . '" updated successfully!';
        } else {
            Semester::create($data);
            $message = 'Semester "' . $this->semesterName . '" created successfully!';
        }
        
        $this->loadActiveSemester();
        $this->closeSemesterModal();
        
        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success',
            'title' => $isEditing ? 'Semester Updated' : 'Semester Created'
        ]);
    }

    public function deleteSemester($semesterId)
    {
        $semester = Semester::findOrFail($semesterId);
        
        $this->deleteSemesterId = $semester->id;
        $this->deleteSemesterName = $semester->name;
        $this->showDeleteSemesterModal = true;
    }

    public function confirmDeleteSemester()
    {
        if ($this->deleteSemesterId) {
            $semester = Semester::findOrFail($this->deleteSemesterId);
            $semesterName = $semester->name;
            
            // Check if semester has students
            if ($semester->studentInfos()->count() > 0) {
                $this->closeDeleteSemesterModal();
                $this->dispatch('show-toast', [
                    'message' => 'Cannot delete semester. It has students assigned.',
                    'type' => 'error',
                    'title' => 'Delete Failed'
                ]);
                return;
            }
            
            $semester->delete();
            $this->loadActiveSemester();
            $this->closeDeleteSemesterModal();
            
            $this->dispatch('show-toast', [
                'message' => 'Semester "' . $semesterName . '" deleted successfully!',
                'type' => 'success',
                'title' => 'Semester Deleted'
            ]);
        }
    }

    public function closeDeleteSemesterModal()
    {
        $this->showDeleteSemesterModal = false;
        $this->deleteSemesterId = null;
        $this->deleteSemesterName = null;
    }

    public function render()
    {
        return view('livewire.student.manage-semester');
    }
}
