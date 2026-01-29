<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoGenerateSchedule extends Command
{
    protected $signature = 'schedule:auto-generate';

    protected $description = 'Automatically generate schedule for next week';

    public function handle()
    {
        $scheduleService = new ScheduleService;

        // Get next Monday
        $nextMonday = Carbon::now()->next(Carbon::MONDAY);
        $nextThursday = $nextMonday->copy()->addDays(3);

        // Check if schedule already exists
        $existingSchedule = Schedule::where('week_start_date', $nextMonday->toDateString())->first();

        if ($existingSchedule && $existingSchedule->status !== 'draft') {
            $this->info("Schedule already exists for week {$nextMonday->format('d M Y')}");

            return;
        }

        // Create or get schedule
        $schedule = $existingSchedule ?? Schedule::create([
            'week_start_date' => $nextMonday->toDateString(),
            'week_end_date' => $nextThursday->toDateString(),
            'status' => 'draft',
        ]);

        // Generate schedule
        $result = $scheduleService->generateSchedule($schedule);

        if ($result['success']) {
            $this->info("Schedule generated successfully for week {$nextMonday->format('d M Y')}");

            // Auto-publish if configured
            if (config('schedule.auto_publish', false)) {
                $scheduleService->publishSchedule($schedule);
                $this->info('Schedule published automatically');
            }
        } else {
            $this->error("Failed to generate schedule: {$result['message']}");
        }
    }
}
