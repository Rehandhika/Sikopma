<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\{Availability, AvailabilityDetail};
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Cache};

class AvailabilityManager extends Component
{
    public int $weekOffset = 0;
    public array $availability = [];
    public string $status = 'draft';

    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday'];
    private const DAY_LABELS = ['monday' => 'Sen', 'tuesday' => 'Sel', 'wednesday' => 'Rab', 'thursday' => 'Kam'];
    private const SESSIONS = [1 => '07:30-10:00', 2 => '10:20-12:50', 3 => '13:30-16:00'];

    public function mount(): void
    {
        $this->initGrid();
        $this->loadData();
    }

    #[Computed]
    public function weekStart(): Carbon
    {
        return now()->startOfWeek(Carbon::MONDAY)->addWeeks($this->weekOffset);
    }

    #[Computed]
    public function weekEnd(): Carbon
    {
        return $this->weekStart->copy()->endOfWeek(Carbon::SUNDAY);
    }

    #[Computed]
    public function weekLabel(): string
    {
        return $this->weekStart->format('d M') . ' - ' . $this->weekEnd->format('d M Y');
    }

    #[Computed]
    public function canEdit(): bool
    {
        return $this->weekOffset >= 0 && $this->status !== 'submitted';
    }

    #[Computed]
    public function days(): array
    {
        return self::DAY_LABELS;
    }

    #[Computed]
    public function sessions(): array
    {
        return self::SESSIONS;
    }

    public function updatedWeekOffset(): void
    {
        $this->initGrid();
        $this->loadData();
        
        // Dispatch event to reset Alpine grid
        $this->dispatch('availability-reset', $this->availability);
    }

    /**
     * Save with data from Alpine.js
     */
    public function saveWithData(array $gridData, string $status): void
    {
        if (!$this->canEdit) return;

        try {
            DB::transaction(function () use ($gridData, $status) {
                // Delete existing
                Availability::where('user_id', auth()->id())
                    ->whereBetween('created_at', [$this->weekStart, $this->weekEnd])
                    ->delete();

                // Count total sessions
                $totalSessions = 0;
                foreach ($gridData as $sessions) {
                    foreach ($sessions as $isAvailable) {
                        if ($isAvailable) $totalSessions++;
                    }
                }

                // Create new
                $avail = Availability::create([
                    'user_id' => auth()->id(),
                    'schedule_id' => null,
                    'status' => $status,
                    'submitted_at' => $status === 'submitted' ? now() : null,
                    'total_available_sessions' => $totalSessions,
                ]);

                // Bulk insert details
                $details = [];
                $now = now();
                foreach ($gridData as $day => $sessions) {
                    foreach ($sessions as $session => $isAvailable) {
                        $details[] = [
                            'availability_id' => $avail->id,
                            'day' => $day,
                            'session' => (string) $session,
                            'is_available' => (bool) $isAvailable,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
                AvailabilityDetail::insert($details);

                $this->status = $status;
                $this->availability = $gridData;
                
                // Clear cache
                Cache::forget("user_availability_" . auth()->id() . "_" . $this->weekStart->format('Y-m-d'));
            });

            $message = $status === 'submitted' ? 'Ketersediaan terkirim!' : 'Draft tersimpan!';
            $this->dispatch('alert', type: 'success', message: $message);
            
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Gagal menyimpan: ' . $e->getMessage());
            throw $e;
        }
    }

    private function initGrid(): void
    {
        $this->availability = [];
        foreach (self::DAYS as $day) {
            $this->availability[$day] = [
                '1' => false,
                '2' => false,
                '3' => false,
            ];
        }
        $this->status = 'draft';
    }

    private function loadData(): void
    {
        $existing = Availability::query()
            ->where('user_id', auth()->id())
            ->whereBetween('created_at', [$this->weekStart, $this->weekEnd])
            ->with('details:id,availability_id,day,session,is_available')
            ->latest()
            ->first();

        if ($existing) {
            $this->status = $existing->status;
            foreach ($existing->details as $d) {
                if (isset($this->availability[$d->day])) {
                    $this->availability[$d->day][(string)$d->session] = (bool)$d->is_available;
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.schedule.availability-manager')
            ->layout('layouts.app')
            ->title('Ketersediaan');
    }
}
