<?php

namespace App\Livewire\Leave;

use App\Models\LeaveRequest;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

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

        if (! $request->canCancel()) {
            $this->dispatch('toast', message: 'Tidak dapat membatalkan permohonan ini', type: 'error');

            return;
        }

        $request->update(['status' => 'cancelled']);
        $this->dispatch('toast', message: 'Permohonan berhasil dibatalkan', type: 'success');
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
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
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
