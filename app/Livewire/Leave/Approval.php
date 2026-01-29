<?php

namespace App\Livewire\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Approval extends Component
{
    use WithPagination;

    public $selectedLeave;
    public $approvalNotes = '';
    public $showModal = false;
    public $action = '';
    public $affectedSchedules = [];

    protected LeaveService $leaveService;

    public function boot(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function viewDetails($id)
    {
        $this->selectedLeave = LeaveRequest::with(['user'])->find($id);
        $this->showModal = true;
        
        // Load affected schedules
        if ($this->selectedLeave) {
            $this->loadAffectedSchedules();
        }
    }

    /**
     * Load affected schedules for the selected leave request
     */
    private function loadAffectedSchedules()
    {
        try {
            $assignments = $this->leaveService->getAffectedAssignments($this->selectedLeave);
            
            $this->affectedSchedules = $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'date' => $assignment->date->format('d M Y'),
                    'session' => $assignment->session,
                    'session_name' => $this->getSessionName($assignment->session),
                    'time' => $this->getSessionTime($assignment->session),
                    'status' => $assignment->status,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading affected schedules', [
                'leave_request_id' => $this->selectedLeave->id,
                'error' => $e->getMessage(),
            ]);
            $this->affectedSchedules = [];
        }
    }

    /**
     * Get session name
     */
    private function getSessionName(int $session): string
    {
        return match($session) {
            1 => 'Sesi 1',
            2 => 'Sesi 2',
            3 => 'Sesi 3',
            default => "Sesi {$session}",
        };
    }

    /**
     * Get session time range
     */
    private function getSessionTime(int $session): string
    {
        return match($session) {
            1 => '08:00 - 12:00',
            2 => '12:00 - 16:00',
            3 => '16:00 - 20:00',
            default => '-',
        };
    }

    public function approve($id)
    {
        $leave = LeaveRequest::with('user')->find($id);
        
        if ($leave && $leave->status === 'pending') {
            try {
                // Use LeaveService to handle approval with automatic schedule updates
                $this->leaveService->approve(
                    $leave,
                    auth()->id(),
                    $this->approvalNotes
                );

                // Log activity
                ActivityLogService::logLeaveApproved(
                    $leave->user->name,
                    $leave->start_date->format('d M Y')
                );

                $this->dispatch('alert', type: 'success', message: 'Cuti disetujui dan jadwal telah diperbarui');
                $this->reset(['showModal', 'approvalNotes', 'selectedLeave', 'affectedSchedules']);
            } catch (\Exception $e) {
                Log::error('Error approving leave', [
                    'leave_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                $this->dispatch('alert', type: 'error', message: 'Gagal menyetujui cuti: ' . $e->getMessage());
            }
        }
    }

    public function reject($id)
    {
        $leave = LeaveRequest::with('user')->find($id);
        
        if ($leave && $leave->status === 'pending') {
            try {
                // Use LeaveService to handle rejection
                $this->leaveService->reject(
                    $leave,
                    auth()->id(),
                    $this->approvalNotes ?: 'Tidak ada catatan'
                );

                // Log activity
                ActivityLogService::logLeaveRejected(
                    $leave->user->name,
                    $leave->start_date->format('d M Y')
                );

                $this->dispatch('alert', type: 'success', message: 'Cuti ditolak');
                $this->reset(['showModal', 'approvalNotes', 'selectedLeave', 'affectedSchedules']);
            } catch (\Exception $e) {
                Log::error('Error rejecting leave', [
                    'leave_id' => $id,
                    'error' => $e->getMessage(),
                ]);
                $this->dispatch('alert', type: 'error', message: 'Gagal menolak cuti: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        $leaves = LeaveRequest::query()
            ->where('status', 'pending')
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved_today' => LeaveRequest::where('status', 'approved')
                ->whereDate('reviewed_at', today())
                ->count(),
        ];

        return view('livewire.leave.approval', [
            'leaves' => $leaves,
            'stats' => $stats,
            'affectedSchedules' => $this->affectedSchedules,
        ])->layout('layouts.app')->title('Persetujuan Cuti');
    }
}
