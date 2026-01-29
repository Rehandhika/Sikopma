<?php

namespace Tests\Unit;

use App\Services\ConflictDetectionService;
use App\Services\ScheduleConfigurationService;
use Mockery;
use PHPUnit\Framework\TestCase;

class ConflictDetectionServiceTest extends TestCase
{
    protected ConflictDetectionService $service;

    protected $configService;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the config service
        $this->configService = Mockery::mock(ScheduleConfigurationService::class);
        $this->service = new ConflictDetectionService($this->configService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_can_get_conflict_severity()
    {
        $severity = $this->service->getConflictSeverity(ConflictDetectionService::TYPE_DUPLICATE_USER_IN_SLOT);
        $this->assertEquals(ConflictDetectionService::SEVERITY_CRITICAL, $severity);

        $severity = $this->service->getConflictSeverity(ConflictDetectionService::TYPE_AVAILABILITY_MISMATCH);
        $this->assertEquals(ConflictDetectionService::SEVERITY_WARNING, $severity);

        $severity = $this->service->getConflictSeverity(ConflictDetectionService::TYPE_CONSECUTIVE_SHIFT);
        $this->assertEquals(ConflictDetectionService::SEVERITY_INFO, $severity);
    }

    public function test_it_can_get_conflict_message()
    {
        $message = $this->service->getConflictMessage(ConflictDetectionService::TYPE_INACTIVE_USER);
        $this->assertNotEmpty($message);
        $this->assertIsString($message);
    }

    public function test_it_can_categorize_conflicts()
    {
        $conflicts = [
            ['type' => 'test1', 'severity' => 'critical', 'message' => 'Critical issue'],
            ['type' => 'test2', 'severity' => 'warning', 'message' => 'Warning issue'],
            ['type' => 'test3', 'severity' => 'info', 'message' => 'Info issue'],
            ['type' => 'test4', 'severity' => 'critical', 'message' => 'Another critical'],
        ];

        $categorized = $this->service->categorizeConflicts($conflicts);

        $this->assertArrayHasKey('critical', $categorized);
        $this->assertArrayHasKey('warning', $categorized);
        $this->assertArrayHasKey('info', $categorized);
        $this->assertArrayHasKey('summary', $categorized);

        $this->assertCount(2, $categorized['critical']);
        $this->assertCount(1, $categorized['warning']);
        $this->assertCount(1, $categorized['info']);

        $this->assertEquals(4, $categorized['summary']['total']);
        $this->assertEquals(2, $categorized['summary']['critical_count']);
        $this->assertTrue($categorized['summary']['has_critical']);
    }

    public function test_it_can_format_conflict_message()
    {
        $conflict = [
            'severity' => 'critical',
            'message' => 'Test conflict',
            'details' => 'This is a test',
        ];

        $formatted = $this->service->formatConflictMessage($conflict);
        $this->assertStringContainsString('❌', $formatted);
        $this->assertStringContainsString('Test conflict', $formatted);
        $this->assertStringContainsString('This is a test', $formatted);
    }

    public function test_it_can_group_conflicts_by_type()
    {
        $conflicts = [
            ['type' => 'type_a', 'severity' => 'critical', 'message' => 'Issue A1'],
            ['type' => 'type_a', 'severity' => 'critical', 'message' => 'Issue A2'],
            ['type' => 'type_b', 'severity' => 'warning', 'message' => 'Issue B1'],
        ];

        $grouped = $this->service->groupConflictsByType($conflicts);

        $this->assertCount(2, $grouped);
        $this->assertEquals(2, $grouped[0]['count']);
        $this->assertEquals(1, $grouped[1]['count']);
    }
}
