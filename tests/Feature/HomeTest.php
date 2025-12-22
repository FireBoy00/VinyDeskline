<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Desk;

test('home page redirects to login when not authenticated', function () {
    $response = $this->get('/home');
    $response->assertRedirect('/');
});

test('home page redirects to no-desk when user has no desk', function () {
    // Create user without desk
    $user = User::factory()->personalized()->create();
    // personalized() ensures no redirect to /personalize interfering

    $response = $this->actingAs($user)->get('/home');

    $response->assertRedirect('/no-desk');
});

test('home page is accessible when user has desk', function () {
    // Create desk and user assigned to it
    $desk = Desk::factory()->create();
    $user = User::factory()
        ->personalized()
        ->withDesk($desk->desk_id)
        ->create();

    $response = $this->actingAs($user)->get('/home');

    $response->assertStatus(200);
});

test('no-desk page is accessible', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/no-desk');
    $response->assertStatus(200);
});
