<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ScheduleChangeRequestService;
use App\Models\{User, Schedule, ScheduleAssignment, ScheduleChangeRequest, PenaltyType};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

class ScheduleChangeRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    private ScheduleChangeRequestService $service;
    private User $user;
    private Schedule $schedule;
    private ScheduleAssignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new ScheduleChangeRequestService();
        
        // Create test user
        $this->user = User::factory()->create([
            'status' => 'active',
        ]);

        // Create test schedule
        $weekStart = now()->next('Monday');
        $this->schedule = Schedule::create([
            'week_start_date' => $weekStart,
            'week_end_date' => $weekStart->copy()->addDays(3),
            'status' => 'published',
            'generated_by' => $this->user->id,
            'generated_at' => now(),
            'total_slots' => 12,
            'filled_slots' => 1,
            'coverage_rate' => 8.33,
        ]);

        // Create test assignment (3 days from now)
        $assignmentDate = now()->addDays(3);
        $this->assignment = ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->user->id,
            'date' => $assignmentDate,
            'day' => strtolower($assignmentDate->englishDayOfWeek),
            'session' => 1,
            'time_start' => '07:30:00',
            'time_end' => '10:00:00',
            'status' => 'scheduled',
        ]);
    }

    /** @test */
    public function it_requires_reason_for_all_requests()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Alasan wajib diisi');

        $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'cancel',
            reason: '', // Empty reason
        );
    }

    /** @test */
    public function it_validates_assignment_belongs_to_user()
    {
        $otherUser = User::factory()->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Jadwal tidak ditemukan atau bukan milik Anda');

        $this->service->submitRequest(
            userId: $otherUser->id,
            assignmentId: $this->assignment->id,
            changeType: 'cancel',
            reason: 'Valid reason',
        );
    }

    /** @test */
    public function it_validates_change_type()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Tipe perubahan tidak valid');

        $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'invalid_type',
            reason: 'Valid reason',
        );
    }

    /** @test */
    public function it_requires_date_and_session_for_reschedule()
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Tanggal dan sesi tujuan wajib diisi');

        $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'reschedule',
            reason: 'Valid reason',
            // Missing requestedDate and requestedSession
        );
    }

    /** @test */
    public function it_detects_conflicts_for_reschedule()
    {
        // Create another assignment at the target time
        $targetDate = Carbon::now()->addDays(4)->startOfDay();
        $conflictingAssignment = ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->user->id,
            'date' => $targetDate,
            'day' => strtolower($targetDate->englishDayOfWeek),
            'session' => 2,
            'time_start' => '10:20:00',
            'time_end' => '12:50:00',
            'status' => 'scheduled',
        ]);

        // Verify the conflicting assignment was created
        $this->assertDatabaseHas('schedule_assignments', [
            'id' => $conflictingAssignment->id,
            'user_id' => $this->user->id,
            'session' => 2,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Anda sudah memiliki jadwal pada waktu tersebut');

        $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'reschedule',
            reason: 'Valid reason',
            requestedDate: $targetDate,
            requestedSession: 2, // Conflict!
        );
    }

    /** @test */
    public function it_prevents_duplicate_pending_requests()
    {
        // Create first request
        $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'cancel',
            reason: 'First request',
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Sudah ada pengajuan pending untuk jadwal ini');

        // Try to create second request
        $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'cancel',
            reason: 'Second request',
        );
    }

    /** @test */
    public function it_successfully_creates_cancel_request()
    {
        $request = $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'cancel',
            reason: 'I have an emergency',
        );

        $this->assertInstanceOf(ScheduleChangeRequest::class, $request);
        $this->assertEquals('cancel', $request->change_type);
        $this->assertEquals('I have an emergency', $request->reason);
        $this->assertEquals('pending', $request->status);
        $this->assertNull($request->requested_date);
        $this->assertNull($request->requested_session);
    }

    /** @test */
    public function it_successfully_creates_reschedule_request()
    {
        $targetDate = now()->addDays(5);

        $request = $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'reschedule',
            reason: 'I have a class conflict',
            requestedDate: $targetDate,
            requestedSession: 3,
        );

        $this->assertInstanceOf(ScheduleChangeRequest::class, $request);
        $this->assertEquals('reschedule', $request->change_type);
        $this->assertEquals('I have a class conflict', $request->reason);
        $this->assertEquals('pending', $request->status);
        $this->assertEquals($targetDate->toDateString(), $request->requested_date->toDateString());
        $this->assertEquals(3, $request->requested_session);
    }

    /** @test */
    public function it_detects_when_cancellation_requires_admin_approval()
    {
        // Create assignment that starts in 12 hours (within 24-hour window)
        $soonDate = now()->addHours(12);
        $soonAssignment = ScheduleAssignment::create([
            'schedule_id' => $this->schedule->id,
            'user_id' => $this->user->id,
            'date' => $soonDate->toDateString(),
            'day' => strtolower($soonDate->englishDayOfWeek),
            'session' => 1,
            'time_start' => $soonDate->format('H:i:s'),
            'time_end' => $soonDate->copy()->addHours(2)->format('H:i:s'),
            'status' => 'scheduled',
        ]);

        $requiresApproval = $this->service->requiresAdminApproval($soonAssignment);
        $this->assertTrue($requiresApproval);
    }

    /** @test */
    public function it_detects_when_cancellation_does_not_require_admin_approval()
    {
        // Assignment is 3 days away (outside 24-hour window)
        $requiresApproval = $this->service->requiresAdminApproval($this->assignment);
        $this->assertFalse($requiresApproval);
    }

    /** @test */
    public function it_allows_user_to_cancel_their_pending_request()
    {
        $request = $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'cancel',
            reason: 'Test reason',
        );

        $result = $this->service->cancelRequest($request, $this->user->id);

        $this->assertTrue($result);
        $this->assertEquals('cancelled', $request->fresh()->status);
    }

    /** @test */
    public function it_prevents_cancelling_non_pending_requests()
    {
        $request = ScheduleChangeRequest::create([
            'user_id' => $this->user->id,
            'original_assignment_id' => $this->assignment->id,
            'change_type' => 'cancel',
            'reason' => 'Test',
            'status' => 'approved', // Already approved
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Hanya pengajuan pending yang dapat dibatalkan');

        $this->service->cancelRequest($request, $this->user->id);
    }

    /** @test */
    public function it_approves_cancel_request_and_deletes_assignment()
    {
        $admin = User::factory()->create();
        
        $request = $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'cancel',
            reason: 'Emergency',
        );

        $result = $this->service->approveRequest($request, $admin->id, 'Approved');

        $this->assertTrue($result);
        $this->assertEquals('approved', $request->fresh()->status);
        $this->assertEquals('Approved', $request->fresh()->admin_response);
        $this->assertEquals($admin->id, $request->fresh()->admin_responded_by);
        $this->assertNotNull($request->fresh()->admin_responded_at);
        $this->assertNotNull($request->fresh()->completed_at);
        
        // Assignment should be deleted
        $this->assertNull(ScheduleAssignment::find($this->assignment->id));
    }

    /** @test */
    public function it_approves_reschedule_request_and_updates_assignment()
    {
        $admin = User::factory()->create();
        $targetDate = now()->addDays(5);
        
        $request = $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'reschedule',
            reason: 'Class conflict',
            requestedDate: $targetDate,
            requestedSession: 2,
        );

        $result = $this->service->approveRequest($request, $admin->id, 'Approved');

        $this->assertTrue($result);
        $this->assertEquals('approved', $request->fresh()->status);
        
        // Assignment should be updated
        $updatedAssignment = ScheduleAssignment::find($this->assignment->id);
        $this->assertNotNull($updatedAssignment);
        $this->assertEquals($targetDate->toDateString(), $updatedAssignment->date->toDateString());
        $this->assertEquals(2, $updatedAssignment->session);
        $this->assertEquals('10:20:00', $updatedAssignment->time_start);
        $this->assertEquals('12:50:00', $updatedAssignment->time_end);
        $this->assertEquals($admin->id, $updatedAssignment->edited_by);
        $this->assertNotNull($updatedAssignment->edited_at);
    }

    /** @test */
    public function it_rejects_request_with_notes()
    {
        $admin = User::factory()->create();
        
        $request = $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'cancel',
            reason: 'Emergency',
        );

        $result = $this->service->rejectRequest($request, $admin->id, 'Not a valid reason');

        $this->assertTrue($result);
        $this->assertEquals('rejected', $request->fresh()->status);
        $this->assertEquals('Not a valid reason', $request->fresh()->admin_response);
        $this->assertEquals($admin->id, $request->fresh()->admin_responded_by);
        $this->assertNotNull($request->fresh()->admin_responded_at);
        
        // Assignment should remain unchanged
        $this->assertNotNull(ScheduleAssignment::find($this->assignment->id));
    }

    /** @test */
    public function it_requires_notes_when_rejecting()
    {
        $admin = User::factory()->create();
        
        $request = $this->service->submitRequest(
            userId: $this->user->id,
            assignmentId: $this->assignment->id,
            changeType: 'cancel',
            reason: 'Emergency',
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Alasan penolakan wajib diisi');

        $this->service->rejectRequest($request, $admin->id, ''); // Empty notes
    }
}

