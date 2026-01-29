<?php

namespace App\Services;

use App\Models\ScheduleAssignment;
use App\Models\ScheduleChangeRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ScheduleChangeRequestService
{
    /**
     * Submit a schedule change request
     *
     * @param  string  $changeType  'reschedule' or 'cancel'
     * @param  string  $reason  Required for all requests
     * @param  Carbon|null  $requestedDate  Required for reschedule
     * @param  int|null  $requestedSession  Required for reschedule
     *
     * @throws ValidationException
     */
    public function submitRequest(
        int $userId,
        int $assignmentId,
        string $changeType,
        string $reason,
        ?Carbon $requestedDate = null,
        ?int $requestedSession = null
    ): ScheduleChangeRequest {
        // Validate reason is provided (Requirement 8.1)
        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan wajib diisi untuk semua pengajuan perubahan jadwal.',
            ]);
        }

        // Validate assignment exists and belongs to user
        $assignment = ScheduleAssignment::where('id', $assignmentId)
            ->where('user_id', $userId)
            ->first();

        if (! $assignment) {
            throw ValidationException::withMessages([
                'assignment_id' => 'Jadwal tidak ditemukan atau bukan milik Anda.',
            ]);
        }

        // Validate change type
        if (! in_array($changeType, ['reschedule', 'cancel'])) {
            throw ValidationException::withMessages([
                'change_type' => 'Tipe perubahan tidak valid.',
            ]);
        }

        // Validate 24-hour notice for cancellations (Requirements 8.2, 8.3)
        if ($changeType === 'cancel') {
            $requiresAdminApproval = $this->requiresAdminApproval($assignment);

            if ($requiresAdminApproval) {
                // Log that this cancellation is within 24 hours and requires admin approval
                Log::info('Schedule cancellation within 24 hours - requires admin approval', [
                    'user_id' => $userId,
                    'assignment_id' => $assignmentId,
                    'assignment_date' => $assignment->date->toDateString(),
                    'assignment_time' => $assignment->time_start,
                ]);
            }
        }

        // Validate reschedule parameters
        if ($changeType === 'reschedule') {
            if (! $requestedDate || ! $requestedSession) {
                throw ValidationException::withMessages([
                    'requested_date' => 'Tanggal dan sesi tujuan wajib diisi untuk reschedule.',
                ]);
            }

            if (! in_array($requestedSession, [1, 2, 3])) {
                throw ValidationException::withMessages([
                    'requested_session' => 'Sesi tidak valid.',
                ]);
            }

            // Check for conflict with existing assignments
            $conflict = ScheduleAssignment::where('user_id', $userId)
                ->whereDate('date', $requestedDate->toDateString())
                ->where('session', $requestedSession)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'requested_date' => 'Anda sudah memiliki jadwal pada waktu tersebut.',
                ]);
            }
        }

        // Check for existing pending request
        $existingRequest = ScheduleChangeRequest::where('user_id', $userId)
            ->where('original_assignment_id', $assignmentId)
            ->where('status', 'pending')
            ->exists();

        if ($existingRequest) {
            throw ValidationException::withMessages([
                'assignment_id' => 'Sudah ada pengajuan pending untuk jadwal ini.',
            ]);
        }

        // Create the request
        $request = ScheduleChangeRequest::create([
            'user_id' => $userId,
            'original_assignment_id' => $assignmentId,
            'change_type' => $changeType,
            'requested_date' => $changeType === 'reschedule' ? $requestedDate : null,
            'requested_session' => $changeType === 'reschedule' ? $requestedSession : null,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        Log::info('Schedule change request submitted', [
            'request_id' => $request->id,
            'user_id' => $userId,
            'change_type' => $changeType,
        ]);

        return $request;
    }

    /**
     * Check if a schedule change requires admin approval
     * (Cancellations within 24 hours require admin approval)
     */
    public function requiresAdminApproval(ScheduleAssignment $assignment): bool
    {
        // Calculate deadline: 24 hours before the assignment start time
        $assignmentDateTime = Carbon::parse($assignment->date->toDateString().' '.$assignment->time_start);
        $deadline = $assignmentDateTime->copy()->subHours(24);

        // If current time is past the deadline, admin approval is required
        return now()->gt($deadline);
    }

    /**
     * Cancel a pending request
     *
     * @throws ValidationException
     */
    public function cancelRequest(ScheduleChangeRequest $request, int $userId): bool
    {
        // Validate ownership
        if ($request->user_id !== $userId) {
            throw ValidationException::withMessages([
                'request' => 'Anda tidak memiliki akses untuk membatalkan pengajuan ini.',
            ]);
        }

        // Validate status
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'request' => 'Hanya pengajuan pending yang dapat dibatalkan.',
            ]);
        }

        $request->update(['status' => 'cancelled']);

        Log::info('Schedule change request cancelled', [
            'request_id' => $request->id,
            'user_id' => $userId,
        ]);

        return true;
    }

    /**
     * Approve a schedule change request
     * (Requirement 8.4: Update assignment status on approval)
     *
     * @throws ValidationException
     */
    public function approveRequest(
        ScheduleChangeRequest $request,
        int $reviewerId,
        ?string $notes = null
    ): bool {
        // Validate status
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'request' => 'Hanya pengajuan pending yang dapat disetujui.',
            ]);
        }

        DB::beginTransaction();
        try {
            // Update request status
            $request->update([
                'status' => 'approved',
                'admin_response' => $notes,
                'admin_responded_by' => $reviewerId,
                'admin_responded_at' => now(),
                'completed_at' => now(),
            ]);

            // Process the schedule change (Requirement 8.4)
            $assignment = $request->originalAssignment;

            if ($request->change_type === 'cancel') {
                // Cancel: Delete the assignment
                $assignment->delete();

                Log::info('Schedule assignment cancelled via approved request', [
                    'request_id' => $request->id,
                    'assignment_id' => $assignment->id,
                ]);
            } else {
                // Reschedule: Update the assignment
                $assignment->update([
                    'date' => $request->requested_date,
                    'day' => strtolower(Carbon::parse($request->requested_date)->englishDayOfWeek),
                    'session' => $request->requested_session,
                    'time_start' => $this->getSessionTime($request->requested_session, 'start'),
                    'time_end' => $this->getSessionTime($request->requested_session, 'end'),
                    'edited_by' => $reviewerId,
                    'edited_at' => now(),
                    'edit_reason' => 'Pengajuan perubahan jadwal disetujui',
                ]);

                Log::info('Schedule assignment rescheduled via approved request', [
                    'request_id' => $request->id,
                    'assignment_id' => $assignment->id,
                    'new_date' => $request->requested_date,
                    'new_session' => $request->requested_session,
                ]);
            }

            DB::commit();

            Log::info('Schedule change request approved', [
                'request_id' => $request->id,
                'reviewer_id' => $reviewerId,
                'change_type' => $request->change_type,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve schedule change request', [
                'request_id' => $request->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject a schedule change request
     *
     * @throws ValidationException
     */
    public function rejectRequest(
        ScheduleChangeRequest $request,
        int $reviewerId,
        string $notes
    ): bool {
        // Validate status
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'request' => 'Hanya pengajuan pending yang dapat ditolak.',
            ]);
        }

        // Validate notes are provided
        if (empty(trim($notes))) {
            throw ValidationException::withMessages([
                'notes' => 'Alasan penolakan wajib diisi.',
            ]);
        }

        $request->update([
            'status' => 'rejected',
            'admin_response' => $notes,
            'admin_responded_by' => $reviewerId,
            'admin_responded_at' => now(),
        ]);

        Log::info('Schedule change request rejected', [
            'request_id' => $request->id,
            'reviewer_id' => $reviewerId,
        ]);

        return true;
    }

    /**
     * Get session time
     *
     * @param  string  $type  'start' or 'end'
     */
    private function getSessionTime(int $session, string $type): string
    {
        $times = [
            1 => ['start' => '07:30:00', 'end' => '10:00:00'],
            2 => ['start' => '10:20:00', 'end' => '12:50:00'],
            3 => ['start' => '13:30:00', 'end' => '16:00:00'],
        ];

        return $times[$session][$type] ?? '00:00:00';
    }
}
