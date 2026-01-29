<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Penalty;
use App\Models\ScheduleAssignment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Index extends Component
{
    /**
     * Listen for schedule-updated event to refresh dashboard data
     * This event is dispatched from CreateSchedule, EditSchedule, etc.
     */
    #[On('schedule-updated')]
    public function onScheduleUpdated(): void
    {
        // Clear computed property cache by unsetting them
        // This forces Livewire to re-compute on next access
        unset($this->userStats);
        unset($this->adminStats);
    }

    /**
     * Listen for attendance-updated event
     */
    #[On('attendance-updated')]
    public function onAttendanceUpdated(): void
    {
        unset($this->userStats);
        unset($this->adminStats);
    }

    /**
     * Listen for notification-updated event
     */
    #[On('notification-updated')]
    public function onNotificationUpdated(): void
    {
        unset($this->userStats);
    }

    #[Computed]
    public function isAdmin(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH'])
            : false;
    }

    #[Computed]
    public function userStats(): array
    {
        $user = auth()->user();
        if (! $user) {
            return $this->defaultUserStats();
        }

        $userId = $user->id;
        $today = now()->format('Y-m-d');
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');

        // Single query untuk monthly attendance stats
        try {
            $attendanceStats = DB::selectOne("
                SELECT 
                    SUM(status = 'present') as present,
                    SUM(status = 'late') as late,
                    COUNT(*) as total
                FROM attendances 
                WHERE user_id = ? AND date BETWEEN ? AND ?
            ", [$userId, $monthStart, $monthEnd]);
        } catch (\Exception $e) {
            $attendanceStats = (object) ['present' => 0, 'late' => 0, 'total' => 0];
        }

        // Single query untuk penalty stats
        try {
            $penaltyStats = DB::selectOne("
                SELECT COALESCE(SUM(points), 0) as points, COUNT(*) as count
                FROM penalties 
                WHERE user_id = ? AND status = 'active'
            ", [$userId]);
        } catch (\Exception $e) {
            $penaltyStats = (object) ['points' => 0, 'count' => 0];
        }

        // Notification count
        try {
            $notifCount = DB::selectOne('
                SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND read_at IS NULL
            ', [$userId]);
        } catch (\Exception $e) {
            $notifCount = (object) ['count' => 0];
        }

        // Today's schedule
        try {
            $todaySchedule = ScheduleAssignment::where('user_id', $userId)
                ->where('date', $today)
                ->where('status', 'scheduled')
                ->with('schedule:id,name')
                ->select('id', 'schedule_id', 'date', 'session', 'status')
                ->first();
        } catch (\Exception $e) {
            $todaySchedule = null;
        }

        // Upcoming schedules (next 7 days)
        try {
            $upcomingSchedules = ScheduleAssignment::where('user_id', $userId)
                ->whereBetween('date', [now()->addDay()->format('Y-m-d'), now()->addDays(7)->format('Y-m-d')])
                ->where('status', 'scheduled')
                ->with('schedule:id,name')
                ->select('id', 'schedule_id', 'date', 'session', 'status')
                ->orderBy('date')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $upcomingSchedules = collect();
        }

        // Recent notifications
        try {
            $notifications = Notification::where('user_id', $userId)
                ->whereNull('read_at')
                ->select('id', 'title', 'message', 'created_at')
                ->latest()
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $notifications = collect();
        }

        return [
            'monthlyAttendance' => [
                'present' => (int) ($attendanceStats->present ?? 0),
                'late' => (int) ($attendanceStats->late ?? 0),
                'total' => (int) ($attendanceStats->total ?? 0),
            ],
            'penalties' => [
                'points' => (int) ($penaltyStats->points ?? 0),
                'count' => (int) ($penaltyStats->count ?? 0),
            ],
            'notificationCount' => (int) ($notifCount->count ?? 0),
            'todaySchedule' => $todaySchedule,
            'upcomingSchedules' => $upcomingSchedules,
            'notifications' => $notifications,
        ];
    }

    #[Computed]
    public function todayAttendanceSummary(): array
    {
        $today = now()->format('Y-m-d');

        try {
            $stats = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM attendances WHERE DATE(date) = ? AND status = 'present') as present,
                    (SELECT COUNT(*) FROM attendances WHERE DATE(date) = ? AND status = 'late') as late,
                    (SELECT COUNT(*) FROM attendances WHERE DATE(date) = ? AND status = 'absent') as absent,
                    (SELECT COUNT(*) FROM attendances WHERE DATE(date) = ? AND status = 'excused') as excused
            ", [$today, $today, $today, $today]);
        } catch (\Exception $e) {
            $stats = (object) ['present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0];
        }

        return [
            'present' => (int) ($stats->present ?? 0),
            'late' => (int) ($stats->late ?? 0),
            'absent' => (int) ($stats->absent ?? 0),
            'excused' => (int) ($stats->excused ?? 0),
        ];
    }

    #[Computed]
    public function pendingLeaveRequests(): \Illuminate\Support\Collection
    {
        if (! $this->isAdmin) {
            return collect();
        }

        try {
            return LeaveRequest::where('status', 'pending')
                ->with(['user:id,name,nim'])
                ->select('id', 'user_id', 'leave_type', 'start_date', 'end_date', 'reason', 'created_at')
                ->latest()
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    #[Computed]
    public function usersApproachingThreshold(): \Illuminate\Support\Collection
    {
        if (! $this->isAdmin) {
            return collect();
        }

        try {
            // Get users with active penalty points >= 40 (80% of 50 point threshold)
            $usersWithPoints = DB::select("
                SELECT 
                    u.id,
                    u.name,
                    u.nim,
                    COALESCE(SUM(p.points), 0) as total_points
                FROM users u
                LEFT JOIN penalties p ON u.id = p.user_id AND p.status = 'active'
                WHERE u.status = 'active' AND u.deleted_at IS NULL
                GROUP BY u.id, u.name, u.nim
                HAVING total_points >= 40
                ORDER BY total_points DESC
                LIMIT 5
            ");

            return collect($usersWithPoints)->map(function ($user) {
                return (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'nim' => $user->nim,
                    'total_points' => (int) $user->total_points,
                ];
            });
        } catch (\Exception $e) {
            return collect();
        }
    }

    #[Computed]
    public function adminStats(): array
    {
        if (! $this->isAdmin) {
            return $this->defaultAdminStats();
        }

        $today = now()->format('Y-m-d');

        // Single query untuk semua admin stats
        try {
            $stats = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM attendances WHERE DATE(date) = ? AND status = 'present') as today_present,
                    (SELECT COUNT(*) FROM schedule_assignments WHERE date = ?) as today_scheduled,
                    (SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE DATE(date) = ? AND deleted_at IS NULL) as today_sales,
                    (SELECT COUNT(*) FROM sales WHERE DATE(date) = ? AND deleted_at IS NULL) as today_transactions,
                    (SELECT COUNT(*) FROM users WHERE status = 'active' AND deleted_at IS NULL) as active_members,
                    (SELECT COUNT(*) FROM products WHERE stock <= min_stock AND deleted_at IS NULL) as low_stock,
                    (SELECT COUNT(*) FROM leave_requests WHERE status = 'pending') as pending_leaves,
                    0 as pending_swaps
            ", [$today, $today, $today, $today]);
        } catch (\Exception $e) {
            // Fallback query with only essential tables
            $stats = DB::selectOne("
                SELECT
                    (SELECT COUNT(*) FROM attendances WHERE DATE(date) = ? AND status = 'present') as today_present,
                    (SELECT COUNT(*) FROM schedule_assignments WHERE date = ?) as today_scheduled,
                    (SELECT COALESCE(SUM(total_amount), 0) FROM sales WHERE DATE(date) = ? AND deleted_at IS NULL) as today_sales,
                    (SELECT COUNT(*) FROM sales WHERE DATE(date) = ? AND deleted_at IS NULL) as today_transactions,
                    (SELECT COUNT(*) FROM users WHERE status = 'active' AND deleted_at IS NULL) as active_members,
                    0 as low_stock,
                    0 as pending_leaves,
                    0 as pending_swaps
            ", [$today, $today, $today, $today]);
        }

        return [
            'todayAttendance' => [
                'present' => (int) ($stats->today_present ?? 0),
                'total' => (int) ($stats->today_scheduled ?? 0),
            ],
            'todaySales' => (float) ($stats->today_sales ?? 0),
            'todayTransactions' => (int) ($stats->today_transactions ?? 0),
            'activeMembers' => (int) ($stats->active_members ?? 0),
            'lowStockProducts' => (int) ($stats->low_stock ?? 0),
            'pendingLeaves' => (int) ($stats->pending_leaves ?? 0),
            'pendingSwaps' => (int) ($stats->pending_swaps ?? 0),
        ];
    }

    private function defaultUserStats(): array
    {
        return [
            'monthlyAttendance' => ['present' => 0, 'late' => 0, 'total' => 0],
            'penalties' => ['points' => 0, 'count' => 0],
            'notificationCount' => 0,
            'todaySchedule' => null,
            'upcomingSchedules' => collect(),
            'notifications' => collect(),
        ];
    }

    private function defaultAdminStats(): array
    {
        return [
            'todayAttendance' => ['present' => 0, 'total' => 0],
            'todaySales' => 0,
            'todayTransactions' => 0,
            'activeMembers' => 0,
            'lowStockProducts' => 0,
            'pendingLeaves' => 0,
            'pendingSwaps' => 0,
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.index')->layout('layouts.app');
    }
}
