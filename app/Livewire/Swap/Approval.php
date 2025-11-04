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
            'originalSchedule', 
            'targetSchedule'
        ])->find($id);
        $this->showModal = true;
    }

    public function approve($id)
    {
        $swap = SwapRequest::find($id);
        
        if ($swap && $swap->status === 'accepted') {
            DB::transaction(function () use ($swap) {
                // Swap the schedules
                $originalUserId = $swap->originalSchedule->user_id;
                $targetUserId = $swap->targetSchedule->user_id;

                $swap->originalSchedule->update(['user_id' => $targetUserId]);
                $swap->targetSchedule->update(['user_id' => $originalUserId]);

                $swap->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'approval_notes' => $this->approvalNotes,
                ]);
            });

            $this->dispatch('alert', type: 'success', message: 'Tukar shift disetujui dan jadwal telah diperbarui');
            $this->reset(['showModal', 'approvalNotes', 'selectedSwap']);
        }
    }

    public function reject($id)
    {
        $swap = SwapRequest::find($id);
        
        if ($swap && $swap->status === 'accepted') {
            $swap->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $this->approvalNotes,
            ]);

            $this->dispatch('alert', type: 'success', message: 'Tukar shift ditolak');
            $this->reset(['showModal', 'approvalNotes', 'selectedSwap']);
        }
    }

    public function render()
    {
        $swaps = SwapRequest::query()
            ->where('status', 'accepted')
            ->with(['requester', 'target', 'originalSchedule', 'targetSchedule'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'pending' => SwapRequest::where('status', 'accepted')->count(),
            'approved_today' => SwapRequest::where('status', 'approved')
                ->whereDate('approved_at', today())
                ->count(),
        ];

        return view('livewire.swap.approval', [
            'swaps' => $swaps,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Persetujuan Tukar Shift');
    }
}
