<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Floor>
 */
class FloorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $floorNumber = 0;
        $floorNumber++;

        $floorNames = [
            'Ground Floor',
            'First Floor',
            'Second Floor',
            'Third Floor',
            'Fourth Floor',
            'Mezzanine Level',
            'Basement Level',
            'Upper Ground',
        ];

        $descriptions = [
            'Open office space with natural lighting',
            'Executive and management area',
            'Development team workspace',
            'Design and creative studio',
            'Meeting and collaboration zones',
            'Call center and customer support',
            'Training and development facility',
            'Quiet focus and concentration area',
            'Flexible working and breakout spaces',
            'Storage and auxiliary facilities',
        ];

        return [
            'name' => $floorNames[$floorNumber - 1] ?? "Floor {$floorNumber}",
            'floor_number' => $floorNumber,
            'description' => fake()->randomElement($descriptions),
        ];
    }

    /**
     * Create a specific floor by floor number.
     */
    public function floor(int $floorNumber): static
    {
        return $this->state(fn (array $attributes) => [
            'floor_number' => $floorNumber,
            'name' => match ($floorNumber) {
                0 => 'Ground Floor',
                1 => 'First Floor',
                2 => 'Second Floor',
                3 => 'Third Floor',
                4 => 'Fourth Floor',
                -1 => 'Basement Level',
                default => "Floor {$floorNumber}",
            },
        ]);
    }
}
