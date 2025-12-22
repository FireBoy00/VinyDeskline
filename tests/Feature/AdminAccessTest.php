<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Desk;

test('admin dashboard is accessible to admin', function () {
    // Create admin user with desk (to avoid no-desk redirect if check.desk applies, though admin routes are exempted in CheckDeskAssignment)
    $desk = Desk::factory()->create();
    $admin = User::factory()->admin()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($admin)->get('/admin/dashboard');

    $response->assertStatus(200);
});

test('admin dashboard is forbidden for regular user', function () {
    // Create regular user with desk
    $desk = Desk::factory()->create();
    $user = User::factory()->personalized()->withDesk($desk->desk_id)->create();

    $response = $this->actingAs($user)->get('/admin/dashboard');

    $response->assertStatus(403);
});

test('admin dashboard redirects unauthenticated user to login', function () {
    $response = $this->get('/admin/dashboard');

    $response->assertRedirect('/');
});
