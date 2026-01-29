<?php

namespace App\Livewire\Penalty;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Penalty;
use App\Services\PenaltyService;
use Illuminate\Support\Facades\Log;

class ManagePenalties extends Component
{
    use WithPagination;

    public $selectedPenalty;
    public $reviewNotes = '';
    public $showReviewModal = false;
    public $filterStatus = 'all';

    protected PenaltyService $penaltyService;

    protected $rules = [
        'reviewNotes' => 'required|string|min:10|max:500',
    ];

    public function boot(PenaltyService $penaltyService)
    {
        $this->penaltyService = $penaltyService;
    }

    /**
     * Open review modal for an appealed penalty
     */
    public function openReviewModal($penaltyId)
    {
        $this->selectedPenalty = Penalty::with(['user', 'penaltyType', 'reference'])
            ->findOrFail($penaltyId);
        
        if ($this->selectedPenalty->status !== 'appealed') {
            $this->dispatch('toast', message: 'Penalti ini tidak dalam status banding', type: 'error');
            return;
        }

        $this->showReviewModal = true;
        $this->reviewNotes = '';
    }

    /**
     * Approve penalty appeal
     */
    public function approveAppeal()
    {
        $this->validate();

        try {
            $this->penaltyService->reviewAppeal(
                $this->selectedPenalty,
                true,
                $this->reviewNotes,
                auth()->id()
            );

            $this->dispatch('toast', message: 'Banding disetujui, penalti telah dibatalkan', type: 'success');
            $this->reset(['showReviewModal', 'reviewNotes', 'selectedPenalty']);
            
            // Refresh the page data
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('Error approving penalty appeal', [
                'penalty_id' => $this->selectedPenalty->id,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('toast', message: 'Gagal menyetujui banding: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Reject penalty appeal
     */
    public function rejectAppeal()
    {
        $this->validate();

        try {
            $this->penaltyService->reviewAppeal(
                $this->selectedPenalty,
                false,
                $this->reviewNotes,
                auth()->id()
            );

            $this->dispatch('toast', message: 'Banding ditolak, penalti tetap aktif', type: 'success');
            $this->reset(['showReviewModal', 'reviewNotes', 'selectedPenalty']);
            
            // Refresh the page data
            $this->resetPage();
        } catch (\Exception $e) {
            Log::error('Error rejecting penalty appeal', [
                'penalty_id' => $this->selectedPenalty->id,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('toast', message: 'Gagal menolak banding: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Update filter status
     */
    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Penalty::with(['user', 'penaltyType', 'reference'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        // Apply status filter
        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $penalties = $query->paginate(20);

        $stats = [
            'total_active' => Penalty::where('status', 'active')->count(),
            'total_appealed' => Penalty::where('status', 'appealed')->count(),
            'total_dismissed' => Penalty::where('status', 'dismissed')->count(),
        ];

        return view('livewire.penalty.manage-penalties', [
            'penalties' => $penalties,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Kelola Penalti');
    }
}
