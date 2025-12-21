<?php

namespace Database\Factories;

use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roomTypes = [
            'Open Office A',
            'Open Office B',
            'Meeting Room 1',
            'Meeting Room 2',
            'Conference Room',
            'Executive Suite',
            'Development Team',
            'Design Studio',
            'Quality Assurance',
            'Sales Department',
            'Marketing Hub',
            'Human Resources',
            'Finance & Admin',
            'Customer Support',
            'Break Room',
            'Quiet Focus Zone',
            'Collaboration Space',
            'Training Room',
            'Innovation Lab',
            'Server Room',
        ];

        $descriptions = [
            'Shared workspace for collaborative work',
            'Dedicated team area with focused workspace',
            'Formal meeting space for presentations',
            'Small huddle room for quick discussions',
            'High-tech conference facility with AV',
            'Premium office space for leadership',
            'Dedicated development workspace',
            'Creative space with standing desks',
            'Testing and quality assurance area',
            'Sales team bullpen with collaboration areas',
            'Marketing creative workspace',
            'Personnel management office',
            'Financial operations center',
            'Customer interaction hub',
            'Relaxation and refreshment area',
            'Individual focus work area',
            'Team brainstorming and planning space',
            'Training and workshop facility',
            'Research and development facility',
            'Equipment and infrastructure room',
        ];

        return [
            'name' => fake()->randomElement($roomTypes),
            'floor_id' => Floor::factory(),
            'description' => fake()->randomElement($descriptions),
        ];
    }

    /**
     * Create a room on a specific floor.
     */
    public function onFloor(Floor $floor): static
    {
        return $this->state(fn (array $attributes) => [
            'floor_id' => $floor->id,
        ]);
    }

    /**
     * Create an open office room.
     */
    public function openOffice(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Open Office A', 'Open Office B', 'Open Office C']),
            'description' => 'Shared workspace for collaborative work',
        ]);
    }

    /**
     * Create a meeting room.
     */
    public function meetingRoom(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Meeting Room 1', 'Meeting Room 2', 'Meeting Room 3']),
            'description' => 'Formal meeting space for presentations and discussions',
        ]);
    }

    /**
     * Create a team-specific room.
     */
    public function team(string $teamName): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => "{$teamName} Team",
            'description' => "Dedicated workspace for the {$teamName} team",
        ]);
    }
}
