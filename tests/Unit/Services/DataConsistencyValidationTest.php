<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AttendanceService;
use App\Services\LeaveService;
use App\Services\PenaltyService;
use App\Models\User;
use App\Models\ScheduleAssignment;
use App\Models\Schedule;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\Penalty;
use App\Models\PenaltyType;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataConsistencyValidationTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $attendanceService;
    protected LeaveService $leaveService;
    protected PenaltyService $penaltyService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->attendanceService = app(AttendanceService::class);
        $this->leaveService = app(LeaveService::class);
        $this->penaltyService = app(PenaltyService::class);
    }

    /** @test */
    public function attendance_requires_valid_schedule_assignment()
    {
        $user = User::factory()->create();
        
        // Try to check in without a valid schedule assignment
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        
        $this->attendanceService->checkIn($user->id, 99999);
    }

    /** @test */
    public function attendance_validates_schedule_date_is_today()
    {
        $user = User::factory()->create();
        
        // Create a schedule for yesterday
        $schedule = Schedule::factory()->create([
            'start_date' => Carbon::yesterday(),
            'end_date' => Carbon::yesterday(),
        ]);
        
        $assignment = ScheduleAssignment::factory()->create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'date' => Carbon::yesterday(),
            'session' => 1,
            'status' => 'scheduled',
        ]);
        
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->expectExceptionMessage('Hanya dapat check-in untuk jadwal hari ini');
        
        $this->attendanceService->checkIn($user->id, $assignment->id);
    }

    /** @test */
    public function leave_approval_prevents_conflict_with_existing_attendance()
    {
        $user = User::factory()->create();
        
        // Create a leave request
        $leaveRequest = LeaveRequest::factory()->create([
            'user_id' => $user->id,
            'leave_type' => 'personal',
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(2),
            'status' => 'pending',
        ]);
        
        // Create an attendance record for one of the days
        $schedule = Schedule::factory()->create([
            'start_date' => Carbon::today(),
            'end_date' => Carbon::today()->addDays(7),
        ]);
        
        $assignment = ScheduleAssignment::factory()->create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'session' => 1,
            'status' => 'scheduled',
        ]);
        
        Attendance::factory()->create([
            'user_id' => $user->id,
            'schedule_assignment_id' => $assignment->id,
            'date' => Carbon::today(),
            'status' => 'present',
            'check_in' => Carbon::now(),
        ]);
        
        $reviewer = User::factory()->create();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot approve leave request. User already has attendance records on:');
        
        $this->leaveService->approve($leaveRequest, $reviewer->id);
    }

    /** @test */
    public function penalty_prevents_duplicate_for_same_reference()
    {
        $user = User::factory()->create();
        
        // Create penalty type
        $penaltyType = PenaltyType::factory()->create([
            'code' => 'LATE_MINOR',
            'name' => 'Terlambat Ringan',
            'points' => 5,
            'is_active' => true,
        ]);
        
        // Create first penalty with reference
        $this->penaltyService->createPenalty(
            $user->id,
            'LATE_MINOR',
            'Test penalty',
            'attendance',
            123
        );
        
        // Try to create duplicate penalty with same reference
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Penalty already exists for this reference');
        
        $this->penaltyService->createPenalty(
            $user->id,
            'LATE_MINOR',
            'Duplicate penalty',
            'attendance',
            123
        );
    }

    /** @test */
    public function penalty_allows_multiple_penalties_without_reference()
    {
        $user = User::factory()->create();
        
        // Create penalty type
        $penaltyType = PenaltyType::factory()->create([
            'code' => 'LATE_MINOR',
            'name' => 'Terlambat Ringan',
            'points' => 5,
            'is_active' => true,
        ]);
        
        // Create first penalty without reference
        $penalty1 = $this->penaltyService->createPenalty(
            $user->id,
            'LATE_MINOR',
            'Manual penalty 1',
            null,
            null
        );
        
        // Create second penalty without reference - should succeed
        $penalty2 = $this->penaltyService->createPenalty(
            $user->id,
            'LATE_MINOR',
            'Manual penalty 2',
            null,
            null
        );
        
        $this->assertNotEquals($penalty1->id, $penalty2->id);
        $this->assertEquals(2, Penalty::where('user_id', $user->id)->count());
    }
}
