<?php

namespace App\Livewire\Profile;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserProfile extends Component
{

    public $userProfile;
    /**
     * Controls which profile tab is active.
     * Valid values: 'overview', 'activities'.
     */
    public $activeTab = 'overview';
    public $activitiesPerPage = 5;

    public function mount()
    {
        $this->userProfile = Auth::user();

        // Read the desired tab from the query string (?tab=overview|activities)
        $tab = request()->query('tab', 'overview');
        if (!in_array($tab, ['overview', 'activities'], true)) {
            $tab = 'overview';
        }

        $this->activeTab = $tab;
    }

    public function loadMoreActivities(): void
    {
        $this->activitiesPerPage += 5;
    }

    public function getProfileCompletionPercentageProperty()
    {
        $user = $this->userProfile;

        if (!$user) {
            return 0;
        }

        $percentage = 0;
        
        // Required fields (15% each = 45% total)
        if (!empty($user->first_name)) $percentage += 15;
        if (!empty($user->last_name)) $percentage += 15;
        if (!empty($user->email)) $percentage += 15;
        
        // Optional name fields (15% total)
        if (!empty($user->middle_name)) $percentage += 10;
        if (!empty($user->name_extension)) $percentage += 5;
        
        // Photo fields (25% total)
        if (!empty($user->photo_path) || !empty($user->avatar)) $percentage += 15;
        if (!empty($user->cover_photo_path)) $percentage += 10;
        
        // Address fields (15% total)
        $primaryAddress = $user->primaryAddress;
        if ($primaryAddress) {
            if (!empty($primaryAddress->phone)) $percentage += 7.5;
            if (!empty($primaryAddress->address)) $percentage += 7.5;
        }
        
        return (int) round($percentage);
    }

    public function render()
    {
        $user = $this->userProfile ?? Auth::user();
        $activityLogs = collect();
        $hasMoreActivityLogs = false;

        if ($user) {
            $query = ActivityLog::where('causer_type', get_class($user))
                ->where('causer_id', $user->getKey())
                ->latest();

            $totalLogs = (clone $query)->count();

            $activityLogs = $query
                ->limit($this->activitiesPerPage)
                ->get();

            $hasMoreActivityLogs = $totalLogs > $activityLogs->count();
        }

        return view('livewire.profile.user-profile', [
            'user' => $user,
            'activityLogs' => $activityLogs,
            'hasMoreActivityLogs' => $hasMoreActivityLogs,
        ]);
    }
}
