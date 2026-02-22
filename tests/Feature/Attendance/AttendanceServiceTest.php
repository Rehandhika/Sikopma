<?php

namespace Tests\Feature\Attendance;

use App\Livewire\Attendance\CheckInOut;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Penalty;
use App\Models\PenaltyType;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Comprehensive Attendance Module Tests
 * 
 * Tests all check-in/check-out scenarios, validation, status determination,
 * penalty integration, and edge cases.
 * 
 * @package Tests\Feature\Attendance
 */
class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Schedule $schedule;
    protected ScheduleAssignment $assignment;
    protected AttendanceService $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->attendanceService = app(AttendanceService::class);
        
        // Create test users
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'active',
        ]);
        
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'status' => 'active',
        ]);
        
        // Seed penalty types
        $this->seedPenaltyTypes();
    }

    protected function seedPenaltyTypes(): void
    {
        PenaltyType::create([
            'code' => 'LATE_MINOR',
            'name' => 'Terlambat Ringan',
            'description' => 'Terlambat 6-15 menit',
            'points' => 5,
            'is_active' => true,
        ]);

        PenaltyType::create([
            'code' => 'LATE_MODERATE',
            'name' => 'Terlambat Sedang',
            'description' => 'Terlambat 16-30 menit',
            'points' => 10,
            'is_active' => true,
        ]);

        PenaltyType::create([
            'code' => 'LATE_SEVERE',
            'name' => 'Terlambat Berat',
            'description' => 'Terlambat lebih dari 30 menit',
            'points' => 15,
            'is_active' => true,
        ]);

        PenaltyType::create([
            'code' => 'ABSENT',
            'name' => 'Tidak Hadir',
            'description' => 'Tidak hadir tanpa izin',
            'points' => 20,
            'is_active' => true,
        ]);
    }

    /**
     * Helper: Create schedule on Monday (valid work day)
     */
    protected function createScheduleAndAssignment(
        User $user,
        string $date = null,
        int $session = 1,
        string $status = 'scheduled'
    ): ScheduleAssignment {
        // Use Monday by default (valid work day)
        if (!$date) {
            $date = today()->next('monday')->format('Y-m-d');
        }
        
        $dateObj = now()->parse($date);
        $dayName = strtolower($dateObj->englishDayOfWeek);
        
        $schedule = Schedule::create([
            'week_start_date' => $dateObj->copy()->startOfWeek(),
            'week_end_date' => $dateObj->copy()->startOfWeek()->addDays(3),
            'status' => 'published',
            'generated_by' => $this->admin->id,
            'total_slots' => 12,
            'filled_slots' => 1,
            'coverage_rate' => 8.33,
        ]);

        $timeStarts = [
            1 => '07:30',
            2 => '10:20',
            3 => '13:30',
        ];
        
        $timeEnds = [
            1 => '10:00',
            2 => '12:50',
            3 => '16:00',
        ];

        return ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => $dayName,
            'session' => $session,
            'date' => $dateObj->format('Y-m-d'),
            'time_start' => $timeStarts[$session] ?? '07:30',
            'time_end' => $timeEnds[$session] ?? '10:00',
            'status' => $status,
        ]);
    }

    // ==========================================
    // 1. CHECK-IN SCENARIOS - NORMAL WITH SCHEDULE
    // ==========================================

    /**
     * CI-001: Check-in on-time (within grace period: 0-15 min late)
     */
    public function test_check_in_on_time_within_grace_period(): void
    {
        $this->assignment = $this->createScheduleAndAssignment($this->user);
        
        // Check-in 10 minutes late (within 15 min grace period)
        $checkInTime = now()->parse($this->assignment->date)->setTimeFromTimeString('07:40');
        
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('present', $attendance->status);
        $this->assertEquals(0, $attendance->work_hours ?? 0);
        $this->assertNotNull($attendance->check_in);
        
        // No penalty for within grace period
        $this->assertDatabaseMissing('penalties', [
            'user_id' => $this->user->id,
            'reference_type' => 'attendance',
            'reference_id' => $attendance->id,
        ]);
    }

    /**
     * CI-002: Check-in exactly at schedule start time
     */
    public function test_check_in_exactly_at_schedule_start(): void
    {
        $this->assignment = $this->createScheduleAndAssignment($this->user);
        
        $checkInTime = now()->parse($this->assignment->date)->setTimeFromTimeString('07:30');
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('present', $attendance->status);
    }

    /**
     * CI-003: Check-in 5 minutes before schedule start
     */
    public function test_check_in_early_within_tolerance(): void
    {
        $this->assignment = $this->createScheduleAndAssignment($this->user);
        
        $checkInTime = now()->parse($this->assignment->date)->setTimeFromTimeString('07:25');
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('present', $attendance->status);
    }

    /**
     * CI-004: Check-in 15 minutes late (exactly at threshold)
     */
    public function test_check_in_exactly_at_grace_threshold(): void
    {
        $this->assignment = $this->createScheduleAndAssignment($this->user);
        
        $checkInTime = now()->parse($this->assignment->date)->setTimeFromTimeString('07:45');
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('present', $attendance->status);
    }

    /**
     * Helper: Create schedule for TODAY (for testing)
     */
    protected function createScheduleAndAssignmentForToday(
        User $user,
        int $session = 1,
        string $status = 'scheduled'
    ): ScheduleAssignment {
        // Use today's date
        $dateObj = today();
        $dayName = strtolower($dateObj->englishDayOfWeek);
        
        // Ensure day is valid (Monday-Thursday)
        if (!in_array($dayName, ['monday', 'tuesday', 'wednesday', 'thursday'])) {
            // Use next Monday if today is not a valid work day
            $dateObj = today()->next('monday');
            $dayName = 'monday';
        }
        
        $schedule = Schedule::create([
            'week_start_date' => $dateObj->copy()->startOfWeek(),
            'week_end_date' => $dateObj->copy()->startOfWeek()->addDays(3),
            'status' => 'published',
            'generated_by' => $this->admin->id,
            'total_slots' => 12,
            'filled_slots' => 1,
            'coverage_rate' => 8.33,
        ]);

        $timeStarts = [
            1 => '07:30',
            2 => '10:20',
            3 => '13:30',
        ];
        
        $timeEnds = [
            1 => '10:00',
            2 => '12:50',
            3 => '16:00',
        ];

        return ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => $dayName,
            'session' => $session,
            'date' => $dateObj->format('Y-m-d'),
            'time_start' => $timeStarts[$session] ?? '07:30',
            'time_end' => $timeEnds[$session] ?? '10:00',
            'status' => $status,
        ]);
    }

    /**
     * CI-005: Check-in 20 minutes late (LATE_MINOR penalty)
     */
    public function test_check_in_20_minutes_late_incurs_minor_penalty(): void
    {
        // Set test now to Monday at 07:30
        $testDate = today()->next('monday')->setTimeFromTimeString('07:30');
        $this->travelTo($testDate);
        
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        // Check in at 07:50 = 20 minutes late
        $checkInTime = today()->setTimeFromTimeString('07:50');
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        // Should be marked as late (20 min > 15 min threshold)
        $this->assertEquals('late', $attendance->status);
        
        // Should have penalty created
        $penalty = Penalty::where('user_id', $this->user->id)
            ->where('reference_id', $attendance->id)
            ->first();
        
        $this->assertNotNull($penalty);
        $this->assertGreaterThanOrEqual(5, $penalty->points);
    }

    /**
     * CI-006: Check-in 35 minutes late (LATE_MODERATE penalty)
     */
    public function test_check_in_35_minutes_late_incurs_moderate_penalty(): void
    {
        $testDate = today()->next('monday')->setTimeFromTimeString('07:30');
        $this->travelTo($testDate);
        
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        // Check in at 08:05 = 35 minutes late
        $checkInTime = today()->setTimeFromTimeString('08:05');
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('late', $attendance->status);
        
        $penalty = Penalty::where('user_id', $this->user->id)
            ->where('reference_id', $attendance->id)
            ->first();
        
        $this->assertNotNull($penalty);
        $this->assertGreaterThanOrEqual(10, $penalty->points);
    }

    /**
     * CI-007: Check-in 45 minutes late (LATE_SEVERE penalty)
     */
    public function test_check_in_45_minutes_late_incurs_severe_penalty(): void
    {
        $testDate = today()->next('monday')->setTimeFromTimeString('07:30');
        $this->travelTo($testDate);
        
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        // Check in at 08:15 = 45 minutes late
        $checkInTime = today()->setTimeFromTimeString('08:15');
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('late', $attendance->status);
        
        $penalty = Penalty::where('user_id', $this->user->id)
            ->where('reference_id', $attendance->id)
            ->first();
        
        $this->assertNotNull($penalty);
        $this->assertGreaterThanOrEqual(15, $penalty->points);
    }

    // ==========================================
    // 2. DUPLICATE CHECK-IN PREVENTION
    // ==========================================

    /**
     * CI-020: Second check-in attempt for same schedule
     */
    public function test_duplicate_check_in_prevented(): void
    {
        $this->assignment = $this->createScheduleAndAssignment($this->user);
        
        // First check-in
        $checkInTime = now()->parse($this->assignment->date)->setTimeFromTimeString('07:30');
        $this->travelTo($checkInTime);
        $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        // Second check-in should fail
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage('Anda sudah check-in untuk jadwal ini');
        
        $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
    }

    /**
     * CI-021: Check-in when attendance record exists with check_in time
     */
    public function test_check_in_with_existing_attendance_prevented(): void
    {
        $this->assignment = $this->createScheduleAndAssignment($this->user);
        
        Attendance::create([
            'user_id' => $this->user->id,
            'schedule_assignment_id' => $this->assignment->id,
            'date' => $this->assignment->date,
            'check_in' => now(),
            'status' => 'present',
        ]);
        
        $this->expectException(\App\Exceptions\BusinessException::class);
        
        $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
    }

    // ==========================================
    // 3. OVERRIDE MODE CHECK-IN (NO SCHEDULE)
    // ==========================================

    /**
     * CI-030: Override check-in when override_mode = true
     */
    public function test_override_check_in_allowed_when_enabled(): void
    {
        config(['app-settings.attendance.override_mode' => true]);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, null);
        
        $this->assertEquals('present', $attendance->status);
        $this->assertNull($attendance->schedule_assignment_id);
    }

    /**
     * CI-031: Override check-in when override_mode = false
     */
    public function test_override_check_in_blocked_when_disabled(): void
    {
        config(['app-settings.attendance.override_mode' => false]);
        
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage('Check-in tanpa jadwal tidak diizinkan');
        
        $this->attendanceService->checkIn($this->user->id, null);
    }

    // ==========================================
    // 4. APPROVED LEAVE INTEGRATION
    // ==========================================

    /**
     * CI-040: Check-in on date with approved leave
     */
    public function test_check_in_with_approved_leave_is_excused(): void
    {
        // Set test now to Monday at 07:30
        $testDate = today()->next('monday')->setTimeFromTimeString('07:30');
        $this->travelTo($testDate);
        
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        LeaveRequest::create([
            'user_id' => $this->user->id,
            'leave_type' => 'sick',
            'start_date' => $this->assignment->date,
            'end_date' => now()->parse($this->assignment->date)->addDays(2),
            'reason' => 'Sick leave',
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);
        
        $checkInTime = today()->setTimeFromTimeString('07:30');
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('excused', $attendance->status);
        
        // No penalty for excused absence
        $this->assertDatabaseMissing('penalties', [
            'user_id' => $this->user->id,
            'reference_type' => 'attendance',
        ]);
    }

    /**
     * CI-042: Check-in on date with pending leave request
     */
    public function test_check_in_with_pending_leave_normal_processing(): void
    {
        $testDate = today()->next('monday')->setTimeFromTimeString('07:30');
        $this->travelTo($testDate);
        
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        LeaveRequest::create([
            'user_id' => $this->user->id,
            'leave_type' => 'sick',
            'start_date' => $this->assignment->date,
            'end_date' => now()->parse($this->assignment->date)->addDays(2),
            'reason' => 'Sick leave',
            'status' => 'pending',
        ]);
        
        // 30 minutes late - should be processed normally
        $checkInTime = today()->setTimeFromTimeString('08:00');
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('late', $attendance->status);
    }

    // ==========================================
    // 5. EARLY CHECK-IN TOLERANCE
    // ==========================================

    /**
     * CI-050: Check-in 30 minutes before schedule (within tolerance)
     */
    public function test_early_check_in_within_tolerance(): void
    {
        $testDate = today()->next('monday')->setTimeFromTimeString('07:30');
        $this->travelTo($testDate);
        
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        // 30 minutes before 07:30 = 07:00
        $checkInTime = today()->setTimeFromTimeString('07:00');
        $this->travelTo($checkInTime);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('present', $attendance->status);
    }

    /**
     * CI-051: Check-in 31 minutes before schedule (outside tolerance)
     */
    public function test_early_check_in_outside_tolerance_blocked(): void
    {
        $testDate = today()->next('monday')->setTimeFromTimeString('07:30');
        $this->travelTo($testDate);
        
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        // 31 minutes before 07:30 = 06:59
        $checkInTime = today()->setTimeFromTimeString('06:59');
        $this->travelTo($checkInTime);
        
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage('Belum waktunya check-in');
        
        $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
    }

    // ==========================================
    // 7. PHOTO VALIDATION (LIVEWIRE)
    // ==========================================

    /**
     * PV-001: Photo validation tests - valid photo
     */
    public function test_check_in_with_valid_photo(): void
    {
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        // Travel to schedule date
        $this->travelTo(today()->setTimeFromTimeString('07:30'));
        
        $photo = UploadedFile::fake()->image('checkin.jpg', 640, 480)->size(1024);
        
        Livewire::actingAs($this->user)
            ->test(CheckInOut::class)
            ->set('checkInPhoto', $photo)
            ->call('checkIn');
        
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'schedule_assignment_id' => $this->assignment->id,
        ]);
        
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->assertNotNull($attendance->check_in_photo);
    }

    /**
     * PV-004: Upload non-image file
     */
    public function test_check_in_with_non_image_file_fails(): void
    {
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        $this->travelTo(today()->setTimeFromTimeString('07:30'));
        
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        
        $component = Livewire::actingAs($this->user)
            ->test(CheckInOut::class)
            ->set('checkInPhoto', $file)
            ->call('checkIn');
        
        $component->assertHasErrors(['checkInPhoto' => 'image']);
    }

    /**
     * PV-005: Upload image > 5MB
     */
    public function test_check_in_with_oversized_photo_fails(): void
    {
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        $this->travelTo(today()->setTimeFromTimeString('07:30'));
        
        // Create 6MB file
        $photo = UploadedFile::fake()->image('checkin.jpg', 640, 480)->size(6144);
        
        $component = Livewire::actingAs($this->user)
            ->test(CheckInOut::class)
            ->set('checkInPhoto', $photo)
            ->call('checkIn');
        
        $component->assertHasErrors(['checkInPhoto' => 'max']);
    }

    /**
     * PV-006: No photo uploaded
     */
    public function test_check_in_without_photo_fails(): void
    {
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        $this->travelTo(today()->setTimeFromTimeString('07:30'));
        
        $component = Livewire::actingAs($this->user)
            ->test(CheckInOut::class)
            ->call('checkIn');
        
        $component->assertHasErrors(['checkInPhoto' => 'required']);
    }

    // ==========================================
    // 8. SCHEDULE VALIDATION
    // ==========================================

    /**
     * CI-010: Check-in for schedule not belonging to user
     */
    public function test_check_in_unauthorized_schedule(): void
    {
        // Create schedule for another user
        $otherUser = User::factory()->create();
        $assignment = $this->createScheduleAndAssignmentForToday($otherUser);
        
        $this->travelTo(today()->setTimeFromTimeString('07:30'));
        
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage('Jadwal tidak sesuai dengan user');
        
        $this->attendanceService->checkIn($this->user->id, $assignment->id);
    }

    /**
     * CI-012: Check-in for schedule that is not published
     */
    public function test_check_in_unpublished_schedule(): void
    {
        $schedule = Schedule::create([
            'week_start_date' => today()->startOfWeek(),
            'week_end_date' => today()->startOfWeek()->addDays(3),
            'status' => 'draft', // Not published
            'generated_by' => $this->admin->id,
        ]);
        
        $assignment = ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $this->user->id,
            'day' => 'monday',
            'session' => 1,
            'date' => today(),
            'time_start' => '07:30',
            'time_end' => '10:00',
            'status' => 'scheduled',
        ]);
        
        $this->travelTo(today()->setTimeFromTimeString('07:30'));
        
        $this->expectException(\App\Exceptions\BusinessException::class);
        
        $this->attendanceService->checkIn($this->user->id, $assignment->id);
    }

    // ==========================================
    // 9. NOTES HANDLING
    // ==========================================

    /**
     * NT-001: Check-in with notes
     */
    public function test_check_in_with_notes(): void
    {
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        $this->travelTo(today()->setTimeFromTimeString('07:30'));
        
        $photo = UploadedFile::fake()->image('checkin.jpg');
        
        Livewire::actingAs($this->user)
            ->test(CheckInOut::class)
            ->set('checkInPhoto', $photo)
            ->set('notes', 'Feeling great today!')
            ->call('checkIn');
        
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->assertEquals('Feeling great today!', $attendance->notes);
    }

    /**
     * NT-004: Check-out with notes update
     */
    public function test_check_out_with_notes_update(): void
    {
        $this->assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        // Check-in first
        $checkInTime = today()->setTimeFromTimeString('07:30');
        $this->travelTo($checkInTime);
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        // Check-out with notes
        $checkOutTime = today()->setTimeFromTimeString('09:30');
        $this->travelTo($checkOutTime);
        
        Livewire::actingAs($this->user)
            ->test(CheckInOut::class)
            ->set('checkOutNotes', 'Finished all tasks')
            ->call('checkOut');
        
        $attendance = Attendance::where('user_id', $this->user->id)->first();
        $this->assertEquals('Finished all tasks', $attendance->notes);
    }

    // ==========================================
    // 10. LIVEWIRE COMPONENT TESTS
    // ==========================================

    /**
     * Test check-in page displays schedule info
     */
    public function test_check_in_page_displays_schedule_info(): void
    {
        $assignment = $this->createScheduleAndAssignmentForToday($this->user);
        
        // Travel to the schedule date
        $this->travelTo(today()->setTimeFromTimeString('07:00'));
        
        Livewire::actingAs($this->user)
            ->test(CheckInOut::class)
            ->assertSee('Sesi 1');
    }

    /**
     * Test check-in page shows no schedule message when user has no schedule today
     */
    public function test_check_in_page_shows_no_schedule_message(): void
    {
        // User has no schedule for the current date
        Livewire::actingAs($this->user)
            ->test(CheckInOut::class)
            ->assertSee('Tidak ada jadwal aktif');
    }
}
