<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
{
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cashier_id' => User::factory(),
            'student_id' => Student::factory(),
            'invoice_number' => $this->generateInvoiceNumber(),
            'date' => fake()->dateTimeBetween('-1 year', 'now'),
            'total_amount' => fake()->randomFloat(2, 10000, 500000),
            'payment_method' => fake()->randomElement(['cash', 'debit', 'credit', 'qris']),
            'payment_amount' => fake()->randomFloat(2, 10000, 600000),
            'change_amount' => fake()->randomFloat(2, 0, 100000),
            'shu_points_earned' => fake()->numberBetween(1, 50),
            'conversion_rate' => 10000, // Rp 10,000 per point
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Generate a unique invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        $date = now();
        $prefix = 'INV/' . $date->format('Ymd') . '/';
        $random = strtoupper(fake()->unique()->bothify('????'));
        return $prefix . $random;
    }

    /**
     * Indicate that the sale is paid with cash.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash',
        ]);
    }

    /**
     * Indicate that the sale is paid with debit card.
     */
    public function debit(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'debit',
        ]);
    }

    /**
     * Indicate that the sale is paid with QRIS.
     */
    public function qris(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'qris',
        ]);
    }

    /**
     * Indicate that the sale has no SHU points.
     */
    public function withoutShuPoints(): static
    {
        return $this->state(fn (array $attributes) => [
            'shu_points_earned' => 0,
        ]);
    }

    /**
     * Indicate that the sale is for a specific student.
     */
    public function forStudent(Student $student): static
    {
        return $this->state(fn (array $attributes) => [
            'student_id' => $student->id,
        ]);
    }

    /**
     * Indicate that the sale is handled by a specific cashier.
     */
    public function handledBy(User $cashier): static
    {
        return $this->state(fn (array $attributes) => [
            'cashier_id' => $cashier->id,
        ]);
    }

    /**
     * Indicate that the sale is today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now(),
        ]);
    }
}
