<?php

namespace App\Livewire\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{Title, On};
use App\Models\LeaveRequest;

#[Title('Riwayat Cuti/Izin Saya')]
class MyRequests extends Component
{
    use WithPagination;

    public $statusFilter = 'all';

    public function cancel($id)
    {
        $request = LeaveRequest::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        if (!$request->canCancel()) {
            session()->flash('error', 'Tidak dapat membatalkan permohonan ini');
            return;
        }

        $request->update(['status' => 'cancelled']);
        session()->flash('success', 'Permohonan berhasil dibatalkan');
    }

    #[On('leave-request-created')] 
    public function refreshList()
    {
        $this->resetPage();
    }

    public function render()
    {
        $requests = LeaveRequest::where('user_id', auth()->id())
            ->with('reviewer')
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => LeaveRequest::where('user_id', auth()->id())->count(),
            'pending' => LeaveRequest::where('user_id', auth()->id())->pending()->count(),
            'approved' => LeaveRequest::where('user_id', auth()->id())->approved()->count(),
            'rejected' => LeaveRequest::where('user_id', auth()->id())->rejected()->count(),
        ];

        return view('livewire.leave.my-requests', [
            'requests' => $requests,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}
