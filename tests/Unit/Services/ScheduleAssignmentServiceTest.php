<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ScheduleAssignmentService;
use App\Models\ScheduleAssignment;
use App\Models\LeaveAffectedSchedule;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ScheduleAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ScheduleAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ScheduleAssignmentService();
    }

    /** @test */
    public function service_can_be_instantiated()
    {
        $this->assertInstanceOf(ScheduleAssignmentService::class, $this->service);
    }

    /** @test */
    public function service_has_required_methods()
    {
        $this->assertTrue(method_exists($this->service, 'updateStatus'));
        $this->assertTrue(method_exists($this->service, 'markAsExcused'));
        $this->assertTrue(method_exists($this->service, 'revertExcused'));
        $this->assertTrue(method_exists($this->service, 'getAssignmentsForPeriod'));
    }

    /** @test */
    public function valid_statuses_constant_is_defined()
    {
        $this->assertTrue(defined('App\Services\ScheduleAssignmentService::VALID_STATUSES'));
        $this->assertIsArray(ScheduleAssignmentService::VALID_STATUSES);
        $this->assertContains('scheduled', ScheduleAssignmentService::VALID_STATUSES);
        $this->assertContains('completed', ScheduleAssignmentService::VALID_STATUSES);
        $this->assertContains('missed', ScheduleAssignmentService::VALID_STATUSES);
        $this->assertContains('swapped', ScheduleAssignmentService::VALID_STATUSES);
        $this->assertContains('excused', ScheduleAssignmentService::VALID_STATUSES);
    }
}

