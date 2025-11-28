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
        $user = Auth::user();
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

        $personalDetails = $user ? $user->personalDetails : null;

        return view('livewire.profile.user-profile', [
            'user' => $user,
            'activityLogs' => $activityLogs,
            'hasMoreActivityLogs' => $hasMoreActivityLogs,
            'personalDetails' => $personalDetails,
        ]);
    }
}
