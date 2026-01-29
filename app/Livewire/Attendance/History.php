<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    public $dateFrom;

    public $dateTo;

    public $status = '';

    public function mount()
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = Carbon::now()->format('Y-m-d');
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function render()
    {
        $attendances = Attendance::query()
            ->where('user_id', auth()->id())
            ->when($this->dateFrom, fn ($q) => $q->whereDate('check_in', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('check_in', '<=', $this->dateTo))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->with('scheduleAssignment')
            ->orderBy('check_in', 'desc')
            ->paginate(15);

        return view('livewire.attendance.history', [
            'attendances' => $attendances,
        ])->layout('layouts.app')->title('Riwayat Absensi');
    }
}
