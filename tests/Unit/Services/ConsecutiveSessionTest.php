<?php

namespace Tests\Unit\Services;

use App\Models\ScheduleAssignment;
use App\Services\AttendanceService;
use Carbon\Carbon;
use ReflectionClass;
use Tests\TestCase;

/**
 * Unit tests for consecutive session detection
 * Tests the isConsecutiveSession method in AttendanceService
 */
class ConsecutiveSessionTest extends TestCase
{
    protected AttendanceService $attendanceService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->attendanceService = app(AttendanceService::class);
    }

    /**
     * Helper method to call protected isConsecutiveSession method
     */
    protected function callIsConsecutiveSession(
        ScheduleAssignment $currentSession,
        ScheduleAssignment $targetSession
    ): bool {
        $reflection = new ReflectionClass($this->attendanceService);
        $method = $reflection->getMethod('isConsecutiveSession');
        $method->setAccessible(true);
        
        return $method->invoke($this->attendanceService, $currentSession, $targetSession);
    }

    /**
     * Test: Consecutive sessions on same date, same user, session+1
     */
    public function test_consecutive_sessions_detected(): void
    {
        $date = Carbon::today();
        
        $session1 = new ScheduleAssignment();
        $session1->user_id = 1;
        $session1->date = $date;
        $session1->session = 1;
        $session1->time_start = '07:30';
        $session1->time_end = '10:00';
        
        $session2 = new ScheduleAssignment();
        $session2->user_id = 1;
        $session2->date = $date;
        $session2->session = 2;
        $session2->time_start = '10:20';
        $session2->time_end = '12:50';
        
        $result = $this->callIsConsecutiveSession($session1, $session2);
        
        $this->assertTrue($result, 'Session 1 -> 2 should be consecutive');
    }

    /**
     * Test: Session 2 -> 3 is consecutive
     */
    public function test_session_2_to_3_is_consecutive(): void
    {
        $date = Carbon::today();
        
        $session2 = new ScheduleAssignment();
        $session2->user_id = 1;
        $session2->date = $date;
        $session2->session = 2;
        
        $session3 = new ScheduleAssignment();
        $session3->user_id = 1;
        $session3->date = $date;
        $session3->session = 3;
        
        $result = $this->callIsConsecutiveSession($session2, $session3);
        
        $this->assertTrue($result, 'Session 2 -> 3 should be consecutive');
    }

    /**
     * Test: Different dates are not consecutive
     */
    public function test_different_dates_not_consecutive(): void
    {
        $session1 = new ScheduleAssignment();
        $session1->user_id = 1;
        $session1->date = Carbon::today();
        $session1->session = 1;
        
        $session2 = new ScheduleAssignment();
        $session2->user_id = 1;
        $session2->date = Carbon::today()->addDay();
        $session2->session = 2;
        
        $result = $this->callIsConsecutiveSession($session1, $session2);
        
        $this->assertFalse($result, 'Different dates should not be consecutive');
    }

    /**
     * Test: Different users are not consecutive
     */
    public function test_different_users_not_consecutive(): void
    {
        $date = Carbon::today();
        
        $session1 = new ScheduleAssignment();
        $session1->user_id = 1;
        $session1->date = $date;
        $session1->session = 1;
        
        $session2 = new ScheduleAssignment();
        $session2->user_id = 2;
        $session2->date = $date;
        $session2->session = 2;
        
        $result = $this->callIsConsecutiveSession($session1, $session2);
        
        $this->assertFalse($result, 'Different users should not be consecutive');
    }

    /**
     * Test: Gap in sessions (1 -> 3) is not consecutive
     */
    public function test_session_gap_not_consecutive(): void
    {
        $date = Carbon::today();
        
        $session1 = new ScheduleAssignment();
        $session1->user_id = 1;
        $session1->date = $date;
        $session1->session = 1;
        
        $session3 = new ScheduleAssignment();
        $session3->user_id = 1;
        $session3->date = $date;
        $session3->session = 3;
        
        $result = $this->callIsConsecutiveSession($session1, $session3);
        
        $this->assertFalse($result, 'Session 1 -> 3 (gap) should not be consecutive');
    }

    /**
     * Test: Same session number is not consecutive
     */
    public function test_same_session_not_consecutive(): void
    {
        $date = Carbon::today();
        
        $session1a = new ScheduleAssignment();
        $session1a->user_id = 1;
        $session1a->date = $date;
        $session1a->session = 1;
        
        $session1b = new ScheduleAssignment();
        $session1b->user_id = 1;
        $session1b->date = $date;
        $session1b->session = 1;
        
        $result = $this->callIsConsecutiveSession($session1a, $session1b);
        
        $this->assertFalse($result, 'Same session should not be consecutive');
    }

    /**
     * Test: Backward session (2 -> 1) is not consecutive
     */
    public function test_backward_session_not_consecutive(): void
    {
        $date = Carbon::today();
        
        $session2 = new ScheduleAssignment();
        $session2->user_id = 1;
        $session2->date = $date;
        $session2->session = 2;
        
        $session1 = new ScheduleAssignment();
        $session1->user_id = 1;
        $session1->date = $date;
        $session1->session = 1;
        
        $result = $this->callIsConsecutiveSession($session2, $session1);
        
        $this->assertFalse($result, 'Backward session (2 -> 1) should not be consecutive');
    }
}
