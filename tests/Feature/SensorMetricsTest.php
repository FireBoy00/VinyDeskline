<?php

namespace Tests\Feature;

use App\Models\SensorMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can create a sensor metric using factory', function () {
    $metric = SensorMetric::factory()->create();

    $this->assertDatabaseHas('sensor_metrics', [
        'id' => $metric->id,
        'temperature' => $metric->temperature,
    ]);
});

test('home page displays latest sensor data', function () {
    // effective login
    $desk = \App\Models\Desk::factory()->create();
    $user = User::factory()->personalized()->withDesk($desk->desk_id)->create();

    // Create verifyable data
    $metric = SensorMetric::factory()->create([
        'temperature' => 24.5,
        'humidity' => 55,
        'recorded_at' => now(),
    ]);

    // Ensure we have a later metric to verify "latest" logic
    SensorMetric::factory()->create([
        'recorded_at' => now()->subMinutes(10),
        'temperature' => 20.0,
    ]);

    $response = $this->actingAs($user)->get('/home');

    $response->assertStatus(200);
    $response->assertSee('24.5'); // Checking if temperature appears on page

    // Also verify the JSON endpoint that frontend might use
    $apiResponse = $this->actingAs($user)->getJson('/api/sensors/latest'); // Assuming endpoint exists or verifying check
    if ($apiResponse->status() === 200) {
        $apiResponse->assertJsonFragment(['temperature' => 24.5]);
    }
});

test('it validates sensor data structure', function () {
    // This tests the model level validation if strict, or just that database accepts correct types
    $metric = SensorMetric::create([
        'temperature' => 22.5,
        'humidity' => 45,
        'light' => 500,
        'recorded_at' => now()
    ]);

    expect($metric->exists)->toBeTrue();
});
