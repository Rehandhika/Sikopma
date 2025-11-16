<?php

namespace App\Livewire\Notification;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Notification;

class MyNotifications extends Component
{
    use WithPagination;

    public $filter = 'all';
    public $search = '';

    protected $queryString = ['filter', 'search'];

    public function mount()
    {
        // Mark all as read when viewing notifications
        $this->markAllAsRead();
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
            $this->dispatch('notification-read', id: $id);
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function delete($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->delete();
            $this->dispatch('notification-deleted', id: $id);
        }
    }

    public function clearAll()
    {
        Notification::where('user_id', auth()->id())->delete();
        $this->dispatch('notifications-cleared');
    }

    public function getUnreadCountProperty()
    {
        return Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    public function render()
    {
        $query = Notification::where('user_id', auth()->id());

        // Apply filter
        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('message', 'like', '%' . $this->search . '%');
            });
        }

        // Group by type for statistics
        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total' => Notification::where('user_id', auth()->id())->count(),
            'unread' => Notification::where('user_id', auth()->id())->whereNull('read_at')->count(),
            'by_type' => Notification::where('user_id', auth()->id())
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];

        return view('livewire.notification.my-notifications', [
            'notifications' => $notifications,
            'stats' => $stats,
            'unreadCount' => $this->unreadCount,
        ])->layout('layouts.app')->title('Notifikasi Saya');
    }
}
