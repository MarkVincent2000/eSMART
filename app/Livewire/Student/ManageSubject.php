<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Subject;

class ManageSubject extends Component
{
    use WithPagination;

    public $search = '';
    
    // Filter properties
    public $yearLevelFilter = 'all'; // 'all' or specific year level
    public $statusFilter = 'all'; // 'all', 'active', 'inactive'

    // Selection properties for checkboxes
    public $selected = [];
    public $selectAll = false;
    public $selectPage = false;

    // Modal state
    public $showCreateModal = false;
    public $showDeleteMultipleModal = false;
    public $showViewSubjectModal = false;
    public $selectedSubjectId = null;
    public $showDeleteSubjectModal = false;
    public $deleteSubjectId = null;
    public $deleteSubjectName = null;

    // Form fields
    public $subjectId = null;
    public $code = '';
    public $name = '';
    public $description = '';
    public $units = '';
    public $yearLevel = null;
    public $isActive = true;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedYearLevelFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function updatedSelectPage($value)
    {
        // Get current page's subject IDs
        $query = Subject::query();

        // Apply same filters as render method
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->yearLevelFilter !== 'all' && !empty($this->yearLevelFilter)) {
            $query->where('year_level', $this->yearLevelFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $pageIds = $query->latest()
            ->paginate(10, ['*'], 'page', $this->getPage())
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();

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
        
        // Get current page's subject IDs
        $query = Subject::query();

        // Apply same filters as render method
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->yearLevelFilter !== 'all' && !empty($this->yearLevelFilter)) {
            $query->where('year_level', $this->yearLevelFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $pageIds = $query->latest()
            ->paginate(10, ['*'], 'page', $this->getPage())
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
        
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
        
        $this->selected = $this->getFilteredSubjects()
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    protected function getFilteredSubjects()
    {
        $query = Subject::query();

        // Search filter - search in code, name, or description
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Year level filter
        if ($this->yearLevelFilter !== 'all' && !empty($this->yearLevelFilter)) {
            $query->where('year_level', $this->yearLevelFilter);
        }

        // Status filter
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->latest()->get();
    }

    public function deleteMultiple()
    {
        if (empty($this->selected)) {
            $this->dispatch('show-toast', [
                'message' => 'Please select at least one subject to delete.',
                'type' => 'warning'
            ]);
            return;
        }
        
        $this->showDeleteMultipleModal = true;
    }

    public function confirmDeleteMultiple()
    {
        if (empty($this->selected)) {
            $this->closeDeleteMultipleModal();
            return;
        }

        $selectedIds = array_filter(array_map('intval', $this->selected));
        
        if (empty($selectedIds)) {
            $this->dispatch('show-toast', [
                'message' => 'No valid subjects selected.',
                'type' => 'error'
            ]);
            $this->closeDeleteMultipleModal();
            return;
        }

        $deletedCount = Subject::whereIn('id', $selectedIds)->delete();

        // Clear selected array
        $this->selected = [];
        $this->selectAll = false;
        $this->selectPage = false;
        
        $this->closeDeleteMultipleModal();
        $this->resetPage();

        $this->dispatch('show-toast', [
            'message' => "Successfully deleted {$deletedCount} subject(s).",
            'type' => 'success'
        ]);
    }

    public function closeDeleteMultipleModal()
    {
        $this->showDeleteMultipleModal = false;
    }

    public function deleteSubject($subjectId)
    {
        $subject = Subject::find($subjectId);
            
        if (!$subject) {
            $this->dispatch('show-toast', [
                'message' => 'Subject not found.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }
        
        $this->deleteSubjectId = $subject->id;
        $this->deleteSubjectName = $subject->name;
        $this->showDeleteSubjectModal = true;
    }

    public function confirmDeleteSubject()
    {
        if (!$this->deleteSubjectId) {
            return;
        }
        
        $subject = Subject::findOrFail($this->deleteSubjectId);
        $subjectName = $subject->name;
        
        // Check if subject has assignments or grades
        $hasAssignments = $subject->assignments()->count() > 0;
        $hasGrades = $subject->grades()->count() > 0;
        
        if ($hasAssignments || $hasGrades) {
            $this->closeDeleteSubjectModal();
            $this->dispatch('show-toast', [
                'message' => 'Cannot delete subject. It has assignments or grades associated with it.',
                'type' => 'error',
                'title' => 'Delete Failed'
            ]);
            return;
        }
        
        $subject->delete();
        
        // Remove from selected array if it was selected
        $this->selected = array_filter($this->selected, fn($id) => $id != $this->deleteSubjectId);
        
        $this->closeDeleteSubjectModal();
        $this->resetPage();
        
        $this->dispatch('show-toast', [
            'message' => 'Subject "' . $subjectName . '" deleted successfully!',
            'type' => 'success',
            'title' => 'Subject Deleted'
        ]);
    }

    public function closeDeleteSubjectModal()
    {
        $this->showDeleteSubjectModal = false;
        $this->deleteSubjectId = null;
        $this->deleteSubjectName = null;
    }

    public function viewSubject($subjectId)
    {
        $subject = Subject::find($subjectId);
            
        if (!$subject) {
            $this->dispatch('show-toast', [
                'message' => 'Subject not found.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }
        
        $this->selectedSubjectId = $subjectId;
        $this->showViewSubjectModal = true;
    }

    public function closeViewSubjectModal()
    {
        $this->showViewSubjectModal = false;
        $this->selectedSubjectId = null;
    }

    #[Computed]
    public function selectedSubject()
    {
        if (!$this->selectedSubjectId) {
            return null;
        }
        
        return Subject::find($this->selectedSubjectId);
    }

    public function openCreateModal()
    {
        $this->subjectId = null;
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function editSubject($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        
        $this->subjectId = $subject->id;
        $this->code = $subject->code ?? '';
        $this->name = $subject->name;
        $this->description = $subject->description ?? '';
        $this->units = $subject->units ? (string) $subject->units : '';
        $this->yearLevel = $subject->year_level;
        $this->isActive = $subject->is_active;
        
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->subjectId = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->units = '';
        $this->yearLevel = null;
        $this->isActive = true;
        $this->resetErrorBag();
    }

    public function saveSubject()
    {
        $isEditing = !is_null($this->subjectId);

        // Validation rules
        $rules = [
            'code' => 'nullable|string|max:50|unique:subjects,code' . ($isEditing ? ',' . $this->subjectId : ''),
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'units' => 'nullable|numeric|min:0|max:999.99',
            'yearLevel' => 'nullable|integer|min:1|max:12',
            'isActive' => 'boolean',
        ];

        $this->validate($rules);

        $subjectData = [
            'code' => $this->code ?: null,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'units' => $this->units ? (float) $this->units : null,
            'year_level' => $this->yearLevel ? (int) $this->yearLevel : null,
            'is_active' => $this->isActive,
        ];

        if ($isEditing) {
            $subject = Subject::findOrFail($this->subjectId);
            $subject->update($subjectData);
            $message = 'Subject updated successfully!';
        } else {
            Subject::create($subjectData);
            $message = 'Subject created successfully!';
            $this->resetPage();
        }

        $this->closeCreateModal();

        // Dispatch browser event to trigger toast notification
        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success'
        ]);
    }

    public function render()
    {
        $query = Subject::query();

        // Search filter - search in code, name, or description
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Year level filter
        if ($this->yearLevelFilter !== 'all' && !empty($this->yearLevelFilter)) {
            $query->where('year_level', $this->yearLevelFilter);
        }

        // Status filter
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $subjects = $query->latest()->paginate(10);
        
        return view('livewire.student.manage-subject', [
            'subjects' => $subjects
        ]);
    }
}
