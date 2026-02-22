<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PenaltyHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PenaltyHistory>
 */
class PenaltyHistoryFactory extends Factory
{
    protected $model = PenaltyHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-6 months', '-3 months');
        $endDate = (clone $startDate)->modify('+3 months');

        return [
            'user_id' => User::factory(),
            'period_start' => $startDate,
            'period_end' => $endDate,
            'total_points' => fake()->numberBetween(0, 100),
            'total_violations' => fake()->numberBetween(1, 10),
            'status' => fake()->randomElement(['active', 'completed', 'reset']),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the history is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the history is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the history is reset.
     */
    public function reset(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reset',
            'total_points' => 0,
        ]);
    }

    /**
     * Indicate that the history is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the history has high points (> 50).
     */
    public function highPoints(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_points' => fake()->numberBetween(51, 150),
            'total_violations' => fake()->numberBetween(5, 20),
        ]);
    }

    /**
     * Indicate that the history has low points (< 30).
     */
    public function lowPoints(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_points' => fake()->numberBetween(0, 29),
            'total_violations' => fake()->numberBetween(1, 3),
        ]);
    }
}
