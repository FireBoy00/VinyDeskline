<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/home.css', 'resources/css/settings.css', 'resources/js/settings.js'])
        
        <title>VinyDeskline - Settings</title>
    </head>
    <body>
        <header class="page-header">
            <h1 class="accent-title">
                <span>Viny</span>
                <span>Deskline</span>
            </h1>

            <div class="user-account-card">
                <div class="user-account-trigger">
                    <span class="user-name">{{ (Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? '') ?: 'User' }}</span>
                    <div class="user-avatar">
                        <span class="material-icons-round">person</span>
                    </div>
                </div>
                <div class="user-dropdown">
                    <div class="dropdown-header">
                        <div class="user-avatar-large">
                            <span class="material-icons-round">person</span>
                        </div>
                        <div class="user-info">
                            <span class="dropdown-user-name">{{ Auth::user()->full_name }}</span>
                            <span class="dropdown-user-email">{{ Auth::user()->email }}</span>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <ul class="dropdown-menu">
                        @if (Auth::user()->is_admin)
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                                    <span class="material-icons-round">dashboard</span>
                                    <span>Admin Dashboard</span>
                                </a>
                            </li>
                        @endif
                        <li>
                            <a href="{{ route('home') }}" class="dropdown-item">
                                <span class="material-icons-round">home</span>
                                <span>Home</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item">
                                <span class="material-icons-round">help_outline</span>
                                <span>Help</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="dropdown-item">
                                <span class="material-icons-round">info</span>
                                <span>About</span>
                            </a>
                        </li>
                        <li class="dropdown-divider-small"></li>
                        <li>
                            <a href="{{ route('logout') }}" class="dropdown-item logout-item">
                                <span class="material-icons-round">logout</span>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="settings-layout">
            <div class="settings-container">
                <div class="settings-content">
                    <!-- LEFT COLUMN: USER INFORMATION -->
                    <div class="settings-column">
                        <h2 class="settings-subtitle">User Information</h2>

                        @if(session('success'))
                            <div class="success-message">
                                <span class="material-icons-round">check_circle</span>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="error-message">
                                <span class="material-icons-round">error</span>
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        <form class="settings-form" id="user-info-form" data-update-url="{{ route('settings.update-info') }}">
                            @csrf
                            <div class="form-row">
                                <label for="first_name">Name <span class="required">*</span></label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    placeholder="Enter name"
                                    value="{{ Auth::user()->first_name ?? '' }}"
                                    required
                                >
                            </div>

                            <div class="form-row">
                                <label for="last_name">Surname <span class="required">*</span></label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    placeholder="Enter surname"
                                    value="{{ Auth::user()->last_name ?? '' }}"
                                    required
                                >
                            </div>

                            <div class="form-row">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="Enter email address"
                                    value="{{ Auth::user()->email ?? '' }}"
                                    required
                                >
                            </div>

                            <div class="form-row">
                                <label for="height">Height</label>
                                <input
                                    type="number"
                                    id="height"
                                    name="height"
                                    placeholder="Enter height (cm)"
                                    value="{{ Auth::user()->height ?? '' }}"
                                    min="100"
                                    max="250"
                                    step="0.1"
                                >
                            </div>

                            <div class="form-row">
                                <label for="age">Age</label>
                                <input
                                    type="number"
                                    id="age"
                                    name="age"
                                    placeholder="Enter age"
                                    value="{{ Auth::user()->age ?? '' }}"
                                    min="18"
                                    max="120"
                                >
                            </div>

                            <button type="submit" class="primary-btn save-btn">
                                <span>Save</span>
                            </button>
                        </form>
                    </div>

                    <!-- RIGHT COLUMN: USER SETTINGS -->
                    <div class="settings-column">
                        <h2 class="settings-subtitle">User Settings</h2>

                        <button type="button" class="danger-btn reset-btn" id="reset-btn" data-reset-url="{{ route('settings.reset-data') }}">
                            <span>Reset Data</span>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
