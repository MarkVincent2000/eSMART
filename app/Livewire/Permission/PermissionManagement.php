<?php

namespace App\Livewire\Permission;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleHasPermission;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class PermissionManagement extends Component
{
    use WithPagination;

    // Modal state
    public $showPermissionModal = false;
    public $showDeleteModal = false;
    
    // Edit mode
    public $permissionId = null;
    
    // Delete mode
    public $deletePermissionId = null;
    public $deletePermissionName = null;
    
    // Form fields
    public $name = '';
    public $guard_name = 'web';
    public $selectedRoles = [];
    
    // Filter properties
    public $search = '';
    public $guardName = 'all'; // 'all', 'web', 'api', etc.

    public function mount()
    {
        // Computed properties are automatically calculated, no need to call them
    }

    #[Computed]
    public function permissions()
    {
        $query = Permission::query()
            ->select('permissions.*')
            ->selectSub(function($subQuery) {
                $subQuery->selectRaw('COUNT(*)')
                    ->from('role_has_permissions')
                    ->whereColumn('role_has_permissions.permission_id', 'permissions.id');
            }, 'roles_count');
        
        // Search filter - search in permission name
        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        
        // Guard name filter
        if ($this->guardName !== 'all') {
            $query->where('guard_name', $this->guardName);
        }
        
        return $query->orderBy('name')->paginate(12);
    }
    
    #[Computed]
    public function hasActiveFilters()
    {
        return !empty($this->search) || $this->guardName !== 'all';
    }
    
    #[Computed]
    public function totalPermissions()
    {
        return Permission::count();
    }
    
    #[Computed]
    public function totalRoles()
    {
        return Role::count();
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

    #[Computed]
    public function roles()
    {
        // Get roles filtered by guard_name (default to 'web' for web guard)
        // Note: This will show roles matching the permission's guard when editing
        return Role::where('guard_name', $this->guard_name ?: 'web')->orderBy('name')->get();
    }

    #[Computed]
    public function roleOptions()
    {
        return $this->roles->map(function($role) {
            return [
                'value' => $role->id,
                'label' => ucfirst(str_replace('-', ' ', $role->name))
            ];
        })->toArray();
    }

    public function openAddModal()
    {
        $this->permissionId = null;
        $this->resetForm();
        $this->showPermissionModal = true;
    }

    public function editPermission($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        
        $this->permissionId = $permission->id;
        $this->name = $permission->name;
        $this->guard_name = $permission->guard_name;
        
        // Load permission's roles (get all role IDs that have this permission)
        // Query through role_has_permissions table using DB facade for reliability
        $this->selectedRoles = DB::table('role_has_permissions')
            ->where('permission_id', $permission->id)
            ->pluck('role_id')
            ->toArray();
        
        $this->resetErrorBag();
        $this->showPermissionModal = true;
    }

    public function closePermissionModal()
    {
        $this->showPermissionModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->permissionId = null;
        $this->name = '';
        $this->guard_name = 'web';
        $this->selectedRoles = [];
        $this->resetErrorBag();
    }

    public function savePermission()
    {
        $isEditing = !is_null($this->permissionId);
        
        // Ensure guard_name has a value
        $guardName = $this->guard_name ?: 'web';
        
        // Validation rules - ensure name is unique per guard_name
        if ($isEditing) {
            $uniqueRule = 'required|string|max:255|unique:permissions,name,' . $this->permissionId . ',id,guard_name,' . $guardName;
        } else {
            $uniqueRule = 'required|string|max:255|unique:permissions,name,NULL,id,guard_name,' . $guardName;
        }
        
        $rules = [
            'name' => $uniqueRule,
            'guard_name' => 'required|string|max:255|in:web,api',
            'selectedRoles' => 'nullable|array',
            'selectedRoles.*' => 'required|integer|exists:roles,id',
        ];

        $this->validate($rules);

        $permissionData = [
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ];

        if ($isEditing) {
            $permission = Permission::findOrFail($this->permissionId);
            $permission->update($permissionData);
            
            // Sync roles (assign permission to selected roles)
            // Get all current roles with this permission using DB facade
            $currentRoleIds = DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->pluck('role_id')
                ->toArray();
            
            // Remove permission from roles that are not in the selected list
            $rolesToRemove = array_diff($currentRoleIds, $this->selectedRoles ?? []);
            if (!empty($rolesToRemove)) {
                $roles = Role::whereIn('id', $rolesToRemove)->get();
                foreach ($roles as $role) {
                    $role->revokePermissionTo($permission);
                }
            }
            
            // Assign permission to newly selected roles
            if (!empty($this->selectedRoles)) {
                $rolesToAdd = array_diff($this->selectedRoles, $currentRoleIds);
                if (!empty($rolesToAdd)) {
                    $roles = Role::whereIn('id', $rolesToAdd)->get();
                    foreach ($roles as $role) {
                        $role->givePermissionTo($permission);
                    }
                }
            } else {
                // If no roles selected, remove permission from all roles
                if (!empty($currentRoleIds)) {
                    $roles = Role::whereIn('id', $currentRoleIds)->get();
                    foreach ($roles as $role) {
                        $role->revokePermissionTo($permission);
                    }
                }
            }
            
            $message = 'Permission updated successfully!';
        } else {
            $permission = Permission::create($permissionData);
            
            // Assign permission to selected roles
            if (!empty($this->selectedRoles)) {
                $roles = Role::whereIn('id', $this->selectedRoles)->get();
                foreach ($roles as $role) {
                    $role->givePermissionTo($permission);
                }
            }
            
            $message = 'Permission created successfully!';
            // Reset pagination to show the new permission
            $this->resetPage();
        }

        $this->closePermissionModal();
        
        // Dispatch browser event to trigger toast notification
        $this->dispatch('show-toast', [
            'message' => $message,
            'type' => 'success'
        ]);
    }

    public function deletePermission($permissionId)
    {
        $permission = Permission::findOrFail($permissionId);
        
        $this->deletePermissionId = $permission->id;
        $this->deletePermissionName = $permission->name;
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        if ($this->deletePermissionId) {
            $permission = Permission::findOrFail($this->deletePermissionId);
            $permissionName = $permission->name;
            
            $permission->delete();
            
            $this->closeDeleteModal();
            
            // Dispatch browser event to trigger toast notification
            $this->dispatch('show-toast', [
                'message' => 'Permission "' . $permissionName . '" deleted successfully!',
                'type' => 'success'
            ]);
            
            // Reset pagination if needed
            if ($this->permissions->isEmpty() && $this->permissions->currentPage() > 1) {
                $this->resetPage();
            }
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deletePermissionId = null;
        $this->deletePermissionName = null;
    }

    public function render()
    {
        return view('livewire.permission.permission-management', [
            'permissions' => $this->permissions,
            'roles' => $this->roles,
            'roleOptions' => $this->roleOptions,
        ]);
    }
}
