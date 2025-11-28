<?php

namespace App\Livewire\Role;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class RoleManagement extends Component
{
    use WithPagination;

    public $selected = [];
    public $selectAll = false;
    public $selectPage = false;
    
    // Modal state
    public $showRoleModal = false;
    public $showDeleteModal = false;
    public $showDeleteMultipleModal = false;
    
    // Edit mode
    public $roleId = null;
    
    // Delete mode
    public $deleteRoleId = null;
    public $deleteRoleName = null;
    
    // Form fields
    public $name = '';
    public $guard_name = 'web';
    
    // Filter properties
    public $search = '';
    public $guardName = 'all'; // 'all', 'web', 'api', etc.

    public function mount()
    {
        // Computed properties are automatically calculated, no need to call them
    }

    #[Computed]
    public function roles()
    {
        $query = Role::query()
            ->select('roles.*')
            ->selectSub(function($subQuery) {
                $subQuery->selectRaw('COUNT(*)')
                    ->from('model_has_roles')
                    ->whereColumn('model_has_roles.role_id', 'roles.id')
                    ->where('model_has_roles.model_type', User::class);
            }, 'users_count');
        
        // Search filter - search in role name
        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        
        // Guard name filter
        if ($this->guardName !== 'all') {
            $query->where('guard_name', $this->guardName);
        }
        
        return $query->orderBy('name')->paginate(10);
    }
    
    #[Computed]
    public function hasActiveFilters()
    {
        return !empty($this->search) || $this->guardName !== 'all';
    }
    
    #[Computed]
    public function totalRoles()
    {
        return Role::count();
    }
    
    #[Computed]
    public function totalUsers()
    {
        return User::count();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedGuardName()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->guardName = 'all';
        $this->resetPage();
    }

    public function updatedSelectPage($value)
    {
        $pageIds = $this->roles->pluck('id')->map(fn($id) => (string) $id)->toArray();

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
        
        $pageIds = $this->roles->pluck('id')->map(fn($id) => (string) $id)->toArray();
        
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
        
        // Apply same filters as roles() method
        $query = Role::query();
        
        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        
        if ($this->guardName !== 'all') {
            $query->where('guard_name', $this->guardName);
        }
        
        $this->selected = $query->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    public function openAddModal()
    {
        $this->roleId = null;
        $this->resetForm();
        $this->showRoleModal = true;
    }

    public function editRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        
        $this->resetErrorBag();
        $this->showRoleModal = true;
    }

    public function closeRoleModal()
    {
        $this->showRoleModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->roleId = null;
        $this->name = '';
        $this->guard_name = 'web';
        $this->resetErrorBag();
    }

    public function saveRole()
    {
        $isEditing = !is_null($this->roleId);
        
        // Validation rules - ensure name is unique per guard_name
        $uniqueRule = 'required|string|max:255|unique:roles,name';
        if ($isEditing) {
            $uniqueRule .= ',' . $this->roleId . ',id';
        }
        $uniqueRule .= ',guard_name,' . $this->guard_name;
        
        $rules = [
            'name' => $uniqueRule,
            'guard_name' => 'required|string|max:255',
        ];

        $this->validate($rules);

        $roleData = [
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ];

        if ($isEditing) {
            $role = Role::findOrFail($this->roleId);
            $role->update($roleData);
            $message = 'Role updated successfully!';
        } else {
            Role::create($roleData);
            $message = 'Role created successfully!';
            // Reset pagination to show the new role
            $this->resetPage();
        }

        $this->closeRoleModal();
        
        // Dispatch browser event to trigger toast notification
        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success'
        ]);
    }

    public function deleteRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        
        $this->deleteRoleId = $role->id;
        $this->deleteRoleName = $role->name;
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        if ($this->deleteRoleId) {
            $role = Role::findOrFail($this->deleteRoleId);
            $roleName = $role->name;
            
            $role->delete();
            
            $this->closeDeleteModal();
            
            // Remove from selected array if it was selected
            $this->selected = array_filter($this->selected, fn($id) => $id != $this->deleteRoleId);
            
            // Dispatch browser event to trigger toast notification
            $this->dispatch('show-toast', [
                'message' => 'Role "' . $roleName . '" deleted successfully!',
                'type' => 'success'
            ]);
            
            // Reset pagination if needed
            if ($this->roles->isEmpty() && $this->roles->currentPage() > 1) {
                $this->resetPage();
            }
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteRoleId = null;
        $this->deleteRoleName = null;
    }

    public function deleteMultiple()
    {
        if (empty($this->selected)) {
            $this->dispatch('show-toast', [
                'message' => 'Please select at least one role to delete.',
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
        
        // Delete all selected roles
        Role::whereIn('id', $selectedIds)->delete();
        
        // Clear selected array
        $this->selected = [];
        $this->selectPage = false;
        $this->selectAll = false;
        
        $this->closeDeleteMultipleModal();
        
        // Dispatch browser event to trigger toast notification
        $this->dispatch('show-toast', [
            'message' => $count . ' ' . ($count === 1 ? 'role' : 'roles') . ' deleted successfully!',
            'type' => 'success'
        ]);
        
        // Reset pagination if needed
        if ($this->roles->isEmpty() && $this->roles->currentPage() > 1) {
            $this->resetPage();
        }
    }

    public function closeDeleteMultipleModal()
    {
        $this->showDeleteMultipleModal = false;
    }

    public function render()
    {
        return view('livewire.role.role-management', [
            'roles' => $this->roles,
        ]);
    }
}
