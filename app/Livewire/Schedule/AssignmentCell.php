<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use App\Models\User;
use Carbon\Carbon;

class AssignmentCell extends Component
{
    // Props
    public $date;
    public $session;
    public $assignment = null;
    public $isEditable = true;
    
    // Computed properties
    public $hasAssignment = false;
    public $hasConflict = false;
    public $hasAvailabilityWarning = false;
    public $availabilityStatus = 'unknown'; // available, not_available, unknown
    
    protected $listeners = ['assignmentUpdated' => '$refresh'];

    public function mount($date, $session, $assignment = null, $isEditable = true)
    {
        $this->date = $date;
        $this->session = $session;
        $this->assignment = $assignment;
        $this->isEditable = $isEditable;
        
        $this->computeStatus();
    }

    /**
     * Compute cell status based on assignment data
     */
    private function computeStatus(): void
    {
        $this->hasAssignment = !is_null($this->assignment);
        
        if ($this->hasAssignment) {
            $this->hasAvailabilityWarning = $this->assignment['has_availability_warning'] ?? false;
            $this->availabilityStatus = $this->hasAvailabilityWarning ? 'not_available' : 'available';
        }
    }

    /**
     * Handle cell click to assign/edit
     */
    public function selectCell(): void
    {
        if (!$this->isEditable) {
            $this->dispatch('toast', message: 'Jadwal ini tidak dapat diedit.', type: 'warning');
            return;
        }
        
        // Dispatch event to parent component to open user selector
        $this->dispatch('cell-selected', date: $this->date, session: $this->session);
    }

    /**
     * Remove assignment from this cell
     */
    public function removeAssignment(): void
    {
        if (!$this->isEditable) {
            $this->dispatch('toast', message: 'Jadwal ini tidak dapat diedit.', type: 'warning');
            return;
        }
        
        // Dispatch event to parent component to remove assignment
        $this->dispatch('remove-assignment', date: $this->date, session: $this->session);
    }

    /**
     * Get formatted date for display
     */
    public function getFormattedDate(): string
    {
        return Carbon::parse($this->date)->locale('id')->isoFormat('dddd, D MMM');
    }

    /**
     * Get session time label
     */
    public function getSessionTime(): string
    {
        $times = [
            1 => '07:30 - 10:00',
            2 => '10:20 - 12:50',
            3 => '13:30 - 16:00',
        ];
        return $times[$this->session] ?? '';
    }

    /**
     * Get user initials for avatar fallback
     */
    public function getUserInitials(): string
    {
        if (!$this->hasAssignment) {
            return '';
        }
        
        $name = $this->assignment['user_name'] ?? '';
        $words = explode(' ', $name);
        
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($name, 0, 2));
    }

    public function render()
    {
        return view('livewire.schedule.assignment-cell');
    }
}
