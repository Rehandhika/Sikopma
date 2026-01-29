<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\LeaveService;
use App\Models\LeaveRequest;
use App\Models\ScheduleAssignment;
use App\Models\LeaveAffectedSchedule;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LeaveServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LeaveService $leaveService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->leaveService = new LeaveService();
    }

    /**
     * @test
     */
    public function it_can_submit_leave_request()
    {
        $user = User::factory()->create();
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);

        $leaveRequest = $this->leaveService->submitRequest(
            userId: $user->id,
            leaveType: 'permission',
            startDate: $startDate,
            endDate: $endDate,
            reason: 'Family event'
        );

        $this->assertInstanceOf(LeaveRequest::class, $leaveRequest);
        $this->assertEquals('pending', $leaveRequest->status);
        $this->assertEquals(3, $leaveRequest->total_days);
    }

    /**
     * @test
     */
    public function it_validates_sick_leave_attachment_requirement()
    {
        $user = User::factory()->create();
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3); // More than 1 day

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Sick leave longer than 1 day requires attachment');

        $this->leaveService->submitRequest(
            userId: $user->id,
            leaveType: 'sick',
            startDate: $startDate,
            endDate: $endDate,
            reason: 'Sick',
            attachmentPath: null
        );
    }

    /**
     * @test
     */
    public function it_allows_sick_leave_without_attachment_for_one_day()
    {
        $user = User::factory()->create();
        $startDate = Carbon::now()->addDays(1);
        $endDate = $startDate->copy(); // Same day = 1 day total

        $leaveRequest = $this->leaveService->submitRequest(
            userId: $user->id,
            leaveType: 'sick',
            startDate: $startDate,
            endDate: $endDate,
            reason: 'Sick',
            attachmentPath: null
        );

        $this->assertInstanceOf(LeaveRequest::class, $leaveRequest);
        $this->assertEquals('pending', $leaveRequest->status);
    }

    /**
     * @test
     */
    public function it_validates_date_range()
    {
        $user = User::factory()->create();
        $startDate = Carbon::now()->addDays(3);
        $endDate = Carbon::now()->addDays(1); // End before start

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('End date must be after or equal to start date');

        $this->leaveService->submitRequest(
            userId: $user->id,
            leaveType: 'permission',
            startDate: $startDate,
            endDate: $endDate,
            reason: 'Test'
        );
    }
}

