<?php

namespace Tests\Unit\Services;

use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleChangeRequest;
use App\Models\User;
use App\Services\SwapService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SwapServiceTest extends TestCase
{
    use RefreshDatabase;

    private SwapService $service;

    private User $userA;

    private User $userB;

    private Schedule $schedule;

    private ScheduleAssignment $assignmentA;

    private ScheduleAssignment $assignmentB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SwapService;

        // Create test users
        $this->userA = User::factory()->create(['status' => 'active']);
        $this->userB = User::factory()->create(['status' => 'active']);

        // Create test schedule
        $weekStart = now()->next('Monday');
        $this->schedule = Schedule::create([
            'week_start_date' => $weekStart,
            'week_end_date' => $weekStart->copy()->addDays(3),
            'status' => 'published',
            'generated_by' => $this->userA->id,
            'generated_at' => now(),
            'total_slots' => 12,
        ]);

        // Create assignments (3 days from now)
        $dateA = now()->addDays(3);
        $this->assignmentA = ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->userA->id,
            'date' => $dateA,
            'day' => strtolower($dateA->englishDayOfWeek),
            'session' => 1,
            'time_start' => '07:30:00',
            'time_end' => '10:00:00',
            'status' => 'scheduled',
        ]);

        $dateB = now()->addDays(4);
        $this->assignmentB = ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->userB->id,
            'date' => $dateB,
            'day' => strtolower($dateB->englishDayOfWeek),
            'session' => 2,
            'time_start' => '10:20:00',
            'time_end' => '12:50:00',
            'status' => 'scheduled',
        ]);
    }

    /** @test */
    public function it_creates_swap_request_successfully()
    {
        $swapRequest = $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Need to swap due to class conflict'
        );

        $this->assertInstanceOf(ScheduleChangeRequest::class, $swapRequest);
        $this->assertEquals('swap', $swapRequest->change_type);
        $this->assertEquals('pending', $swapRequest->status);
        $this->assertEquals($this->userA->id, $swapRequest->user_id);
        $this->assertEquals($this->userB->id, $swapRequest->target_id);
    }

    /** @test */
    public function it_validates_both_assignments_are_scheduled()
    {
        $this->assignmentA->update(['status' => 'cancelled']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Kedua jadwal harus berstatus "scheduled"');

        $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Test reason'
        );
    }

    /** @test */
    public function it_validates_minimum_notice_time()
    {
        // Create assignment that starts in 12 hours (less than 24)
        $soonDate = now()->addHours(12);
        $soonAssignment = ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->userA->id,
            'date' => $soonDate->toDateString(),
            'day' => strtolower($soonDate->englishDayOfWeek),
            'session' => 1,
            'time_start' => $soonDate->format('H:i:s'),
            'time_end' => $soonDate->copy()->addHours(2)->format('H:i:s'),
            'status' => 'scheduled',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('minimal 24 jam sebelum jadwal dimulai');

        $this->service->createSwapRequest(
            $soonAssignment,
            $this->assignmentB,
            'Test reason'
        );
    }

    /** @test */
    public function it_detects_conflict_when_requester_has_assignment_at_target_time()
    {
        // Create conflicting assignment for userA at the same time as assignmentB
        $conflictingAssignment = ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->userA->id,
            'date' => $this->assignmentB->date,
            'day' => $this->assignmentB->day,
            'session' => $this->assignmentB->session,
            'time_start' => $this->assignmentB->time_start,
            'time_end' => $this->assignmentB->time_end,
            'status' => 'scheduled',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Anda sudah memiliki jadwal lain pada waktu target');

        $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Test reason'
        );
    }

    /** @test */
    public function it_detects_conflict_when_target_has_assignment_at_requester_time()
    {
        // Create conflicting assignment for userB at the same time as assignmentA
        $conflictingAssignment = ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->userB->id,
            'date' => $this->assignmentA->date,
            'day' => $this->assignmentA->day,
            'session' => $this->assignmentA->session,
            'time_start' => $this->assignmentA->time_start,
            'time_end' => $this->assignmentA->time_end,
            'status' => 'scheduled',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Target sudah memiliki jadwal lain pada waktu jadwal Anda');

        $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Test reason'
        );
    }

    /** @test */
    public function it_prevents_duplicate_pending_swap_requests()
    {
        // Create first swap request
        $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'First request'
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Sudah ada permintaan tukar jadwal pending');

        // Try to create second swap request
        $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Second request'
        );
    }

    /** @test */
    public function it_allows_target_to_approve_swap_request()
    {
        $swapRequest = $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Test reason'
        );

        $this->service->targetRespond($swapRequest, true, 'I agree');

        $this->assertEquals('target_approved', $swapRequest->fresh()->status);
        $this->assertEquals('I agree', $swapRequest->fresh()->target_response);
        $this->assertNotNull($swapRequest->fresh()->target_responded_at);
    }

    /** @test */
    public function it_allows_target_to_reject_swap_request()
    {
        $swapRequest = $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Test reason'
        );

        $this->service->targetRespond($swapRequest, false, 'I cannot swap');

        $this->assertEquals('target_rejected', $swapRequest->fresh()->status);
        $this->assertEquals('I cannot swap', $swapRequest->fresh()->target_response);
    }

    /** @test */
    public function it_executes_swap_when_admin_approves()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $swapRequest = $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Test reason'
        );

        // Target approves
        $swapRequest->update(['status' => 'target_approved']);

        // Admin approves
        $this->service->adminRespond($swapRequest, true, 'Approved');

        // Check status
        $this->assertEquals('admin_approved', $swapRequest->fresh()->status);
        $this->assertNotNull($swapRequest->fresh()->completed_at);

        // Check assignments were swapped
        $this->assertEquals($this->userB->id, $this->assignmentA->fresh()->user_id);
        $this->assertEquals($this->userA->id, $this->assignmentB->fresh()->user_id);
    }

    /** @test */
    public function it_does_not_execute_swap_when_admin_rejects()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $swapRequest = $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Test reason'
        );

        // Target approves
        $swapRequest->update(['status' => 'target_approved']);

        // Admin rejects
        $this->service->adminRespond($swapRequest, false, 'Not approved');

        // Check status
        $this->assertEquals('admin_rejected', $swapRequest->fresh()->status);
        $this->assertNull($swapRequest->fresh()->completed_at);

        // Check assignments were NOT swapped
        $this->assertEquals($this->userA->id, $this->assignmentA->fresh()->user_id);
        $this->assertEquals($this->userB->id, $this->assignmentB->fresh()->user_id);
    }

    /** @test */
    public function it_validates_assignments_still_exist_before_swap()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $swapRequest = $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Test reason'
        );

        // Target approves
        $swapRequest->update(['status' => 'target_approved']);

        // Delete one assignment
        $this->assignmentA->delete();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Salah satu jadwal sudah tidak ada');

        // Try to approve
        $this->service->adminRespond($swapRequest, true, 'Approved');
    }

    /** @test */
    public function it_validates_no_new_conflicts_before_swap_execution()
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        $swapRequest = $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Test reason'
        );

        // Target approves
        $swapRequest->update(['status' => 'target_approved']);

        // Create a new conflicting assignment after request was created
        ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->userA->id,
            'date' => $this->assignmentB->date,
            'day' => $this->assignmentB->day,
            'session' => $this->assignmentB->session,
            'time_start' => $this->assignmentB->time_start,
            'time_end' => $this->assignmentB->time_end,
            'status' => 'scheduled',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Anda sudah memiliki jadwal lain pada waktu target');

        // Try to approve - should fail due to new conflict
        $this->service->adminRespond($swapRequest, true, 'Approved');
    }

    /** @test */
    public function it_respects_monthly_limit()
    {
        // Create 2 approved swaps this month (assuming limit is 2)
        for ($i = 0; $i < 2; $i++) {
            $otherUser = User::factory()->create();
            $otherAssignment = ScheduleAssignment::create([
                'schedule_id' => $this->schedule->id,
                'user_id' => $otherUser->id,
                'date' => now()->addDays(5 + $i),
                'day' => 'monday',
                'session' => 3,
                'time_start' => '13:30:00',
                'time_end' => '16:00:00',
                'status' => 'scheduled',
            ]);

            ScheduleChangeRequest::create([
                'user_id' => $this->userA->id,
                'target_id' => $otherUser->id,
                'original_assignment_id' => $this->assignmentA->id,
                'target_assignment_id' => $otherAssignment->id,
                'change_type' => 'swap',
                'reason' => 'Test',
                'status' => 'admin_approved',
                'created_at' => now(),
            ]);
        }

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Batas maksimal');

        // Try to create third swap
        $this->service->createSwapRequest(
            $this->assignmentA,
            $this->assignmentB,
            'Third request'
        );
    }
}
