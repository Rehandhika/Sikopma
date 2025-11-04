<?php

namespace App\Livewire\Notification;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Notification;

class Index extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, unread, read

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->update(['read_at' => now()]);
            $this->dispatch('alert', type: 'success', message: 'Notifikasi ditandai sudah dibaca');
        }
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->dispatch('alert', type: 'success', message: 'Semua notifikasi ditandai sudah dibaca');
    }

    public function delete($id)
    {
        $notification = Notification::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->delete();
            $this->dispatch('alert', type: 'success', message: 'Notifikasi dihapus');
        }
    }

    public function deleteAll()
    {
        Notification::where('user_id', auth()->id())->delete();
        $this->dispatch('alert', type: 'success', message: 'Semua notifikasi dihapus');
    }

    public function render()
    {
        $query = Notification::where('user_id', auth()->id());

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => Notification::where('user_id', auth()->id())->count(),
            'unread' => Notification::where('user_id', auth()->id())->whereNull('read_at')->count(),
        ];

        return view('livewire.notification.index', [
            'notifications' => $notifications,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Notifikasi');
    }
}
