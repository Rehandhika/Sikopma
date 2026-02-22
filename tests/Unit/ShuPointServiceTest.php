<?php

namespace Tests\Unit;

use App\Services\ShuPointService;
use PHPUnit\Framework\TestCase;

class ShuPointServiceTest extends TestCase
{
    public function test_compute_earned_points_returns_zero_when_conversion_amount_zero(): void
    {
        $service = new ShuPointService();
        $this->assertSame(0, $service->computeEarnedPoints(10000, 0));
    }

    public function test_compute_earned_points_uses_conversion_amount_and_floors(): void
    {
        $service = new ShuPointService();
        
        // 1 point per 10,000
        $conversion = 10000;
        
        $this->assertSame(1, $service->computeEarnedPoints(10000, $conversion));
        $this->assertSame(2, $service->computeEarnedPoints(25000, $conversion));
        $this->assertSame(0, $service->computeEarnedPoints(9999, $conversion));
    }

    public function test_compute_earned_points_handles_large_amounts(): void
    {
        $service = new ShuPointService();
        $conversion = 5000; // 1 point per 5,000

        $this->assertSame(20, $service->computeEarnedPoints(100000, $conversion));
    }
}
