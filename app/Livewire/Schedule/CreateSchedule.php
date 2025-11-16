<?php

namespace App\Livewire\Schedule;

use Livewire\Component;
use Livewire\Attributes\{Title, Layout};
use App\Models\{Schedule, ScheduleAssignment, User, ScheduleTemplate};
use App\Services\{ScheduleService, AutoAssignmentService, TemplateService, ConflictDetectionService};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

#[Title('Buat Jadwal Baru')]
#[Layout('layouts.app')]
class CreateSchedule extends Component
{
    // Schedule data
    public $weekStartDate;
    public $weekEndDate;
    public $notes = '';
    public $scheduleId = null;
    
    // Assignment grid (4 days x 3 sessions)
    public $assignments = [];
    
    // UI state
    public $selectedDate = null;
    public $selectedSession = null;
    public $showUserSelector = false;
    public $availableUsers = [];
    
    // Statistics
    public $totalAssignments = 0;
    public $coverageRate = 0;
    public $assignmentsPerUser = [];
    
    // Modes
    public $mode = 'manual'; // manual, auto, template
    public $selectedTemplateId = null;
    public $templates = [];
    
    // Auto-assignment preview
    public $showAutoPreview = false;
    public $autoPreviewData = [];
    
    // Conflicts
    public $conflicts = [];
    
    // Loading states
    public $isLoading = false;
    public $isSaving = false;
    
    // Undo/Redo functionality
    public $history = [];
    public $historyIndex = -1;
    public $maxHistorySteps = 20;
    public $canUndo = false;
    public $canRedo = false;

    protected $rules = [
        'weekStartDate' => 'required|date',
        'weekEndDate' => 'required|date|after:weekStartDate',
        'notes' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        // Set default dates (next Monday)
        $nextMonday = Carbon::now()->next(Carbon::MONDAY);
        $this->weekStartDate = $nextMonday->format('Y-m-d');
        $this->weekEndDate = $nextMonday->copy()->addDays(3)->format('Y-m-d');
        
        $this->initializeGrid();
        $this->loadTemplates();
        $this->updateStatistics();
        $this->detectConflicts();
        
        // Save initial state to history
        $this->saveToHistory();
    }

    /**
     * Initialize empty grid
     */
    public function initializeGrid(): void
    {
        $startDate = Carbon::parse($this->weekStartDate);
        
        for ($day = 0; $day < 4; $day++) {
            $date = $startDate->copy()->addDays($day);
            $dateStr = $date->format('Y-m-d');
            
            $this->assignments[$dateStr] = [];
            
            for ($session = 1; $session <= 3; $session++) {
                $this->assignments[$dateStr][$session] = null;
            }
        }
    }

    /**
     * Load available templates
     */
    public function loadTemplates(): void
    {
        $templateService = app(TemplateService::class);
        $this->templates = $templateService->listTemplates(auth()->user())->toArray();
    }

    /**
     * Select a cell to assign user
     */
    public function selectCell(string $date, int $session): void
    {
        $this->selectedDate = $date;
        $this->selectedSession = $session;
        $this->showUserSelector = true;
        
        // Load available users
        $this->loadAvailableUsers($date, $session);
    }

    /**
     * Load available users for a slot
     */
    private function loadAvailableUsers(string $date, int $session): void
    {
        $dayName = strtolower(Carbon::parse($date)->englishDayOfWeek);
        
        $this->availableUsers = User::where('status', 'active')
            ->with(['availabilities' => function($query) use ($dayName, $session) {
                $query->whereHas('details', function($q) use ($dayName, $session) {
                    $q->where('day', $dayName)
                      ->where('session', $session)
                      ->where('is_available', true);
                });
            }])
            ->get()
            ->map(function($user) use ($date, $session, $dayName) {
                // Check if user already has assignment at this time
                $hasConflict = collect($this->assignments)->flatten(1)->contains(function($assignment) use ($user, $date, $session) {
                    return $assignment && 
                           $assignment['user_id'] == $user->id && 
                           $assignment['date'] == $date && 
                           $assignment['session'] == $session;
                });
                
                // Count current assignments
                $currentAssignments = collect($this->assignments)->flatten(1)->filter(function($assignment) use ($user) {
                    return $assignment && $assignment['user_id'] == $user->id;
                })->count();
                
                // Check availability status
                $isAvailable = $user->availabilities->isNotEmpty();
                
                // Check if user marked as NOT available for this specific slot
                $isNotAvailable = false;
                foreach ($user->availabilities as $availability) {
                    foreach ($availability->details as $detail) {
                        if ($detail->day === $dayName && 
                            $detail->session == $session && 
                            !$detail->is_available) {
                            $isNotAvailable = true;
                            break 2;
                        }
                    }
                }
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nim' => $user->nim,
                    'photo' => $user->photo,
                    'is_available' => $isAvailable,
                    'is_not_available' => $isNotAvailable,
                    'has_conflict' => $hasConflict,
                    'current_assignments' => $currentAssignments,
                ];
            })
            ->sortBy([
                ['has_conflict', 'asc'],
                ['is_not_available', 'asc'],
                ['is_available', 'desc'],
                ['current_assignments', 'asc'],
            ])
            ->values()
            ->toArray();
    }

    /**
     * Assign user to selected slot with validation and conflict detection
     */
    public function assignUser(int $userId): void
    {
        if (!$this->selectedDate || !$this->selectedSession) {
            $this->dispatch('alert', type: 'error', message: 'Pilih slot terlebih dahulu.');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            $this->dispatch('alert', type: 'error', message: 'User tidak ditemukan.');
            return;
        }

        // Validate user is active
        if ($user->status !== 'active') {
            $this->dispatch('alert', type: 'error', message: 'User tidak aktif dan tidak dapat ditugaskan.');
            return;
        }

        // Check for conflicts - user already assigned at this time
        $conflict = $this->checkAssignmentConflict($userId, $this->selectedDate, $this->selectedSession);
        if ($conflict) {
            $this->dispatch('alert', type: 'error', message: 'User sudah memiliki assignment pada waktu yang sama.');
            return;
        }

        // Check availability mismatch (warning only)
        $availabilityWarning = $this->checkAvailabilityMismatch($userId, $this->selectedDate, $this->selectedSession);
        
        // Create assignment data
        $this->assignments[$this->selectedDate][$this->selectedSession] = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_nim' => $user->nim,
            'user_photo' => $user->photo,
            'date' => $this->selectedDate,
            'session' => $this->selectedSession,
            'day' => strtolower(Carbon::parse($this->selectedDate)->englishDayOfWeek),
            'has_availability_warning' => $availabilityWarning,
        ];

        // Update statistics and detect conflicts
        $this->updateStatistics();
        $this->detectConflicts();
        
        // Save to history for undo/redo
        $this->saveToHistory();

        $this->showUserSelector = false;
        $this->selectedDate = null;
        $this->selectedSession = null;

        if ($availabilityWarning) {
            $this->dispatch('alert', type: 'warning', message: 'Assignment ditambahkan, tetapi user tidak tersedia pada waktu ini.');
        } else {
            $this->dispatch('alert', type: 'success', message: 'Assignment berhasil ditambahkan.');
        }
    }

    /**
     * Remove assignment
     */
    public function removeAssignment(string $date, int $session): void
    {
        if (!isset($this->assignments[$date][$session])) {
            $this->dispatch('alert', type: 'error', message: 'Assignment tidak ditemukan.');
            return;
        }

        $this->assignments[$date][$session] = null;
        
        // Update statistics and re-check conflicts
        $this->updateStatistics();
        $this->detectConflicts();
        
        // Save to history for undo/redo
        $this->saveToHistory();
        
        $this->dispatch('alert', type: 'success', message: 'Assignment berhasil dihapus.');
    }

    /**
     * Check for assignment conflict (same user, same time)
     */
    private function checkAssignmentConflict(int $userId, string $date, int $session): bool
    {
        return collect($this->assignments)->flatten(1)->contains(function($assignment) use ($userId, $date, $session) {
            return $assignment && 
                   $assignment['user_id'] == $userId && 
                   $assignment['date'] == $date && 
                   $assignment['session'] == $session;
        });
    }

    /**
     * Check for availability mismatch
     */
    private function checkAvailabilityMismatch(int $userId, string $date, int $session): bool
    {
        $dayName = strtolower(Carbon::parse($date)->englishDayOfWeek);
        
        $user = User::with(['availabilities.details' => function($query) use ($dayName, $session) {
            $query->where('day', $dayName)
                  ->where('session', $session);
        }])->find($userId);

        if (!$user) {
            return false;
        }

        // Check if user explicitly marked as NOT available
        foreach ($user->availabilities as $availability) {
            foreach ($availability->details as $detail) {
                if ($detail->day === $dayName && 
                    $detail->session == $session && 
                    !$detail->is_available) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detect conflicts in current assignments
     */
    private function detectConflicts(): void
    {
        $this->conflicts = [
            'critical' => [],
            'warning' => [],
            'info' => [],
        ];

        $allAssignments = collect($this->assignments)->flatten(1)->filter();

        // Check for double assignments (shouldn't happen with validation, but check anyway)
        $grouped = $allAssignments->groupBy(function($assignment) {
            return $assignment['user_id'] . '_' . $assignment['date'] . '_' . $assignment['session'];
        });

        foreach ($grouped as $key => $group) {
            if ($group->count() > 1) {
                $this->conflicts['critical'][] = [
                    'type' => 'double_assignment',
                    'message' => "User {$group->first()['user_name']} memiliki lebih dari 1 assignment pada waktu yang sama",
                ];
            }
        }

        // Check for availability mismatches
        foreach ($allAssignments as $assignment) {
            if (isset($assignment['has_availability_warning']) && $assignment['has_availability_warning']) {
                $this->conflicts['warning'][] = [
                    'type' => 'availability_mismatch',
                    'message' => "User {$assignment['user_name']} tidak tersedia pada " . 
                                 Carbon::parse($assignment['date'])->format('d M') . 
                                 " Sesi {$assignment['session']}",
                ];
            }
        }

        // Check for overloaded users (more than 4 shifts)
        $userCounts = $allAssignments->groupBy('user_id');
        foreach ($userCounts as $userId => $assignments) {
            if ($assignments->count() > 4) {
                $this->conflicts['warning'][] = [
                    'type' => 'overloaded_user',
                    'message' => "User {$assignments->first()['user_name']} memiliki terlalu banyak shift ({$assignments->count()} shift)",
                ];
            }
        }

        // Check for low coverage
        if ($this->coverageRate < 80) {
            $this->conflicts['info'][] = [
                'type' => 'low_coverage',
                'message' => "Coverage rendah: {$this->coverageRate}% (target: 80%+)",
            ];
        }
    }

    /**
     * Update statistics
     */
    private function updateStatistics(): void
    {
        $allAssignments = collect($this->assignments)->flatten(1)->filter();
        
        $this->totalAssignments = $allAssignments->count();
        $this->coverageRate = round(($this->totalAssignments / 12) * 100, 2);
        
        // Count per user
        $this->assignmentsPerUser = $allAssignments->groupBy('user_id')
            ->map(function($group) {
                return [
                    'user_name' => $group->first()['user_name'],
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->toArray();
    }

    /**
     * Auto assign using algorithm - generates preview
     */
    public function autoAssign(): void
    {
        $this->isLoading = true;
        
        try {
            // Validate dates first
            $this->validate([
                'weekStartDate' => 'required|date',
                'weekEndDate' => 'required|date|after:weekStartDate',
            ]);
            
            // Create temporary schedule for auto-assignment
            $tempSchedule = new Schedule([
                'week_start_date' => $this->weekStartDate,
                'week_end_date' => $this->weekEndDate,
            ]);
            
            // Call auto-assignment service to generate preview
            $autoService = app(AutoAssignmentService::class);
            $preview = $autoService->previewAssignments($tempSchedule);
            
            // Check if any assignments were generated
            if (empty($preview['assignments'])) {
                $this->dispatch('alert', type: 'warning', message: 'Tidak ada assignment yang dapat di-generate. Pastikan ada user dengan availability yang sesuai.');
                return;
            }
            
            // Store preview data and show modal
            $this->autoPreviewData = $preview;
            $this->showAutoPreview = true;
            
            // Log success
            \Illuminate\Support\Facades\Log::info('Auto-assignment preview generated', [
                'total_assignments' => $preview['statistics']['total_assignments'],
                'coverage_rate' => $preview['statistics']['coverage_rate'],
                'fairness_score' => $preview['statistics']['fairness_score'],
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to show field errors
            throw $e;
        } catch (\Exception $e) {
            // Log error for debugging
            \Illuminate\Support\Facades\Log::error('Auto-assignment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Show user-friendly error message
            $this->dispatch('alert', type: 'error', message: 'Gagal generate auto-assignment: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Apply auto-assignment from preview
     */
    public function applyAutoAssignment(): void
    {
        // Validate preview data exists
        if (empty($this->autoPreviewData['assignments'])) {
            $this->dispatch('alert', type: 'error', message: 'Tidak ada assignment untuk diterapkan.');
            return;
        }

        $this->isLoading = true;
        
        try {
            // Clear current assignments
            $this->initializeGrid();
            
            // Apply auto-assignments
            $appliedCount = 0;
            $skippedCount = 0;
            
            foreach ($this->autoPreviewData['assignments'] as $assignment) {
                $user = User::find($assignment['user_id']);
                
                // Validate user still exists and is active
                if (!$user || $user->status !== 'active') {
                    $skippedCount++;
                    \Illuminate\Support\Facades\Log::warning('Skipped assignment for inactive/missing user', [
                        'user_id' => $assignment['user_id'],
                    ]);
                    continue;
                }
                
                // Check availability warning
                $availabilityWarning = $this->checkAvailabilityMismatch(
                    $user->id, 
                    $assignment['date'], 
                    $assignment['session']
                );
                
                // Apply assignment
                $this->assignments[$assignment['date']][$assignment['session']] = [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_nim' => $user->nim,
                    'user_photo' => $user->photo,
                    'date' => $assignment['date'],
                    'session' => $assignment['session'],
                    'day' => $assignment['day'],
                    'has_availability_warning' => $availabilityWarning,
                ];
                
                $appliedCount++;
            }

            // Update statistics and detect conflicts
            $this->updateStatistics();
            $this->detectConflicts();
            
            // Save to history for undo/redo
            $this->saveToHistory();
            
            // Close preview modal
            $this->showAutoPreview = false;
            
            // Show success message with details
            $message = "Auto-assignment berhasil diterapkan! {$appliedCount} assignment ditambahkan.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} assignment dilewati (user tidak aktif).";
            }
            
            $this->dispatch('alert', type: 'success', message: $message);
            
            // Log success
            \Illuminate\Support\Facades\Log::info('Auto-assignment applied', [
                'applied_count' => $appliedCount,
                'skipped_count' => $skippedCount,
                'coverage_rate' => $this->coverageRate,
            ]);
            
        } catch (\Exception $e) {
            // Log error
            \Illuminate\Support\Facades\Log::error('Failed to apply auto-assignment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Show error message
            $this->dispatch('alert', type: 'error', message: 'Gagal menerapkan auto-assignment: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }
    
    /**
     * Cancel auto-assignment preview
     */
    public function cancelAutoPreview(): void
    {
        $this->showAutoPreview = false;
        $this->autoPreviewData = [];
        $this->dispatch('alert', type: 'info', message: 'Auto-assignment dibatalkan.');
    }

    /**
     * Load template
     */
    public function loadTemplate(int $templateId): void
    {
        $this->isLoading = true;
        
        try {
            $template = ScheduleTemplate::findOrFail($templateId);
            $pattern = $template->pattern;
            
            // Clear current assignments
            $this->initializeGrid();
            
            // Apply template pattern
            $startDate = Carbon::parse($this->weekStartDate);
            
            foreach ($pattern as $item) {
                $dayIndex = $this->getDayIndex($item['day']);
                $date = $startDate->copy()->addDays($dayIndex);
                $dateStr = $date->format('Y-m-d');
                
                $user = User::find($item['user_id']);
                if ($user && $user->status === 'active') {
                    // Check availability warning
                    $availabilityWarning = $this->checkAvailabilityMismatch($user->id, $dateStr, $item['session']);
                    
                    $this->assignments[$dateStr][$item['session']] = [
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_nim' => $user->nim,
                        'user_photo' => $user->photo,
                        'date' => $dateStr,
                        'session' => $item['session'],
                        'day' => $item['day'],
                        'has_availability_warning' => $availabilityWarning,
                    ];
                }
            }
            
            $this->updateStatistics();
            $this->detectConflicts();
            
            // Save to history for undo/redo
            $this->saveToHistory();
            
            $this->dispatch('alert', type: 'success', message: 'Template berhasil diterapkan!');
            
        } catch (\Exception $e) {
            $this->dispatch('alert', type: 'error', message: 'Gagal load template: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Validate schedule before publish
     */
    public function validateSchedule(): array
    {
        $errors = [];
        $warnings = [];
        
        // Check minimum coverage (50% for publish)
        if ($this->coverageRate < 50) {
            $errors[] = "Coverage minimal 50% untuk publish. Saat ini: {$this->coverageRate}%";
        }
        
        // Check for critical conflicts
        if (!empty($this->conflicts['critical'])) {
            foreach ($this->conflicts['critical'] as $conflict) {
                $errors[] = $conflict['message'];
            }
        }
        
        // Check for warnings
        if (!empty($this->conflicts['warning'])) {
            foreach ($this->conflicts['warning'] as $conflict) {
                $warnings[] = $conflict['message'];
            }
        }
        
        // Check if any assignments exist
        if ($this->totalAssignments === 0) {
            $errors[] = 'Tidak ada assignment. Tambahkan minimal 1 assignment.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Save as draft
     */
    public function saveDraft()
    {
        $this->validate();
        $this->isSaving = true;
        
        try {
            // Check if there are any assignments
            if ($this->totalAssignments === 0) {
                $this->dispatch('alert', type: 'warning', message: 'Tidak ada assignment untuk disimpan.');
                return;
            }
            
            DB::beginTransaction();
            
            $scheduleService = app(ScheduleService::class);
            
            // Create schedule
            $schedule = $scheduleService->createSchedule([
                'week_start_date' => $this->weekStartDate,
                'week_end_date' => $this->weekEndDate,
                'notes' => $this->notes,
            ]);
            
            // Add assignments
            $assignmentCount = 0;
            foreach ($this->assignments as $date => $sessions) {
                foreach ($sessions as $session => $assignment) {
                    if ($assignment) {
                        $scheduleService->addAssignment($schedule, [
                            'user_id' => $assignment['user_id'],
                            'date' => $date,
                            'session' => $session,
                        ]);
                        $assignmentCount++;
                    }
                }
            }
            
            $this->scheduleId = $schedule->id;
            
            DB::commit();
            
            // Success notification
            $this->dispatch('alert', type: 'success', message: "Jadwal berhasil disimpan sebagai draft! {$assignmentCount} assignment ditambahkan.");
            
            // Redirect to schedule list
            return redirect()->route('schedule.index');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // Let validation errors show in form
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Failed to save draft', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('alert', type: 'error', message: 'Gagal menyimpan jadwal: ' . $e->getMessage());
        } finally {
            $this->isSaving = false;
        }
    }

    /**
     * Publish schedule
     */
    public function publish()
    {
        $this->validate();
        
        // Validate schedule before publish
        $validation = $this->validateSchedule();
        
        if (!$validation['valid']) {
            // Show all errors
            $errorMessage = 'Jadwal tidak dapat dipublikasikan:<br>';
            foreach ($validation['errors'] as $error) {
                $errorMessage .= '• ' . $error . '<br>';
            }
            
            $this->dispatch('alert', type: 'error', message: $errorMessage);
            return;
        }
        
        // Show warnings if any (but allow publish)
        if (!empty($validation['warnings'])) {
            \Illuminate\Support\Facades\Log::warning('Publishing schedule with warnings', [
                'warnings' => $validation['warnings'],
            ]);
        }
        
        $this->isSaving = true;
        
        try {
            DB::beginTransaction();
            
            $scheduleService = app(ScheduleService::class);
            
            // Create schedule
            $schedule = $scheduleService->createSchedule([
                'week_start_date' => $this->weekStartDate,
                'week_end_date' => $this->weekEndDate,
                'notes' => $this->notes,
            ]);
            
            // Add assignments
            $assignmentCount = 0;
            foreach ($this->assignments as $date => $sessions) {
                foreach ($sessions as $session => $assignment) {
                    if ($assignment) {
                        $scheduleService->addAssignment($schedule, [
                            'user_id' => $assignment['user_id'],
                            'date' => $date,
                            'session' => $session,
                        ]);
                        $assignmentCount++;
                    }
                }
            }
            
            // Publish schedule
            $scheduleService->publishSchedule($schedule);
            
            // Clear history after publish
            $this->clearHistory();
            
            $this->scheduleId = $schedule->id;
            
            DB::commit();
            
            // Log success
            \Illuminate\Support\Facades\Log::info('Schedule published successfully', [
                'schedule_id' => $schedule->id,
                'assignment_count' => $assignmentCount,
                'coverage_rate' => $this->coverageRate,
                'published_by' => auth()->id(),
            ]);
            
            // Success notification with details
            $message = "Jadwal berhasil dipublikasikan! {$assignmentCount} assignment ditambahkan. ";
            $message .= "Coverage: {$this->coverageRate}%. ";
            $message .= "Notifikasi telah dikirim ke anggota yang ditugaskan.";
            
            $this->dispatch('alert', type: 'success', message: $message);
            
            // Redirect to schedule list
            return redirect()->route('schedule.index');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            // Let validation errors show in form
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Illuminate\Support\Facades\Log::error('Failed to publish schedule', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            $this->dispatch('alert', type: 'error', message: 'Gagal publish jadwal: ' . $e->getMessage());
        } finally {
            $this->isSaving = false;
        }
    }

    /**
     * Assign user to all sessions in a specific day
     * Requirements: 10.1
     */
    public function assignToAllSessions(string $date, int $userId): void
    {
        $user = User::find($userId);
        if (!$user) {
            $this->dispatch('alert', type: 'error', message: 'User tidak ditemukan.');
            return;
        }

        // Validate user is active
        if ($user->status !== 'active') {
            $this->dispatch('alert', type: 'error', message: 'User tidak aktif dan tidak dapat ditugaskan.');
            return;
        }

        $assignedCount = 0;
        $conflictCount = 0;
        $warningCount = 0;

        // Assign to all 3 sessions
        for ($session = 1; $session <= 3; $session++) {
            // Check for conflicts
            $conflict = $this->checkAssignmentConflict($userId, $date, $session);
            if ($conflict) {
                $conflictCount++;
                continue;
            }

            // Check availability mismatch (warning only)
            $availabilityWarning = $this->checkAvailabilityMismatch($userId, $date, $session);
            if ($availabilityWarning) {
                $warningCount++;
            }

            // Create assignment
            $this->assignments[$date][$session] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_nim' => $user->nim,
                'user_photo' => $user->photo,
                'date' => $date,
                'session' => $session,
                'day' => strtolower(Carbon::parse($date)->englishDayOfWeek),
                'has_availability_warning' => $availabilityWarning,
            ];

            $assignedCount++;
        }

        // Update statistics and detect conflicts
        $this->updateStatistics();
        $this->detectConflicts();
        
        // Save to history for undo/redo
        $this->saveToHistory();

        // Build message
        $message = "{$assignedCount} assignment berhasil ditambahkan untuk semua sesi.";
        if ($conflictCount > 0) {
            $message .= " {$conflictCount} sesi dilewati karena konflik.";
        }
        if ($warningCount > 0) {
            $message .= " {$warningCount} sesi memiliki warning availability.";
        }

        $type = $conflictCount > 0 ? 'warning' : ($warningCount > 0 ? 'warning' : 'success');
        $this->dispatch('alert', type: $type, message: $message);
    }

    /**
     * Assign user to same session across all days
     * Requirements: 10.2
     */
    public function assignToAllDays(int $session, int $userId): void
    {
        $user = User::find($userId);
        if (!$user) {
            $this->dispatch('alert', type: 'error', message: 'User tidak ditemukan.');
            return;
        }

        // Validate user is active
        if ($user->status !== 'active') {
            $this->dispatch('alert', type: 'error', message: 'User tidak aktif dan tidak dapat ditugaskan.');
            return;
        }

        $assignedCount = 0;
        $conflictCount = 0;
        $warningCount = 0;

        // Assign to all 4 days
        $startDate = Carbon::parse($this->weekStartDate);
        for ($day = 0; $day < 4; $day++) {
            $date = $startDate->copy()->addDays($day);
            $dateStr = $date->format('Y-m-d');

            // Check for conflicts
            $conflict = $this->checkAssignmentConflict($userId, $dateStr, $session);
            if ($conflict) {
                $conflictCount++;
                continue;
            }

            // Check availability mismatch (warning only)
            $availabilityWarning = $this->checkAvailabilityMismatch($userId, $dateStr, $session);
            if ($availabilityWarning) {
                $warningCount++;
            }

            // Create assignment
            $this->assignments[$dateStr][$session] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_nim' => $user->nim,
                'user_photo' => $user->photo,
                'date' => $dateStr,
                'session' => $session,
                'day' => strtolower($date->englishDayOfWeek),
                'has_availability_warning' => $availabilityWarning,
            ];

            $assignedCount++;
        }

        // Update statistics and detect conflicts
        $this->updateStatistics();
        $this->detectConflicts();
        
        // Save to history for undo/redo
        $this->saveToHistory();

        // Build message
        $message = "{$assignedCount} assignment berhasil ditambahkan untuk semua hari.";
        if ($conflictCount > 0) {
            $message .= " {$conflictCount} hari dilewati karena konflik.";
        }
        if ($warningCount > 0) {
            $message .= " {$warningCount} hari memiliki warning availability.";
        }

        $type = $conflictCount > 0 ? 'warning' : ($warningCount > 0 ? 'warning' : 'success');
        $this->dispatch('alert', type: $type, message: $message);
    }

    /**
     * Clear all assignments in a specific day
     * Requirements: 10.4
     */
    public function clearDay(string $date): void
    {
        if (!isset($this->assignments[$date])) {
            $this->dispatch('alert', type: 'error', message: 'Tanggal tidak valid.');
            return;
        }

        $clearedCount = 0;

        // Clear all sessions for this day
        for ($session = 1; $session <= 3; $session++) {
            if ($this->assignments[$date][$session] !== null) {
                $this->assignments[$date][$session] = null;
                $clearedCount++;
            }
        }

        if ($clearedCount === 0) {
            $this->dispatch('alert', type: 'info', message: 'Tidak ada assignment untuk dihapus pada hari ini.');
            return;
        }

        // Update statistics and detect conflicts
        $this->updateStatistics();
        $this->detectConflicts();
        
        // Save to history for undo/redo
        $this->saveToHistory();

        $dayName = Carbon::parse($date)->locale('id')->isoFormat('dddd, D MMM');
        $this->dispatch('alert', type: 'success', message: "{$clearedCount} assignment berhasil dihapus dari {$dayName}.");
    }

    /**
     * Clear all assignments
     * Requirements: 10.3
     */
    public function clearAll(): void
    {
        $this->initializeGrid();
        $this->updateStatistics();
        $this->detectConflicts();
        
        // Save to history for undo/redo
        $this->saveToHistory();
        
        $this->dispatch('alert', type: 'success', message: 'Semua assignment berhasil dihapus.');
    }

    /**
     * Get day index
     */
    private function getDayIndex(string $day): int
    {
        $days = ['monday' => 0, 'tuesday' => 1, 'wednesday' => 2, 'thursday' => 3];
        return $days[strtolower($day)] ?? 0;
    }

    /**
     * Save current state to history for undo/redo
     */
    private function saveToHistory(): void
    {
        // Create a snapshot of current assignments
        $snapshot = [
            'assignments' => json_decode(json_encode($this->assignments), true), // Deep clone
            'timestamp' => now()->toDateTimeString(),
        ];
        
        // If we're not at the end of history, remove all future states
        if ($this->historyIndex < count($this->history) - 1) {
            $this->history = array_slice($this->history, 0, $this->historyIndex + 1);
        }
        
        // Add new snapshot to history
        $this->history[] = $snapshot;
        
        // Limit history to max steps
        if (count($this->history) > $this->maxHistorySteps) {
            array_shift($this->history);
        } else {
            $this->historyIndex++;
        }
        
        // Update undo/redo availability
        $this->updateUndoRedoState();
    }
    
    /**
     * Undo last change
     */
    public function undo(): void
    {
        if (!$this->canUndo) {
            $this->dispatch('alert', type: 'warning', message: 'Tidak ada yang bisa di-undo.');
            return;
        }
        
        // Move back in history
        $this->historyIndex--;
        
        // Restore state from history
        $this->restoreFromHistory();
        
        $this->dispatch('alert', type: 'success', message: 'Perubahan berhasil di-undo.');
    }
    
    /**
     * Redo last undone change
     */
    public function redo(): void
    {
        if (!$this->canRedo) {
            $this->dispatch('alert', type: 'warning', message: 'Tidak ada yang bisa di-redo.');
            return;
        }
        
        // Move forward in history
        $this->historyIndex++;
        
        // Restore state from history
        $this->restoreFromHistory();
        
        $this->dispatch('alert', type: 'success', message: 'Perubahan berhasil di-redo.');
    }
    
    /**
     * Restore assignments from history at current index
     */
    private function restoreFromHistory(): void
    {
        if ($this->historyIndex >= 0 && $this->historyIndex < count($this->history)) {
            $snapshot = $this->history[$this->historyIndex];
            $this->assignments = $snapshot['assignments'];
            
            // Update statistics and conflicts
            $this->updateStatistics();
            $this->detectConflicts();
            
            // Update undo/redo availability
            $this->updateUndoRedoState();
        }
    }
    
    /**
     * Update undo/redo availability flags
     */
    private function updateUndoRedoState(): void
    {
        $this->canUndo = $this->historyIndex > 0;
        $this->canRedo = $this->historyIndex < count($this->history) - 1;
    }
    
    /**
     * Clear history (called when schedule is published)
     */
    private function clearHistory(): void
    {
        $this->history = [];
        $this->historyIndex = -1;
        $this->updateUndoRedoState();
    }

    /**
     * Get session time label
     */
    public function getSessionTime(int $session): string
    {
        $times = [
            1 => '08:00 - 12:00',
            2 => '13:00 - 17:00',
            3 => '17:00 - 21:00',
        ];
        return $times[$session] ?? '';
    }

    public function render()
    {
        return view('livewire.schedule.create-schedule');
    }
}
