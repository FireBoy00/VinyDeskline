<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Desk>
 */
class DeskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate realistic desk MAC addresses
        $deskId = sprintf(
            '%02x:%02x:%02x:%02x:%02x:%02x',
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255),
            fake()->numberBetween(0, 255)
        );

        $room = Room::inRandomOrder()->first();
        $roomId = $room ? $room->id : null;
        $floorId = $room && $room->floor_id ? $room->floor_id : null;

        $deskNames = [
            'Desk A1', 'Desk A2', 'Desk A3', 'Desk A4', 'Desk A5',
            'Desk B1', 'Desk B2', 'Desk B3', 'Desk B4', 'Desk B5',
            'Desk C1', 'Desk C2', 'Desk C3', 'Desk C4', 'Desk C5',
            'Corner Desk', 'Window Desk', 'Standing Desk', 'Meeting Table',
        ];

        return [
            'desk_id' => $deskId,
            'name' => fake()->randomElement($deskNames),
            'room_id' => $roomId,
            'floor_id' => $floorId,
            'is_removed_from_api' => false,
        ];
    }

    /**
     * Create a desk in a specific room.
     */
    public function inRoom(Room $room): static
    {
        return $this->state(fn (array $attributes) => [
            'room_id' => $room->id,
            'floor_id' => $room->floor_id,
        ]);
    }

    /**
     * Create a desk on a specific floor (not in a room).
     */
    public function onFloor(Floor $floor): static
    {
        return $this->state(fn (array $attributes) => [
            'floor_id' => $floor->id,
            'room_id' => null,
        ]);
    }

    /**
     * Create a desk with a specific ID.
     */
    public function withId(string $deskId): static
    {
        return $this->state(fn (array $attributes) => [
            'desk_id' => $deskId,
        ]);
    }

    /**
     * Mark desk as removed from API.
     */
    public function removed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_removed_from_api' => true,
        ]);
    }

    /**
     * Create a standing desk.
     */
    public function standing(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->randomElement(['Standing Desk A', 'Standing Desk B', 'Active Desk']),
        ]);
    }

    /**
     * Create a desk with a specific name.
     */
    public function named(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }
}
