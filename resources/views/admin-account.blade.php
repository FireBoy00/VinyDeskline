<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/general-admin.css'])
        @vite(['resources/css/admin-account.css', 'resources/js/admin-account.js'])
        
        <title>VinyDeskline - Account Settings</title>
    </head>
    <body>
        <x-navbar active="account" />
        
        <main class="dashboard">
            <section class="section account-header">
                <h1 class="section-title"><span>A</span>ccount Settings</h1>
            </section>

            <section class="section account-section">

                <div class="account-layout">
                    <!-- LEFT COLUMN: USER INFORMATION -->
                    <div class="account-column account-user-info">
                        <h2 class="account-subtitle">User Information</h2>

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

                        <form class="account-form" id="user-info-form" data-update-url="{{ route('admin.account.update-info') }}">
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

                            <button type="submit" class="primary-btn save-account-btn">
                                <span>Save Changes</span>
                                <span class="material-icons-round">check_circle</span>
                            </button>
                        </form>
                    </div>

                    <!-- RIGHT COLUMN: USER SETTINGS -->
                    <div class="account-column account-user-settings">
                        <h2 class="account-subtitle">User Settings</h2>

                        <form class="account-form" id="user-settings-form" data-update-url="{{ route('admin.account.update-settings') }}">
                            @csrf
                            <div class="form-row">
                                <label for="height">Height (cm)</label>
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

                            <div class="form-actions">
                                <button type="submit" class="primary-btn save-settings-btn">
                                    <span>Save Settings</span>
                                    <span class="material-icons-round">check_circle</span>
                                </button>
                                <button type="button" class="danger-btn reset-account-btn" id="reset-btn" data-reset-url="{{ route('admin.account.reset-settings') }}">
                                    <span class="material-icons-round">delete_forever</span>
                                    <span>Reset Data</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
