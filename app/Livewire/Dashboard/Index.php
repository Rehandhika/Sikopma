<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\{
    ScheduleAssignment, 
    Penalty, 
    Notification, 
    Attendance, 
    User, 
    Sale, 
    Product,
    LeaveRequest,
    SwapRequest
};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Index extends Component
{
    /**
     * Render the dashboard with statistics
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $user = auth()->user();

        // Initialize default values
        $isAdmin = false;
        
        // Complete admin stats with ALL required keys
        $adminStats = [
            'todayAttendance' => [
                'present' => 0,
                'total' => 0
            ],
            'todaySales' => 0,
            'todayTransactions' => 0,
            'activeMembers' => 0,
            'pendingRequests' => 0,
            'lowStockProducts' => 0,      // ← Added
            'pendingLeaves' => 0,          // ← Added
            'pendingSwaps' => 0,           // ← Added
        ];
        
        // Complete user stats
        $userStats = [
            'todaySchedule' => null,
            'upcomingSchedules' => collect(),
            'monthlyAttendance' => [
                'present' => 0,
                'late' => 0,
                'total' => 0,
            ],
            'penalties' => [
                'points' => 0,
                'count' => 0,
            ],
            'notifications' => collect(),
        ];

        // Only load real stats when user is authenticated
        if ($user) {
            // Check if user is admin
            $isAdmin = method_exists($user, 'hasAnyRole')
                ? $user->hasAnyRole(['Super Admin', 'Ketua', 'Wakil Ketua', 'BPH'])
                : false;

            // Load User Stats
            try {
                $userStats = [
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
                            ->sum('points') ?? 0,
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
            } catch (\Exception $e) {
                Log::error('Dashboard user stats error: ' . $e->getMessage());
            }

            // Load Admin Stats (if admin)
            if ($isAdmin) {
                try {
                    $adminStats = [
                        'todayAttendance' => [
                            'present' => Attendance::whereDate('check_in', today())
                                ->where('status', 'present')
                                ->count(),
                            'total' => ScheduleAssignment::where('date', today())
                                ->count(),
                        ],
                        'todaySales' => Sale::whereDate('created_at', today())
                            ->sum('total_amount') ?? 0,
                        'todayTransactions' => Sale::whereDate('created_at', today())
                            ->count(),
                        'activeMembers' => User::where('status', 'active')
                            ->count(),
                        'pendingRequests' => 0, // Placeholder
                        'lowStockProducts' => Product::where('stock', '<=', DB::raw('minimum_stock'))
                            ->count(),
                        'pendingLeaves' => LeaveRequest::where('status', 'pending')
                            ->count(),
                        'pendingSwaps' => SwapRequest::where('status', 'pending')
                            ->count(),
                    ];
                } catch (\Exception $e) {
                    Log::error('Dashboard admin stats error: ' . $e->getMessage());
                    // Keep default values on error
                }
            }
        }

        return view('livewire.dashboard.index', [
            'userStats' => $userStats,
            'adminStats' => $adminStats,
            'isAdmin' => $isAdmin,
        ])->layout('layouts.app')->title('Dashboard');
    }
}
