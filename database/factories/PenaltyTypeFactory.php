<?php

namespace Database\Factories;

use App\Models\PenaltyType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenaltyTypeFactory extends Factory
{
    protected $model = PenaltyType::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->word()),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'points' => $this->faker->numberBetween(5, 20),
            'is_active' => true,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function lateMinor(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'LATE_MINOR',
            'name' => 'Terlambat Ringan',
            'description' => 'Terlambat 6-15 menit',
            'points' => 5,
        ]);
    }

    public function lateModerate(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'LATE_MODERATE',
            'name' => 'Terlambat Sedang',
            'description' => 'Terlambat 16-30 menit',
            'points' => 10,
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'ABSENT',
            'name' => 'Tidak Hadir',
            'description' => 'Tidak hadir tanpa izin',
            'points' => 20,
        ]);
    }
}
