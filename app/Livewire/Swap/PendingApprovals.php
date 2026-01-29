<?php

namespace App\Livewire\Swap;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{SwapRequest, ScheduleAssignment, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PendingApprovals extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, pending, approved, rejected
    public $search = '';
    public $selectedRequest = null;
    public $showDetailModal = false;
    public $responseMessage = '';
    public $showResponseModal = false;
    public $responseType = 'approve'; // approve, reject

    protected $queryString = ['filter', 'search'];

    public function getStatsProperty()
    {
        return [
            'pending' => SwapRequest::where('target_id', auth()->id())->where('status', 'pending')->count(),
            'approved' => SwapRequest::where('target_id', auth()->id())->where('status', 'target_approved')->count(),
            'rejected' => SwapRequest::where('target_id', auth()->id())->whereIn('status', ['target_rejected'])->count(),
            'total' => SwapRequest::where('target_id', auth()->id())->count(),
        ];
    }

    public function approveRequest($id)
    {
        $request = SwapRequest::where('id', $id)
            ->where('target_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$request) {
            $this->dispatch('alert', type: 'error', message: 'Permintaan tidak ditemukan atau sudah diproses.');
            return;
        }

        // Check if deadline has passed
        $deadline = $request->targetAssignment->date->copy()
            ->setTimeFromTimeString($request->targetAssignment->time_start)
            ->subHours(24);
        
        if (now()->greaterThan($deadline)) {
            $this->dispatch('alert', type: 'error', message: 'Tidak dapat menyetujui permintaan dalam 24 jam sebelum shift.');
            return;
        }

        $this->selectedRequest = $request;
        $this->responseType = 'approve';
        $this->showResponseModal = true;
    }

    public function rejectRequest($id)
    {
        $request = SwapRequest::where('id', $id)
            ->where('target_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$request) {
            $this->dispatch('alert', type: 'error', message: 'Permintaan tidak ditemukan atau sudah diproses.');
            return;
        }

        $this->selectedRequest = $request;
        $this->responseType = 'reject';
        $this->showResponseModal = true;
    }

    public function confirmResponse()
    {
        if (!$this->selectedRequest) {
            return;
        }

        try {
            DB::beginTransaction();

            $status = $this->responseType === 'approve' ? 'target_approved' : 'target_rejected';
            
            $this->selectedRequest->update([
                'status' => $status,
                'target_response' => $this->responseMessage,
                'target_responded_at' => now(),
            ]);

            // Create notification for requester
            $this->createNotification($this->selectedRequest->requester_id, 'swap_response', [
                'title' => $this->responseType === 'approve' ? 'Permintaan Tukar Shift Disetujui' : 'Permintaan Tukar Shift Ditolak',
                'message' => auth()->user()->name . ' telah ' . ($this->responseType === 'approve' ? 'menyetujui' : 'menolak') . ' permintaan tukar shift Anda.',
                'swap_request_id' => $this->selectedRequest->id,
            ]);

            // If approved, notify admins for final approval
            if ($this->responseType === 'approve') {
                $this->notifyAdmins($this->selectedRequest);
            }

            DB::commit();

            $this->dispatch('alert', type: 'success', message: 'Permintaan tukar shift berhasil ' . ($this->responseType === 'approve' ? 'disetujui' : 'ditolak') . '.');
            $this->reset(['showResponseModal', 'responseMessage', 'selectedRequest', 'responseType']);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', type: 'error', message: 'Gagal memproses permintaan: ' . $e->getMessage());
        }
    }

    public function viewDetails($id)
    {
        $this->selectedRequest = SwapRequest::with([
            'requester:id,name,nim',
            'target:id,name,nim',
            'requesterAssignment.schedule',
            'targetAssignment.schedule'
        ])->find($id);

        if ($this->selectedRequest && $this->selectedRequest->target_id === auth()->id()) {
            $this->showDetailModal = true;
        } else {
            $this->dispatch('alert', type: 'error', message: 'Permintaan tidak ditemukan.');
        }
    }

    private function createNotification($userId, $type, $data)
    {
        // Implementation depends on your notification system
    }

    private function notifyAdmins($swapRequest)
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

    public function getStatusColor($status)
    {
        return match($status) {
            'pending' => 'yellow',
            'target_approved' => 'blue',
            'target_rejected' => 'red',
            default => 'gray'
        };
    }

    public function getStatusText($status)
    {
        return match($status) {
            'pending' => 'Menunggu Respons',
            'target_approved' => 'Saya Setujui',
            'target_rejected' => 'Saya Tolak',
            default => 'Unknown'
        };
    }

    public function canRespond($request)
    {
        if ($request->status !== 'pending') {
            return false;
        }

        // Check if deadline has passed
        $deadline = $request->targetAssignment->date->copy()
            ->setTimeFromTimeString($request->targetAssignment->time_start)
            ->subHours(24);
        
        return !now()->greaterThan($deadline);
    }

    public function render()
    {
        $query = SwapRequest::where('target_id', auth()->id())
            ->with([
                'requester:id,name,nim',
                'requesterAssignment.schedule',
                'targetAssignment.schedule'
            ]);

        // Apply filter
        if ($this->filter !== 'all') {
            switch ($this->filter) {
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'approved':
                    $query->where('status', 'target_approved');
                    break;
                case 'rejected':
                    $query->where('status', 'target_rejected');
                    break;
            }
        }

        // Apply search
        if ($this->search) {
            $query->whereHas('requester', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('nim', 'like', '%' . $this->search . '%');
            });
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.swap.pending-approvals', [
            'requests' => $requests,
            'stats' => $this->stats,
        ])->layout('layouts.app')->title('Persetajuan Tukar Shift');
    }
}
