<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\{ScheduleAssignment, Penalty, Notification, Attendance, User, Sale, Product, LeaveRequest, SwapRequest};
use Illuminate\Support\Facades\DB;

#[Title('Dashboard')]
class Index extends Component
{
    #[Computed]
    public function isAdmin(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        
        return method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH'])
            : false;
    }

    #[Computed]
    public function userStats(): array
    {
        $user = auth()->user();
        if (!$user) {
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
            $notifCount = DB::selectOne("
                SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND read_at IS NULL
            ", [$userId]);
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
                'present' => (int)($attendanceStats->present ?? 0),
                'late' => (int)($attendanceStats->late ?? 0),
                'total' => (int)($attendanceStats->total ?? 0),
            ],
            'penalties' => [
                'points' => (int)($penaltyStats->points ?? 0),
                'count' => (int)($penaltyStats->count ?? 0),
            ],
            'notificationCount' => (int)($notifCount->count ?? 0),
            'todaySchedule' => $todaySchedule,
            'upcomingSchedules' => $upcomingSchedules,
            'notifications' => $notifications,
        ];
    }

    #[Computed]
    public function adminStats(): array
    {
        if (!$this->isAdmin) {
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
                'present' => (int)($stats->today_present ?? 0),
                'total' => (int)($stats->today_scheduled ?? 0),
            ],
            'todaySales' => (float)($stats->today_sales ?? 0),
            'todayTransactions' => (int)($stats->today_transactions ?? 0),
            'activeMembers' => (int)($stats->active_members ?? 0),
            'lowStockProducts' => (int)($stats->low_stock ?? 0),
            'pendingLeaves' => (int)($stats->pending_leaves ?? 0),
            'pendingSwaps' => (int)($stats->pending_swaps ?? 0),
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
