<?php

namespace App\Livewire\Report;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

#[Title('Laporan Kehadiran')]
class AttendanceReport extends Component
{
    use WithPagination;

    public string $dateFrom = '';
    public string $dateTo = '';
    public string $userFilter = 'all';
    public string $statusFilter = 'all';
    public string $period = 'month';

    public function mount()
    {
        $this->setPeriod('month');
    }

    public function setPeriod(string $period)
    {
        $this->period = $period;
        $now = now();

        [$this->dateFrom, $this->dateTo] = match ($period) {
            'today' => [$now->format('Y-m-d'), $now->format('Y-m-d')],
            'week' => [$now->copy()->startOfWeek()->format('Y-m-d'), $now->copy()->endOfWeek()->format('Y-m-d')],
            'month' => [$now->copy()->startOfMonth()->format('Y-m-d'), $now->copy()->endOfMonth()->format('Y-m-d')],
            'year' => [$now->copy()->startOfYear()->format('Y-m-d'), $now->copy()->endOfYear()->format('Y-m-d')],
            default => [$this->dateFrom, $this->dateTo],
        };

        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->updatePeriodBasedOnDates();
        $this->resetPage();
    }

    public function updatedDateTo()
    {
        $this->updatePeriodBasedOnDates();
        $this->resetPage();
    }

    private function updatePeriodBasedOnDates()
    {
        if (empty($this->dateFrom) || empty($this->dateTo)) {
            $this->period = 'custom';
            return;
        }

        $now = now();
        $dateFrom = \Carbon\Carbon::parse($this->dateFrom);
        $dateTo = \Carbon\Carbon::parse($this->dateTo);

        // Check if dates match predefined periods
        if ($dateFrom->format('Y-m-d') === $now->format('Y-m-d') && 
            $dateTo->format('Y-m-d') === $now->format('Y-m-d')) {
            $this->period = 'today';
        } elseif ($dateFrom->format('Y-m-d') === $now->copy()->startOfWeek()->format('Y-m-d') && 
                  $dateTo->format('Y-m-d') === $now->copy()->endOfWeek()->format('Y-m-d')) {
            $this->period = 'week';
        } elseif ($dateFrom->format('Y-m-d') === $now->copy()->startOfMonth()->format('Y-m-d') && 
                  $dateTo->format('Y-m-d') === $now->copy()->endOfMonth()->format('Y-m-d')) {
            $this->period = 'month';
        } elseif ($dateFrom->format('Y-m-d') === $now->copy()->startOfYear()->format('Y-m-d') && 
                  $dateTo->format('Y-m-d') === $now->copy()->endOfYear()->format('Y-m-d')) {
            $this->period = 'year';
        } else {
            $this->period = 'custom';
        }
    }

    public function updatedUserFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    /**
     * Single optimized query untuk statistik
     */
    #[Computed]
    public function stats()
    {
        $userCondition = $this->userFilter !== 'all' ? "AND user_id = ?" : "";
        $params = [$this->dateFrom, $this->dateTo];
        
        if ($this->userFilter !== 'all') {
            $params[] = $this->userFilter;
        }

        $result = DB::select("
            SELECT 
                COUNT(*) as total,
                SUM(status = 'present') as present,
                SUM(status = 'late') as late,
                SUM(status = 'absent') as absent
            FROM attendances 
            WHERE date BETWEEN ? AND ? {$userCondition}
        ", $params);

        return $result[0] ?? (object)['total' => 0, 'present' => 0, 'late' => 0, 'absent' => 0];
    }

    /**
     * Cache users list - jarang berubah
     */
    #[Computed]
    public function users()
    {
        return DB::select("SELECT id, name FROM users WHERE deleted_at IS NULL ORDER BY name");
    }

    public function render()
    {
        $attendances = Attendance::query()
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->userFilter !== 'all', fn($q) => $q->where('user_id', $this->userFilter))
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->with('user:id,name')
            ->select('id', 'user_id', 'date', 'check_in', 'check_out', 'work_hours', 'status')
            ->latest('date')
            ->paginate(15);

        return view('livewire.report.attendance-report', ['attendances' => $attendances])
            ->layout('layouts.app');
    }
}
