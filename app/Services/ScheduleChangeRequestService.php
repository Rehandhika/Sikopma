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
        // Check permission
        if (!auth()->user()->can('ajukan_perubahan_jadwal')) {
            throw ValidationException::withMessages([
                'permission' => 'Anda tidak memiliki izin untuk mengajukan perubahan jadwal.',
            ]);
        }

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

        // Get configuration for minimum notice hours
        $minNoticeHours = $this->getMinNoticeHours($changeType);

        // Validate minimum notice time for both cancel and reschedule
        $assignmentDateTime = Carbon::parse($assignment->date->toDateString().' '.$assignment->time_start);
        $deadline = $assignmentDateTime->copy()->subHours($minNoticeHours);

        if (now()->gt($deadline)) {
            throw ValidationException::withMessages([
                'assignment_id' => "Pengajuan {$changeType} minimal {$minNoticeHours} jam sebelum jadwal dimulai.",
            ]);
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

            // Validate requested date is not in the past
            if ($requestedDate->lt(now()->startOfDay())) {
                throw ValidationException::withMessages([
                    'requested_date' => 'Tanggal tujuan tidak boleh di masa lalu.',
                ]);
            }

            // Check for conflict with existing assignments
            $conflict = ScheduleAssignment::where('user_id', $userId)
                ->whereDate('date', $requestedDate->toDateString())
                ->where('session', $requestedSession)
                ->where('id', '!=', $assignmentId) // Exclude current assignment
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

        // Check monthly limit
        $this->validateMonthlyLimit($userId, $changeType);

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
            'min_notice_hours' => $minNoticeHours,
        ]);

        // Send notification
        $this->notifyUser($request, 'submitted');

        return $request;
    }

    /**
     * Get minimum notice hours based on change type
     */
    private function getMinNoticeHours(string $changeType): int
    {
        return match ($changeType) {
            'reschedule' => config('schedule-change.reschedule.min_notice_hours', 3),
            'cancel' => config('schedule-change.cancel.min_notice_hours', 24),
            default => 24,
        };
    }

    /**
     * Validate monthly limit for change requests
     */
    private function validateMonthlyLimit(int $userId, string $changeType): void
    {
        $maxPerMonth = match ($changeType) {
            'reschedule' => config('schedule-change.reschedule.max_per_month', 0),
            'cancel' => config('schedule-change.cancel.max_per_month', 0),
            default => 0,
        };

        // Skip validation if limit is 0 (unlimited)
        if ($maxPerMonth <= 0) {
            return;
        }

        $thisMonthCount = ScheduleChangeRequest::where('user_id', $userId)
            ->where('change_type', $changeType)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'approved')
            ->count();

        if ($thisMonthCount >= $maxPerMonth) {
            throw ValidationException::withMessages([
                'change_type' => "Batas maksimal {$maxPerMonth} kali {$changeType} per bulan telah tercapai.",
            ]);
        }
    }

    /**
     * Check if a schedule change requires admin approval
     * (All changes require admin approval in current implementation)
     */
    public function requiresAdminApproval(ScheduleAssignment $assignment): bool
    {
        // All changes require admin approval
        return true;
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

        $request->forceFill(['status' => 'cancelled'])->save();

        Log::info('Schedule change request cancelled', [
            'request_id' => $request->id,
            'user_id' => $userId,
        ]);

        return true;
    }

    /**
     * Approve a schedule change request
     *
     * @throws ValidationException
     */
    public function approveRequest(
        ScheduleChangeRequest $request,
        int $reviewerId,
        ?string $notes = null
    ): bool {
        // Check permission
        if (!auth()->user()->can('setujui_perubahan_jadwal')) {
            throw ValidationException::withMessages([
                'permission' => 'Anda tidak memiliki izin untuk menyetujui perubahan jadwal.',
            ]);
        }

        // Validate status
        if ($request->status !== 'pending') {
            throw ValidationException::withMessages([
                'request' => 'Hanya pengajuan pending yang dapat disetujui.',
            ]);
        }

        // Validate assignment still exists
        $assignment = $request->originalAssignment;
        if (! $assignment) {
            throw ValidationException::withMessages([
                'request' => 'Jadwal asli tidak ditemukan. Mungkin sudah dihapus.',
            ]);
        }

        DB::beginTransaction();
        try {
            // Update request status
            $request->forceFill([
                'status' => 'approved',
                'admin_response' => $notes,
                'admin_responded_by' => $reviewerId,
                'admin_responded_at' => now(),
                'completed_at' => now(),
            ])->save();

            // Process the schedule change
            if ($request->change_type === 'cancel') {
                $this->processCancellation($request, $assignment, $reviewerId);
            } else {
                $this->processReschedule($request, $assignment, $reviewerId);
            }

            DB::commit();

            Log::info('Schedule change request approved', [
                'request_id' => $request->id,
                'reviewer_id' => $reviewerId,
                'change_type' => $request->change_type,
            ]);

            // Send notification
            $this->notifyUser($request, 'approved');

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve schedule change request', [
                'request_id' => $request->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Process cancellation
     */
    private function processCancellation(
        ScheduleChangeRequest $request,
        ScheduleAssignment $assignment,
        int $reviewerId
    ): void {
        // Save old values for audit
        $oldValues = $assignment->only(['date', 'session', 'day', 'time_start', 'time_end', 'schedule_id']);

        // Delete the assignment
        $assignment->delete();

        // Create audit log
        \App\Models\AssignmentEditHistory::create([
            'assignment_id' => $assignment->id,
            'schedule_id' => $assignment->schedule_id,
            'edited_by' => $reviewerId,
            'action' => 'deleted',
            'old_values' => $oldValues,
            'new_values' => null,
            'reason' => 'Pengajuan batal jadwal disetujui: '.$request->reason,
        ]);

        Log::info('Schedule assignment cancelled via approved request', [
            'request_id' => $request->id,
            'assignment_id' => $assignment->id,
        ]);
    }

    /**
     * Process reschedule
     */
    private function processReschedule(
        ScheduleChangeRequest $request,
        ScheduleAssignment $assignment,
        int $reviewerId
    ): void {
        // Double-check no conflict at approval time
        $conflict = ScheduleAssignment::where('user_id', $assignment->user_id)
            ->whereDate('date', $request->requested_date->toDateString())
            ->where('session', $request->requested_session)
            ->where('id', '!=', $assignment->id)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'request' => 'Konflik jadwal terdeteksi. User sudah memiliki jadwal di waktu tujuan.',
            ]);
        }

        // Save old values for audit
        $oldValues = $assignment->only(['date', 'session', 'day', 'time_start', 'time_end', 'schedule_id']);

        // Find or create schedule for the target week
        $newSchedule = \App\Models\Schedule::forDate($request->requested_date->toDateString());

        if (! $newSchedule) {
            $weekStart = $request->requested_date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            $newSchedule = \App\Models\Schedule::create([
                'week_start_date' => $weekStart->toDateString(),
                'week_end_date' => $weekStart->copy()->addDays(3)->toDateString(),
                'status' => 'published',
                'total_slots' => 12,
                'created_by' => $reviewerId,
            ]);
        }

        // Create new assignment
        $newAssignment = $assignment->replicate();
        $newAssignment->schedule_id = $newSchedule->id;
        $newAssignment->date = $request->requested_date;
        $newAssignment->day = strtolower($request->requested_date->englishDayOfWeek);
        $newAssignment->session = $request->requested_session;
        $newAssignment->time_start = $this->getSessionTime($request->requested_session, 'start');
        $newAssignment->time_end = $this->getSessionTime($request->requested_session, 'end');
        $newAssignment->edited_by = $reviewerId;
        $newAssignment->edited_at = now();
        $newAssignment->edit_reason = 'Pengajuan perubahan jadwal disetujui: '.$request->reason;
        $newAssignment->save();

        // Delete the old assignment (soft delete)
        $assignment->delete();

        // Create audit log for deletion of old assignment
        \App\Models\AssignmentEditHistory::create([
            'assignment_id' => $assignment->id,
            'schedule_id' => $assignment->schedule_id,
            'edited_by' => $reviewerId,
            'action' => 'deleted',
            'old_values' => $oldValues,
            'new_values' => null,
            'reason' => 'Pengajuan pindah jadwal disetujui (jadwal lama dihapus): '.$request->reason,
        ]);

        // Create audit log for creation of new assignment
        \App\Models\AssignmentEditHistory::create([
            'assignment_id' => $newAssignment->id,
            'schedule_id' => $newSchedule->id,
            'edited_by' => $reviewerId,
            'action' => 'created',
            'old_values' => null,
            'new_values' => $newAssignment->only(['date', 'session', 'day', 'time_start', 'time_end', 'schedule_id']),
            'reason' => 'Pengajuan pindah jadwal disetujui (jadwal baru dibuat): '.$request->reason,
        ]);

        Log::info('Schedule assignment rescheduled via approved request', [
            'request_id' => $request->id,
            'old_assignment_id' => $assignment->id,
            'new_assignment_id' => $newAssignment->id,
            'new_date' => $request->requested_date->toDateString(),
            'new_session' => $request->requested_session,
        ]);
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
        // Check permission
        if (!auth()->user()->can('setujui_perubahan_jadwal')) {
            throw ValidationException::withMessages([
                'permission' => 'Anda tidak memiliki izin untuk menolak perubahan jadwal.',
            ]);
        }

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

        $request->forceFill([
            'status' => 'rejected',
            'admin_response' => $notes,
            'admin_responded_by' => $reviewerId,
            'admin_responded_at' => now(),
        ])->save();

        Log::info('Schedule change request rejected', [
            'request_id' => $request->id,
            'reviewer_id' => $reviewerId,
        ]);

        // Send notification
        $this->notifyUser($request, 'rejected');

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

    /**
     * Send notification to user
     */
    private function notifyUser(ScheduleChangeRequest $request, string $event): void
    {
        try {
            $user = $request->user;
            if (! $user) {
                return;
            }

            match ($event) {
                'submitted' => NotificationService::send(
                    $user,
                    'schedule_change_submitted',
                    'Pengajuan Diterima',
                    'Pengajuan perubahan jadwal Anda sedang diproses oleh admin.',
                    ['request_id' => $request->id],
                    route('schedule.change-requests')
                ),
                'approved' => NotificationService::send(
                    $user,
                    'schedule_change_approved',
                    'Pengajuan Disetujui',
                    'Pengajuan perubahan jadwal Anda telah disetujui.',
                    ['request_id' => $request->id],
                    route('schedule.change-requests')
                ),
                'rejected' => NotificationService::send(
                    $user,
                    'schedule_change_rejected',
                    'Pengajuan Ditolak',
                    "Pengajuan ditolak: {$request->admin_response}",
                    ['request_id' => $request->id],
                    route('schedule.change-requests')
                ),
                default => null,
            };
        } catch (\Exception $e) {
            Log::warning('Failed to send notification', [
                'request_id' => $request->id,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
