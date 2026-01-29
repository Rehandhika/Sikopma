<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PenaltyService;
use App\Services\AttendanceService;
use App\Models\User;
use App\Models\PenaltyType;
use App\Models\Penalty;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PenaltyAttendanceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected PenaltyService $penaltyService;
    protected AttendanceService $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->penaltyService = app(PenaltyService::class);
        $this->attendanceService = app(AttendanceService::class);
        
        // Seed penalty types
        $this->seedPenaltyTypes();
    }

    protected function seedPenaltyTypes(): void
    {
        PenaltyType::create([
            'code' => 'LATE_MINOR',
            'name' => 'Terlambat Ringan',
            'points' => 5,
            'description' => 'Terlambat 6-15 menit',
            'is_active' => true,
        ]);

        PenaltyType::create([
            'code' => 'LATE_MODERATE',
            'name' => 'Terlambat Sedang',
            'points' => 10,
            'description' => 'Terlambat 16-30 menit',
            'is_active' => true,
        ]);

        PenaltyType::create([
            'code' => 'LATE_SEVERE',
            'name' => 'Terlambat Berat',
            'points' => 15,
            'description' => 'Terlambat >30 menit',
            'is_active' => true,
        ]);

        PenaltyType::create([
            'code' => 'ABSENT',
            'name' => 'Tidak Hadir',
            'points' => 20,
            'description' => 'Tidak hadir tanpa izin',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function penalty_service_can_create_penalty_with_reference()
    {
        $user = User::factory()->create();

        $penalty = $this->penaltyService->createPenalty(
            $user->id,
            'LATE_MINOR',
            'Test penalty',
            'attendance',
            123
        );

        $this->assertInstanceOf(Penalty::class, $penalty);
        $this->assertEquals($user->id, $penalty->user_id);
        $this->assertEquals('attendance', $penalty->reference_type);
        $this->assertEquals(123, $penalty->reference_id);
        $this->assertEquals(5, $penalty->points);
        $this->assertEquals('active', $penalty->status);
    }

    /** @test */
    public function penalty_service_calculates_total_points_correctly()
    {
        $user = User::factory()->create();

        // Create multiple penalties
        $this->penaltyService->createPenalty($user->id, 'LATE_MINOR', 'Test 1');
        $this->penaltyService->createPenalty($user->id, 'LATE_MODERATE', 'Test 2');
        $this->penaltyService->createPenalty($user->id, 'LATE_MINOR', 'Test 3');

        $totalPoints = $this->penaltyService->getUserTotalPoints($user->id);

        $this->assertEquals(20, $totalPoints); // 5 + 10 + 5
    }

    /** @test */
    public function penalty_service_excludes_dismissed_penalties_from_total()
    {
        $user = User::factory()->create();

        $penalty1 = $this->penaltyService->createPenalty($user->id, 'LATE_MINOR', 'Test 1');
        $penalty2 = $this->penaltyService->createPenalty($user->id, 'LATE_MODERATE', 'Test 2');

        // Dismiss one penalty
        $penalty1->update(['status' => 'dismissed']);

        $totalPoints = $this->penaltyService->getUserTotalPoints($user->id);

        $this->assertEquals(10, $totalPoints); // Only penalty2 counts
    }

    /** @test */
    public function penalty_service_triggers_warning_at_threshold()
    {
        $user = User::factory()->create();

        // Create penalties totaling 20 points (warning threshold)
        $this->penaltyService->createPenalty($user->id, 'LATE_MODERATE', 'Test 1'); // 10
        $this->penaltyService->createPenalty($user->id, 'LATE_MODERATE', 'Test 2'); // 10

        $result = $this->penaltyService->checkThresholds($user->id);

        $this->assertEquals('warning', $result);
    }

    /** @test */
    public function penalty_service_triggers_critical_at_threshold()
    {
        $user = User::factory()->create();

        // Create penalties totaling 50 points (critical threshold)
        $this->penaltyService->createPenalty($user->id, 'ABSENT', 'Test 1'); // 20
        $this->penaltyService->createPenalty($user->id, 'ABSENT', 'Test 2'); // 20
        $this->penaltyService->createPenalty($user->id, 'LATE_MODERATE', 'Test 3'); // 10

        $result = $this->penaltyService->checkThresholds($user->id);

        $this->assertEquals('critical', $result);
    }

    /** @test */
    public function attendance_service_determines_status_correctly_for_on_time()
    {
        $user = User::factory()->create();
        $schedule = Schedule::factory()->create(['date' => today()]);
        $assignment = ScheduleAssignment::factory()->create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'date' => today(),
            'session' => 1, // 07:30
            'status' => 'scheduled',
        ]);

        $scheduleStart = today()->setTimeFromTimeString('07:30');
        $checkInTime = $scheduleStart->copy()->addMinutes(3); // 3 minutes late (within grace period)

        $result = $this->attendanceService->determineStatus($checkInTime, $assignment);

        $this->assertEquals('present', $result['status']);
        $this->assertEquals(0, $result['late_minutes']);
    }

    /** @test */
    public function attendance_service_determines_status_correctly_for_late()
    {
        $user = User::factory()->create();
        $schedule = Schedule::factory()->create(['date' => today()]);
        $assignment = ScheduleAssignment::factory()->create([
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'date' => today(),
            'session' => 1, // 07:30
            'status' => 'scheduled',
        ]);

        $scheduleStart = today()->setTimeFromTimeString('07:30');
        $checkInTime = $scheduleStart->copy()->addMinutes(10); // 10 minutes late

        $result = $this->attendanceService->determineStatus($checkInTime, $assignment);

        $this->assertEquals('late', $result['status']);
        $this->assertEquals(10, $result['late_minutes']);
    }

    /** @test */
    public function attendance_service_checks_approved_leave_correctly()
    {
        $user = User::factory()->create();
        
        LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'start_date' => today(),
            'end_date' => today()->addDays(2),
            'status' => 'approved',
        ]);

        $hasLeave = $this->attendanceService->hasApprovedLeave($user->id, today());

        $this->assertTrue($hasLeave);
    }

    /** @test */
    public function attendance_service_returns_false_for_no_approved_leave()
    {
        $user = User::factory()->create();

        $hasLeave = $this->attendanceService->hasApprovedLeave($user->id, today());

        $this->assertFalse($hasLeave);
    }

    /** @test */
    public function penalty_appeal_workflow_works_correctly()
    {
        $user = User::factory()->create();
        $reviewer = User::factory()->create();

        $penalty = $this->penaltyService->createPenalty($user->id, 'LATE_MINOR', 'Test penalty');

        // Submit appeal
        $this->penaltyService->submitAppeal($penalty, 'I had a valid reason');

        $penalty->refresh();
        $this->assertEquals('appealed', $penalty->status);
        $this->assertEquals('pending', $penalty->appeal_status);

        // Approve appeal
        $this->penaltyService->reviewAppeal($penalty, true, 'Approved', $reviewer->id);

        $penalty->refresh();
        $this->assertEquals('dismissed', $penalty->status);
        $this->assertEquals('approved', $penalty->appeal_status);
        $this->assertEquals($reviewer->id, $penalty->reviewed_by);
    }

    /** @test */
    public function penalty_appeal_rejection_keeps_penalty_active()
    {
        $user = User::factory()->create();
        $reviewer = User::factory()->create();

        $penalty = $this->penaltyService->createPenalty($user->id, 'LATE_MINOR', 'Test penalty');

        // Submit appeal
        $this->penaltyService->submitAppeal($penalty, 'I had a valid reason');

        // Reject appeal
        $this->penaltyService->reviewAppeal($penalty, false, 'Rejected', $reviewer->id);

        $penalty->refresh();
        $this->assertEquals('active', $penalty->status);
        $this->assertEquals('rejected', $penalty->appeal_status);
    }
}
