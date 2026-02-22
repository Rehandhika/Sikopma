<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sale;
use App\Models\ShuPointTransaction;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShuPointTransaction>
 */
class ShuPointTransactionFactory extends Factory
{
    protected $model = ShuPointTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'sale_id' => Sale::factory(),
            'type' => 'earn',
            'amount' => fake()->randomFloat(0, 50000, 500000),
            'conversion_rate' => 10000, // Rp 10,000 per point
            'points' => fake()->numberBetween(5, 50),
            'cash_amount' => null,
            'notes' => fake()->optional(0.3)->sentence(),
            'created_by' => User::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the transaction is an earn type.
     */
    public function earn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'earn',
            'cash_amount' => null,
        ]);
    }

    /**
     * Indicate that the transaction is a redeem type.
     */
    public function redeem(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'redeem',
            'cash_amount' => fake()->randomFloat(0, 10000, 100000),
        ]);
    }

    /**
     * Indicate that the transaction is an adjustment type.
     */
    public function adjust(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'adjust',
        ]);
    }

    /**
     * Indicate that the transaction is for a specific student.
     */
    public function forStudent(Student $student): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $student->id,
        ]);
    }

    /**
     * Indicate that the transaction is linked to a specific sale.
     */
    public function forSale(Sale $sale): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_id' => $sale->id,
        ]);
    }

    /**
     * Indicate that the transaction is today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now(),
        ]);
    }
}
