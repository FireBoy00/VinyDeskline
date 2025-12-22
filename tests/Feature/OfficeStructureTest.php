<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Desk;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can create a floor', function () {
    $desk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($admin)->postJson('/api/floors', [
        'name' => 'Ground Floor',
        'floor_number' => 1,
        'description' => 'Main lobby and reception'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('floors', [
        'name' => 'Ground Floor',
        'floor_number' => 1,
    ]);
});

test('admin can update a floor', function () {
    $floor = Floor::factory()->create(['name' => 'Old Name']);
    $desk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($admin)->putJson("/api/floors/{$floor->id}", [
        'name' => 'New Name',
        'floor_number' => $floor->floor_number,
        'description' => 'Updated desc'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('floors', [
        'id' => $floor->id,
        'name' => 'New Name',
    ]);
});

test('admin cannot delete floor with rooms', function () {
    $floor = Floor::factory()->create();
    Room::factory()->create(['floor_id' => $floor->id]);

    $desk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($admin)->deleteJson("/api/floors/{$floor->id}");

    $response->assertStatus(422); // Assuming controller returns 422 for this logic
    $this->assertDatabaseHas('floors', ['id' => $floor->id]);
});

test('admin can create a room', function () {
    $floor = Floor::factory()->create();
    $desk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($admin)->postJson('/api/rooms', [
        'name' => 'Meeting Room A',
        'floor_id' => $floor->id,
        'description' => 'Small meeting room'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('rooms', [
        'name' => 'Meeting Room A',
        'floor_id' => $floor->id,
    ]);
});

test('admin can assign desk to room', function () {
    $room = Room::factory()->create();
    $targetDesk = Desk::factory()->create(); // Desk to be assigned

    $myDesk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($myDesk->desk_id)->create();

    $response = $this->actingAs($admin)->putJson("/api/desks/{$targetDesk->desk_id}/location", [
        'room_id' => $room->id
    ]);

    $response->assertStatus(200);

    // Refresh to check DB
    $targetDesk->refresh();
    expect($targetDesk->room_id)->toBe($room->id)
        ->and($targetDesk->floor_id)->toBe($room->floor_id); // Should inherit floor
});
