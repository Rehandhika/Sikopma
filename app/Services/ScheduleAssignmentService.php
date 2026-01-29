<?php

namespace App\Services;

use App\Models\AssignmentEditHistory;
use App\Models\LeaveAffectedSchedule;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ScheduleAssignmentService
{
    /**
     * Valid assignment statuses
     */
    const VALID_STATUSES = [
        'scheduled',
        'completed',
        'missed',
        'swapped',
        'excused',
    ];

    /**
     * Update assignment status
     *
     * @throws Exception
     */
    public function updateStatus(
        ScheduleAssignment $assignment,
        string $status,
        ?string $reason = null
    ): bool {
        // Validate status
        if (! in_array($status, self::VALID_STATUSES)) {
            throw new Exception("Invalid status: {$status}. Valid statuses are: ".implode(', ', self::VALID_STATUSES));
        }

        // Store previous status for history
        $previousStatus = $assignment->status;

        // Update assignment status
        $assignment->update([
            'status' => $status,
            'notes' => $reason ? ($assignment->notes ? $assignment->notes."\n".$reason : $reason) : $assignment->notes,
        ]);

        // Log the status change in edit history
        AssignmentEditHistory::create([
            'assignment_id' => $assignment->id,
            'edited_by' => auth()->id(),
            'field_changed' => 'status',
            'old_value' => $previousStatus,
            'new_value' => $status,
            'reason' => $reason,
        ]);

        return true;
    }

    /**
     * Bulk update assignments to 'excused' status
     * Used when leave request is approved
     *
     * @return int Number of assignments updated
     */
    public function markAsExcused(
        Collection $assignments,
        int $leaveRequestId
    ): int {
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($assignments as $assignment) {
                // Update status to excused
                $assignment->update(['status' => 'excused']);

                // Create LeaveAffectedSchedule record
                LeaveAffectedSchedule::create([
                    'leave_request_id' => $leaveRequestId,
                    'schedule_assignment_id' => $assignment->id,
                ]);

                $count++;
            }

            DB::commit();

            return $count;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Revert excused assignments back to scheduled
     * Used when leave request is cancelled
     *
     * @return int Number of assignments reverted
     */
    public function revertExcused(int $leaveRequestId): int
    {
        $count = 0;

        DB::beginTransaction();
        try {
            // Get all affected schedules for this leave request
            $affectedSchedules = LeaveAffectedSchedule::where('leave_request_id', $leaveRequestId)->get();

            foreach ($affectedSchedules as $affectedSchedule) {
                // Get the assignment
                $assignment = ScheduleAssignment::find($affectedSchedule->schedule_assignment_id);

                // Only revert if status is still 'excused'
                if ($assignment && $assignment->status === 'excused') {
                    $assignment->update(['status' => 'scheduled']);
                    $count++;
                }
            }

            // Delete the affected schedule records
            LeaveAffectedSchedule::where('leave_request_id', $leaveRequestId)->delete();

            DB::commit();

            return $count;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get assignments for a user within a date range
     */
    public function getAssignmentsForPeriod(
        int $userId,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        return ScheduleAssignment::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('session')
            ->get();
    }
}
