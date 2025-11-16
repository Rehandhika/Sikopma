<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use App\Models\User;
use Carbon\Carbon;

class UserSelector extends Component
{
    // Props
    public $date;
    public $session;
    public $currentAssignments = [];
    public $show = false;
    
    // Search
    public $search = '';
    
    // Users data
    public $users = [];
    public $availableUsers = [];
    public $notAvailableUsers = [];
    public $inactiveUsers = [];
    
    protected $listeners = [
        'open-user-selector' => 'openModal',
        'close-user-selector' => 'closeModal',
    ];

    /**
     * Open modal and load users
     */
    public function openModal($date, $session, $currentAssignments = []): void
    {
        $this->date = $date;
        $this->session = $session;
        $this->currentAssignments = $currentAssignments;
        $this->search = '';
        $this->show = true;
        
        $this->loadUsers();
    }

    /**
     * Close modal
     */
    public function closeModal(): void
    {
        $this->show = false;
        $this->reset(['date', 'session', 'search', 'users', 'availableUsers', 'notAvailableUsers', 'inactiveUsers']);
    }

    /**
     * Load and categorize users
     */
    public function loadUsers(): void
    {
        $dayName = strtolower(Carbon::parse($this->date)->englishDayOfWeek);
        
        // Get all users with their availability data
        $allUsers = User::with(['availabilities.details' => function($query) use ($dayName) {
            $query->where('day', $dayName)
                  ->where('session', $this->session);
        }])
        ->when($this->search, function($query) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('nim', 'like', '%' . $this->search . '%');
            });
        })
        ->get();
        
        // Categorize users
        $this->availableUsers = [];
        $this->notAvailableUsers = [];
        $this->inactiveUsers = [];
        
        foreach ($allUsers as $user) {
            // Count current assignments for this user
            $currentShifts = collect($this->currentAssignments)->flatten(1)->filter(function($assignment) use ($user) {
                return $assignment && $assignment['user_id'] == $user->id;
            })->count();
            
            // Check if user already assigned at this time
            $hasConflict = collect($this->currentAssignments)->flatten(1)->contains(function($assignment) use ($user) {
                return $assignment && 
                       $assignment['user_id'] == $user->id && 
                       $assignment['date'] == $this->date && 
                       $assignment['session'] == $this->session;
            });
            
            // Check availability status
            $isAvailable = false;
            $isNotAvailable = false;
            
            foreach ($user->availabilities as $availability) {
                foreach ($availability->details as $detail) {
                    if ($detail->day === $dayName && $detail->session == $this->session) {
                        if ($detail->is_available) {
                            $isAvailable = true;
                        } else {
                            $isNotAvailable = true;
                        }
                    }
                }
            }
            
            // Determine availability level
            $availabilityLevel = 'unknown';
            if ($isAvailable) {
                $availabilityLevel = 'high';
            } elseif ($isNotAvailable) {
                $availabilityLevel = 'low';
            }
            
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'nim' => $user->nim,
                'photo' => $user->photo,
                'status' => $user->status,
                'current_shifts' => $currentShifts,
                'has_conflict' => $hasConflict,
                'is_available' => $isAvailable,
                'is_not_available' => $isNotAvailable,
                'availability_level' => $availabilityLevel,
            ];
            
            // Categorize
            if ($user->status !== 'active') {
                $this->inactiveUsers[] = $userData;
            } elseif ($hasConflict) {
                $this->notAvailableUsers[] = $userData;
            } elseif ($isAvailable) {
                $this->availableUsers[] = $userData;
            } elseif ($isNotAvailable) {
                $this->notAvailableUsers[] = $userData;
            } else {
                // Unknown availability - add to available but with lower priority
                $this->availableUsers[] = $userData;
            }
        }
        
        // Sort available users by current shifts (ascending)
        usort($this->availableUsers, function($a, $b) {
            return $a['current_shifts'] <=> $b['current_shifts'];
        });
        
        // Sort not available users by current shifts
        usort($this->notAvailableUsers, function($a, $b) {
            return $a['current_shifts'] <=> $b['current_shifts'];
        });
    }

    /**
     * Select user and dispatch to parent
     */
    public function selectUser(int $userId): void
    {
        $this->dispatch('user-selected', userId: $userId);
        $this->closeModal();
    }

    /**
     * Update search and reload users
     */
    public function updatedSearch(): void
    {
        $this->loadUsers();
    }

    /**
     * Get formatted date for display
     */
    public function getFormattedDate(): string
    {
        return Carbon::parse($this->date)->locale('id')->isoFormat('dddd, D MMMM YYYY');
    }

    /**
     * Get session time label
     */
    public function getSessionTime(): string
    {
        $times = [
            1 => '08:00 - 12:00',
            2 => '13:00 - 17:00',
            3 => '17:00 - 21:00',
        ];
        return $times[$this->session] ?? '';
    }

    /**
     * Get user initials for avatar fallback
     */
    public function getUserInitials(string $name): string
    {
        $words = explode(' ', $name);
        
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        
        return strtoupper(substr($name, 0, 2));
    }

    public function render()
    {
        return view('livewire.schedule.user-selector');
    }
}
