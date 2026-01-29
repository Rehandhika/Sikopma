<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\ScheduleAssignment;
use App\Services\PenaltyService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckMissedSchedules extends Command
{
    protected $signature = 'schedule:check-missed';

    protected $description = 'Check for missed schedules and create penalties';

    public function handle()
    {
        $penaltyService = new PenaltyService;

        // Get yesterday's scheduled assignments
        $yesterday = Carbon::yesterday()->toDateString();

        $scheduledAssignments = ScheduleAssignment::where('date', $yesterday)
            ->where('status', 'scheduled')
            ->get();

        $missedCount = 0;

        foreach ($scheduledAssignments as $assignment) {
            // Check if there's attendance record
            $attendance = Attendance::where('user_id', $assignment->user_id)
                ->where('date', $yesterday)
                ->where('schedule_assignment_id', $assignment->id)
                ->first();

            if (! $attendance || ! $attendance->check_in) {
                // Mark as missed
                $assignment->update(['status' => 'missed']);

                // Create penalty
                $penaltyService->createPenalty(
                    $assignment->user,
                    'MISSED_SCHEDULE',
                    "Tidak hadir pada jadwal {$assignment->day_label}, {$assignment->date->format('d M Y')} - Sesi {$assignment->session}",
                    'schedule_assignment',
                    $assignment->id,
                    Carbon::parse($yesterday)
                );

                $missedCount++;
            } else {
                // Mark as completed
                $assignment->update(['status' => 'completed']);
            }
        }

        $this->info("Checked {$scheduledAssignments->count()} assignments, found {$missedCount} missed schedules");
    }
}
