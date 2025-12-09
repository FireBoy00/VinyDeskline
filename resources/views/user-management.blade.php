<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/general-admin.css'])
    @vite(['resources/css/user-management.css', 'resources/js/user-management.js'])
    <title>VinyDeskline - User Management</title>
</head>
<body>
<x-navbar active="user-management" />

<main class="dashboard">
    <section class="section um-header-section">
        <h1 class="um-header-title">Viny Deskline - User Management</h1>
    </section>

    <section class="section um-list-section">
        <div class="um-list-header">
            <h2 class="um-list-title">List of Desks Users</h2>
            <button id="um-add-btn" class="um-add-btn">Add user</button>
        </div>

        <div class="um-rows" id="um-rows">
            @foreach($users as $u)
            <div class="um-row" data-id="{{ $u->id }}">
                <span class="um-name">{{ $u->name }}</span>
                <div class="um-divider"></div>
              <div class="um-pill-group">
    <button class="um-pill">ID: {{ $u->desk_id }}</button>
    <button class="um-pill">Status: {{ $u->status }}</button>
    <button class="um-pill">Height: {{ $u->height }}</button>
</div>

                <button class="um-remove">Remove</button>
            </div>
            @endforeach
        </div>
    </section>
</main>

<div id="um-overlay" class="um-overlay">
    <div class="um-modal">
        <h2 class="um-modal-title">Add New User</h2>
        <form id="um-add-form">
            <label class="um-label">Full Name</label>
            <input type="text" name="name" class="um-input" required>

            <label class="um-label">Email</label>
            <input type="email" name="email" class="um-input" required>

            <label class="um-label">Desk ID</label>
            <input type="text" name="desk_id" class="um-input" required>

            <label class="um-label">Status</label>
            <select name="status" class="um-select">
                <option value="Occupied">Occupied</option>
                <option value="Not Occupied">Not Occupied</option>
            </select>

            <label class="um-label">Height</label>
            <input type="text" name="height" class="um-input" required>

            <div class="um-modal-actions">
                <button type="button" id="um-cancel" class="um-cancel">Cancel</button>
                <button type="submit" class="um-confirm">Add user</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
