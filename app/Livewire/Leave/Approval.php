<?php

namespace App\Livewire\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;

class Approval extends Component
{
    use WithPagination;

    public $selectedLeave;
    public $approvalNotes = '';
    public $showModal = false;
    public $action = '';

    public function viewDetails($id)
    {
        $this->selectedLeave = LeaveRequest::with(['user', 'leaveType'])->find($id);
        $this->showModal = true;
    }

    public function approve($id)
    {
        $leave = LeaveRequest::find($id);
        
        if ($leave && $leave->status === 'pending') {
            DB::transaction(function () use ($leave) {
                $leave->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'approval_notes' => $this->approvalNotes,
                ]);

                // Update affected schedules
                $this->updateSchedules($leave);
            });

            $this->dispatch('alert', type: 'success', message: 'Cuti disetujui');
            $this->reset(['showModal', 'approvalNotes', 'selectedLeave']);
        }
    }

    public function reject($id)
    {
        $leave = LeaveRequest::find($id);
        
        if ($leave && $leave->status === 'pending') {
            $leave->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $this->approvalNotes,
            ]);

            $this->dispatch('alert', type: 'success', message: 'Cuti ditolak');
            $this->reset(['showModal', 'approvalNotes', 'selectedLeave']);
        }
    }

    private function updateSchedules($leave)
    {
        // Mark schedules as excused for approved leave dates
        \App\Models\ScheduleAssignment::where('user_id', $leave->user_id)
            ->whereBetween('date', [$leave->date_from, $leave->date_to])
            ->update(['status' => 'excused']);
    }

    public function render()
    {
        $leaves = LeaveRequest::query()
            ->where('status', 'pending')
            ->with(['user', 'leaveType'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved_today' => LeaveRequest::where('status', 'approved')
                ->whereDate('approved_at', today())
                ->count(),
        ];

        return view('livewire.leave.approval', [
            'leaves' => $leaves,
            'stats' => $stats,
        ])->layout('layouts.app')->title('Persetujuan Cuti');
    }
}
