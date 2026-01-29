<?php

namespace Database\Factories;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $startDate = Carbon::today()->addDays($this->faker->numberBetween(1, 7));
        $endDate = $startDate->copy()->addDays($this->faker->numberBetween(0, 3));

        return [
            'user_id' => User::factory(),
            'leave_type' => $this->faker->randomElement(['sick', 'permission', 'emergency']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'reason' => $this->faker->sentence(),
            'attachment' => null,
            'status' => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
            'review_notes' => $this->faker->sentence(),
        ]);
    }

    public function sick(): static
    {
        return $this->state(fn (array $attributes) => [
            'leave_type' => 'sick',
        ]);
    }

    public function permission(): static
    {
        return $this->state(fn (array $attributes) => [
            'leave_type' => 'permission',
        ]);
    }
}
