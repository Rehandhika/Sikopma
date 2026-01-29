<?php

namespace App\Livewire\Schedule;

use App\Models\AssignmentEditHistory;
use App\Models\Schedule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * EditHistory Livewire Component
 *
 * Displays the edit history for a schedule, showing all changes made
 * to assignments with details about who made the changes and why.
 */
class EditHistory extends Component
{
    use WithPagination;

    /**
     * Schedule being viewed
     */
    public Schedule $schedule;

    /**
     * Filter by action type
     */
    public string $filterAction = 'all';

    /**
     * Filter by editor
     */
    public ?int $filterEditor = null;

    /**
     * Search term
     */
    public string $search = '';

    /**
     * Mount component
     */
    public function mount(Schedule $schedule): void
    {
        $this->authorize('view', $schedule);
        $this->schedule = $schedule;
    }

    /**
     * Get filtered history
     */
    public function getHistoryProperty()
    {
        $query = AssignmentEditHistory::where('schedule_id', $this->schedule->id)
            ->with(['editor:id,name', 'assignment.user:id,name'])
            ->orderBy('created_at', 'desc');

        // Apply action filter
        if ($this->filterAction !== 'all') {
            $query->where('action', $this->filterAction);
        }

        // Apply editor filter
        if ($this->filterEditor) {
            $query->where('edited_by', $this->filterEditor);
        }

        // Apply search
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reason', 'like', '%'.$this->search.'%')
                    ->orWhereHas('editor', function ($q2) {
                        $q2->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        return $query->paginate(20);
    }

    /**
     * Get unique editors for filter
     */
    public function getEditorsProperty()
    {
        return AssignmentEditHistory::where('schedule_id', $this->schedule->id)
            ->with('editor:id,name')
            ->select('edited_by')
            ->distinct()
            ->get()
            ->pluck('editor')
            ->filter();
    }

    /**
     * Reset filters
     */
    public function resetFilters(): void
    {
        $this->filterAction = 'all';
        $this->filterEditor = null;
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.schedule.edit-history', [
            'history' => $this->history,
            'editors' => $this->editors,
        ]);
    }
}
