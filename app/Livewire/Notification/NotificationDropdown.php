<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;

class NotificationDropdown extends Component
{
    public $selected = [];
    public $limit = 10;

    public function mount()
    {
        // Initialize component
    }

    #[Computed]
    public function unreadCount()
    {
        return Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    #[Computed]
    public function allNotifications()
    {
        return Notification::where('user_id', Auth::id())
            ->latest()
            ->limit($this->limit)
            ->get();
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);
        
        if ($notification->isUnread()) {
            $notification->markAsRead();
            
            $this->dispatch('show-toast', [
                'message' => 'Notification marked as read',
                'type' => 'success'
            ]);
        }
    }

    public function updatedSelected()
    {
        // Ensure selected values are integers
        $this->selected = array_map('intval', $this->selected);
        $this->selected = array_values(array_filter($this->selected));
    }

    public function markAllAsRead()
    {
        if (empty($this->selected)) {
            // Mark all unread notifications as read
            $count = Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } else {
            // Ensure selected values are integers
            $selectedIds = array_map('intval', $this->selected);
            
            // Mark only selected notifications as read
            $count = Notification::where('user_id', Auth::id())
                ->whereIn('id', $selectedIds)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
        
        $this->dispatch('show-toast', [
            'message' => $count . ' notification(s) marked as read',
            'type' => 'success'
        ]);
        
        // Clear selected after marking as read
        $this->selected = [];
    }

    public function deleteSelected()
    {
        if (empty($this->selected)) {
            return;
        }

        // Ensure selected values are integers
        $selectedIds = array_map('intval', $this->selected);
        
        $count = Notification::where('user_id', Auth::id())
            ->whereIn('id', $selectedIds)
            ->delete();
        
        $this->dispatch('show-toast', [
            'message' => $count . ' notification(s) deleted',
            'type' => 'success'
        ]);
        
        // Clear selected after deletion
        $this->selected = [];
    }

    public function deleteNotification($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);
        
        $notification->delete();
        
        $this->dispatch('show-toast', [
            'message' => 'Notification deleted',
            'type' => 'success'
        ]);
    }

    public function render()
    {
        return view('livewire.notification.notification-dropdown');
    }
}
