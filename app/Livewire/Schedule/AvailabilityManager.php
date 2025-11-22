<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use App\Models\{User, Availability, AvailabilityDetail};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AvailabilityManager extends Component
{
    public $selectedWeekOffset = 0;
    public $weekStart;
    public $weekEnd;
    public $weekRange;
    
    // Availability data per day and session
    public $availability = [];
    public $notes = '';
    public $status = 'draft';
    
    // Predefined sessions
    public $sessions = [
        1 => 'Sesi 1 (Pagi)',
        2 => 'Sesi 2 (Siang)',
        3 => 'Sesi 3 (Sore)',
    ];
    
    // Days of week
    public $days = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu',
    ];

    protected $rules = [
        'availability' => 'required|array|min:1',
        'availability.*' => 'required|array',
        'availability.*.*' => 'boolean',
        'notes' => 'nullable|string|max:500',
        'status' => 'required|in:draft,submitted',
    ];

    protected $messages = [
        'availability.required' => 'Pilih minimal satu sesi yang tersedia.',
        'availability.min' => 'Pilih minimal satu sesi yang tersedia.',
    ];

    public function mount()
    {
        $this->status = 'draft'; // Initialize status first
        $this->initializeWeek();
        $this->initializeAvailability(); // Initialize array structure first
        $this->loadCurrentAvailability(); // Then load existing data
    }

    private function initializeWeek()
    {
        $this->weekStart = now()->startOfWeek(Carbon::MONDAY)->addWeeks($this->selectedWeekOffset);
        $this->weekEnd = now()->endOfWeek(Carbon::SUNDAY)->addWeeks($this->selectedWeekOffset);
        $this->weekRange = $this->weekStart->locale('id')->format('d F') . ' - ' . 
                          $this->weekEnd->locale('id')->format('d F Y');
    }

    private function initializeAvailability()
    {
        // Always initialize the structure to ensure all keys exist
        foreach ($this->days as $dayKey => $dayName) {
            if (!isset($this->availability[$dayKey])) {
                $this->availability[$dayKey] = [];
            }
            foreach ($this->sessions as $sessionKey => $sessionName) {
                if (!isset($this->availability[$dayKey][$sessionKey])) {
                    $this->availability[$dayKey][$sessionKey] = false;
                }
            }
        }
    }

    private function loadCurrentAvailability()
    {
        // Check if user has existing availability for this week
        $existingAvailability = Availability::where('user_id', auth()->id())
            ->where(function($query) {
                $query->whereBetween('submitted_at', [
                    $this->weekStart->copy()->startOfDay(),
                    $this->weekEnd->copy()->endOfDay()
                ])
                ->orWhere(function($q) {
                    // Also check for drafts created this week
                    $q->where('status', 'draft')
                      ->whereBetween('created_at', [
                          $this->weekStart->copy()->startOfDay(),
                          $this->weekEnd->copy()->endOfDay()
                      ]);
                });
            })
            ->with('details')
            ->latest()
            ->first();

        if ($existingAvailability) {
            $this->status = $existingAvailability->status;
            $this->notes = $existingAvailability->notes ?? '';
            
            // Load availability details - array structure already initialized
            foreach ($existingAvailability->details as $detail) {
                $this->availability[$detail->day][$detail->session] = $detail->is_available;
            }
        }
    }

    public function updatedSelectedWeekOffset()
    {
        $this->initializeWeek();
        $this->resetAvailability();
        $this->initializeAvailability(); // Initialize structure first
        $this->loadCurrentAvailability(); // Then load data
    }

    public function resetAvailability()
    {
        $this->availability = [];
        $this->notes = '';
        $this->status = 'draft';
    }

    public function toggleAvailability($day, $session)
    {
        $this->availability[$day][$session] = !$this->availability[$day][$session];
    }

    public function setDayAvailability($day, $isAvailable)
    {
        foreach ($this->sessions as $sessionKey => $sessionName) {
            $this->availability[$day][$sessionKey] = $isAvailable;
        }
    }

    public function setSessionAvailability($session, $isAvailable)
    {
        foreach ($this->days as $dayKey => $dayName) {
            $this->availability[$dayKey][$session] = $isAvailable;
        }
    }

    public function selectAll()
    {
        foreach ($this->days as $dayKey => $dayName) {
            foreach ($this->sessions as $sessionKey => $sessionName) {
                $this->availability[$dayKey][$sessionKey] = true;
            }
        }
    }

    public function clearAll()
    {
        foreach ($this->days as $dayKey => $dayName) {
            foreach ($this->sessions as $sessionKey => $sessionName) {
                $this->availability[$dayKey][$sessionKey] = false;
            }
        }
    }

    public function saveAsDraft()
    {
        $this->status = 'draft';
        $this->saveAvailability();
    }

    public function submitAvailability()
    {
        $this->status = 'submitted';
        $this->saveAvailability();
    }

    public function saveAvailability()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Delete existing availability for this week
            Availability::where('user_id', auth()->id())
                ->where(function($query) {
                    $query->whereBetween('submitted_at', [
                        $this->weekStart->copy()->startOfDay(),
                        $this->weekEnd->copy()->endOfDay()
                    ])
                    ->orWhere(function($q) {
                        $q->where('status', 'draft')
                          ->whereBetween('created_at', [
                              $this->weekStart->copy()->startOfDay(),
                              $this->weekEnd->copy()->endOfDay()
                          ]);
                    });
                })
                ->delete();

            // Create new availability record
            $availability = Availability::create([
                'user_id' => auth()->id(),
                'schedule_id' => null,
                'status' => $this->status,
                'submitted_at' => $this->status === 'submitted' ? now() : null,
                'total_available_sessions' => $this->getTotalAvailableSessions(),
                'notes' => $this->notes,
            ]);

            // Create availability details
            $details = [];
            foreach ($this->availability as $day => $sessions) {
                foreach ($sessions as $session => $isAvailable) {
                    $details[] = [
                        'availability_id' => $availability->id,
                        'day' => $day,
                        'session' => (string) $session,
                        'is_available' => $isAvailable,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            AvailabilityDetail::insert($details);

            DB::commit();

            $message = $this->status === 'submitted' 
                ? 'Ketersediaan berhasil dikirim!' 
                : 'Ketersediaan berhasil disimpan sebagai draft!';
            
            $this->dispatch('alert', type: 'success', message: $message);
            $this->dispatch('availability-updated');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', type: 'error', message: 'Gagal menyimpan ketersediaan: ' . $e->getMessage());
        }
    }

    public function getTotalAvailableSessions()
    {
        $total = 0;
        foreach ($this->availability as $day => $sessions) {
            foreach ($sessions as $session => $isAvailable) {
                if ($isAvailable) {
                    $total++;
                }
            }
        }
        return $total;
    }

    public function getTotalAvailableHours()
    {
        $sessions = $this->getTotalAvailableSessions();
        // Assuming each session is 4 hours
        return $sessions * 4;
    }

    public function getAvailableDaysCount()
    {
        $days = 0;
        foreach ($this->availability as $day => $sessions) {
            if (array_filter($sessions)) {
                $days++;
            }
        }
        return $days;
    }

    public function isCurrentWeek()
    {
        return $this->selectedWeekOffset === 0;
    }

    public function canEdit()
    {
        // Allow editing if it's current week or future week, and status is draft
        return $this->selectedWeekOffset >= 0 && $this->status === 'draft';
    }

    public function getDayName($day)
    {
        return $this->days[$day] ?? $day;
    }

    public function getSessionName($session)
    {
        return $this->sessions[$session] ?? "Sesi $session";
    }

    public function getSessionTime($session)
    {
        $times = [
            1 => '08:00 - 12:00',
            2 => '13:00 - 17:00',
            3 => '17:00 - 21:00',
        ];
        
        return $times[$session] ?? '';
    }

    public function __invoke()
    {
        return $this->render();
    }

    public function render()
    {
        return view('livewire.schedule.availability-manager', [
            'status' => $this->status,
            'selectedWeekOffset' => $this->selectedWeekOffset,
            'weekRange' => $this->weekRange,
            'availability' => $this->availability,
            'notes' => $this->notes,
            'sessions' => $this->sessions,
            'days' => $this->days,
            'totalSessions' => $this->getTotalAvailableSessions(),
            'totalHours' => $this->getTotalAvailableHours(),
            'availableDays' => $this->getAvailableDaysCount(),
            'canEdit' => $this->canEdit(),
            'isCurrentWeek' => $this->isCurrentWeek(),
        ])->layout('layouts.app')->title('Manajemen Ketersediaan');
    }
}
