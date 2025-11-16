<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\{ScheduleAssignment, Penalty, Notification, Attendance, User, Sale, Product};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    /**
     * Render the dashboard with cached statistics
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']);

        // User Stats (cached per user for 10 minutes)
        $userStats = cache()->remember(
            "dashboard.user_stats.{$user->id}." . today()->format('Y-m-d'),
            now()->addMinutes(config('sikopma.cache.user_stats', 10)),
            function () use ($user) {
                return [
                    'todaySchedule' => ScheduleAssignment::where('user_id', $user->id)
                        ->where('date', today())
                        ->where('status', 'scheduled')
                        ->with('schedule')
                        ->first(),
                    
                    'upcomingSchedules' => ScheduleAssignment::where('user_id', $user->id)
                        ->whereBetween('date', [today()->addDay(), today()->addDays(7)])
                        ->where('status', 'scheduled')
                        ->with('schedule')
                        ->orderBy('date')
                        ->limit(5)
                        ->get(),
                    
                    'monthlyAttendance' => [
                        'present' => Attendance::where('user_id', $user->id)
                            ->whereMonth('check_in', now()->month)
                            ->where('status', 'present')
                            ->count(),
                        'late' => Attendance::where('user_id', $user->id)
                            ->whereMonth('check_in', now()->month)
                            ->where('status', 'late')
                            ->count(),
                        'total' => ScheduleAssignment::where('user_id', $user->id)
                            ->whereMonth('date', now()->month)
                            ->count(),
                    ],
                    
                    'penalties' => [
                        'points' => Penalty::where('user_id', $user->id)
                            ->where('status', 'active')
                            ->sum('points'),
                        'count' => Penalty::where('user_id', $user->id)
                            ->where('status', 'active')
                            ->count(),
                    ],
                ];
            }
        );

        // Notifications (not cached - need real-time)
        $userStats['notifications'] = Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Admin Stats (cached for 5 minutes)
        $adminStats = null;
        if ($isAdmin) {
            $adminStats = cache()->remember(
                'dashboard.admin_stats.' . today()->format('Y-m-d-H'),
                now()->addMinutes(config('sikopma.cache.dashboard_stats', 5)),
                function () {
                    return [
                        'todayAttendance' => [
                            'present' => Attendance::whereDate('check_in', today())->where('status', 'present')->count(),
                            'late' => Attendance::whereDate('check_in', today())->where('status', 'late')->count(),
                            'total' => ScheduleAssignment::where('date', today())->count(),
                        ],
                        
                        'todaySales' => Sale::whereDate('created_at', today())->sum('total_amount'),
                        'todayTransactions' => Sale::whereDate('created_at', today())->count(),
                        
                        'lowStockProducts' => Product::whereColumn('stock', '<=', 'min_stock')
                            ->where('stock', '>', 0)
                            ->count(),
                        
                        'outOfStockProducts' => Product::where('stock', 0)->count(),
                        
                        'pendingLeaves' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
                        'pendingSwaps' => \App\Models\SwapRequest::where('status', 'target_approved')->count(),
                        
                        'activeUsers' => User::where('status', 'active')->count(),
                        'totalUsers' => User::count(),
                    ];
                }
            );
        }

        return view('livewire.dashboard.index', [
            'userStats' => $userStats,
            'adminStats' => $adminStats,
            'isAdmin' => $isAdmin,
        ])->layout('layouts.app')->title('Dashboard');
    }
}
