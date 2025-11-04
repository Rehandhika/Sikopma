<?php

namespace App\Livewire\Swap;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SwapRequest;

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
            $swap->update(['status' => 'accepted']);
            $this->dispatch('alert', type: 'success', message: 'Permintaan tukar shift diterima');
        }
    }

    public function rejectRequest($id)
    {
        $swap = SwapRequest::find($id);
        
        if ($swap && $swap->target_id === auth()->id() && $swap->status === 'pending') {
            $swap->update(['status' => 'rejected']);
            $this->dispatch('alert', type: 'success', message: 'Permintaan tukar shift ditolak');
        }
    }

    public function render()
    {
        $swaps = SwapRequest::query()
            ->when($this->tab === 'my-requests', fn($q) => $q->where('requester_id', auth()->id()))
            ->when($this->tab === 'received', fn($q) => $q->where('target_id', auth()->id()))
            ->with(['requester', 'target', 'originalSchedule', 'targetSchedule'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.swap.index', [
            'swaps' => $swaps
        ])->layout('layouts.app')->title('Tukar Shift');
    }
}
