<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\{Title, Layout, Computed};
use App\Models\{Schedule, User, Availability};
use Illuminate\Support\Facades\{DB, Cache};
use Carbon\Carbon;

#[Title('Manajemen Jadwal')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $filterStatus = '';
    public string $filterMonth = '';
    public string $filterYear = '';
    public string $search = '';
    
    // Member availability
    public bool $showMemberModal = false;
    public ?array $selectedMemberAvailability = null;
    public ?string $selectedMemberName = null;

    protected $queryString = [
        'filterStatus' => ['except' => ''],
        'filterMonth' => ['except' => ''],
        'filterYear' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterYear()
    {
        $this->resetPage();
    }

    #[Computed]
    public function currentWeekStart(): Carbon
    {
        return now()->startOfWeek(Carbon::MONDAY);
    }

    #[Computed]
    public function currentWeekEnd(): Carbon
    {
        return now()->endOfWeek(Carbon::SUNDAY);
    }

    #[Computed]
    public function membersWithAvailability(): array
    {
        $weekStart = $this->currentWeekStart;
        
        return Cache::remember(
            "members_availability_{$weekStart->format('Y-m-d')}",
            60, // 1 minute cache
            function () use ($weekStart) {
                // Get all active users
                $members = User::query()
                    ->where('status', 'active')
                    ->select('id', 'name', 'nim')
                    ->orderBy('name')
                    ->get();

                // Get submitted availabilities for current week by week_start_date
                $availabilities = Availability::query()
                    ->where('status', 'submitted')
                    ->where('week_start_date', $weekStart->format('Y-m-d'))
                    ->with('details:id,availability_id,day,session,is_available')
                    ->get()
                    ->keyBy('user_id');

                return $members->map(function ($member) use ($availabilities) {
                    $availability = $availabilities->get($member->id);
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'nim' => $member->nim,
                        'has_submitted' => $availability !== null,
                        'total_sessions' => $availability?->total_available_sessions ?? 0,
                        'submitted_at' => $availability?->submitted_at?->format('d M H:i'),
                    ];
                })->toArray();
            }
        );
    }

    #[Computed]
    public function availabilityStats(): array
    {
        $members = $this->membersWithAvailability;
        $total = count($members);
        $submitted = collect($members)->where('has_submitted', true)->count();
        
        return [
            'total' => $total,
            'submitted' => $submitted,
            'pending' => $total - $submitted,
            'percentage' => $total > 0 ? round(($submitted / $total) * 100) : 0,
        ];
    }

    public function viewMemberAvailability(int $userId): void
    {
        $weekStart = $this->currentWeekStart;
        
        $user = User::find($userId);
        $availability = Availability::query()
            ->where('user_id', $userId)
            ->where('status', 'submitted')
            ->where('week_start_date', $weekStart->format('Y-m-d'))
            ->with('details')
            ->first();

        if (!$availability) {
            $this->dispatch('alert', type: 'warning', message: 'Anggota belum mengisi ketersediaan.');
            return;
        }

        $this->selectedMemberName = $user->name;
        $this->selectedMemberAvailability = [];
        
        foreach ($availability->details as $detail) {
            $this->selectedMemberAvailability[$detail->day][$detail->session] = $detail->is_available;
        }
        
        $this->showMemberModal = true;
    }

    public function closeMemberModal(): void
    {
        $this->showMemberModal = false;
        $this->selectedMemberAvailability = null;
        $this->selectedMemberName = null;
    }

    public function publish(int $scheduleId): void
    {
        try {
            $schedule = Schedule::findOrFail($scheduleId);
            
            if ($schedule->status === 'published') {
                $this->dispatch('alert', type: 'warning', message: 'Jadwal sudah dipublish.');
                return;
            }
            
            $schedule->update(['status' => 'published']);
            
            $this->dispatch('alert', type: 'success', message: 'Jadwal berhasil dipublish!');
            
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Gagal publish jadwal: ' . $e->getMessage());
        }
    }

    public function delete(int $scheduleId): void
    {
        try {
            DB::beginTransaction();
            
            $schedule = Schedule::findOrFail($scheduleId);
            
            // Delete assignments first
            $schedule->assignments()->delete();
            
            // Delete schedule
            $schedule->delete();
            
            DB::commit();
            
            $this->dispatch('alert', type: 'success', message: 'Jadwal berhasil dihapus!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', type: 'error', message: 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $schedules = Schedule::query()
            ->with(['assignments'])
            ->withCount('assignments')
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterMonth, fn($q) => $q->whereMonth('week_start_date', $this->filterMonth))
            ->when($this->filterYear, fn($q) => $q->whereYear('week_start_date', $this->filterYear))
            ->when($this->search, fn($q) => $q->where('notes', 'like', "%{$this->search}%"))
            ->latest('week_start_date')
            ->paginate(10);

        return view('livewire.schedule.index', compact('schedules'));
    }
}
