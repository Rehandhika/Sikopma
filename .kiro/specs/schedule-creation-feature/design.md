# Design Document - Schedule Creation Feature

## Overview

Fitur Schedule Creation adalah sistem komprehensif untuk membuat dan mengelola jadwal shift anggota koperasi. Design ini menggunakan pendekatan modular dengan separation of concerns yang jelas, memanfaatkan Livewire untuk interaktivitas real-time, dan mengimplementasikan algoritma auto-assignment yang adil dan efisien.

### Key Design Principles

1. **User-Centric**: Interface intuitif dengan drag-and-drop, visual feedback, dan minimal clicks
2. **Performance**: Lazy loading, caching, dan batch operations untuk handling data besar
3. **Reliability**: Validasi berlapis, conflict detection, dan transaction safety
4. **Flexibility**: Support manual dan auto mode, templates, dan bulk operations
5. **Maintainability**: Clean code, service layer, dan comprehensive testing

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ CreateSchedule│  │ EditSchedule │  │ PreviewSchedule│    │
│  │  (Livewire)  │  │  (Livewire)  │  │   (Livewire)   │    │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                      Service Layer                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ScheduleService│  │AutoAssignment│  │TemplateService│     │
│  │              │  │   Service    │  │              │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                       Data Layer                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Schedule   │  │ScheduleAssign│  │ Availability │      │
│  │    Model     │  │  ment Model  │  │    Model     │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
```

### Component Structure

```
app/Livewire/Schedule/
├── CreateSchedule.php          # Main schedule creation component
├── EditSchedule.php            # Edit existing draft schedule
├── PreviewSchedule.php         # Preview before publish
├── AssignmentCell.php          # Individual cell component (reusable)
└── ScheduleStatistics.php      # Statistics sidebar component

app/Services/
├── ScheduleService.php         # Core schedule business logic
├── AutoAssignmentService.php   # Auto-assignment algorithm
├── TemplateService.php         # Template management
├── ConflictDetectionService.php # Conflict validation
└── ScheduleExportService.php   # Export to PDF/Excel

app/Models/
├── Schedule.php                # Enhanced with new methods
├── ScheduleAssignment.php      # Enhanced with new methods
├── ScheduleTemplate.php        # NEW: Template model
└── AssignmentHistory.php       # NEW: For undo/redo
```

## Components and Interfaces

### 1. CreateSchedule Livewire Component

**Purpose**: Main component untuk membuat jadwal baru

**Properties**:
```php
class CreateSchedule extends Component
{
    // Schedule data
    public $weekStartDate;
    public $weekEndDate;
    public $notes = '';
    public $status = 'draft';
    
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
    
    // Undo/Redo
    public $history = [];
    public $historyIndex = -1;
    
    // Modes
    public $mode = 'manual'; // manual, auto, template
    public $selectedTemplateId = null;
}
```

**Key Methods**:
```php
// Initialization
public function mount(): void
public function initializeGrid(): void

// Assignment operations
public function selectCell(string $date, int $session): void
public function assignUser(int $userId): void
public function removeAssignment(int $assignmentId): void

// Auto-assignment
public function autoAssign(): void
public function previewAutoAssignment(): array

// Template operations
public function loadTemplate(int $templateId): void
public function saveAsTemplate(string $name): void

// Bulk operations
public function assignToAllSessions(string $date, int $userId): void
public function assignToAllDays(int $session, int $userId): void
public function clearAll(): void
public function clearDay(string $date): void

// Undo/Redo
public function undo(): void
public function redo(): void
private function saveToHistory(): void

// Validation
public function validateSchedule(): array
public function checkConflicts(): array

// Save and publish
public function saveDraft(): void
public function publish(): void

// Export
public function exportToPdf(): Response
public function exportToExcel(): Response
```

### 2. ScheduleService

**Purpose**: Core business logic untuk schedule operations

```php
class ScheduleService
{
    public function createSchedule(array $data): Schedule
    {
        // Validate dates
        // Check for duplicates
        // Create schedule with draft status
        // Initialize empty assignments grid
    }
    
    public function addAssignment(Schedule $schedule, array $data): ScheduleAssignment
    {
        // Validate user availability
        // Check conflicts
        // Create assignment
        // Update statistics
    }
    
    public function removeAssignment(ScheduleAssignment $assignment): bool
    {
        // Delete assignment
        // Update statistics
    }
    
    public function publishSchedule(Schedule $schedule): bool
    {
        // Validate completeness
        // Check conflicts
        // Update status to published
        // Send notifications
        // Lock schedule from editing
    }
    
    public function calculateStatistics(Schedule $schedule): array
    {
        // Total assignments
        // Coverage rate
        // Assignments per user
        // Distribution per session
        // Fairness score
    }
    
    public function copyFromPreviousWeek(Schedule $sourceSchedule, Schedule $targetSchedule): void
    {
        // Copy all assignments
        // Adjust dates
        // Validate users still active
        // Mark conflicts for review
    }
}
```

### 3. AutoAssignmentService

**Purpose**: Algoritma untuk auto-assignment yang adil

```php
class AutoAssignmentService
{
    private $fairnessWeight = 0.7;
    private $availabilityWeight = 0.3;
    
    public function generateAssignments(Schedule $schedule, array $options = []): array
    {
        // Get all available users with their availability
        // Calculate optimal distribution
        // Assign using weighted algorithm
        // Return assignments array
    }
    
    private function getUserAvailability(string $weekStart): Collection
    {
        // Get availability data for all users
        // Group by user
        // Calculate availability score
    }
    
    private function calculateOptimalDistribution(int $totalSlots, int $totalUsers): array
    {
        // Calculate base assignments per user
        // Handle remainder slots
        // Return distribution map
    }
    
    private function selectBestUser(array $slot, array $availableUsers, array $currentDistribution): ?User
    {
        // Score each user based on:
        // 1. Availability for this slot
        // 2. Current assignment count (fairness)
        // 3. Preference (if any)
        // Return user with highest score
    }
    
    private function calculateFairnessScore(array $distribution): float
    {
        // Calculate standard deviation
        // Lower deviation = more fair
        // Return score 0-100
    }
}
```

### 4. ConflictDetectionService

**Purpose**: Detect dan resolve conflicts

```php
class ConflictDetectionService
{
    public function detectConflicts(Schedule $schedule): array
    {
        $conflicts = [];
        
        // Check for double assignments (same user, same time)
        $conflicts['double_assignments'] = $this->checkDoubleAssignments($schedule);
        
        // Check availability mismatches
        $conflicts['availability_mismatches'] = $this->checkAvailabilityMismatches($schedule);
        
        // Check inactive users
        $conflicts['inactive_users'] = $this->checkInactiveUsers($schedule);
        
        // Check overloaded users (too many shifts)
        $conflicts['overloaded_users'] = $this->checkOverloadedUsers($schedule);
        
        return $conflicts;
    }
    
    public function resolveConflict(string $conflictType, array $conflictData): bool
    {
        // Auto-resolve if possible
        // Return success status
    }
    
    public function suggestAlternatives(ScheduleAssignment $conflictingAssignment): array
    {
        // Find alternative users for this slot
        // Return ranked list of suggestions
    }
}
```

### 5. TemplateService

**Purpose**: Manage schedule templates

```php
class TemplateService
{
    public function createTemplate(Schedule $schedule, string $name, string $description = ''): ScheduleTemplate
    {
        // Extract pattern from schedule
        // Save as template
        // Return template
    }
    
    public function applyTemplate(ScheduleTemplate $template, Schedule $targetSchedule): void
    {
        // Load template pattern
        // Adjust dates to target schedule
        // Validate users still active
        // Create assignments
    }
    
    public function listTemplates(User $user): Collection
    {
        // Get templates created by user or public templates
        // Order by usage count
    }
    
    public function deleteTemplate(ScheduleTemplate $template): bool
    {
        // Soft delete template
    }
}
```

## Data Models

### Enhanced Schedule Model

```php
class Schedule extends Model
{
    protected $fillable = [
        'week_start_date',
        'week_end_date',
        'status',
        'generated_by',
        'generated_at',
        'published_at',
        'published_by',
        'notes',
        'total_slots',
        'filled_slots',
        'coverage_rate',
    ];
    
    // New methods
    public function getAssignmentGrid(): array
    {
        // Return 4x3 grid of assignments
    }
    
    public function calculateCoverage(): float
    {
        // Calculate and update coverage_rate
    }
    
    public function canEdit(): bool
    {
        // Check if schedule can be edited (draft status)
    }
    
    public function canPublish(): bool
    {
        // Check if schedule is ready to publish
        // - No conflicts
        // - Minimum coverage met
    }
}
```

### New ScheduleTemplate Model

```php
class ScheduleTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'pattern', // JSON: [{day, session, user_id}]
        'is_public',
        'usage_count',
    ];
    
    protected $casts = [
        'pattern' => 'array',
        'is_public' => 'boolean',
    ];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
```

### New AssignmentHistory Model

```php
class AssignmentHistory extends Model
{
    protected $fillable = [
        'schedule_id',
        'action', // create, update, delete
        'assignment_data', // JSON snapshot
        'performed_by',
        'performed_at',
    ];
    
    protected $casts = [
        'assignment_data' => 'array',
        'performed_at' => 'datetime',
    ];
    
    // For undo/redo functionality
    public function revert(): void
    {
        // Revert to this state
    }
}
```

## User Interface Design

### Main Schedule Creation View

```
┌─────────────────────────────────────────────────────────────────┐
│  Buat Jadwal Baru                                    [? Help]   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Periode: [Senin, 18 Nov 2025] - [Kamis, 21 Nov 2025]          │
│  Catatan: [_______________________________________________]      │
│                                                                  │
│  Mode: ○ Manual  ○ Auto  ○ Template                            │
│                                                                  │
│  [↶ Undo] [↷ Redo]  [📋 Copy Previous]  [💾 Save Template]    │
│                                                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│         │  Sesi 1      │  Sesi 2      │  Sesi 3               │
│         │  08:00-12:00 │  13:00-17:00 │  17:00-21:00          │
│  ───────┼──────────────┼──────────────┼──────────────          │
│  Senin  │  [+ Assign]  │  Ahmad R.    │  [+ Assign]           │
│  18 Nov │              │  ✓ Available │                        │
│  ───────┼──────────────┼──────────────┼──────────────          │
│  Selasa │  Budi S.     │  [+ Assign]  │  Citra D.             │
│  19 Nov │  ⚠ Conflict  │              │  ✓ Available          │
│  ───────┼──────────────┼──────────────┼──────────────          │
│  Rabu   │  [+ Assign]  │  Dewi A.     │  [+ Assign]           │
│  20 Nov │              │  ✓ Available │                        │
│  ───────┼──────────────┼──────────────┼──────────────          │
│  Kamis  │  Eko P.      │  [+ Assign]  │  Fitri M.             │
│  21 Nov │  ✓ Available │              │  ✓ Available          │
│                                                                  │
├─────────────────────────────────────────────────────────────────┤
│  Statistik:                                                      │
│  • Total Assignments: 6/12 (50%)                                │
│  • Coverage Rate: 50%                                           │
│  • Conflicts: 1                                                 │
│  • Unassigned Slots: 6                                          │
│                                                                  │
│  [🔍 Preview] [💾 Save Draft] [✓ Publish]                      │
└─────────────────────────────────────────────────────────────────┘
```

### User Selection Modal

```
┌─────────────────────────────────────────────────────────────────┐
│  Pilih Anggota - Selasa, 19 Nov 2025, Sesi 1 (08:00-12:00)     │
├─────────────────────────────────────────────────────────────────┤
│  🔍 [Search anggota...]                                         │
│                                                                  │
│  ✓ Available (3)                                                │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ ✓ Ahmad Rizki        Shifts: 2  Availability: High      │  │
│  │ ✓ Dewi Anggraini     Shifts: 1  Availability: Medium    │  │
│  │ ✓ Fitri Maharani     Shifts: 1  Availability: High      │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ⚠ Not Available (2)                                            │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ ⚠ Budi Santoso       Shifts: 3  Not available this time │  │
│  │ ⚠ Citra Dewi         Shifts: 2  Already assigned        │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ⊗ Inactive (1)                                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ ⊗ Eko Prasetyo       Status: Suspended                   │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
│  [Cancel] [Assign Selected]                                     │
└─────────────────────────────────────────────────────────────────┘
```

### Auto-Assignment Preview

```
┌─────────────────────────────────────────────────────────────────┐
│  Preview Auto-Assignment                                         │
├─────────────────────────────────────────────────────────────────┤
│  Algorithm: Fair Distribution + Availability                     │
│                                                                  │
│  Results:                                                        │
│  • Total Assignments: 12/12 (100%)                              │
│  • Coverage Rate: 100%                                          │
│  • Fairness Score: 95/100                                       │
│  • Conflicts: 0                                                 │
│                                                                  │
│  Distribution per User:                                          │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │ Ahmad Rizki      ████████░░ 3 shifts                     │  │
│  │ Budi Santoso     ████████░░ 3 shifts                     │  │
│  │ Citra Dewi       ██████░░░░ 2 shifts                     │  │
│  │ Dewi Anggraini   ██████░░░░ 2 shifts                     │  │
│  │ Eko Prasetyo     ██████░░░░ 2 shifts                     │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
│  [Cancel] [Apply Auto-Assignment]                               │
└─────────────────────────────────────────────────────────────────┘
```

## Algorithms

### Auto-Assignment Algorithm

```
Algorithm: Fair Distribution with Availability Weighting

Input:
- Schedule (week_start, week_end)
- Available users with availability data
- Constraints (min/max shifts per user)

Output:
- Array of assignments

Steps:
1. Get all slots (4 days × 3 sessions = 12 slots)
2. Get all available users with their availability data
3. Calculate optimal distribution:
   - base_shifts = total_slots / total_users
   - remainder = total_slots % total_users
   - Distribute remainder to users with highest availability

4. For each slot:
   a. Get users available for this slot
   b. Filter out users who already have assignment at this time
   c. Score each user:
      score = (availability_score × 0.3) + (fairness_score × 0.7)
      where:
      - availability_score = user's availability for this slot (0-100)
      - fairness_score = 100 - (current_assignments / target_assignments × 100)
   d. Select user with highest score
   e. Assign user to slot
   f. Update user's assignment count

5. Validate no conflicts
6. Return assignments

Complexity: O(n × m) where n = slots, m = users
```

### Conflict Detection Algorithm

```
Algorithm: Multi-Level Conflict Detection

Input:
- Schedule with assignments

Output:
- Array of conflicts with severity levels

Steps:
1. Check Level 1 - Critical Conflicts:
   - Double assignments (same user, same time)
   - Inactive user assignments
   - Deleted user assignments

2. Check Level 2 - Warning Conflicts:
   - Availability mismatches
   - Overloaded users (> max_shifts)
   - Underloaded users (< min_shifts)

3. Check Level 3 - Info:
   - Unbalanced distribution
   - Low coverage rate
   - Missing preferred users

4. For each conflict:
   - Calculate severity (critical, warning, info)
   - Generate description
   - Suggest resolution

5. Return conflicts grouped by severity

Complexity: O(n) where n = total assignments
```

## Error Handling

### Validation Errors

```php
// Week date validation
if (!$weekStart->isMonday()) {
    throw new ValidationException('Week start must be Monday');
}

if (!$weekEnd->isThursday()) {
    throw new ValidationException('Week end must be Thursday');
}

// Duplicate schedule check
if (Schedule::where('week_start_date', $weekStart)->exists()) {
    throw new ValidationException('Schedule already exists for this week');
}

// Assignment validation
if ($this->hasConflict($userId, $date, $session)) {
    throw new ConflictException('User already has assignment at this time');
}

if (!$this->isUserAvailable($userId, $date, $session)) {
    throw new AvailabilityException('User is not available at this time');
}
```

### Transaction Safety

```php
DB::beginTransaction();
try {
    // Create schedule
    $schedule = Schedule::create($data);
    
    // Create assignments
    foreach ($assignments as $assignment) {
        ScheduleAssignment::create($assignment);
    }
    
    // Update statistics
    $schedule->calculateCoverage();
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

## Performance Optimization

### Lazy Loading

```php
// Load assignments only when needed
public function getAssignmentsProperty()
{
    return $this->schedule->assignments()
        ->with('user:id,name,photo')
        ->get()
        ->groupBy(function($assignment) {
            return $assignment->date . '_' . $assignment->session;
        });
}
```

### Caching

```php
// Cache availability data
public function getUserAvailability(string $weekStart)
{
    return Cache::remember(
        "availability_{$weekStart}",
        now()->addHours(1),
        function() use ($weekStart) {
            return Availability::with('details', 'user')
                ->where('week_start', $weekStart)
                ->get();
        }
    );
}
```

### Batch Operations

```php
// Batch insert assignments
ScheduleAssignment::insert($assignments);

// Batch update statistics
Schedule::whereIn('id', $scheduleIds)
    ->update(['coverage_rate' => DB::raw('(filled_slots / total_slots) * 100')]);
```

## Testing Strategy

### Unit Tests

```php
// ScheduleServiceTest.php
test('can create schedule with valid dates')
test('cannot create duplicate schedule')
test('can add assignment without conflict')
test('detects double assignment conflict')
test('calculates coverage correctly')

// AutoAssignmentServiceTest.php
test('distributes shifts fairly')
test('respects user availability')
test('handles insufficient users gracefully')
test('achieves high coverage rate')

// ConflictDetectionServiceTest.php
test('detects double assignments')
test('detects availability mismatches')
test('detects inactive users')
test('suggests valid alternatives')
```

### Feature Tests

```php
// CreateScheduleTest.php
test('admin can access create schedule page')
test('can create schedule with manual assignments')
test('can use auto-assignment')
test('can save as draft')
test('can publish schedule')
test('sends notifications on publish')

// EditScheduleTest.php
test('can edit draft schedule')
test('cannot edit published schedule')
test('can undo changes')
test('can redo changes')
```

## Security Considerations

### Authorization

```php
// Only admin/ketua can create schedules
Gate::define('create-schedule', function (User $user) {
    return $user->hasRole(['super_admin', 'ketua', 'wakil_ketua']);
});

// Only creator can edit draft
Gate::define('edit-schedule', function (User $user, Schedule $schedule) {
    return $schedule->isDraft() && 
           ($user->id === $schedule->generated_by || $user->hasRole('super_admin'));
});
```

### Input Validation

```php
// Sanitize all inputs
$validated = $request->validate([
    'week_start_date' => 'required|date|after_or_equal:today',
    'week_end_date' => 'required|date|after:week_start_date',
    'notes' => 'nullable|string|max:500',
]);
```

## Conclusion

Design ini memberikan solusi komprehensif untuk pembuatan jadwal yang:
- ✅ User-friendly dengan interface intuitif
- ✅ Flexible dengan support manual, auto, dan template
- ✅ Reliable dengan validasi berlapis dan conflict detection
- ✅ Performant dengan caching dan batch operations
- ✅ Maintainable dengan clean architecture dan service layer
- ✅ Testable dengan comprehensive test coverage

Next step: Implementation tasks dengan prioritas yang jelas.
