<?php

namespace Database\Factories;

use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $weekStart = Carbon::now()->startOfWeek();
        
        return [
            'week_start_date' => $weekStart,
            'week_end_date' => $weekStart->copy()->addDays(3),
            'status' => 'published',
            'generated_by' => User::factory(),
            'generated_at' => now(),
            'total_slots' => 12,
            'filled_slots' => 0,
            'coverage_rate' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $attributes['generated_by'],
        ]);
    }

    public function forDate(Carbon $date): static
    {
        $weekStart = $date->copy()->startOfWeek();
        return $this->state(fn (array $attributes) => [
            'week_start_date' => $weekStart,
            'week_end_date' => $weekStart->copy()->addDays(3),
        ]);
    }
}
