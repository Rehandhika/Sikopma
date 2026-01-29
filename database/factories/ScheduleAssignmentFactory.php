<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\ScheduleAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleAssignmentFactory extends Factory
{
    protected $model = ScheduleAssignment::class;

    public function definition(): array
    {
        $date = Carbon::today();
        $session = $this->faker->numberBetween(1, 3);

        $sessionTimes = [
            1 => ['start' => '07:30:00', 'end' => '10:00:00'],
            2 => ['start' => '10:20:00', 'end' => '12:50:00'],
            3 => ['start' => '13:30:00', 'end' => '16:00:00'],
        ];

        return [
            'schedule_id' => Schedule::factory(),
            'user_id' => User::factory(),
            'date' => $date,
            'day' => strtolower($date->englishDayOfWeek),
            'session' => $session,
            'time_start' => $sessionTimes[$session]['start'],
            'time_end' => $sessionTimes[$session]['end'],
            'status' => 'scheduled',
        ];
    }

    public function forSession(int $session): static
    {
        $sessionTimes = [
            1 => ['start' => '07:30:00', 'end' => '10:00:00'],
            2 => ['start' => '10:20:00', 'end' => '12:50:00'],
            3 => ['start' => '13:30:00', 'end' => '16:00:00'],
        ];

        return $this->state(fn (array $attributes) => [
            'session' => $session,
            'time_start' => $sessionTimes[$session]['start'],
            'time_end' => $sessionTimes[$session]['end'],
        ]);
    }

    public function forDate(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
            'day' => strtolower($date->englishDayOfWeek),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
}
