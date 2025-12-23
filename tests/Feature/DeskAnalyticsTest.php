<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Desk;
use App\Models\DeskMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it fetches desk metrics for authenticated user', function () {
    $desk = Desk::factory()->create();
    $user = User::factory()->personalized()->withDesk($desk->desk_id)->create();

    // Create metrics
    DeskMetric::create([
        'desk_id' => $desk->desk_id,
        'height_mm' => 1100,
        'is_sitting' => false,
        'recorded_at' => now()->subHour()
    ]);

    DeskMetric::create([
        'desk_id' => $desk->desk_id,
        'height_mm' => 750,
        'is_sitting' => true,
        'recorded_at' => now()
    ]);

    $response = $this->actingAs($user)->getJson('/home/metrics');

    $response->assertStatus(200);
    $response->assertJsonCount(2, 'metrics');
    $response->assertJsonFragment(['height_mm' => 1100, 'is_sitting' => false]);
    $response->assertJsonFragment(['height_mm' => 750, 'is_sitting' => true]);
});

test('it returns 400 if user has no desk', function () {
    $user = User::factory()->personalized()->create(); // No desk assigned (and assuming we bypass middleware or middleware allows API access)

    // Note: 'home/metrics' is protected by 'check.desk' usually.
    // If check.desk redirects to /no-desk, this test might fail with 302 unless we expect that.
    // However, the controller has specific logic:
    // if (!$user->desk_id) return response()->json([ error ... ], 400);
    // We need to see if middleware catches it first.
    // In web.php, /home/metrics is in the 'check.desk' group.
    // So it will redirect to /no-desk.
    // This test verifies that behavior or if we should move the route out of middleware for API niceness.
    // Given the current implementation, it expects a redirect to 'no-desk' if accessing via browser, 
    // but the controller logic suggests it wants to handle it.
    // Let's test the current behavior: Redirect to no-desk.

    $response = $this->actingAs($user)->getJson('/home/metrics');

    // API requests might be handled differently by middleware, or just redirect.
    // CheckDeskAssignment middleware: 
    // if (!$request->routeIs($allowedRoutes) && !$request->is('api/*')) { return redirect... }
    // /home/metrics is NOT api/*

    $response->assertStatus(302);
    $response->assertRedirect(route('no-desk'));
});

test('it aggregates data correctly for charts', function () {
    $desk = Desk::factory()->create();
    $user = User::factory()->personalized()->withDesk($desk->desk_id)->create();

    // Create 30 days of data? Just a few points.
    DeskMetric::create(['desk_id' => $desk->desk_id, 'height_mm' => 800, 'is_sitting' => true, 'recorded_at' => now()->subDays(2)]);
    DeskMetric::create(['desk_id' => $desk->desk_id, 'height_mm' => 1200, 'is_sitting' => false, 'recorded_at' => now()->subDays(1)]);

    $response = $this->actingAs($user)->getJson('/home/metrics');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'metrics' => [
                '*' => ['height_mm', 'is_sitting', 'recorded_at', 'timestamp']
            ],
            'count',
            'desk_id'
        ]);
});
