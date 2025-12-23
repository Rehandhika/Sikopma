<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Schedule, ScheduleAssignment, User};
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        if (Schedule::count() > 0) {
            return;
        }

        // Get active users (anggota only for schedule assignments)
        $users = User::where('status', 'active')
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['Anggota', 'BPH']);
            })
            ->get();

        if ($users->isEmpty()) {
            $this->command->warn('No active users found. Please run UserSeeder first.');
            return;
        }

        // Get admin user for generated_by and published_by
        $admin = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['Super Admin', 'Ketua']);
        })->first();

        if (!$admin) {
            $this->command->warn('No admin user found. Please run UserSeeder first.');
            return;
        }

        // Create 3 schedules: past (published), current (published), future (draft)
        $schedules = [
            [
                'week_start_date' => Carbon::now()->subWeeks(2)->startOfWeek(),
                'status' => 'published',
                'published' => true,
            ],
            [
                'week_start_date' => Carbon::now()->startOfWeek(),
                'status' => 'published',
                'published' => true,
            ],
            [
                'week_start_date' => Carbon::now()->addWeeks(1)->startOfWeek(),
                'status' => 'draft',
                'published' => false,
            ],
        ];

        foreach ($schedules as $scheduleData) {
            $startDate = $scheduleData['week_start_date'];
            $endDate = $startDate->copy()->addDays(3); // Monday to Thursday

            $schedule = Schedule::create([
                'week_start_date' => $startDate->format('Y-m-d'),
                'week_end_date' => $endDate->format('Y-m-d'),
                'status' => $scheduleData['status'],
                'generated_by' => $admin->id,
                'generated_at' => now(),
                'published_at' => $scheduleData['published'] ? now() : null,
                'published_by' => $scheduleData['published'] ? $admin->id : null,
                'total_slots' => 12, // 4 days × 3 sessions
                'notes' => 'Jadwal ' . $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
            ]);

            // Create assignments for each day and session
            $userIndex = 0;
            $totalUsers = $users->count();

            for ($day = 0; $day < 4; $day++) {
                $date = $startDate->copy()->addDays($day);
                $dayName = strtolower($date->englishDayOfWeek);

                for ($session = 1; $session <= 3; $session++) {
                    // Assign 1-2 users per slot randomly
                    $usersPerSlot = rand(1, 2);
                    
                    for ($i = 0; $i < $usersPerSlot; $i++) {
                        if ($totalUsers === 0) break;
                        
                        $user = $users[$userIndex % $totalUsers];
                        $userIndex++;

                        ScheduleAssignment::create([
                            'schedule_id' => $schedule->id,
                            'user_id' => $user->id,
                            'day' => $dayName,
                            'session' => (string) $session,
                            'date' => $date->format('Y-m-d'),
                            'time_start' => $this->getSessionStartTime($session),
                            'time_end' => $this->getSessionEndTime($session),
                            'status' => 'scheduled',
                        ]);
                    }
                }
            }

            // Calculate coverage
            $schedule->calculateCoverage();

            $this->command->info("Created schedule: {$schedule->week_start_date} ({$schedule->status})");
        }
    }

    private function getSessionStartTime(int $session): string
    {
        return ['07:30', '10:20', '13:30'][$session - 1] ?? '07:30';
    }

    private function getSessionEndTime(int $session): string
    {
        return ['10:00', '12:50', '16:00'][$session - 1] ?? '10:00';
    }
}
