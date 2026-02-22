<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Penalty;
use App\Models\PenaltyType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Penalty>
 */
class PenaltyFactory extends Factory
{
    protected $model = Penalty::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'penalty_type_id' => PenaltyType::factory(),
            'reference_type' => 'attendances',
            'reference_id' => null,
            'points' => fake()->randomElement([5, 10, 15, 20]),
            'description' => fake()->sentence(),
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'status' => 'active',
            'appeal_reason' => null,
            'appeal_status' => null,
            'appealed_at' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ];
    }

    /**
     * Indicate that the penalty is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the penalty is appealed.
     */
    public function appealed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'appealed',
            'appeal_reason' => fake()->sentence(),
            'appealed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the penalty is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'reviewed_by' => User::factory(),
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'review_notes' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the penalty is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'reviewed_by' => User::factory(),
            'reviewed_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'review_notes' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the penalty is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the penalty is for a specific type.
     */
    public function forType(PenaltyType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'penalty_type_id' => $type->id,
            'points' => $type->points,
        ]);
    }

    /**
     * Indicate that the penalty is for late attendance.
     */
    public function forLate(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'attendances',
            'description' => 'Terlambat datang',
        ]);
    }

    /**
     * Indicate that the penalty is for absence.
     */
    public function forAbsence(): static
    {
        return $this->state(fn (array $attributes) => [
            'reference_type' => 'attendances',
            'description' => 'Tidak hadir tanpa izin',
            'points' => 20,
        ]);
    }
}
