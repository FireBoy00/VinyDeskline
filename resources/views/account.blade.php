<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/admin-pages.css'])
        @vite(['resources/css/dashboard.css'])
        
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

                        <form class="account-form">
                            <div class="form-row">
                                <label for="first_name">Name <span class="required">*</span></label>
                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    placeholder="Enter name"
                                >
                            </div>

                            <div class="form-row">
                                <label for="last_name">Surname <span class="required">*</span></label>
                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    placeholder="Enter surname"
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
                                >
                            </div>

                            <div class="form-row">
                                <label for="height">Height</label>
                                <input
                                    type="number"
                                    id="height"
                                    name="height"
                                    placeholder="Enter height (cm)"
                                >
                            </div>

                            <div class="form-row">
                                <label for="age">Age</label>
                                <input
                                    type="number"
                                    id="age"
                                    name="age"
                                    placeholder="Enter age"
                                >
                            </div>

                            <button type="button" class="primary-btn save-account-btn">
                                <span>Save Changes</span>
                                <span class="material-icons-round">check_circle</span>
                            </button>
                        </form>
                    </div>

                    <!-- RIGHT COLUMN: USER SETTINGS -->
                    <div class="account-column account-user-settings">
                        <div class="account-settings-header">
                            <h2 class="account-subtitle">User Settings</h2>
                            <button type="button" class="danger-btn reset-account-btn">
                                <span class="material-icons-round">delete_forever</span>
                                <span>Reset Data</span>
                            </button>
                        </div>

                        <p class="account-settings-placeholder">
                            Configure user settings here.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
