<?php

namespace App\Livewire\Leave;

use App\Models\LeaveRequest;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Persetujuan Cuti/Izin')]
class PendingApprovals extends Component
{
    use WithPagination;

    public $statusFilter = 'pending';

    public $reviewModal = false;

    public $reviewId;

    public $reviewAction;

    public $review_notes = '';

    public function openReview($id, $action)
    {
        $this->reviewId = $id;
        $this->reviewAction = $action;
        $this->review_notes = '';
        $this->reviewModal = true;
    }

    public function submitReview()
    {
        $this->validate([
            'review_notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $request = LeaveRequest::with('user')->findOrFail($this->reviewId);

            $request->update([
                'status' => $this->reviewAction,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => $this->review_notes,
            ]);

            // Log activity
            $userName = $request->user->name;
            $startDate = $request->start_date->format('d/m/Y');
            if ($this->reviewAction === 'approved') {
                ActivityLogService::logLeaveApproved($userName, $startDate);
            } else {
                ActivityLogService::logLeaveRejected($userName, $startDate);
            }

            DB::commit();

            $message = $this->reviewAction === 'approved' ? 'Pengajuan disetujui' : 'Pengajuan ditolak';
            $this->dispatch('toast', message: $message, type: 'success');

            $this->reviewModal = false;
            $this->reset(['reviewId', 'reviewAction', 'review_notes']);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('toast', message: 'Terjadi kesalahan: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        $requests = LeaveRequest::with(['user', 'reviewer'])
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        $stats = [
            'pending' => LeaveRequest::pending()->count(),
            'approved' => LeaveRequest::approved()->count(),
            'rejected' => LeaveRequest::rejected()->count(),
        ];

        return view('livewire.leave.pending-approvals', [
            'requests' => $requests,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
