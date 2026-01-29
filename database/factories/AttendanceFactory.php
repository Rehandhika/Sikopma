<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\ScheduleAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'schedule_assignment_id' => ScheduleAssignment::factory(),
            'date' => Carbon::today(),
            'check_in' => Carbon::now(),
            'check_out' => null,
            'work_hours' => null,
            'status' => 'present',
            'notes' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
        ]);
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
            'check_in' => null,
        ]);
    }

    public function withCheckOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_out' => Carbon::now()->addHours(2),
            'work_hours' => 2.0,
        ]);
    }
}
