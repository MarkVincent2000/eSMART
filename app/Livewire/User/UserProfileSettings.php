<?php

namespace App\Livewire\User;

use App\Models\User;
use App\Models\Address;
use App\Models\LoginHistory;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserProfileSettings extends Component
{
    use WithFileUploads;
    use WithPagination;

    // Profile fields
    public $first_name = '';
    public $last_name = '';
    public $middle_name = '';
    public $name_extension = '';
    public $email = '';
    
    // Address fields
    public $address_id = null;
    public $phone = '';
    public $address = '';
    
    // Password change fields
    public $current_password = '';
    public $new_password = '';
    public $confirm_password = '';

    // File upload fields
    public $profile_photo;
    public $cover_photo;
    public $profile_photo_preview;
    public $cover_photo_preview;

    // Active tab
    public $activeTab = 'personalDetails';
    public $loginHistoryPerPage = 5;
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $user = Auth::user();
        
        // Get active tab from URL query parameter
        $this->activeTab = request()->query('tab', 'personalDetails');
        
        // Validate tab value
        if (!in_array($this->activeTab, ['personalDetails', 'changePassword'])) {
            $this->activeTab = 'personalDetails';
        }
        
        if ($user) {
            // Load user profile fields
            $this->first_name = $user->first_name ?? '';
            $this->last_name = $user->last_name ?? '';
            $this->middle_name = $user->middle_name ?? '';
            $this->name_extension = $user->name_extension ?? '';
            $this->email = $user->email ?? '';
            
            // Load address data from Address model
            $primaryAddress = $user->primaryAddress;
            if ($primaryAddress) {
                $this->address_id = $primaryAddress->id;
                $this->phone = $primaryAddress->phone ?? '';
                $this->address = $primaryAddress->address ?? '';
            }
        }
    }

    public function updatingActiveTab()
    {
        $this->resetPage('loginHistoryPage');
    }

    public function updateProfile()
    {
        $user = Auth::user();
        
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'name_extension' => 'nullable|string|max:50',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        // Build full name from components
        $nameParts = array_filter([$this->first_name, $this->middle_name, $this->last_name]);
        $fullName = implode(' ', $nameParts);
        if (!empty($this->name_extension)) {
            $fullName .= ', ' . $this->name_extension;
        }

        // Update user profile
        $user->update([
            'name' => $fullName ?: $this->first_name . ' ' . $this->last_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name ?: null,
            'name_extension' => $this->name_extension ?: null,
            'email' => $this->email,
        ]);

        // Update or create address
        if ($this->address_id) {
            // Update existing address
            $address = Address::where('id', $this->address_id)
                ->where('user_id', $user->id)
                ->first();
            
            if ($address) {
                $address->update([
                    'phone' => $this->phone ?: null,
                    'address' => $this->address ?: null,
                ]);
            }
        } else {
            // Create new address if any field has data
            if (!empty($this->phone) || !empty($this->address)) {
                $address = Address::create([
                    'user_id' => $user->id,
                    'phone' => $this->phone ?: null,
                    'address' => $this->address ?: null,
                ]);
                $this->address_id = $address->id;
            }
        }

        // Handle profile photo upload
        if ($this->profile_photo) {
            $this->uploadProfilePhoto($user);
        }

        // Handle cover photo upload
        if ($this->cover_photo) {
            $this->uploadCoverPhoto($user);
        }

        // Dispatch browser event to trigger toast notification
        $this->dispatch('show-toast', [
            'message' => 'Profile updated successfully!',
            'type' => 'success'
        ]);
    }

    /**
     * Upload and save profile photo.
     *
     * @param  User  $user
     * @return void
     */
    public function uploadProfilePhoto($user)
    {
        $this->validate([
            'profile_photo' => 'image|max:2048|mimes:jpeg,jpg,png,gif,webp', // 2MB max
        ]);

        // Delete old photo if exists
        if ($user->photo_path) {
            // Remove storage/ prefix if it exists (from URLs), otherwise use path as is
            $oldPath = str_replace('storage/', '', $user->photo_path);
            $oldPath = ltrim($oldPath, '/');
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // Store new photo
        $path = $this->profile_photo->store('profile-photos', 'public');
        
        // Update user photo_path
        $user->update([
            'photo_path' => $path,
        ]);

        // Reset preview and file
        $this->profile_photo = null;
        $this->profile_photo_preview = null;
    }

    /**
     * Upload and save cover photo.
     *
     * @param  User  $user
     * @return void
     */
    public function uploadCoverPhoto($user)
    {
        $this->validate([
            'cover_photo' => 'image|max:5120|mimes:jpeg,jpg,png,gif,webp', // 5MB max
        ]);

        // Delete old cover photo if exists
        if ($user->cover_photo_path) {
            // Remove storage/ prefix if it exists (from URLs), otherwise use path as is
            $oldPath = str_replace('storage/', '', $user->cover_photo_path);
            $oldPath = ltrim($oldPath, '/');
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // Store new cover photo
        $path = $this->cover_photo->store('cover-photos', 'public');
        
        // Update user cover_photo_path
        $user->update([
            'cover_photo_path' => $path,
        ]);

        // Reset preview and file
        $this->cover_photo = null;
        $this->cover_photo_preview = null;
    }

    /**
     * Handle profile photo upload separately.
     *
     * @return void
     */
    public function updateProfilePhoto()
    {
        $user = Auth::user();
        
        if (!$this->profile_photo) {
            $this->addError('profile_photo', 'Please select a photo to upload.');
            return;
        }

        $this->uploadProfilePhoto($user);

        $this->dispatch('refresh-page');

        $this->dispatch('show-toast', [
            'message' => 'Profile photo updated successfully!',
            'type' => 'success'
        ]);
    }

    /**
     * Handle cover photo upload separately.
     *
     * @return void
     */
    public function updateCoverPhoto()
    {
        $user = Auth::user();
        
        if (!$this->cover_photo) {
            $this->addError('cover_photo', 'Please select a photo to upload.');
            return;
        }

        $this->uploadCoverPhoto($user);

        $this->dispatch('show-toast', [
            'message' => 'Cover photo updated successfully!',
            'type' => 'success'
        ]);
    }

    /**
     * Updated profile photo preview.
     *
     * @return void
     */
    public function updatedProfilePhoto()
    {
        $this->validate([
            'profile_photo' => 'image|max:2048|mimes:jpeg,jpg,png,gif,webp',
        ]);
        
        $this->profile_photo_preview = $this->profile_photo->temporaryUrl();
    }

    /**
     * Updated cover photo preview.
     *
     * @return void
     */
    public function updatedCoverPhoto()
    {
        $this->validate([
            'cover_photo' => 'image|max:5120|mimes:jpeg,jpg,png,gif,webp',
        ]);
        
        $this->cover_photo_preview = $this->cover_photo->temporaryUrl();
    }

    public function updatePassword()
    {
        $user = Auth::user();
        
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8',
            'confirm_password' => 'required|same:new_password',
        ]);

        // Verify current password
        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'The current password is incorrect.');
            return;
        }

        $user->update([
            'password' => bcrypt($this->new_password),
        ]);

        // Reset password fields
        $this->current_password = '';
        $this->new_password = '';
        $this->confirm_password = '';

        // Dispatch browser event to trigger toast notification
        $this->dispatch('show-toast', [
            'message' => 'Password changed successfully!',
            'type' => 'success'
        ]);
    }

    /**
     * Calculate profile completion percentage
     */
    public function getProfileCompletionPercentageProperty()
    {
        $user = Auth::user();
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

    /**
     * Logout from a specific device/session.
     *
     * @param  int  $historyId
     * @return mixed
     */
    public function logoutDevice($historyId)
    {
        $user = Auth::user();
        $history = LoginHistory::where('id', $historyId)
            ->where('user_id', $user->id)
            ->first();

        if (!$history) {
            $this->dispatch('show-toast', [
                'message' => 'Login history not found!',
                'type' => 'error'
            ]);
            return;
        }

        // If it's the current session, logout normally
        if ($history->session_id === Session::getId()) {
            Auth::logout();
            $this->dispatch('show-toast', [
                'message' => 'Logged out successfully!',
                'type' => 'success'
            ]);
            return $this->redirect(route('login'), navigate: true);
        }

        // Delete the session from storage if session driver is database
        if (config('session.driver') === 'database') {
            DB::table('sessions')
                ->where('id', $history->session_id)
                ->delete();
        }

        // Delete the login history record
        $history->delete();

        $this->dispatch('show-toast', [
            'message' => 'Device logged out successfully!',
            'type' => 'success'
        ]);
    }

    /**
     * Logout from all devices except current.
     *
     * @return void
     */
    public function logoutAllDevices()
    {
        $user = Auth::user();
        $currentSessionId = Session::getId();

        $histories = LoginHistory::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->get();

        // Delete sessions from storage if session driver is database
        if (config('session.driver') === 'database') {
            $sessionIds = $histories->pluck('session_id')->filter()->toArray();
            if (!empty($sessionIds)) {
                DB::table('sessions')
                    ->whereIn('id', $sessionIds)
                    ->delete();
            }
        }

        // Delete all login history records except current
        LoginHistory::where('user_id', $user->id)
            ->where('session_id', '!=', $currentSessionId)
            ->delete();

        $this->dispatch('show-toast', [
            'message' => 'Logged out from all other devices successfully!',
            'type' => 'success'
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $loginHistories = $user
            ? $user->loginHistories()
                ->latest('login_at')
                ->paginate($this->loginHistoryPerPage, ['*'], 'loginHistoryPage')
            : collect();

        return view('livewire.user.user-profile-settings', [
            'user' => $user,
            'loginHistories' => $loginHistories,
        ]);
    }
}
