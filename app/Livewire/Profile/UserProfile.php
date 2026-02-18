<?php

namespace App\Livewire\Profile;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class UserProfile extends Component
{
    use WithPagination;

    public $userProfile;
    public $userId = null;
    /**
     * Controls which profile tab is active.
     * Valid values: 'overview', 'activities', 'users'.
     */
    public $activeTab = 'overview';
    public $activitiesPerPage = 5;
    public $usersPerPage = 10;
    public $userSearch = '';

    public function mount($userId = null)
    {
        // Get user ID from parameter or query string
        $this->userId = $userId ?? request()->query('user_id');
        
        // Load the specified user or default to authenticated user
        if ($this->userId) {
            $this->userProfile = User::find($this->userId);
            
            // If user not found, fall back to authenticated user
            if (!$this->userProfile) {
                $this->userProfile = Auth::user();
                $this->userId = null;
            }
        } else {
            $this->userProfile = Auth::user();
        }

        // Read the desired tab from the query string (?tab=overview|activities|users)
        $tab = request()->query('tab', 'overview');
        if (!in_array($tab, ['overview', 'activities', 'users'], true)) {
            $tab = 'overview';
        }

        $this->activeTab = $tab;
    }
    
    public function updatedUserSearch()
    {
        $this->resetPage('usersPage');
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage('activitiesPage');
        $this->resetPage('usersPage');
    }

    /**
     * Navigate to a user's profile page.
     *
     * @param int $userId
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function viewUserProfile($userId)
    {
        // Validate that the user exists
        $user = User::find($userId);
        
        if (!$user) {
            $this->dispatch('show-toast', [
                'message' => 'User not found.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }

        // Redirect to profile page with user_id parameter
        // Using dot notation format: profile.index (matches sidebar links)
        return redirect()->to('profile.index?user_id=' . $userId);
    }

    public function getProfileCompletionPercentageProperty()
    {
        $user = $this->userProfile ?? Auth::user();
        if (!$user) {
            return 0;
        }

        $percentage = 0;
        
        // Required fields (10% each = 30% total)
        if (!empty($user->first_name)) $percentage += 10;
        if (!empty($user->last_name)) $percentage += 10;
        if (!empty($user->email)) $percentage += 10;
        
        // Optional name fields (10% total)
        if (!empty($user->middle_name)) $percentage += 7;
        if (!empty($user->name_extension)) $percentage += 3;
        
        // Photo fields (20% total)
        if (!empty($user->photo_path) || !empty($user->avatar)) $percentage += 12;
        if (!empty($user->cover_photo_path)) $percentage += 8;
        
        // Personal details fields (25% total)
        $personalDetails = $user->personalDetails;
        if ($personalDetails) {
            if (!empty($personalDetails->sex)) $percentage += 5;
            if (!empty($personalDetails->date_of_birth)) $percentage += 5;
            if (!empty($personalDetails->religion)) $percentage += 5;
            if (!empty($personalDetails->contact_no)) $percentage += 5;
            if (!empty($personalDetails->address)) $percentage += 5;
        }
        
        // Guardian information fields (15% total)
        if ($personalDetails) {
            if (!empty($personalDetails->guardian_first_name)) $percentage += 3;
            if (!empty($personalDetails->guardian_last_name)) $percentage += 3;
            if (!empty($personalDetails->guardian_relationship)) $percentage += 3;
            if (!empty($personalDetails->guardian_contact_no)) $percentage += 3;
            if (!empty($personalDetails->guardian_middle_name)) $percentage += 2;
            if (!empty($personalDetails->guardian_suffix)) $percentage += 1;
        }
        
        return (int) round($percentage);
    }

    public function render()
    {
        $user = $this->userProfile ?? Auth::user();
        $activityLogs = collect();

        if ($user) {
            $query = ActivityLog::where('causer_type', get_class($user))
                ->where('causer_id', $user->getKey())
                ->latest();

            $activityLogs = $query->paginate($this->activitiesPerPage, pageName: 'activitiesPage');
        }

        $personalDetails = $user ? $user->personalDetails : null;

        $usersQuery = User::query()
            ->select('id', 'name', 'first_name', 'last_name', 'middle_name', 'name_extension', 'email', 'active_status', 'created_at', 'photo_path', 'avatar')
            ->with('roles');

        if (!empty($this->userSearch)) {
            $usersQuery->where(function ($q) {
                $q->where('name', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('first_name', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('last_name', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('middle_name', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->userSearch . '%');
            });
        }

        $users = $usersQuery->latest()->paginate($this->usersPerPage, pageName: 'usersPage');

        return view('livewire.profile.user-profile', [
            'user' => $user,
            'activityLogs' => $activityLogs,
            'personalDetails' => $personalDetails,
            'users' => $users,
        ]);
    }
}
