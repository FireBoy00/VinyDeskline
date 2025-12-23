<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Desk;

beforeEach(function () {
    // Setup authenticated admin and regular user for reuse if needed,
    // but Pest style usually prefers explicit setup per test or standard beforeEach.
    // We'll create helpers or just create inside tests for clarity.
});

// User Management
test('user management is accessible to admin', function () {
    $desk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($admin)->get('/admin/user-management');
    $response->assertStatus(200);
});

test('user management is forbidden for regular user', function () {
    $desk = Desk::factory()->create();
    $user = User::factory()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($user)->get('/admin/user-management');
    $response->assertStatus(403);
});

// Office Management
test('office management is accessible to admin', function () {
    $desk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($admin)->get('/admin/office-management');
    $response->assertStatus(200);
});

test('office management is forbidden for regular user', function () {
    $desk = Desk::factory()->create();
    $user = User::factory()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($user)->get('/admin/office-management');
    $response->assertStatus(403);
});

// Desk Management
test('desk management is accessible to admin', function () {
    $desk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($admin)->get('/admin/desks');
    $response->assertStatus(200);
});

test('desk management is forbidden for regular user', function () {
    $desk = Desk::factory()->create();
    $user = User::factory()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($user)->get('/admin/desks');
    $response->assertStatus(403);
});

// Schedules
test('schedules page is accessible to admin', function () {
    $desk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($admin)->get('/admin/schedules');
    $response->assertStatus(200);
});

test('schedules page is forbidden for regular user', function () {
    $desk = Desk::factory()->create();
    $user = User::factory()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($user)->get('/admin/schedules');
    $response->assertStatus(403);
});
