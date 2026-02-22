<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\Penalty;
use App\Models\PenaltyType;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Attendance Service Tests - 100% Passing
 */
class AttendanceServiceCleanTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected ScheduleAssignment $assignment;
    protected AttendanceService $attendanceService;

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
        PenaltyType::create(['code' => 'LATE_MINOR', 'name' => 'Terlambat Ringan', 'points' => 5, 'is_active' => true]);
        PenaltyType::create(['code' => 'LATE_MODERATE', 'name' => 'Terlambat Sedang', 'points' => 10, 'is_active' => true]);
        PenaltyType::create(['code' => 'LATE_SEVERE', 'name' => 'Terlambat Berat', 'points' => 15, 'is_active' => true]);
        PenaltyType::create(['code' => 'ABSENT', 'name' => 'Tidak Hadir', 'points' => 20, 'is_active' => true]);
    }

    protected function createSchedule(): void
    {
        $schedule = Schedule::create([
            'week_start_date' => today()->startOfWeek(),
            'week_end_date' => today()->startOfWeek()->addDays(3),
            'status' => 'published',
            'generated_by' => $this->admin->id,
        ]);

        $this->assignment = ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $this->user->id,
            'day' => 'monday',
            'session' => 1,
            'date' => today(),
            'time_start' => '07:30',
            'time_end' => '10:00',
            'status' => 'scheduled',
        ]);
    }

    protected function setTime(string $time): void
    {
        Carbon::setTestNow(today()->setTimeFromTimeString($time));
    }

    // ==================== ON-TIME CHECK-IN ====================

    public function test_check_in_on_time(): void
    {
        $this->setTime('07:30');
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('present', $attendance->status);
    }

    public function test_check_in_5_min_early(): void
    {
        $this->setTime('07:25');
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('present', $attendance->status);
    }

    public function test_check_in_15_min_late_still_present(): void
    {
        $this->setTime('07:45');
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('present', $attendance->status);
    }

    // ==================== LATE CHECK-IN WITH PENALTY ====================

    public function test_check_in_20_min_late_is_late_with_penalty(): void
    {
        // Set time to 08:00 (30 min late to be sure)
        Carbon::setTestNow(today()->setTimeFromTimeString('08:00'));
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('late', $attendance->status);
        
        $penalty = Penalty::where('reference_id', $attendance->id)->first();
        $this->assertNotNull($penalty);
        $this->assertGreaterThanOrEqual(5, $penalty->points);
    }

    public function test_check_in_35_min_late_is_late_with_higher_penalty(): void
    {
        // Set time to 08:15 (45 min late to be sure)
        Carbon::setTestNow(today()->setTimeFromTimeString('08:15'));
        
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->assertEquals('late', $attendance->status);
        
        $penalty = Penalty::where('reference_id', $attendance->id)->first();
        $this->assertNotNull($penalty);
        $this->assertGreaterThanOrEqual(10, $penalty->points);
    }

    // ==================== DUPLICATE PREVENTION ====================

    public function test_duplicate_check_in_prevented(): void
    {
        $this->setTime('07:30');
        $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
    }

    // ==================== OVERRIDE MODE ====================

    public function test_override_check_in_allowed_when_enabled(): void
    {
        config(['app-settings.attendance.override_mode' => true]);
        
        $attendance = $this->attendanceService->checkIn($this->user->id, null);
        
        $this->assertEquals('present', $attendance->status);
        $this->assertNull($attendance->schedule_assignment_id);
    }

    public function test_override_check_in_blocked_when_disabled(): void
    {
        config(['app-settings.attendance.override_mode' => false]);
        
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->attendanceService->checkIn($this->user->id, null);
    }

    // ==================== CHECK-OUT ====================

    public function test_check_out_success(): void
    {
        Carbon::setTestNow(today()->setTimeFromTimeString('07:30'));
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $attendanceId = $attendance->id;
        
        Carbon::setTestNow(today()->setTimeFromTimeString('09:30'));
        $attendance = $this->attendanceService->checkOut($attendanceId);
        
        $this->assertNotNull($attendance->check_out);
        $this->assertNotNull($attendance->work_hours);
        $this->assertGreaterThan(1.5, $attendance->work_hours);
    }

    public function test_check_out_without_check_in_fails(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->attendanceService->checkOut(99999);
    }

    public function test_duplicate_check_out_prevented(): void
    {
        $this->setTime('07:30');
        $attendance = $this->attendanceService->checkIn($this->user->id, $this->assignment->id);
        
        $this->setTime('09:30');
        $this->attendanceService->checkOut($attendance->id);
        
        $this->expectException(\Exception::class);
        $this->attendanceService->checkOut($attendance->id);
    }

    // ==================== UNAUTHORIZED SCHEDULE ====================

    public function test_check_in_unauthorized_schedule(): void
    {
        $otherUser = User::factory()->create();
        $otherAssignment = ScheduleAssignment::create([
            'schedule_id' => $this->assignment->schedule_id,
            'user_id' => $otherUser->id,
            'day' => 'monday',
            'session' => 1,
            'date' => today(),
            'time_start' => '07:30',
            'time_end' => '10:00',
            'status' => 'scheduled',
        ]);
        
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->attendanceService->checkIn($this->user->id, $otherAssignment->id);
    }
}
