<?php

namespace Tests\Feature;

use App\Models\User;

test('login page is accessible', function () {
    $response = $this->get('/');
    // It might redirect to / if already logged in, or just show login
    // But as a guest, it should be 200 OK
    $response->assertStatus(200);
});

test('user can login with valid credentials', function () {
    // Create a personalized user so they go to home
    $user = User::factory()->personalized()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // If personalized, they go to home (which might redirect to no-desk if no desk assigned, 
    // but AuthController redirects to 'home' INTENDED).
    // The AssertRedirect checks the location header of the response relative to logic in AuthController.
    $response->assertRedirect('/home');
    $this->assertAuthenticatedAs($user);
});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'wrong@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email' => 'wrong@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/'); // or login
    $this->assertGuest();
});
