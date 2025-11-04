<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Livewire\Attributes\{Lazy, Computed, On};
use App\Models\Attendance;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Cache};

#[Lazy]
class Index extends Component
{
    public $todayStatus;
    public $currentSchedule;
    public $canCheckIn = false;
    public $canCheckOut = false;
    public $recentAttendances;
    public $monthlyStats;
    public $latitude;
    public $longitude;

    public function mount()
    {
        $this->loadData();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>
        HTML;
    }

    public function loadData()
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Today's schedule
        $this->currentSchedule = ScheduleAssignment::where('user_id', $user->id)
            ->where('date', $today)
            ->where('status', 'scheduled')
            ->first();

        // Today's attendance
        $this->todayStatus = Attendance::where('user_id', $user->id)
            ->whereDate('check_in', $today)
            ->first();

        // Check if can check in/out
        $this->canCheckIn = $this->currentSchedule && !$this->todayStatus;
        $this->canCheckOut = $this->todayStatus && !$this->todayStatus->check_out;

        // Recent 7 days attendance
        $this->recentAttendances = Attendance::where('user_id', $user->id)
            ->whereDate('check_in', '>=', $today->copy()->subDays(7))
            ->orderBy('check_in', 'desc')
            ->limit(7)
            ->get();

        // Monthly stats
        $this->monthlyStats = $this->calculateMonthlyStats($user->id);
    }

    #[Computed(cache: true)]
    private function calculateMonthlyStats($userId)
    {
        $cacheKey = "monthly_stats_{$userId}_" . Carbon::now()->format('Y-m');
        
        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            $stats = Attendance::where('user_id', $userId)
                ->whereBetween('check_in', [$startOfMonth, $endOfMonth])
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as on_time,
                    SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late
                ')
                ->first();

            $absent = ScheduleAssignment::where('user_id', $userId)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('status', 'missed')
                ->count();

            return [
                'total' => $stats->total ?? 0,
                'on_time' => $stats->on_time ?? 0,
                'late' => $stats->late ?? 0,
                'absent' => $absent,
            ];
        });
    }

    public function checkIn()
    {
        if (!$this->canCheckIn) {
            $this->dispatch('alert', type: 'error', message: 'Tidak dapat check-in saat ini');
            return;
        }

        if (!$this->latitude || !$this->longitude) {
            $this->dispatch('alert', type: 'error', message: 'Lokasi tidak terdeteksi');
            return;
        }

        $now = Carbon::now();
        $scheduleStart = Carbon::parse($this->currentSchedule->time_start);
        $lateThreshold = 15; // minutes

        $status = $now->diffInMinutes($scheduleStart, false) > $lateThreshold ? 'late' : 'present';

        Attendance::create([
            'user_id' => auth()->id(),
            'schedule_assignment_id' => $this->currentSchedule->id,
            'check_in' => $now,
            'status' => $status,
            'location_lat' => $this->latitude,
            'location_lng' => $this->longitude,
        ]);

        $this->dispatch('alert', type: 'success', message: 'Check-in berhasil!');
        $this->loadData();
    }

    public function checkOut()
    {
        if (!$this->canCheckOut) {
            $this->dispatch('alert', type: 'error', message: 'Tidak dapat check-out saat ini');
            return;
        }

        $this->todayStatus->update([
            'check_out' => Carbon::now(),
        ]);

        // Update schedule status
        if ($this->currentSchedule) {
            $this->currentSchedule->update(['status' => 'completed']);
        }

        $this->dispatch('alert', type: 'success', message: 'Check-out berhasil!');
        $this->loadData();
    }

    public function updateLocation($lat, $lng)
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
    }

    public function render()
    {
        return view('livewire.attendance.index')
            ->layout('layouts.app')
            ->title('Absensi');
    }
}
