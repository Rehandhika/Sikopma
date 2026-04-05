<?php

namespace App\Services;

use App\Models\ScheduleAssignment;
use App\Models\SwapRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SwapService
{
    /**
     * Create swap request
     */
    public function createSwapRequest(
        ScheduleAssignment $requesterAssignment,
        ScheduleAssignment $targetAssignment,
        string $reason
    ): SwapRequest {
        // Check permission
        if (!auth()->user()->can('ajukan_tukar_jadwal')) {
            throw ValidationException::withMessages([
                'permission' => 'Anda tidak memiliki izin untuk mengajukan tukar jadwal.',
            ]);
        }

        // Validate
        $this->validateSwapRequest($requesterAssignment, $targetAssignment);

        // Validate no conflicts after swap
        if (config('schedule-change.swap.validate_conflicts', true)) {
            $this->validateNoConflictAfterSwap($requesterAssignment, $targetAssignment);
        }

        $swapRequest = SwapRequest::create([
            'requester_id' => $requesterAssignment->user_id,
            'target_id' => $targetAssignment->user_id,
            'requester_assignment_id' => $requesterAssignment->id,
            'target_assignment_id' => $targetAssignment->id,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        // Notify target user
        try {
            NotificationService::send(
                $targetAssignment->user,
                'swap_request_received',
                'Permintaan Tukar Jadwal',
                "{$requesterAssignment->user->name} ingin menukar jadwal dengan Anda. {$requesterAssignment->day_label} {$requesterAssignment->date->format('d M')} Sesi {$requesterAssignment->session} ↔ {$targetAssignment->day_label} {$targetAssignment->date->format('d M')} Sesi {$targetAssignment->session}",
                ['swap_request_id' => $swapRequest->id],
                route('admin.swap.my-requests')
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send swap request notification', [
                'swap_request_id' => $swapRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Swap request created', [
            'swap_request_id' => $swapRequest->id,
            'requester_id' => $requesterAssignment->user_id,
            'target_id' => $targetAssignment->user_id,
        ]);

        return $swapRequest;
    }

    /**
     * Validate swap request
     */
    private function validateSwapRequest(
        ScheduleAssignment $requesterAssignment,
        ScheduleAssignment $targetAssignment
    ): void {
        // Check both are scheduled
        if (! $requesterAssignment->isScheduled() || ! $targetAssignment->isScheduled()) {
            throw ValidationException::withMessages([
                'assignment' => 'Kedua jadwal harus berstatus "scheduled" (terjadwal).',
            ]);
        }

        // Check minimum notice time
        $minNoticeHours = config('schedule-change.swap.min_notice_hours', 24);
        $requesterDeadline = $requesterAssignment->date
            ->copy()
            ->setTimeFromTimeString($requesterAssignment->time_start)
            ->subHours($minNoticeHours);

        if (now()->gt($requesterDeadline)) {
            throw ValidationException::withMessages([
                'assignment' => "Permintaan tukar jadwal harus diajukan minimal {$minNoticeHours} jam sebelum jadwal dimulai.",
            ]);
        }

        // Check target assignment deadline too
        $targetDeadline = $targetAssignment->date
            ->copy()
            ->setTimeFromTimeString($targetAssignment->time_start)
            ->subHours($minNoticeHours);

        if (now()->gt($targetDeadline)) {
            throw ValidationException::withMessages([
                'assignment' => "Jadwal target terlalu dekat (kurang dari {$minNoticeHours} jam).",
            ]);
        }

        // Check monthly limit (if enabled)
        $maxPerMonth = config('schedule-change.swap.max_per_month', 0);
        
        if ($maxPerMonth > 0) {
            $thisMonthCount = SwapRequest::where('requester_id', $requesterAssignment->user_id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereIn('status', ['admin_approved'])
                ->count();

            if ($thisMonthCount >= $maxPerMonth) {
                throw ValidationException::withMessages([
                    'limit' => "Batas maksimal {$maxPerMonth} kali tukar jadwal per bulan telah tercapai.",
                ]);
            }
        }

        // Check for existing pending swap request
        $existingRequest = SwapRequest::where('requester_id', $requesterAssignment->user_id)
            ->where('requester_assignment_id', $requesterAssignment->id)
            ->whereIn('status', ['pending', 'target_approved'])
            ->exists();

        if ($existingRequest) {
            throw ValidationException::withMessages([
                'assignment' => 'Sudah ada permintaan tukar jadwal pending untuk jadwal ini.',
            ]);
        }
    }

    /**
     * Validate no conflicts after swap
     * This ensures neither user will have double booking after the swap
     */
    private function validateNoConflictAfterSwap(
        ScheduleAssignment $requesterAssignment,
        ScheduleAssignment $targetAssignment
    ): void {
        // Check if requester will have conflict at target's time slot
        $requesterConflict = ScheduleAssignment::where('user_id', $requesterAssignment->user_id)
            ->where('date', $targetAssignment->date)
            ->where('session', $targetAssignment->session)
            ->where('id', '!=', $requesterAssignment->id)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($requesterConflict) {
            throw ValidationException::withMessages([
                'conflict' => 'Anda sudah memiliki jadwal lain pada waktu target tukar jadwal ini.',
            ]);
        }

        // Check if target will have conflict at requester's time slot
        $targetConflict = ScheduleAssignment::where('user_id', $targetAssignment->user_id)
            ->where('date', $requesterAssignment->date)
            ->where('session', $requesterAssignment->session)
            ->where('id', '!=', $targetAssignment->id)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($targetConflict) {
            throw ValidationException::withMessages([
                'conflict' => 'Target sudah memiliki jadwal lain pada waktu jadwal Anda.',
            ]);
        }
    }

    /**
     * Target user responds to swap request
     */
    public function targetRespond(SwapRequest $swapRequest, bool $approved, ?string $response = null): void
    {
        // Check permission
        if (!auth()->user()->can('ajukan_tukar_jadwal')) {
            throw ValidationException::withMessages([
                'permission' => 'Anda tidak memiliki izin untuk merespons tukar jadwal.',
            ]);
        }

        if ($swapRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'request' => 'Hanya permintaan pending yang dapat direspons.',
            ]);
        }

        $swapRequest->forceFill([
            'status' => $approved ? 'target_approved' : 'target_rejected',
            'target_response' => $response,
            'target_responded_at' => now(),
        ])->save();

        if ($approved) {
            // Notify admins for final approval
            $this->notifyAdminsForApproval($swapRequest);
        } else {
            // Notify requester about rejection
            try {
                NotificationService::send(
                    $swapRequest->requester,
                    'swap_request_target_rejected',
                    'Permintaan Tukar Ditolak',
                    "{$swapRequest->target->name} menolak permintaan tukar jadwal Anda. Alasan: {$response}",
                    ['swap_request_id' => $swapRequest->id],
                    route('admin.swap.my-requests')
                );
            } catch (\Exception $e) {
                Log::warning('Failed to send rejection notification', [
                    'swap_request_id' => $swapRequest->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Target responded to swap request', [
            'swap_request_id' => $swapRequest->id,
            'approved' => $approved,
        ]);
    }

    /**
     * Notify admins for approval
     */
    private function notifyAdminsForApproval(SwapRequest $swapRequest): void
    {
        try {
            $admins = User::permission('setujui_tukar_jadwal')->get();
            foreach ($admins as $admin) {
                NotificationService::send(
                    $admin,
                    'swap_request_target_approved',
                    'Persetujuan Tukar Jadwal',
                    "Permintaan tukar jadwal antara {$swapRequest->requester->name} dan {$swapRequest->target->name} telah disetujui target. Menunggu approval admin.",
                    ['swap_request_id' => $swapRequest->id],
                    route('admin.swap.approvals')
                );
            }
        } catch (\Exception $e) {
            Log::warning('Failed to notify admins', [
                'swap_request_id' => $swapRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Admin approves/rejects swap request
     */
    public function adminRespond(
        SwapRequest $swapRequest,
        bool $approved,
        ?string $response = null
    ): void {
        // Check permission
        if (!auth()->user()->can('setujui_tukar_jadwal')) {
            throw ValidationException::withMessages([
                'permission' => 'Anda tidak memiliki izin untuk menyetujui tukar jadwal.',
            ]);
        }

        // Validate status
        $validStatuses = config('schedule-change.swap.requires_target_approval', true)
            ? ['target_approved']
            : ['pending'];

        if (! in_array($swapRequest->status, $validStatuses)) {
            throw ValidationException::withMessages([
                'request' => 'Status request tidak valid untuk approval admin.',
            ]);
        }

        try {
            DB::beginTransaction();

            $newStatus = $approved ? 'admin_approved' : 'admin_rejected';

            $swapRequest->forceFill([
                'status' => $newStatus,
                'admin_response' => $response,
                'admin_responded_by' => auth()->id(),
                'admin_responded_at' => now(),
                'completed_at' => $approved ? now() : null,
            ])->save();

            if ($approved) {
                // Re-validate before executing swap
                $this->validateSwapBeforeExecution($swapRequest);

                // Execute the swap
                $this->executeSwap($swapRequest);

                // Notify both users
                $this->notifySwapApproved($swapRequest);
            } else {
                // Notify requester about admin rejection
                $this->notifySwapRejected($swapRequest);
            }

            DB::commit();

            Log::info('Admin responded to swap request', [
                'swap_request_id' => $swapRequest->id,
                'approved' => $approved,
                'admin_id' => auth()->id(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process admin response', [
                'swap_request_id' => $swapRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate swap before execution
     */
    private function validateSwapBeforeExecution(SwapRequest $swapRequest): void
    {
        // Check assignments still exist
        if (! $swapRequest->requesterAssignment || ! $swapRequest->targetAssignment) {
            throw ValidationException::withMessages([
                'request' => 'Salah satu jadwal sudah tidak ada. Swap tidak dapat dilakukan.',
            ]);
        }

        // Re-validate no conflicts
        if (config('schedule-change.swap.validate_conflicts', true)) {
            $this->validateNoConflictAfterSwap(
                $swapRequest->requesterAssignment,
                $swapRequest->targetAssignment
            );
        }
    }

    /**
     * Execute the schedule swap
     */
    private function executeSwap(SwapRequest $swapRequest): void
    {
        $requesterAssignment = $swapRequest->requesterAssignment;
        $targetAssignment = $swapRequest->targetAssignment;

        // Save old values for audit
        $requesterOldValues = $requesterAssignment->only(['user_id', 'date', 'session']);
        $targetOldValues = $targetAssignment->only(['user_id', 'date', 'session']);

        // Swap user IDs
        $tempUserId = $requesterAssignment->user_id;

        $requesterAssignment->update([
            'user_id' => $targetAssignment->user_id,
            'status' => 'scheduled',
            'swapped_to_user_id' => $tempUserId,
            'edited_by' => auth()->id(),
            'edited_at' => now(),
            'edit_reason' => 'Tukar jadwal disetujui',
        ]);

        $targetAssignment->update([
            'user_id' => $tempUserId,
            'status' => 'scheduled',
            'swapped_to_user_id' => $targetAssignment->user_id,
            'edited_by' => auth()->id(),
            'edited_at' => now(),
            'edit_reason' => 'Tukar jadwal disetujui',
        ]);

        // Create audit logs
        \App\Models\AssignmentEditHistory::create([
            'assignment_id' => $requesterAssignment->id,
            'schedule_id' => $requesterAssignment->schedule_id,
            'edited_by' => auth()->id(),
            'action' => 'swapped',
            'old_values' => $requesterOldValues,
            'new_values' => $requesterAssignment->only(['user_id', 'date', 'session']),
            'reason' => "Tukar jadwal dengan {$swapRequest->target->name}",
        ]);

        \App\Models\AssignmentEditHistory::create([
            'assignment_id' => $targetAssignment->id,
            'schedule_id' => $targetAssignment->schedule_id,
            'edited_by' => auth()->id(),
            'action' => 'swapped',
            'old_values' => $targetOldValues,
            'new_values' => $targetAssignment->only(['user_id', 'date', 'session']),
            'reason' => "Tukar jadwal dengan {$swapRequest->requester->name}",
        ]);

        Log::info('Swap executed successfully', [
            'swap_request_id' => $swapRequest->id,
            'requester_assignment_id' => $requesterAssignment->id,
            'target_assignment_id' => $targetAssignment->id,
        ]);
    }

    /**
     * Notify users about approved swap
     */
    private function notifySwapApproved(SwapRequest $swapRequest): void
    {
        try {
            NotificationService::send(
                $swapRequest->requester,
                'swap_request_approved',
                'Tukar Jadwal Disetujui',
                "Permintaan tukar jadwal Anda dengan {$swapRequest->target->name} telah disetujui admin.",
                ['swap_request_id' => $swapRequest->id],
                route('admin.swap.my-requests')
            );

            NotificationService::send(
                $swapRequest->target,
                'swap_request_approved',
                'Tukar Jadwal Disetujui',
                "Permintaan tukar jadwal dengan {$swapRequest->requester->name} telah disetujui admin.",
                ['swap_request_id' => $swapRequest->id],
                route('admin.swap.my-requests')
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send approval notifications', [
                'swap_request_id' => $swapRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify requester about rejected swap
     */
    private function notifySwapRejected(SwapRequest $swapRequest): void
    {
        try {
            NotificationService::send(
                $swapRequest->requester,
                'swap_request_admin_rejected',
                'Tukar Jadwal Ditolak Admin',
                "Admin menolak permintaan tukar jadwal Anda. Alasan: {$swapRequest->admin_response}",
                ['swap_request_id' => $swapRequest->id],
                route('admin.swap.my-requests')
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send rejection notification', [
                'swap_request_id' => $swapRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
