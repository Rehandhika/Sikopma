<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduleAssignment;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Services\PenaltyService;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAbsencesJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:process-absences {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process absences for scheduled assignments without attendance and without approved leave';

    protected PenaltyService $penaltyService;
    protected AttendanceService $attendanceService;

    /**
     * Create a new command instance.
     */
    public function __construct(PenaltyService $penaltyService, AttendanceService $attendanceService)
    {
        parent::__construct();
        $this->penaltyService = $penaltyService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Get the date to process (default to yesterday)
        $dateString = $this->argument('date') ?? Carbon::yesterday()->toDateString();
        $date = Carbon::parse($dateString);

        $this->info("Processing absences for date: {$date->format('Y-m-d')}");

        // Query scheduled assignments without attendance and without approved leave
        // Skip assignments with status 'excused' or 'swapped'
        $scheduledAssignments = ScheduleAssignment::where('date', $date)
            ->where('status', 'scheduled')
            ->whereNotIn('status', ['excused', 'swapped'])
            ->get();

        $this->info("Found {$scheduledAssignments->count()} scheduled assignments to check");

        $processedCount = 0;
        $skippedCount = 0;

        foreach ($scheduledAssignments as $assignment) {
            // Check if there's already an attendance record
            $hasAttendance = Attendance::where('user_id', $assignment->user_id)
                ->where('schedule_assignment_id', $assignment->id)
                ->where('date', $date)
                ->exists();

            if ($hasAttendance) {
                $this->line("  Skipping assignment #{$assignment->id} - already has attendance");
                $skippedCount++;
                continue;
            }

            // Check if user has approved leave for this date
            $hasApprovedLeave = $this->attendanceService->hasApprovedLeave($assignment->user_id, $date);

            if ($hasApprovedLeave) {
                $this->line("  Skipping assignment #{$assignment->id} - user has approved leave");
                $skippedCount++;
                continue;
            }

            // Process absence
            try {
                $this->processAbsence($assignment, $date);
                $processedCount++;
                $this->info("  ✓ Processed absence for user #{$assignment->user_id}, assignment #{$assignment->id}");
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to process absence for assignment #{$assignment->id}: {$e->getMessage()}");
                Log::error('Failed to process absence', [
                    'assignment_id' => $assignment->id,
                    'user_id' => $assignment->user_id,
                    'date' => $date->toDateString(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->newLine();
        $this->info("Absence processing completed:");
        $this->info("  - Processed: {$processedCount}");
        $this->info("  - Skipped: {$skippedCount}");
        $this->info("  - Total checked: {$scheduledAssignments->count()}");

        return Command::SUCCESS;
    }

    /**
     * Process absence for a single assignment
     * Creates attendance with status 'absent', creates ABSENT penalty, and updates assignment status to 'missed'
     *
     * @param ScheduleAssignment $assignment
     * @param Carbon $date
     * @return void
     * @throws \Exception
     */
    protected function processAbsence(ScheduleAssignment $assignment, Carbon $date): void
    {
        DB::transaction(function () use ($assignment, $date) {
            // Create attendance with status 'absent'
            $attendance = Attendance::create([
                'user_id' => $assignment->user_id,
                'schedule_assignment_id' => $assignment->id,
                'date' => $date,
                'status' => 'absent',
                'notes' => 'Tidak hadir - diproses otomatis oleh sistem',
            ]);

            // Create ABSENT penalty (20 points)
            $this->penaltyService->createPenalty(
                $assignment->user_id,
                'ABSENT',
                "Tidak hadir pada {$date->locale('id')->isoFormat('dddd, D MMMM Y')} - Sesi {$assignment->session}",
                'attendance',
                $attendance->id,
                $date
            );

            // Update assignment status to 'missed'
            $assignment->update(['status' => 'missed']);

            Log::info('Absence processed successfully', [
                'assignment_id' => $assignment->id,
                'user_id' => $assignment->user_id,
                'attendance_id' => $attendance->id,
                'date' => $date->toDateString(),
            ]);
        });
    }
}
