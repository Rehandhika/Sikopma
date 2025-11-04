<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\{ScheduleAssignment, Penalty, Notification, Attendance, User, Sale, Product};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH']);

        // User Stats
        $userStats = [
            'todaySchedule' => ScheduleAssignment::where('user_id', $user->id)
                ->where('date', today())
                ->where('status', 'scheduled')
                ->first(),
            
            'upcomingSchedules' => ScheduleAssignment::where('user_id', $user->id)
                ->whereBetween('date', [today()->addDay(), today()->addDays(7)])
                ->where('status', 'scheduled')
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
                // Using existing schema: only points and status values: active/appealed/dismissed/expired
                'points' => Penalty::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->sum('points'),
                'count' => Penalty::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->count(),
            ],
            
            'notifications' => Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        // Admin Stats
        $adminStats = null;
        if ($isAdmin) {
            $adminStats = [
                'todayAttendance' => [
                    'present' => Attendance::whereDate('check_in', today())->where('status', 'present')->count(),
                    'late' => Attendance::whereDate('check_in', today())->where('status', 'late')->count(),
                    'total' => ScheduleAssignment::where('date', today())->count(),
                ],
                
                'todaySales' => Sale::whereDate('created_at', today())->sum('total_amount'),
                'todayTransactions' => Sale::whereDate('created_at', today())->count(),
                
                'lowStockProducts' => Product::where('stock', '<=', DB::raw('min_stock'))
                    ->where('stock', '>', 0)
                    ->count(),
                
                'outOfStockProducts' => Product::where('stock', 0)->count(),
                
                'pendingLeaves' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
                'pendingSwaps' => \App\Models\SwapRequest::where('status', 'accepted')->count(),
                
                'activeUsers' => User::where('status', 'active')->count(),
                'totalUsers' => User::count(),
            ];
        }

        return view('livewire.dashboard.index', [
            'userStats' => $userStats,
            'adminStats' => $adminStats,
            'isAdmin' => $isAdmin,
        ])->layout('layouts.app')->title('Dashboard');
    }
}
