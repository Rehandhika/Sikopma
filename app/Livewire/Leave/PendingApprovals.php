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

        try {
            $request = LeaveRequest::findOrFail($this->reviewId);
            $service = app(\App\Services\LeaveService::class);

            if ($this->reviewAction === 'approved') {
                $service->approve($request, auth()->id(), $this->review_notes);
            } else {
                $service->reject($request, auth()->id(), $this->review_notes);
            }

            $message = $this->reviewAction === 'approved' ? 'Pengajuan disetujui' : 'Pengajuan ditolak';
            $this->dispatch('toast', message: $message, type: 'success');

            $this->reviewModal = false;
            $this->reset(['reviewId', 'reviewAction', 'review_notes']);
        } catch (\Exception $e) {
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
