<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceHistory extends Component
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
            // FIX: Gunakan kolom 'date' bukan 'check_in' agar data absent/excused muncul
            ->when($this->dateFrom, fn ($q) => $q->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('date', '<=', $this->dateTo))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            // FIX: Load relasi schedule untuk menampilkan info sesi
            ->with(['scheduleAssignment.schedule'])
            // FIX: Order by date first, then check_in (handle null check_in)
            ->orderBy('date', 'desc')
            ->orderByRaw('check_in IS NULL, check_in DESC')
            ->paginate(15);

        return view('livewire.attendance.attendance-history', [
            'attendances' => $attendances,
        ])->layout('layouts.app')->title('Riwayat Absensi');
    }
}
