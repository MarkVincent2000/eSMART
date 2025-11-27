<?php

namespace App\Livewire\User;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class UserManagement extends Component
{
    use WithPagination;

    public $selected = [];
    public $selectAll = false;
    public $selectPage = false;
    
    // Modal state
    public $showInviteModal = false;
    public $showDeleteModal = false;
    public $showDeleteMultipleModal = false;
    
    // Edit mode
    public $userId = null;
    
    // Delete mode
    public $deleteUserId = null;
    public $deleteUserName = null;
    
    // Form fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $active_status = true;
    
    // Filter properties
    public $search = '';
    public $dateFrom = null;
    public $dateTo = null;
    public $status = 'all'; // 'all', 'active', 'inactive'

    #[Computed]
    public function users()
    {
        $query = User::query()
            ->select('id', 'name', 'email', 'active_status', 'created_at');
        
        // Search filter - search in name and email
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
        
        // Status filter
        if ($this->status !== 'all') {
            $query->where('active_status', $this->status === 'active');
        }
        
        // Date range filter
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        
        return $query->latest()->paginate(10);
    }
    
    #[Computed]
    public function hasActiveFilters()
    {
        return !empty($this->search) 
            || $this->status !== 'all' 
            || !is_null($this->dateFrom) 
            || !is_null($this->dateTo);
    }
    
    #[Computed]
    public function totalUsers()
    {
        return User::count();
    }
    
    #[Computed]
    public function totalActiveUsers()
    {
        return User::where('active_status', true)->count();
    }
    
    #[Computed]
    public function totalInactiveUsers()
    {
        return User::where('active_status', false)->count();
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedStatus()
    {
        $this->resetPage();
    }
    
    public function updatedDateFrom()
    {
        $this->resetPage();
    }
    
    public function updatedDateTo()
    {
        $this->resetPage();
    }
    
    public function resetFilters()
    {
        $this->search = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->status = 'all';
        $this->resetPage();
    }

    public function updatedSelectPage($value)
    {
        $pageIds = $this->users->pluck('id')->map(fn($id) => (string) $id)->toArray();

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
        
        $pageIds = $this->users->pluck('id')->map(fn($id) => (string) $id)->toArray();
        
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
        
        // Apply same filters as users() method
        $query = User::query();
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->status !== 'all') {
            $query->where('active_status', $this->status === 'active');
        }
        
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }
        
        $this->selected = $query->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function openInviteModal()
    {
        $this->userId = null;
        $this->resetForm();
        $this->showInviteModal = true;
    }

    public function editUser($userId)
    {
        $user = User::findOrFail($userId);
        
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = ''; // Don't load password, leave it empty for user to optionally change
        $this->active_status = $user->active_status;
        
        $this->resetErrorBag();
        $this->showInviteModal = true;
    }

    public function closeInviteModal()
    {
        $this->showInviteModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->active_status = true;
        $this->resetErrorBag();
    }

    public function saveUser()
    {
        $isEditing = !is_null($this->userId);
        
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email' . ($isEditing ? ',' . $this->userId : ''),
            'active_status' => 'boolean',
        ];
        
        // Password is required only when creating, optional when editing
        if (!$isEditing) {
            $rules['password'] = 'required|string|min:8';
        } else {
            // If editing and password is provided, validate it
            if (!empty($this->password)) {
                $rules['password'] = 'string|min:8';
            }
        }

        $this->validate($rules);

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'active_status' => $this->active_status,
        ];
        
        // Only update password if it's provided (when editing) or always when creating
        if (!$isEditing || !empty($this->password)) {
            $userData['password'] = bcrypt($this->password);
        }

        if ($isEditing) {
            $user = User::findOrFail($this->userId);
            $user->update($userData);
            $message = 'User updated successfully!';
        } else {
            User::create($userData);
            $message = 'User invited successfully!';
            // Reset pagination to show the new user
            $this->resetPage();
        }

        $this->closeInviteModal();
        
        // Dispatch browser event to trigger toast notification
        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success'
        ]);
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        
        $this->deleteUserId = $user->id;
        $this->deleteUserName = $user->name;
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        if ($this->deleteUserId) {
            $user = User::findOrFail($this->deleteUserId);
            $userName = $user->name;
            
            $user->delete();
            
            $this->closeDeleteModal();
            
            // Remove from selected array if it was selected
            $this->selected = array_filter($this->selected, fn($id) => $id != $this->deleteUserId);
            
            // Dispatch browser event to trigger toast notification
            $this->dispatch('show-toast', [
                'message' => 'User "' . $userName . '" deleted successfully!',
                'type' => 'success'
            ]);
            
            // Reset pagination if needed
            if ($this->users->isEmpty() && $this->users->currentPage() > 1) {
                $this->resetPage();
            }
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteUserId = null;
        $this->deleteUserName = null;
    }

    public function deleteMultiple()
    {
        if (empty($this->selected)) {
            $this->dispatch('show-toast', [
                'message' => 'Please select at least one user to delete.',
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

        $count = count($this->selected);
        $selectedIds = $this->selected;
        
        // Delete all selected users
        User::whereIn('id', $selectedIds)->delete();
        
        // Clear selected array
        $this->selected = [];
        $this->selectPage = false;
        $this->selectAll = false;
        
        $this->closeDeleteMultipleModal();
        
        // Dispatch browser event to trigger toast notification
        $this->dispatch('show-toast', [
            'message' => $count . ' ' . ($count === 1 ? 'user' : 'users') . ' deleted successfully!',
            'type' => 'success'
        ]);
        
        // Reset pagination if needed
        if ($this->users->isEmpty() && $this->users->currentPage() > 1) {
            $this->resetPage();
        }
    }

    public function closeDeleteMultipleModal()
    {
        $this->showDeleteMultipleModal = false;
    }

    public function render()
    {
        return view('livewire.user.user-management', [
            'users' => $this->users,
        ]);
    }
}
