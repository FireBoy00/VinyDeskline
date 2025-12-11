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
        <section class="section user-header">
            <h1 class="section-title"><span>U</span>ser Management</h1>
        </section>

        <section class="section user-list-section">
            <div class="user-list-header">
                <h2 class="user-list-title">Registered Users</h2>
            </div>

            <div class="user-rows" id="user-rows">
                @foreach ($users as $user)
                    <div class="user-row" data-id="{{ $user->id }}">
                        <span class="user-name">{{ $user->full_name }}</span>
                        <div class="user-divider"></div>
                        <div class="user-pill-group">
                            <button class="user-pill">{{ $user->email }}</button>
                            @if ($user->height)
                                <button class="user-pill">Height: {{ $user->height }}cm</button>
                            @endif
                            @if ($user->age)
                                <button class="user-pill">Age: {{ $user->age }}</button>
                            @endif
                            <button class="user-pill {{ $user->is_admin ? 'admin' : 'regular' }}">
                                {{ $user->is_admin ? 'Admin' : 'User' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </main>
</body>

</html>
