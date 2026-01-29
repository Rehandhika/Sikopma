<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;
use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\Attendance;
use App\Models\Penalty;
use App\Models\PenaltyType;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessAbsencesJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed penalty types
        $this->artisan('db:seed', ['--class' => 'PenaltyTypeSeeder']);
    }

    /** @test */
    public function it_processes_absences_for_scheduled_assignments_without_attendance()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a schedule manually
        $schedule = Schedule::create([
            'week_start_date' => Carbon::yesterday()->startOfWeek(),
            'week_end_date' => Carbon::yesterday()->endOfWeek(),
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        // Create a scheduled assignment for yesterday
        $assignment = ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => strtolower(Carbon::yesterday()->format('l')),
            'session' => 1,
            'date' => Carbon::yesterday(),
            'status' => 'scheduled',
        ]);

        // Run the command
        $this->artisan('attendance:process-absences', ['date' => Carbon::yesterday()->toDateString()])
            ->assertExitCode(0);

        // Assert attendance was created with status 'absent'
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'schedule_assignment_id' => $assignment->id,
            'date' => Carbon::yesterday()->toDateString(),
            'status' => 'absent',
        ]);

        // Assert penalty was created
        $penaltyType = PenaltyType::where('code', 'ABSENT')->first();
        $this->assertDatabaseHas('penalties', [
            'user_id' => $user->id,
            'penalty_type_id' => $penaltyType->id,
            'reference_type' => 'attendance',
            'status' => 'active',
        ]);

        // Assert assignment status was updated to 'missed'
        $this->assertDatabaseHas('schedule_assignments', [
            'id' => $assignment->id,
            'status' => 'missed',
        ]);
    }

    /** @test */
    public function it_skips_assignments_with_existing_attendance()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a schedule manually
        $schedule = Schedule::create([
            'week_start_date' => Carbon::yesterday()->startOfWeek(),
            'week_end_date' => Carbon::yesterday()->endOfWeek(),
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        // Create a scheduled assignment for yesterday
        $assignment = ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => strtolower(Carbon::yesterday()->format('l')),
            'session' => 1,
            'date' => Carbon::yesterday(),
            'status' => 'scheduled',
        ]);

        // Create existing attendance
        Attendance::create([
            'user_id' => $user->id,
            'schedule_assignment_id' => $assignment->id,
            'date' => Carbon::yesterday(),
            'check_in' => Carbon::yesterday()->setTime(7, 30),
            'status' => 'present',
        ]);

        $initialAttendanceCount = Attendance::count();
        $initialPenaltyCount = Penalty::count();

        // Run the command
        $this->artisan('attendance:process-absences', ['date' => Carbon::yesterday()->toDateString()])
            ->assertExitCode(0);

        // Assert no new attendance was created
        $this->assertEquals($initialAttendanceCount, Attendance::count());

        // Assert no new penalty was created
        $this->assertEquals($initialPenaltyCount, Penalty::count());
    }

    /** @test */
    public function it_skips_assignments_with_approved_leave()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a schedule manually
        $schedule = Schedule::create([
            'week_start_date' => Carbon::yesterday()->startOfWeek(),
            'week_end_date' => Carbon::yesterday()->endOfWeek(),
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        // Create a scheduled assignment for yesterday
        $assignment = ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => strtolower(Carbon::yesterday()->format('l')),
            'session' => 1,
            'date' => Carbon::yesterday(),
            'status' => 'scheduled',
        ]);

        // Create approved leave request
        LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type' => 'sick',
            'start_date' => Carbon::yesterday(),
            'end_date' => Carbon::yesterday(),
            'total_days' => 1,
            'reason' => 'Sakit',
            'status' => 'approved',
        ]);

        $initialAttendanceCount = Attendance::count();
        $initialPenaltyCount = Penalty::count();

        // Run the command
        $this->artisan('attendance:process-absences', ['date' => Carbon::yesterday()->toDateString()])
            ->assertExitCode(0);

        // Assert no attendance was created
        $this->assertEquals($initialAttendanceCount, Attendance::count());

        // Assert no penalty was created
        $this->assertEquals($initialPenaltyCount, Penalty::count());
    }

    /** @test */
    public function it_skips_excused_and_swapped_assignments()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a schedule manually
        $schedule = Schedule::create([
            'week_start_date' => Carbon::yesterday()->startOfWeek(),
            'week_end_date' => Carbon::yesterday()->endOfWeek(),
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        // Create an excused assignment
        $excusedAssignment = ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => strtolower(Carbon::yesterday()->format('l')),
            'session' => 1,
            'date' => Carbon::yesterday(),
            'status' => 'excused',
        ]);

        // Create a swapped assignment
        $swappedAssignment = ScheduleAssignment::create([
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'day' => strtolower(Carbon::yesterday()->format('l')),
            'session' => 2,
            'date' => Carbon::yesterday(),
            'status' => 'swapped',
        ]);

        $initialAttendanceCount = Attendance::count();
        $initialPenaltyCount = Penalty::count();

        // Run the command
        $this->artisan('attendance:process-absences', ['date' => Carbon::yesterday()->toDateString()])
            ->assertExitCode(0);

        // Assert no attendance was created
        $this->assertEquals($initialAttendanceCount, Attendance::count());

        // Assert no penalty was created
        $this->assertEquals($initialPenaltyCount, Penalty::count());
    }
}
