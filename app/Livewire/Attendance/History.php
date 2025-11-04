<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use Carbon\Carbon;

class History extends Component
{
    use WithPagination;

    public $dateFrom;
    public $dateTo;
    public $status = '';
    public $search = '';

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function applyFilter()
    {
        $this->resetPage();
    }

    public function resetFilter()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
        $this->status = '';
        $this->search = '';
        $this->resetPage();
    }

    public function export()
    {
        // Export logic will be implemented
        $this->dispatch('alert', type: 'info', message: 'Export sedang diproses...');
    }

    public function render()
    {
        $attendances = Attendance::query()
            ->where('user_id', auth()->id())
            ->when($this->dateFrom, fn($q) => $q->whereDate('check_in', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('check_in', '<=', $this->dateTo))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->search, fn($q) => $q->whereHas('scheduleAssignment', function($query) {
                $query->where('day', 'like', '%' . $this->search . '%');
            }))
            ->with('scheduleAssignment')
            ->orderBy('check_in', 'desc')
            ->paginate(15);

        return view('livewire.attendance.history', [
            'attendances' => $attendances
        ])->layout('layouts.app')->title('Riwayat Absensi');
    }
}
