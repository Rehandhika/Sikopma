<?php

namespace App\Livewire\Schedule;

use App\Models\ScheduleTemplate;
use App\Models\User;
use App\Services\ActivityLogService;
use Livewire\Component;

class TemplateSelector extends Component
{
    // Props
    public $show = false;

    public $templates = [];

    public $selectedTemplateId = null;

    public $previewTemplate = null;

    // Search and filter
    public $search = '';

    public $filterType = 'all'; // all, my, public

    protected $listeners = [
        'open-template-selector' => 'openModal',
        'close-template-selector' => 'closeModal',
    ];

    /**
     * Open modal and load templates
     */
    public function openModal(): void
    {
        $this->show = true;
        $this->loadTemplates();
    }

    /**
     * Close modal
     */
    public function closeModal(): void
    {
        $this->show = false;
        $this->reset(['selectedTemplateId', 'previewTemplate', 'search', 'filterType']);
    }

    /**
     * Load templates based on filters
     */
    public function loadTemplates(): void
    {
        $query = ScheduleTemplate::with('creator')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            });

        // Apply filter
        if ($this->filterType === 'my') {
            $query->where('created_by', auth()->id());
        } elseif ($this->filterType === 'public') {
            $query->where('is_public', true);
        } else {
            // Show user's templates and public templates
            $query->where(function ($q) {
                $q->where('created_by', auth()->id())
                    ->orWhere('is_public', true);
            });
        }

        $this->templates = $query->orderByDesc('usage_count')
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    /**
     * Update search and reload templates
     */
    public function updatedSearch(): void
    {
        $this->loadTemplates();
    }

    /**
     * Update filter and reload templates
     */
    public function updatedFilterType(): void
    {
        $this->loadTemplates();
    }

    /**
     * Preview template
     */
    public function previewTemplate(int $templateId): void
    {
        $template = ScheduleTemplate::find($templateId);

        if ($template) {
            $this->selectedTemplateId = $templateId;
            $this->previewTemplate = $template->toArray();

            // Load user details for preview
            $pattern = $template->pattern;
            $userIds = array_unique(array_column($pattern, 'user_id'));
            $users = User::whereIn('id', $userIds)->get()->keyBy('id');

            // Enhance pattern with user details
            foreach ($this->previewTemplate['pattern'] as &$item) {
                $user = $users->get($item['user_id']);
                if ($user) {
                    $item['user_name'] = $user->name;
                    $item['user_nim'] = $user->nim;
                    $item['user_status'] = $user->status;
                } else {
                    $item['user_name'] = 'User tidak ditemukan';
                    $item['user_nim'] = '-';
                    $item['user_status'] = 'inactive';
                }
            }
        }
    }

    /**
     * Select template and dispatch to parent
     */
    public function selectTemplate(int $templateId): void
    {
        $this->dispatch('template-selected', templateId: $templateId);
        $this->closeModal();
    }

    /**
     * Delete template (only if user is creator)
     */
    public function deleteTemplate(int $templateId): void
    {
        $template = ScheduleTemplate::find($templateId);

        if ($template && $template->created_by === auth()->id()) {
            $templateName = $template->name;
            $template->delete();

            // Log activity
            ActivityLogService::log("Menghapus template jadwal '{$templateName}'");

            $this->loadTemplates();
            $this->dispatch('toast', message: 'Template berhasil dihapus.', type: 'success');

            // Clear preview if deleted template was being previewed
            if ($this->selectedTemplateId === $templateId) {
                $this->selectedTemplateId = null;
                $this->previewTemplate = null;
            }
        } else {
            $this->dispatch('toast', message: 'Anda tidak memiliki izin untuk menghapus template ini.', type: 'error');
        }
    }

    /**
     * Get session time label
     */
    public function getSessionTime(int $session): string
    {
        $times = [
            1 => '07:30 - 10:00',
            2 => '10:20 - 12:50',
            3 => '13:30 - 16:00',
        ];

        return $times[$session] ?? '';
    }

    /**
     * Get day name in Indonesian
     */
    public function getDayName(string $day): string
    {
        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
        ];

        return $days[strtolower($day)] ?? $day;
    }

    /**
     * Get pattern statistics
     */
    public function getPatternStats(array $pattern): array
    {
        $totalAssignments = count($pattern);
        $uniqueUsers = count(array_unique(array_column($pattern, 'user_id')));
        $coverage = round(($totalAssignments / 12) * 100, 1);

        return [
            'total_assignments' => $totalAssignments,
            'unique_users' => $uniqueUsers,
            'coverage' => $coverage,
        ];
    }

    public function render()
    {
        return view('livewire.schedule.template-selector');
    }
}
