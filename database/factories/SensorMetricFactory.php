<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SensorMetric>
 */
class SensorMetricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'temperature' => $this->faker->randomFloat(1, 18, 30),
            'humidity' => $this->faker->numberBetween(30, 70),
            'light' => $this->faker->numberBetween(200, 1000),
            'recorded_at' => now(),
        ];
    }
}
