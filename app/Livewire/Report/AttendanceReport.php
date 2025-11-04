<?php

namespace App\Livewire\Report;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use App\Models\{Attendance, User};
use Carbon\Carbon;

#[Title('Laporan Kehadiran')]
class AttendanceReport extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $userFilter = 'all';
    public $statusFilter = 'all';

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function render()
    {
        $attendances = Attendance::with(['user', 'scheduleAssignment'])
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->userFilter !== 'all', fn($q) => $q->where('user_id', $this->userFilter))
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->latest('date')
            ->paginate(20);

        $stats = [
            'total' => Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])->count(),
            'present' => Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])->present()->count(),
            'late' => Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])->late()->count(),
            'absent' => Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo])->absent()->count(),
        ];

        $users = User::orderBy('name')->get();

        return view('livewire.report.attendance-report', [
            'attendances' => $attendances,
            'stats' => $stats,
            'users' => $users,
        ])->layout('layouts.app');
    }
}
