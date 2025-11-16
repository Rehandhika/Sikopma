<?php

namespace App\Livewire\Swap;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SwapRequest;
use Illuminate\Support\Facades\DB;

class Approval extends Component
{
    use WithPagination;

    public $selectedSwap;
    public $approvalNotes = '';
    public $showModal = false;

    public function viewDetails($id)
    {
        $this->selectedSwap = SwapRequest::with([
            'requester', 
            'target', 
            'requesterAssignment', 
            'targetAssignment'
        ])->find($id);
        $this->showModal = true;
    }

    public function approve($id)
    {
        $swap = SwapRequest::with(['requesterAssignment', 'targetAssignment'])->find($id);
        
        if ($swap && $swap->status === 'target_approved') {
            DB::transaction(function () use ($swap) {
                // Swap the schedules
                $originalUserId = $swap->requesterAssignment->user_id;
                $targetUserId = $swap->targetAssignment->user_id;

                $swap->requesterAssignment->update(['user_id' => $targetUserId]);
                $swap->targetAssignment->update(['user_id' => $originalUserId]);

                $swap->update([
                    'status' => 'admin_approved',
                    'admin_responded_by' => auth()->id(),
                    'admin_responded_at' => now(),
                    'admin_response' => $this->approvalNotes,
                    'completed_at' => now(),
                ]);
            });

            $this->dispatch('alert', type: 'success', message: 'Tukar shift disetujui dan jadwal telah diperbarui');
            $this->reset(['showModal', 'approvalNotes', 'selectedSwap']);
        }
    }

    public function reject($id)
    {
        $swap = SwapRequest::find($id);
        
        if ($swap && $swap->status === 'target_approved') {
            $swap->update([
                'status' => 'admin_rejected',
                'admin_responded_by' => auth()->id(),
                'admin_responded_at' => now(),
                'admin_response' => $this->approvalNotes,
            ]);

            $this->dispatch('alert', type: 'success', message: 'Tukar shift ditolak');
            $this->reset(['showModal', 'approvalNotes', 'selectedSwap']);
        }
    }

    public function render()
    {
        $swaps = SwapRequest::query()
            ->where('status', 'target_approved')
            ->with(['requester', 'target', 'requesterAssignment', 'targetAssignment'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'pending' => SwapRequest::where('status', 'target_approved')->count(),
            'approved_today' => SwapRequest::where('status', 'admin_approved')
                ->whereDate('admin_responded_at', today())
                ->count(),
        ];

        return view('livewire.swap.approval', [
            'swaps' => $swaps,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Persetujuan Tukar Shift');
    }
}
