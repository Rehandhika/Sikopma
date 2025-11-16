<?php

namespace App\Livewire\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveRequest;

class Index extends Component
{
    use WithPagination;

    public $statusFilter = '';

    public function cancelRequest($id)
    {
        $leave = LeaveRequest::find($id);
        
        if ($leave && $leave->user_id === auth()->id() && $leave->status === 'pending') {
            $leave->update(['status' => 'cancelled']);
            $this->dispatch('alert', type: 'success', message: 'Permintaan cuti dibatalkan');
        }
    }

    public function render()
    {
        $leaves = LeaveRequest::query()
            ->where('user_id', auth()->id())
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->with(['reviewer:id,name', 'affectedSchedules'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Optimize summary with single query using selectRaw
        $summary = LeaveRequest::where('user_id', auth()->id())
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved')
            ->selectRaw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending')
            ->selectRaw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected')
            ->first()
            ->toArray();

        return view('livewire.leave.index', [
            'leaves' => $leaves,
            'summary' => $summary,
        ])->layout('layouts.app')->title('Permintaan Cuti');
    }
}
