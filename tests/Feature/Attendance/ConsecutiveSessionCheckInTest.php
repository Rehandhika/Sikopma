<?php

namespace Tests\Feature\Attendance;

use App\Exceptions\BusinessException;
use App\Models\Attendance;
use App\Models\PenaltyType;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration tests for consecutive session check-in functionality
 * Tests Requirements 3.2, 3.3, 3.4, 3.5, 3.8
 */
class ConsecutiveSessionCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected AttendanceService $attendanceService;
    protected Schedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set test date to Monday 2026-02-23 at 07:30
        Carbon::setTestNow(Carbon::create(2026, 2, 23, 7, 30, 0));
        
        $this->attendanceService = app(AttendanceService::class);
        
        $this->admin = User::factory()->create(['status' => 'active']);
        $this->user = User::factory()->create(['status' => 'active']);
        
        $this->seedPenaltyTypes();
        $this->createSchedule();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function seedPenaltyTypes(): void
    {
        PenaltyType::create(['code' => 'LATE_A', 'name' => 'Terlambat A', 'points' => 5, 'is_active' => true]);
        PenaltyType::create(['code' => 'LATE_B', 'name' => 'Terlambat B', 'points' => 10, 'is_active' => true]);
        PenaltyType::create(['code' => 'LATE_C', 'name' => 'Terlambat C', 'points' => 15, 'is_active' => true]);
        PenaltyType::create(['code' => 'ABSENT', 'name' => 'Tidak Hadir', 'points' => 20, 'is_active' => true]);
    }

    protected function createSchedule(): void
    {
        $this->schedule = Schedule::create([
            'week_start_date' => today()->startOfWeek(),
            'week_end_date' => today()->startOfWeek()->addDays(3),
            'status' => 'published',
            'generated_by' => $this->admin->id,
        ]);
    }

    protected function createAssignment(int $session, string $timeStart, string $timeEnd): ScheduleAssignment
    {
        return ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->user->id,
            'day' => 'monday',
            'session' => $session,
            'date' => today(),
            'time_start' => $timeStart,
            'time_end' => $timeEnd,
            'status' => 'scheduled',
        ]);
    }

    protected function setTime(string $time): void
    {
        Carbon::setTestNow(today()->setTimeFromTimeString($time));
    }

    /**
     * Test: User can check-in to consecutive session after checking out from previous session
     * Validates: Requirements 3.2, 3.4
     */
    public function test_consecutive_session_check_in_allowed_after_checkout(): void
    {
        $session1 = $this->createAssignment(1, '07:30', '10:00');
        $session2 = $this->createAssignment(2, '10:20', '12:50');
        
        // Check-in to session 1
        $this->setTime('07:30');
        $attendance1 = $this->attendanceService->checkIn($this->user->id, $session1->id);
        $this->assertEquals('present', $attendance1->status);
        
        // Check-out from session 1
        $this->setTime('10:00');
        $this->attendanceService->checkOut($attendance1->id);
        
        // Check-in to session 2 (consecutive)
        $this->setTime('10:20');
        $attendance2 = $this->attendanceService->checkIn($this->user->id, $session2->id);
        
        $this->assertEquals('present', $attendance2->status);
        $this->assertEquals(2, Attendance::where('user_id', $this->user->id)->count());
    }

    /**
     * Test: User can check-in to session 2 while session 1 is still active (consecutive)
     * This tests the new behavior where consecutive sessions don't block each other
     * Validates: Requirements 3.2, 3.8
     */
    public function test_consecutive_session_check_in_allowed_while_previous_active(): void
    {
        $session1 = $this->createAssignment(1, '07:30', '10:00');
        $session2 = $this->createAssignment(2, '10:20', '12:50');
        
        // Check-in to session 1
        $this->setTime('07:30');
        $attendance1 = $this->attendanceService->checkIn($this->user->id, $session1->id);
        $this->assertEquals('present', $attendance1->status);
        
        // Try to check-in to session 2 while session 1 is still active
        // This should be ALLOWED because they are consecutive
        $this->setTime('09:50'); // Still within session 1 time
        $attendance2 = $this->attendanceService->checkIn($this->user->id, $session2->id);
        
        $this->assertEquals('present', $attendance2->status);
        $this->assertEquals(2, Attendance::where('user_id', $this->user->id)->count());
    }

    /**
     * Test: User cannot check-in to non-consecutive session while another session is active
     * Validates: Requirements 3.5
     */
    public function test_non_consecutive_session_blocked_when_active_session_exists(): void
    {
        $session1 = $this->createAssignment(1, '07:30', '10:00');
        $session3 = $this->createAssignment(3, '13:30', '16:00');
        
        // Check-in to session 1
        $this->setTime('07:30');
        $attendance1 = $this->attendanceService->checkIn($this->user->id, $session1->id);
        $this->assertEquals('present', $attendance1->status);
        
        // Try to check-in to session 3 (not consecutive - gap exists)
        $this->setTime('09:00');
        
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Anda masih memiliki sesi check-in aktif');
        $this->attendanceService->checkIn($this->user->id, $session3->id);
    }

    /**
     * Test: Session 2 -> 3 consecutive check-in works
     * Validates: Requirements 3.2, 3.6
     */
    public function test_session_2_to_3_consecutive_check_in(): void
    {
        $session2 = $this->createAssignment(2, '10:20', '12:50');
        $session3 = $this->createAssignment(3, '13:30', '16:00');
        
        // Check-in to session 2
        $this->setTime('10:20');
        $attendance2 = $this->attendanceService->checkIn($this->user->id, $session2->id);
        $this->assertEquals('present', $attendance2->status);
        
        // Check-out from session 2
        $this->setTime('12:50');
        $this->attendanceService->checkOut($attendance2->id);
        
        // Check-in to session 3 (consecutive)
        $this->setTime('13:30');
        $attendance3 = $this->attendanceService->checkIn($this->user->id, $session3->id);
        
        $this->assertEquals('present', $attendance3->status);
        $this->assertEquals(2, Attendance::where('user_id', $this->user->id)->count());
    }

    /**
     * Test: Three consecutive sessions (1 -> 2 -> 3) work correctly
     * Validates: Requirements 3.2, 3.6
     */
    public function test_three_consecutive_sessions(): void
    {
        $session1 = $this->createAssignment(1, '07:30', '10:00');
        $session2 = $this->createAssignment(2, '10:20', '12:50');
        $session3 = $this->createAssignment(3, '13:30', '16:00');
        
        // Session 1
        $this->setTime('07:30');
        $attendance1 = $this->attendanceService->checkIn($this->user->id, $session1->id);
        $this->setTime('10:00');
        $this->attendanceService->checkOut($attendance1->id);
        
        // Session 2
        $this->setTime('10:20');
        $attendance2 = $this->attendanceService->checkIn($this->user->id, $session2->id);
        $this->setTime('12:50');
        $this->attendanceService->checkOut($attendance2->id);
        
        // Session 3
        $this->setTime('13:30');
        $attendance3 = $this->attendanceService->checkIn($this->user->id, $session3->id);
        
        $this->assertEquals(3, Attendance::where('user_id', $this->user->id)->count());
        $this->assertEquals('present', $attendance1->status);
        $this->assertEquals('present', $attendance2->status);
        $this->assertEquals('present', $attendance3->status);
    }

    /**
     * Test: User cannot check-in to same session twice
     * Validates: Requirements 3.3
     */
    public function test_duplicate_check_in_to_same_session_prevented(): void
    {
        $session1 = $this->createAssignment(1, '07:30', '10:00');
        
        $this->setTime('07:30');
        $this->attendanceService->checkIn($this->user->id, $session1->id);
        
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Anda sudah check-in untuk jadwal ini');
        $this->attendanceService->checkIn($this->user->id, $session1->id);
    }

    /**
     * Test: Pessimistic locking prevents race conditions
     * Validates: Requirements 3.8, 5.2
     */
    public function test_pessimistic_locking_prevents_concurrent_check_in(): void
    {
        $session1 = $this->createAssignment(1, '07:30', '10:00');
        
        $this->setTime('07:30');
        
        // First check-in should succeed
        $attendance1 = $this->attendanceService->checkIn($this->user->id, $session1->id);
        $this->assertNotNull($attendance1);
        
        // Second check-in should fail due to duplicate check
        $this->expectException(BusinessException::class);
        $this->attendanceService->checkIn($this->user->id, $session1->id);
    }

    /**
     * Test: Override mode still enforces single active session rule
     * Validates: Requirements 3.9
     */
    public function test_override_mode_single_active_session(): void
    {
        config(['app-settings.attendance.override_mode' => true]);
        
        $this->setTime('07:30');
        $attendance1 = $this->attendanceService->checkIn($this->user->id, null);
        $this->assertNull($attendance1->schedule_assignment_id);
        
        // Try to check-in again in override mode
        $this->expectException(BusinessException::class);
        $this->expectExceptionMessage('Anda masih memiliki sesi check-in aktif');
        $this->attendanceService->checkIn($this->user->id, null);
    }

    /**
     * Test: Assignment status updates correctly for consecutive sessions
     * Validates: Requirements 3.2
     */
    public function test_assignment_status_updates_for_consecutive_sessions(): void
    {
        $session1 = $this->createAssignment(1, '07:30', '10:00');
        $session2 = $this->createAssignment(2, '10:20', '12:50');
        
        // Check-in to session 1
        $this->setTime('07:30');
        $this->attendanceService->checkIn($this->user->id, $session1->id);
        $this->assertEquals('in_progress', $session1->fresh()->status);
        
        // Check-out from session 1
        $this->setTime('10:00');
        $attendance1 = Attendance::where('schedule_assignment_id', $session1->id)->first();
        $this->attendanceService->checkOut($attendance1->id);
        $this->assertEquals('completed', $session1->fresh()->status);
        
        // Check-in to session 2
        $this->setTime('10:20');
        $this->attendanceService->checkIn($this->user->id, $session2->id);
        $this->assertEquals('in_progress', $session2->fresh()->status);
    }
}
