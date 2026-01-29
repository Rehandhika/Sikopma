<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
#[Title('Log Aktivitas')]
class ActivityLogViewer extends Component
{
    use WithPagination;

    #[Url(as: 'from')]
    public string $dateFrom = '';

    #[Url(as: 'to')]
    public string $dateTo = '';

    #[Url(as: 'user')]
    public string $userId = '';

    #[Url(as: 'q')]
    public string $search = '';

    public string $datePreset = 'week';

    protected int $perPage = 25;

    public function mount(): void
    {
        if (empty($this->dateFrom) && empty($this->dateTo)) {
            $this->setDatePreset('week');
        }
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="space-y-6 animate-pulse">
            <div class="h-8 bg-gray-200 rounded w-1/4"></div>
            <div class="flex flex-wrap gap-2">
                <div class="h-9 bg-gray-200 rounded w-20"></div>
                <div class="h-9 bg-gray-200 rounded w-20"></div>
                <div class="h-9 bg-gray-200 rounded w-24"></div>
                <div class="h-9 bg-gray-200 rounded w-24"></div>
            </div>
            <div class="h-16 bg-gray-200 rounded-lg"></div>
            <div class="h-96 bg-gray-200 rounded-lg"></div>
        </div>
        HTML;
    }

    public function setDatePreset(string $preset): void
    {
        $this->datePreset = $preset;
        $today = Carbon::today();

        match ($preset) {
            'today' => [$this->dateFrom, $this->dateTo] = [$today->format('Y-m-d'), $today->format('Y-m-d')],
            'yesterday' => [$this->dateFrom, $this->dateTo] = [$today->copy()->subDay()->format('Y-m-d'), $today->copy()->subDay()->format('Y-m-d')],
            'week' => [$this->dateFrom, $this->dateTo] = [$today->copy()->subDays(7)->format('Y-m-d'), $today->format('Y-m-d')],
            'month' => [$this->dateFrom, $this->dateTo] = [$today->copy()->subDays(30)->format('Y-m-d'), $today->format('Y-m-d')],
            default => null,
        };

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUserId(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->datePreset = '';
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->datePreset = '';
        $this->resetPage();
    }

    /**
     * Get list of users for filter dropdown with caching
     */
    #[Computed]
    public function users(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('activity_log_users', 300, function () {
            return User::select('id', 'name', 'nim')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get activity statistics
     */
    #[Computed]
    public function stats(): array
    {
        $cacheKey = "activity_stats_{$this->dateFrom}_{$this->dateTo}_{$this->userId}";

        return Cache::remember($cacheKey, 60, function () {
            $query = ActivityLog::query()
                ->when($this->dateFrom, fn ($q) => $q->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
                ->when($this->dateTo, fn ($q) => $q->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay()))
                ->when($this->userId, fn ($q) => $q->byUser((int) $this->userId));

            return [
                'total' => (clone $query)->count(),
                'unique_users' => (clone $query)->distinct('user_id')->count('user_id'),
            ];
        });
    }

    /**
     * Build the base query with filters
     */
    private function buildBaseQuery()
    {
        return ActivityLog::query()
            ->when($this->dateFrom, fn ($q) => $q->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay()))
            ->when($this->dateTo, fn ($q) => $q->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay()))
            ->when($this->userId, fn ($q) => $q->byUser((int) $this->userId))
            ->when($this->search, fn ($q) => $q->search($this->search));
    }

    public function render()
    {
        $activities = $this->buildBaseQuery()
            ->with(['user:id,name,nim'])
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return view('livewire.admin.activity-log-viewer', [
            'activities' => $activities,
        ])->layout('layouts.app');
    }
}
