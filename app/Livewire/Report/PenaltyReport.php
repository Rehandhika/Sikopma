<?php

namespace App\Livewire\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Penalty, User};
use Carbon\Carbon;

class PenaltyReport extends Component
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
        $penalties = Penalty::with(['user:id,name,nim', 'penaltyType:id,name,code,points'])
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->userFilter !== 'all', fn($q) => $q->where('user_id', $this->userFilter))
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->latest('date')
            ->paginate(20);

        // Summary statistics
        $stats = Penalty::whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN status = "appealed" THEN 1 ELSE 0 END) as appealed')
            ->selectRaw('SUM(CASE WHEN status = "dismissed" THEN 1 ELSE 0 END) as dismissed')
            ->selectRaw('SUM(CASE WHEN status = "expired" THEN 1 ELSE 0 END) as expired')
            ->selectRaw('SUM(points) as total_points')
            ->first();

        $users = User::orderBy('name')->get();

        return view('livewire.report.penalty-report', [
            'penalties' => $penalties,
            'stats' => $stats,
            'users' => $users,
        ])->layout('layouts.app')->title('Laporan Penalti');
    }
}
