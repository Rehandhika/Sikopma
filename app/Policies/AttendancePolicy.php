<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Attendance Policy
 * 
 * PERMISSION MODEL:
 * - check_in_out: Self-service permission for all authenticated users
 * - lihat_absensi_sendiri: View own attendance history
 * - kelola_absensi: Manage all attendance records (admin)
 */
class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * This is for viewing ALL attendance records (management).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('kelola_absensi');
    }

    /**
     * Determine whether the user can view the model.
     * Users can always view their own attendance.
     * Viewing others' attendance requires kelola_absensi.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // User can view their own attendance (self-service)
        if ($user->id === $attendance->user_id) {
            return true;
        }

        // Viewing others requires management permission
        return $user->can('kelola_absensi');
    }

    /**
     * Determine whether the user can create models.
     * This is for manual attendance entry by admins.
     */
    public function create(User $user): bool
    {
        return $user->can('kelola_absensi');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        return $user->can('kelola_absensi');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return $user->can('kelola_absensi');
    }

    /**
     * Determine whether the user can check in.
     * This is a self-service action - all authenticated active users can check in.
     */
    public function checkIn(User $user): bool
    {
        return $user->can('check_in_out') && $user->isActive();
    }

    /**
     * Determine whether the user can check out.
     * Users can only check out their own attendance.
     */
    public function checkOut(User $user, Attendance $attendance): bool
    {
        // User can only check out their own attendance
        if ($user->id !== $attendance->user_id) {
            return false;
        }

        // Can only check out if already checked in
        if (! $attendance->check_in) {
            return false;
        }

        // Cannot check out if already checked out
        if ($attendance->check_out) {
            return false;
        }

        return $user->can('check_in_out') && $user->isActive();
    }

    /**
     * Determine whether the user can export attendance.
     */
    public function export(User $user): bool
    {
        return $user->can('ekspor_data') || $user->can('kelola_absensi');
    }

    /**
     * Determine whether the user can view attendance reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->can('lihat_laporan');
    }

    /**
     * Determine whether the user can override attendance.
     * This is for admin to manually create/edit attendance without schedule.
     */
    public function override(User $user): bool
    {
        return $user->can('kelola_absensi');
    }
}
