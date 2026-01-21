<?php

namespace App\Livewire\User;

use App\Models\User;
use App\Models\Role;
use App\Models\UserPersonalDetails;
use App\Models\Notification;
use App\Enums\Sex;
use App\Enums\Religion;
use App\Enums\GuardianRelationship;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
    public $showUpdateRolesModal = false;
    
    // Edit mode
    public $userId = null;
    
    // Delete mode
    public $deleteUserId = null;
    public $deleteUserName = null;
    
    // Form fields
    public $name = '';
    public $first_name = '';
    public $last_name = '';
    public $middle_name = '';
    public $name_extension = '';
    public $email = '';
    public $password = '';
    public $active_status = true;
    public $selectedRoles = [];
    public $bulkUpdateRoles = []; // Roles selected for bulk update
    
    // Personal Details fields
    public $sex = '';
    public $address = '';
    public $contact_no = '';
    public $date_of_birth = '';
    public $religion = '';
    
    // Guardian fields
    public $guardian_first_name = '';
    public $guardian_last_name = '';
    public $guardian_middle_name = '';
    public $guardian_suffix = '';
    public $guardian_relationship = '';
    public $guardian_contact_no = '';
    
    // Filter properties
    public $search = '';
    public $dateFrom = null;
    public $dateTo = null;
    public $status = 'all'; // 'all', 'active', 'inactive'
    public $roleFilter = 'all'; // 'all' or specific role name


    public function mount()
    {
        // Computed properties are automatically calculated, no need to call them
    }

    #[Computed]
    public function users()
    {
        $currentUser = Auth::user();
        $canAssignSuperAdmin = $currentUser && $currentUser->can('can-assign-super-admin-role');
        
        $query = User::query()
            ->select('id', 'name', 'first_name', 'last_name', 'middle_name', 'name_extension', 'email', 'active_status', 'created_at', 'photo_path', 'avatar')
            ->with('roles'); // Eager load roles to avoid N+1 queries
        
        // Hide users with 'super-admin' role if current user doesn't have permission
        if (!$canAssignSuperAdmin) {
            $query->whereDoesntHave('roles', function($q) {
                $q->where('name', 'super-admin');
            });
        }
        
        // Search filter - search in name fields and email
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('middle_name', 'like', '%' . $this->search . '%')
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
        
        // Role filter
        if ($this->roleFilter !== 'all') {
            $query->whereHas('roles', function($q) {
                $q->where('name', $this->roleFilter);
            });
        }
        
        return $query->latest()->paginate(10);
    }
    
    #[Computed]
    public function hasActiveFilters()
    {
        return !empty($this->search) 
            || $this->status !== 'all' 
            || $this->roleFilter !== 'all'
            || !is_null($this->dateFrom) 
            || !is_null($this->dateTo);
    }
    
    #[Computed]
    public function totalUsers()
    {
        $currentUser = Auth::user();
        $canAssignSuperAdmin = $currentUser && $currentUser->can('can-assign-super-admin-role');
        
        $query = User::query();
        
        // Exclude super-admin users if current user doesn't have permission
        if (!$canAssignSuperAdmin) {
            $query->whereDoesntHave('roles', function($q) {
                $q->where('name', 'super-admin');
            });
        }
        
        return $query->count();
    }
    
    #[Computed]
    public function totalActiveUsers()
    {
        $currentUser = Auth::user();
        $canAssignSuperAdmin = $currentUser && $currentUser->can('can-assign-super-admin-role');
        
        $query = User::where('active_status', true);
        
        // Exclude super-admin users if current user doesn't have permission
        if (!$canAssignSuperAdmin) {
            $query->whereDoesntHave('roles', function($q) {
                $q->where('name', 'super-admin');
            });
        }
        
        return $query->count();
    }
    
    #[Computed]
    public function totalInactiveUsers()
    {
        $currentUser = Auth::user();
        $canAssignSuperAdmin = $currentUser && $currentUser->can('can-assign-super-admin-role');
        
        $query = User::where('active_status', false);
        
        // Exclude super-admin users if current user doesn't have permission
        if (!$canAssignSuperAdmin) {
            $query->whereDoesntHave('roles', function($q) {
                $q->where('name', 'super-admin');
            });
        }
        
        return $query->count();
    }
    
    #[Computed]
    public function roles()
    {
        return Role::where('guard_name', 'web')->orderBy('name')->get();
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
        $this->roleFilter = 'all';
        $this->resetPage();
    }
    
    public function updatedRoleFilter()
    {
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
        
        $currentUser = Auth::user();
        $canAssignSuperAdmin = $currentUser && $currentUser->can('can-assign-super-admin-role');
        
        // Apply same filters as users() method
        $query = User::query();
        
        // Hide users with 'super-admin' role if current user doesn't have permission
        if (!$canAssignSuperAdmin) {
            $query->whereDoesntHave('roles', function($q) {
                $q->where('name', 'super-admin');
            });
        }
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('middle_name', 'like', '%' . $this->search . '%')
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
        
        // Role filter
        if ($this->roleFilter !== 'all') {
            $query->whereHas('roles', function($q) {
                $q->where('name', $this->roleFilter);
            });
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
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->middle_name = $user->middle_name ?? '';
        $this->name_extension = $user->name_extension ?? '';
        $this->email = $user->email;
        $this->password = ''; // Don't load password, leave it empty for user to optionally change
        
        // Explicitly cast to boolean and ensure it's set correctly
        $this->active_status = $user->active_status ? true : false;
        
        // Load user's roles (get all roles as array of role names)
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        
        // Load personal details
        $personalDetails = $user->personalDetails;
        if ($personalDetails) {
            $this->sex = $personalDetails->sex ?? '';
            $this->address = $personalDetails->address ?? '';
            $this->contact_no = $personalDetails->contact_no ?? '';
            $this->date_of_birth = $personalDetails->date_of_birth ? Carbon::parse($personalDetails->date_of_birth)->format('Y-m-d') : '';
            $this->religion = $personalDetails->religion ?? '';
            
            // Guardian fields
            $this->guardian_first_name = $personalDetails->guardian_first_name ?? '';
            $this->guardian_last_name = $personalDetails->guardian_last_name ?? '';
            $this->guardian_middle_name = $personalDetails->guardian_middle_name ?? '';
            $this->guardian_suffix = $personalDetails->guardian_suffix ?? '';
            $this->guardian_relationship = $personalDetails->guardian_relationship ?? '';
            $this->guardian_contact_no = $personalDetails->guardian_contact_no ?? '';
        } else {
            // Reset personal details if none exist
            $this->sex = '';
            $this->address = '';
            $this->contact_no = '';
            $this->date_of_birth = '';
            $this->religion = '';
            $this->guardian_first_name = '';
            $this->guardian_last_name = '';
            $this->guardian_middle_name = '';
            $this->guardian_suffix = '';
            $this->guardian_relationship = '';
            $this->guardian_contact_no = '';
        }
        
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
        $this->first_name = '';
        $this->last_name = '';
        $this->middle_name = '';
        $this->name_extension = '';
        $this->email = '';
        $this->password = '';
        $this->active_status = true;
        $this->selectedRoles = [];
        
        // Reset personal details
        $this->sex = '';
        $this->address = '';
        $this->contact_no = '';
        $this->date_of_birth = '';
        $this->religion = '';
        
        // Reset guardian fields
        $this->guardian_first_name = '';
        $this->guardian_last_name = '';
        $this->guardian_middle_name = '';
        $this->guardian_suffix = '';
        $this->guardian_relationship = '';
        $this->guardian_contact_no = '';
        
        $this->resetErrorBag();
    }

    public function saveUser()
    {
        $isEditing = !is_null($this->userId);
        
        // Validation rules
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'name_extension' => 'nullable|string|max:50',
            'email' => 'required|string|email|max:255|unique:users,email' . ($isEditing ? ',' . $this->userId : ''),
            'active_status' => 'boolean',
            'selectedRoles' => 'required|array|min:1',
            'selectedRoles.*' => 'required|string|exists:roles,name',
            'sex' => 'nullable|string|in:' . implode(',', array_column(Sex::cases(), 'value')),
            'address' => 'nullable|string',
            'contact_no' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'religion' => 'nullable|string|in:' . implode(',', array_column(Religion::cases(), 'value')),
            'guardian_first_name' => 'nullable|string|max:255',
            'guardian_last_name' => 'nullable|string|max:255',
            'guardian_middle_name' => 'nullable|string|max:255',
            'guardian_suffix' => 'nullable|string|max:50',
            'guardian_relationship' => 'nullable|string|in:' . implode(',', array_column(GuardianRelationship::cases(), 'value')),
            'guardian_contact_no' => 'nullable|string|max:255',
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

        // Check if user is trying to assign super-admin or admin roles without being super-admin
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser && $currentUser->hasRole('super-admin');
        
        if (!$isSuperAdmin) {
            $restrictedRoles = array_intersect($this->selectedRoles, ['super-admin', 'admin']);
            if (!empty($restrictedRoles)) {
                $this->addError('selectedRoles', 'You do not have permission to assign super-admin or admin roles.');
                $this->dispatch('show-toast', [
                    'message' => 'You do not have permission to assign super-admin or admin roles.',
                    'type' => 'error'
                ]);
                return;
            }
        }

        // Build full name from components
        $nameParts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        $fullName = implode(' ', $nameParts);
        if (!empty($this->name_extension)) {
            $fullName .= ', ' . $this->name_extension;
        }

        $userData = [
            'name' => $fullName ?: $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name ?: null,
            'name_extension' => $this->name_extension ?: null,
            'email' => $this->email,
            'active_status' => $this->active_status,
        ];
        
        // Only update password if it's provided (when editing) or always when creating
        if (!$isEditing || !empty($this->password)) {
            $userData['password'] = bcrypt($this->password);
        }

        // Initialize roles variable
        $roles = collect();
        
        if ($isEditing) {
            $user = User::findOrFail($this->userId);
            $user->update($userData);
            
            // Sync multiple roles (remove all existing roles and assign the new ones)
            $roles = Role::whereIn('name', $this->selectedRoles)->where('guard_name', 'web')->get();
            if ($roles->isNotEmpty()) {
                $user->syncRoles($roles);
            }
            
            $message = 'User updated successfully!';
        } else {
            $user = User::create($userData);
            
            // Assign multiple roles to new user
            $roles = Role::whereIn('name', $this->selectedRoles)->where('guard_name', 'web')->get();
            if ($roles->isNotEmpty()) {
                $user->syncRoles($roles);
            }
            
            $message = 'User invited successfully!';
            // Reset pagination to show the new user
            $this->resetPage();
        }
        
        // Send notifications to users who have the assigned roles
        if (!empty($this->selectedRoles)) {
            try {
                $this->notifyUsersWithRoles($user, $this->selectedRoles, $isEditing);
            } catch (\Exception $e) {
                // Log error but don't fail the user creation/update
                Log::error('Failed to send role assignment notifications: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
            }
        }
        
        // Save or update personal details
        $personalDetailsData = [
            'sex' => $this->sex ?: null,
            'address' => $this->address ?: null,
            'contact_no' => $this->contact_no ?: null,
            'date_of_birth' => $this->date_of_birth ?: null,
            'religion' => $this->religion ?: null,
            'guardian_first_name' => $this->guardian_first_name ?: null,
            'guardian_last_name' => $this->guardian_last_name ?: null,
            'guardian_middle_name' => $this->guardian_middle_name ?: null,
            'guardian_suffix' => $this->guardian_suffix ?: null,
            'guardian_relationship' => $this->guardian_relationship ?: null,
            'guardian_contact_no' => $this->guardian_contact_no ?: null,
        ];
        
        // Only save if at least one field has data
        if (array_filter($personalDetailsData)) {
            $personalDetails = $user->personalDetails;
            if ($personalDetails) {
                $personalDetails->update($personalDetailsData);
            } else {
                UserPersonalDetails::create(array_merge([
                    'user_id' => $user->id,
                ], $personalDetailsData));
            }
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

    public function openUpdateRolesModal()
    {
        if (empty($this->selected)) {
            $this->dispatch('show-toast', [
                'message' => 'Please select at least one user to update roles.',
                'type' => 'warning'
            ]);
            return;
        }
        
        $this->bulkUpdateRoles = [];
        $this->showUpdateRolesModal = true;
    }

    public function closeUpdateRolesModal()
    {
        $this->showUpdateRolesModal = false;
        $this->bulkUpdateRoles = [];
    }

    public function updateBulkRoles()
    {
        if (empty($this->selected)) {
            $this->dispatch('show-toast', [
                'message' => 'Please select at least one user to update roles.',
                'type' => 'warning'
            ]);
            return;
        }

        // Validate that at least one role is selected
        if (empty($this->bulkUpdateRoles)) {
            $this->dispatch('show-toast', [
                'message' => 'Please select at least one role to assign.',
                'type' => 'warning'
            ]);
            return;
        }

        // Check if user is trying to assign super-admin or admin roles without being super-admin
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser && $currentUser->hasRole('super-admin');
        
        if (!$isSuperAdmin) {
            $restrictedRoles = array_intersect($this->bulkUpdateRoles, ['super-admin', 'admin']);
            if (!empty($restrictedRoles)) {
                $this->dispatch('show-toast', [
                    'message' => 'You do not have permission to assign super-admin or admin roles.',
                    'type' => 'error'
                ]);
                return;
            }
        }

        // Validate roles exist
        $validRoles = Role::whereIn('name', $this->bulkUpdateRoles)
            ->where('guard_name', 'web')
            ->get();

        if ($validRoles->isEmpty()) {
            $this->dispatch('show-toast', [
                'message' => 'Invalid roles selected.',
                'type' => 'error'
            ]);
            return;
        }

        // Get selected user IDs
        $selectedUserIds = $this->selected;
        $users = User::whereIn('id', $selectedUserIds)->get();

        if ($users->isEmpty()) {
            $this->dispatch('show-toast', [
                'message' => 'No users found to update.',
                'type' => 'error'
            ]);
            return;
        }

        $updatedCount = 0;
        $roleNames = $this->bulkUpdateRoles;

        // Update roles for each selected user
        foreach ($users as $user) {
            try {
                // Sync roles (replace all existing roles with new ones)
                $user->syncRoles($validRoles);
                $updatedCount++;

                // Send notifications for role assignment
                try {
                    $this->notifyUsersWithRoles($user, $roleNames, true); // true = isEditing
                } catch (\Exception $e) {
                    // Log but don't fail the update
                    Log::error('Failed to send notifications for user ' . $user->id . ': ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                Log::error('Failed to update roles for user ' . $user->id . ': ' . $e->getMessage());
            }
        }

        // Clear selection
        $this->selected = [];
        $this->selectPage = false;
        $this->selectAll = false;
        $this->bulkUpdateRoles = [];

        $this->closeUpdateRolesModal();

        // Dispatch success message
        $this->dispatch('show-toast', [
            'message' => "Successfully updated roles for {$updatedCount} " . ($updatedCount === 1 ? 'user' : 'users') . '!',
            'type' => 'success'
        ]);
    }

    #[Computed]
    public function roleOptions()
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser && $currentUser->hasRole('super-admin');
        
        return $this->roles
            ->filter(function($role) use ($isSuperAdmin) {
                // If user is super-admin, only show super-admin and admin roles
                if ($isSuperAdmin) {
                    return in_array($role->name, ['super-admin', 'admin']);
                }
                // If user is not super-admin, hide super-admin and admin roles
                return !in_array($role->name, ['super-admin', 'admin']);
            })
            ->map(function($role) {
                return [
                    'value' => $role->name,
                    'label' => ucfirst(str_replace('-', ' ', $role->name))
                ];
            })
            ->values()
            ->toArray();
    }
    
    #[Computed]
    public function roleFilterOptions()
    {
        $currentUser = Auth::user();
        $canAssignSuperAdmin = $currentUser && $currentUser->can('can-assign-super-admin-role');
        
        $options = [['value' => 'all', 'label' => 'All Roles']];
        
        $roleOptions = $this->roles
            ->filter(function($role) use ($canAssignSuperAdmin) {
                // If role is 'super-admin', only show it if user has permission
                if ($role->name === 'super-admin') {
                    return $canAssignSuperAdmin;
                }
                // Show all other roles
                return true;
            })
            ->map(function($role) {
                return [
                    'value' => $role->name,
                    'label' => ucfirst(str_replace('-', ' ', $role->name))
                ];
            })
            ->toArray();
        
        return array_merge($options, $roleOptions);
    }
    
    /**
     * Get all sex enum cases for dropdown
     */
    public function getSexOptionsProperty()
    {
        return Sex::cases();
    }

    /**
     * Get all religion enum cases for dropdown
     */
    public function getReligionOptionsProperty()
    {
        return Religion::cases();
    }

    /**
     * Get all guardian relationship enum cases for dropdown
     */
    public function getGuardianRelationshipOptionsProperty()
    {
        return GuardianRelationship::cases();
    }

    /**
     * Notify users who have the roles that were assigned to the user
     */
    protected function notifyUsersWithRoles(User $assignedUser, array $assignedRoleNames, bool $isEditing = false)
    {
        try {
            Log::info('Starting notification process for user: ' . $assignedUser->id . ' with roles: ' . implode(', ', $assignedRoleNames));
            
            // Get role IDs from role names
            $roleIds = Role::whereIn('name', $assignedRoleNames)
                ->where('guard_name', 'web')
                ->pluck('id')
                ->toArray();
            
            Log::info('Found role IDs: ' . implode(', ', $roleIds));
            
            if (empty($roleIds)) {
                Log::warning('No role IDs found for role names: ' . implode(', ', $assignedRoleNames));
                return;
            }
            
            // Format role names for display
            $roleLabels = array_map(function($roleName) {
                return ucfirst(str_replace('-', ' ', $roleName));
            }, $assignedRoleNames);
            $rolesText = count($roleLabels) === 1 
                ? $roleLabels[0] 
                : implode(', ', array_slice($roleLabels, 0, -1)) . ' and ' . end($roleLabels);
            
            // Prepare notification data
            $action = $isEditing ? 'updated' : 'assigned';
            $notificationData = [
                'assigned_user_id' => $assignedUser->id,
                'assigned_user_name' => $assignedUser->name,
                'assigned_user_email' => $assignedUser->email,
                'roles' => $assignedRoleNames,
                'action' => $action,
            ];
            
            $notificationCount = 0;
            
            // 1. Notify the user who was assigned the roles (about their new roles)
            if ($assignedUser->active_status) {
                try {
                    $userTitle = $isEditing 
                        ? 'Your Roles Have Been Updated' 
                        : 'You Have Been Assigned New Roles';
                    
                    $userBody = $isEditing
                        ? "Your account has been updated with the following role(s): {$rolesText}"
                        : "You have been assigned the following role(s): {$rolesText}";
                    
                    $notification = Notification::create([
                        'user_id' => $assignedUser->id,
                        'type' => 'role_assigned',
                        'title' => $userTitle,
                        'body' => $userBody,
                        'url' => '#',
                        'data' => $notificationData,
                        'notifiable_id' => $assignedUser->id,
                        'notifiable_type' => User::class,
                        'read_at' => null,
                    ]);
                    $notificationCount++;
                    Log::info("Created notification ID: {$notification->id} for assigned user: {$assignedUser->id} ({$assignedUser->name})");
                } catch (\Exception $e) {
                    Log::error("Failed to create notification for assigned user {$assignedUser->id}: " . $e->getMessage());
                }
            }
            
            // 2. Notify other users who have these roles (about the new member)
            $otherUsersToNotify = collect();
            
            foreach ($assignedRoleNames as $roleName) {
                $usersWithRole = User::role($roleName)
                    ->where('id', '!=', $assignedUser->id)
                    ->where('active_status', true)
                    ->get();
                $otherUsersToNotify = $otherUsersToNotify->merge($usersWithRole);
            }
            
            // Remove duplicates by user ID
            $otherUsersToNotify = $otherUsersToNotify->unique('id')->values();
            
            Log::info('Found ' . $otherUsersToNotify->count() . ' other users with the assigned roles to notify');
            
            if ($otherUsersToNotify->isNotEmpty()) {
                $otherUsersTitle = $isEditing 
                    ? 'User Roles Updated' 
                    : 'New User Assigned Roles';
                
                $otherUsersBody = $isEditing
                    ? "{$assignedUser->name} has been updated with the following role(s): {$rolesText}"
                    : "{$assignedUser->name} has been assigned the following role(s): {$rolesText}";
                
                foreach ($otherUsersToNotify as $notifiedUser) {
                    try {
                        $notification = Notification::create([
                            'user_id' => $notifiedUser->id,
                            'type' => 'role_assigned',
                            'title' => $otherUsersTitle,
                            'body' => $otherUsersBody,
                            'url' => '#',
                            'data' => $notificationData,
                            'notifiable_id' => $assignedUser->id,
                            'notifiable_type' => User::class,
                            'read_at' => null,
                        ]);
                        $notificationCount++;
                        Log::info("Created notification ID: {$notification->id} for user: {$notifiedUser->id} ({$notifiedUser->name})");
                    } catch (\Exception $e) {
                        // Log error but continue with other users
                        Log::error("Failed to create notification for user {$notifiedUser->id}: " . $e->getMessage());
                        Log::error("Exception trace: " . $e->getTraceAsString());
                    }
                }
            }
            
            // Log notification count for debugging
            Log::info("Successfully created {$notificationCount} role assignment notifications for user: {$assignedUser->name} (ID: {$assignedUser->id})");
            
        } catch (\Exception $e) {
            // Log error but don't fail the user creation/update
            Log::error("Failed to send role assignment notifications: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.user.user-management', [
            'users' => $this->users,
            'roles' => $this->roles,
            'roleOptions' => $this->roleOptions,
            'roleFilterOptions' => $this->roleFilterOptions,
        ]);
    }
}
