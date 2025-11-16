<?php

namespace App\Livewire\Swap;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{SwapRequest, ScheduleAssignment, User};

class Index extends Component
{
    use WithPagination;

    public $tab = 'my-requests'; // my-requests, received, all

    public function cancelRequest($id)
    {
        $swap = SwapRequest::find($id);
        
        if ($swap && $swap->requester_id === auth()->id() && $swap->status === 'pending') {
            $swap->update(['status' => 'cancelled']);
            $this->dispatch('alert', type: 'success', message: 'Permintaan tukar shift dibatalkan');
        }
    }

    public function acceptRequest($id)
    {
        $swap = SwapRequest::find($id);
        
        if ($swap && $swap->target_id === auth()->id() && $swap->status === 'pending') {
            $swap->update(['status' => 'target_approved', 'target_responded_at' => now()]);
            
            // Create notification for requester
            $this->createNotification($swap->requester_id, 'swap_accepted', [
                'title' => 'Permintaan Tukar Shift Disetujui',
                'message' => auth()->user()->name . ' menyetujui permintaan tukar shift Anda.',
                'swap_request_id' => $swap->id,
            ]);
            
            // Notify admins for final approval
            $this->notifyAdminsForApproval($swap);
            
            $this->dispatch('alert', type: 'success', message: 'Permintaan tukar shift diterima');
        }
    }

    public function rejectRequest($id)
    {
        $swap = SwapRequest::find($id);
        
        if ($swap && $swap->target_id === auth()->id() && $swap->status === 'pending') {
            $swap->update([
                'status' => 'target_rejected', 
                'target_responded_at' => now(),
                'target_response' => 'Ditolak oleh target user'
            ]);
            
            // Create notification for requester
            $this->createNotification($swap->requester_id, 'swap_rejected', [
                'title' => 'Permintaan Tukar Shift Ditolak',
                'message' => auth()->user()->name . ' menolak permintaan tukar shift Anda.',
                'swap_request_id' => $swap->id,
            ]);
            
            $this->dispatch('alert', type: 'success', message: 'Permintaan tukar shift ditolak');
        }
    }

    public function render()
    {
        $query = SwapRequest::query()
            ->when($this->tab === 'my-requests', fn($q) => $q->where('requester_id', auth()->id()))
            ->when($this->tab === 'received', fn($q) => $q->where('target_id', auth()->id()))
            ->with([
                'requester:id,name,nim',
                'target:id,name,nim',
                'requesterAssignment.schedule',
                'targetAssignment.schedule'
            ])
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('requester', function($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                           ->orWhere('nim', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('target', function($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%')
                           ->orWhere('nim', 'like', '%' . $this->search . '%');
                });
            });
        }

        $swaps = $query->paginate(15);

        // Get statistics for current tab
        $stats = $this->getTabStats();

        return view('livewire.swap.index', [
            'swaps' => $swaps,
            'stats' => $stats
        ])->layout('layouts.app')->title('Tukar Shift');
    }

    private function getTabStats()
    {
        $query = SwapRequest::query();
        
        if ($this->tab === 'my-requests') {
            $query->where('requester_id', auth()->id());
        } elseif ($this->tab === 'received') {
            $query->where('target_id', auth()->id());
        }

        return [
            'total' => $query->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'approved' => $query->whereIn('status', ['target_approved', 'admin_approved'])->count(),
            'rejected' => $query->whereIn('status', ['target_rejected', 'admin_rejected'])->count(),
        ];
    }

    private function createNotification($userId, $type, $data)
    {
        try {
            $user = \App\Models\User::findOrFail($userId);
            \App\Services\NotificationService::createSwapNotification($user, $type, $data);
        } catch (\Exception $e) {
            \Log::error('Failed to create swap notification', [
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function notifyAdminsForApproval($swapRequest)
    {
        // Get users with admin roles
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']);
        })->get();

        foreach ($adminUsers as $admin) {
            $this->createNotification($admin->id, 'swap_admin_approval', [
                'title' => 'Persetajuan Tukar Shift Diperlukan',
                'message' => 'Permintaan tukar shift antara ' . $swapRequest->requester->name . ' dan ' . $swapRequest->target->name . ' menunggu persetujuan admin.',
                'swap_request_id' => $swapRequest->id,
            ]);
        }
    }
}
