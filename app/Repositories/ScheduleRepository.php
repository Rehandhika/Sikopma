<?php

namespace App\Repositories;

use App\Models\ScheduleAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleRepository
{
    /**
     * Get schedules for user in date range
     */
    public function getUserSchedules(int $userId, Carbon $startDate, Carbon $endDate): Collection
    {
        return ScheduleAssignment::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['schedule'])
            ->orderBy('date')
            ->orderBy('session')
            ->get();
    }

    /**
     * Get user's schedule for specific date
     */
    public function getUserScheduleByDate(int $userId, Carbon $date): ?ScheduleAssignment
    {
        return ScheduleAssignment::where('user_id', $userId)
            ->where('date', $date)
            ->where('status', 'scheduled')
            ->with(['schedule'])
            ->first();
    }

    /**
     * Get schedules for date range
     */
    public function getSchedulesByDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return ScheduleAssignment::whereBetween('date', [$startDate, $endDate])
            ->with(['user', 'schedule'])
            ->orderBy('date')
            ->orderBy('session')
            ->get();
    }

    /**
     * Create schedule assignment
     */
    public function create(array $data): ScheduleAssignment
    {
        return ScheduleAssignment::create($data);
    }

    /**
     * Update schedule assignment
     */
    public function update(int $id, array $data): bool
    {
        return ScheduleAssignment::where('id', $id)->update($data) > 0;
    }

    /**
     * Delete schedule assignment
     */
    public function delete(int $id): bool
    {
        return ScheduleAssignment::where('id', $id)->delete() > 0;
    }

    /**
     * Get available users for schedule
     */
    public function getAvailableUsers(Carbon $date, int $session): Collection
    {
        return User::where('status', 'active')
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH', 'Anggota']);
            })
            ->whereDoesntHave('scheduleAssignments', function ($query) use ($date, $session) {
                $query->where('date', $date)
                    ->where('session', $session)
                    ->where('status', 'scheduled');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'nim']);
    }

    /**
     * Check for schedule conflicts
     */
    public function hasConflict(int $userId, Carbon $date, int $session): bool
    {
        return ScheduleAssignment::where('user_id', $userId)
            ->where('date', $date)
            ->where('session', $session)
            ->where('status', 'scheduled')
            ->exists();
    }

    /**
     * Get schedule statistics
     */
    public function getScheduleStats(Carbon $startDate, Carbon $endDate): array
    {
        $totalSchedules = ScheduleAssignment::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'scheduled')
            ->count();

        $completedSchedules = ScheduleAssignment::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        $usersWithSchedule = ScheduleAssignment::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'scheduled')
            ->distinct('user_id')
            ->count();

        return [
            'total' => $totalSchedules,
            'completed' => $completedSchedules,
            'pending' => $totalSchedules - $completedSchedules,
            'users_with_schedule' => $usersWithSchedule,
        ];
    }

    /**
     * Bulk create schedule assignments
     */
    public function bulkCreate(array $assignments): bool
    {
        try {
            ScheduleAssignment::insert($assignments);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get upcoming schedules for user
     */
    public function getUpcomingSchedules(int $userId, int $days = 7): Collection
    {
        return ScheduleAssignment::where('user_id', $userId)
            ->where('date', '>=', today())
            ->where('date', '<=', today()->addDays($days))
            ->where('status', 'scheduled')
            ->with(['schedule'])
            ->orderBy('date')
            ->orderBy('session')
            ->get();
    }
}
