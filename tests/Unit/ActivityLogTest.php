<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    #[Test]
    public function activity_log_model_has_correct_fillable_fields()
    {
        $activityLog = new ActivityLog;

        $expectedFillable = [
            'user_id',
            'activity',
        ];

        $this->assertEquals($expectedFillable, $activityLog->getFillable());
    }

    #[Test]
    public function activity_log_model_has_correct_casts()
    {
        $activityLog = new ActivityLog;

        $casts = $activityLog->getCasts();

        $this->assertArrayHasKey('created_at', $casts);
        $this->assertEquals('datetime', $casts['created_at']);
    }

    #[Test]
    public function activity_log_does_not_use_updated_at()
    {
        $this->assertNull(ActivityLog::UPDATED_AT);
    }

    #[Test]
    public function activity_log_model_has_user_relationship()
    {
        $activityLog = new ActivityLog;

        // Check that the user relationship method exists and returns BelongsTo
        $this->assertTrue(method_exists($activityLog, 'user'));
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $activityLog->user()
        );
    }

    #[Test]
    public function activity_log_model_has_recent_scope()
    {
        $activityLog = new ActivityLog;

        $this->assertTrue(method_exists($activityLog, 'scopeRecent'));
    }

    #[Test]
    public function activity_log_model_has_by_user_scope()
    {
        $activityLog = new ActivityLog;

        $this->assertTrue(method_exists($activityLog, 'scopeByUser'));
    }

    #[Test]
    public function activity_log_model_has_search_scope()
    {
        $activityLog = new ActivityLog;

        $this->assertTrue(method_exists($activityLog, 'scopeSearch'));
    }

    #[Test]
    public function activity_log_model_has_date_range_scope()
    {
        $activityLog = new ActivityLog;

        $this->assertTrue(method_exists($activityLog, 'scopeDateRange'));
    }

    #[Test]
    public function activity_log_table_name_is_correct()
    {
        $activityLog = new ActivityLog;

        $this->assertEquals('activity_logs', $activityLog->getTable());
    }

    #[Test]
    public function activity_log_user_relationship_returns_user_model()
    {
        $activityLog = new ActivityLog;

        $relation = $activityLog->user();

        $this->assertEquals(User::class, $relation->getRelated()::class);
    }
}
