<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['uniform', 'cleaning']);

        return [
            'type' => $type,
            'title' => $type === 'uniform'
                ? fake()->randomElement([
                    'Morning Sit-Stand Rotation',
                    'Afternoon Activity Break',
                    'Standing Work Session',
                    'Sitting Work Session',
                    'Lunch Break Position',
                    'Mid-day Movement',
                    'End of Day Adjustment',
                ])
                : fake()->randomElement([
                    'Night Cleaning',
                    'End of Day Sanitization',
                    'Morning Deep Clean',
                    'Weekly Desk Cleaning',
                    'Surface Disinfection',
                ]),
            'height' => fake()->randomElement([
                fake()->numberBetween(700, 850),   // Sitting
                fake()->numberBetween(1000, 1200), // Standing
            ]),
            'start_time' => fake()->time('H:i:s'),
            'end_time' => fake()->time('H:i:s'),
            'date' => fake()->optional(0.6)->date(),
            'frequency' => fake()->randomElement(['once', 'daily', 'multiple']),
        ];
    }

    /**
     * Create a sitting-focused schedule.
     */
    public function sitting(): static
    {
        return $this->state(fn (array $attributes) => [
            'height' => fake()->numberBetween(700, 850),
            'title' => fake()->randomElement([
                'Sitting Work Session',
                'Lunch Break',
                'Computer Work',
            ]),
        ]);
    }

    /**
     * Create a standing-focused schedule.
     */
    public function standing(): static
    {
        return $this->state(fn (array $attributes) => [
            'height' => fake()->numberBetween(1000, 1200),
            'title' => fake()->randomElement([
                'Standing Work Session',
                'Active Break',
                'Standing Meeting',
            ]),
        ]);
    }

    /**
     * Create a uniform/posture adjustment schedule.
     */
    public function uniformSchedule(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'uniform',
            'title' => fake()->randomElement([
                'Morning Sit-Stand Rotation',
                'Afternoon Activity Break',
                'Standing Work Session',
                'Sitting Work Session',
                'Mid-day Movement',
            ]),
        ]);
    }

    /**
     * Create a cleaning schedule.
     */
    public function cleaningSchedule(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cleaning',
            'title' => fake()->randomElement([
                'Night Cleaning',
                'End of Day Sanitization',
                'Morning Deep Clean',
            ]),
        ]);
    }

    /**
     * Create a daily recurring schedule.
     */
    public function daily(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'daily',
            'date' => null,
        ]);
    }

    /**
     * Create a one-time schedule.
     */
    public function once(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'once',
            'date' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
        ]);
    }

    /**
     * Create a multiple times schedule.
     */
    public function multiple(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'multiple',
        ]);
    }
}
