<?php

namespace App\Livewire\Notification;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationPage extends Component
{
    public $limit = 10;
    public $search = '';
    protected $loadStep = 10;

    #[Computed]
    public function unreadCount(): int
    {
        return Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    #[Computed]
    public function notifications()
    {
        return Notification::where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhere('body', 'like', $term);
                });
            })
            ->latest()
            ->limit($this->limit)
            ->get();
    }

    #[Computed]
    public function totalCount(): int
    {
        return Notification::where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', $term)
                        ->orWhere('body', 'like', $term);
                });
            })
            ->count();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->totalCount > $this->limit;
    }

    public function loadMore(): void
    {
        if ($this->hasMore) {
            $this->limit += $this->loadStep;
        }
    }

    public function updatedSearch(): void
    {
        $this->limit = 10;
    }

    public function markAsRead(int $notificationId): void
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);

        if ($notification->isUnread()) {
            $notification->markAsRead();

            $this->dispatch('show-toast', [
                'message' => 'Notification marked as read',
                'type' => 'success',
            ]);
        }
    }

    public function markAllAsRead(): void
    {
        $unreadQuery = Notification::where('user_id', Auth::id())
            ->whereNull('read_at');
        $count = $unreadQuery->count();

        if ($count === 0) {
            $this->dispatch('show-toast', [
                'message' => 'All notifications are already marked as read',
                'type' => 'info',
            ]);
            return;
        }

        $unreadQuery->update(['read_at' => now()]);

        $this->dispatch('show-toast', [
            'message' => $count . ' notification(s) marked as read',
            'type' => 'success',
        ]);
    }

    public function deleteNotification(int $notificationId): void
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($notificationId);

        $notification->delete();

        $this->dispatch('show-toast', [
            'message' => 'Notification deleted',
            'type' => 'success',
        ]);
    }

    public function render()
    {
        return view('livewire.notification.notification-page', [
            'notifications' => $this->notifications,
            'hasMore' => $this->hasMore,
            'totalCount' => $this->totalCount,
        ]);
    }
}
