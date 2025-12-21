<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeskMetric>
 */
class DeskMetricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Realistic height range for standing desks
        // Sitting height: typically 700-850mm
        // Standing height: typically 1000-1200mm
        $heightMm = fake()->randomElement([
            // Sitting positions
            ...array_fill(0, 3, fake()->numberBetween(700, 850)),
            // Standing positions
            ...array_fill(0, 2, fake()->numberBetween(1000, 1200)),
        ]);

        $isSitting = $heightMm < 900;

        return [
            'desk_id' => 'desk-' . fake()->uuid(),
            'height_mm' => $heightMm,
            'is_sitting' => $isSitting,
            'recorded_at' => fake()->dateTimeBetween('-30 days'),
        ];
    }

    /**
     * Create a sitting position metric.
     */
    public function sitting(): static
    {
        return $this->state(fn (array $attributes) => [
            'height_mm' => fake()->numberBetween(700, 850),
            'is_sitting' => true,
        ]);
    }

    /**
     * Create a standing position metric.
     */
    public function standing(): static
    {
        return $this->state(fn (array $attributes) => [
            'height_mm' => fake()->numberBetween(1000, 1200),
            'is_sitting' => false,
        ]);
    }

    /**
     * Create a metric for a specific desk.
     */
    public function forDesk(string $deskId): static
    {
        return $this->state(fn (array $attributes) => [
            'desk_id' => $deskId,
        ]);
    }

    /**
     * Create a metric with a specific height.
     */
    public function withHeight(int $heightMm): static
    {
        return $this->state(fn (array $attributes) => [
            'height_mm' => $heightMm,
            'is_sitting' => $heightMm < 900,
        ]);
    }

    /**
     * Create a metric from a specific date range.
     */
    public function fromDateRange(string $startDate, string $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => fake()->dateTimeBetween($startDate, $endDate),
        ]);
    }
}
