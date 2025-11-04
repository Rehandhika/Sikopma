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
            ->with('leaveType')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $summary = [
            'total' => LeaveRequest::where('user_id', auth()->id())->count(),
            'approved' => LeaveRequest::where('user_id', auth()->id())->where('status', 'approved')->count(),
            'pending' => LeaveRequest::where('user_id', auth()->id())->where('status', 'pending')->count(),
            'rejected' => LeaveRequest::where('user_id', auth()->id())->where('status', 'rejected')->count(),
        ];

        return view('livewire.leave.index', [
            'leaves' => $leaves,
            'summary' => $summary,
        ])->layout('layouts.app')->title('Permintaan Cuti');
    }
}
