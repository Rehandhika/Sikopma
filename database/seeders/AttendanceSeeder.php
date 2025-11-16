<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Attendance};

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // Seed last 7 days attendance for a subset of users
        $users = User::whereIn('email', [
            'admin@sikopma.test',
            'ketua@sikopma.test',
            'wakil@sikopma.test',
            'bph1@sikopma.test',
            'anggota1@sikopma.test',
        ])->get();

        if ($users->isEmpty()) return;

        foreach ($users as $user) {
            for ($d = 0; $d < 7; $d++) {
                $date = now()->subDays($d)->toDateString();

                // Random status distribution
                $roll = rand(1, 100);
                if ($roll <= 70) {
                    // present or late
                    $late = rand(0, 10) < 2; // ~20% late among presents
                    $checkIn = $late ? '08:20:00' : '08:00:00';
                    $checkOut = '12:00:00';
                    $hours = 4.0 - ($late ? 0.33 : 0);
                    $status = $late ? 'late' : 'present';

                    Attendance::firstOrCreate(
                        ['user_id' => $user->id, 'date' => $date],
                        [
                            'schedule_assignment_id' => null,
                            'check_in' => $checkIn,
                            'check_out' => $checkOut,
                            'work_hours' => $hours,
                            'status' => $status,
                            'notes' => $late ? 'Datang terlambat' : null,
                        ]
                    );
                } elseif ($roll <= 85) {
                    // excused
                    Attendance::firstOrCreate(
                        ['user_id' => $user->id, 'date' => $date],
                        [
                            'schedule_assignment_id' => null,
                            'check_in' => null,
                            'check_out' => null,
                            'work_hours' => null,
                            'status' => 'excused',
                            'notes' => 'Izin kegiatan kampus',
                        ]
                    );
                } else {
                    // absent
                    Attendance::firstOrCreate(
                        ['user_id' => $user->id, 'date' => $date],
                        [
                            'schedule_assignment_id' => null,
                            'check_in' => null,
                            'check_out' => null,
                            'work_hours' => null,
                            'status' => 'absent',
                            'notes' => null,
                        ]
                    );
                }
            }
        }
    }
}
