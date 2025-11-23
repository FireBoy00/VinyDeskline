<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/admin-pages.css'])
        @vite(['resources/css/dashboard.css'])
        @vite(['resources/css/schedules.css'])
        @vite(['resources/css/schedules.js'])

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

        <title>Schedules</title>
    </head>

    <body>
        <x-navbar active="schedules" />
        
        <main class="dashboard">
            <section class="section schedule-header">
                <h1 class="section-title"><span>S</span>chedule Management</h1>
            </section>

            <div class="schedules-container">
                <!-- Uniform Schedule Card -->
                <section class="section schedule-card">
                    <div class="schedule-card-header">
                        <div class="schedule-icon-wrapper uniform-icon">
                            <span class="material-icons-round">desk</span>
                        </div>
                        <h2 class="schedule-card-title">Uniform Schedule</h2>
                    </div>

                    <div class="schedule-form">
                        <div class="form-row">
                            <div class="input-group">
                                <label class="input-label" for="uniform-title">Title</label>
                                <input type="text" class="schedule-input" id="uniform-title" placeholder="Enter title">
                            </div>
                            <div class="input-group">
                                <label class="input-label" for="uniform-time-range">Time Range</label>
                                <div class="time-range-inputs">
                                    <input type="time" class="schedule-input" name="start_time" id="uniformStart" aria-label="Start time">
                                    <span style="margin: 0 8px;">-</span>
                                    <input type="time" class="schedule-input" name="end_time" id="uniformEnd" aria-label="End time">
                                </div>
                            </div>
                            <div id="uniformDateContainer" style="display: none;">
                                        <input type="text" id="uniformDate" class="schedule-input" placeholder="Select date">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="uniform-frequency" value="daily" checked>
                                    <span class="radio-label">Every day</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="uniform-frequency" value="once">
                                    <span class="material-icons-round radio-icon">calendar_today</span>
                                    <span class="radio-label">Once</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="uniform-frequency" value="multiple">
                                    <span class="material-icons-round radio-icon">calendar_today</span>
                                    <span class="radio-label">Multiple</span>
                                </label>
                            </div>
                        </div>
                        <button type="button" class="schedule-save-btn" id="saveUniformBtn">
                            <span>Save Schedule</span>
                            <span class="material-icons-round">check_circle</span>
                        </button>
                    </div>
                    <div id="uniformList"></div>
                </section>


                <template id="scheduleTemplate">
                        <div class="current-schedule">
                           <!-- <div class="current-schedule-badge">Regular</div> -->
                            <div class="current-schedule-info">
                                <span class="material-icons-round">schedule</span>
                                <span class="schedule-label">Title:</span>
                                <span class="schedule-date">Date:</span>
                                <span class="schedule-time">Time:</span>  
                            </div>
                            <button class="delete-btn material-icons-round">delete</button>
                        </div>
                    </template>


                <!-- Cleaning Schedule Card -->
                <section class="section schedule-card">
                    <div class="schedule-card-header">
                        <div class="schedule-icon-wrapper cleaning-icon">
                            <span class="material-icons-round">cleaning_services</span>
                        </div>
                        <h2 class="schedule-card-title">Cleaning Schedule</h2>
                    </div>

                    <div class="schedule-form">
                        <div class="form-row">
                            <div class="input-group">
                                <label class="input-label">Title</label>
                                <input type="text" class="schedule-input" id="cleaning-title" placeholder="Enter title">
                            </div>
                            <div class="input-group">
                                <label class="input-label" for="uniform-time-range">Time Range</label>
                                <div class="time-range-inputs">
                                    <input type="time" class="schedule-input" id="cleaningStart" name="start_time" aria-label="Start time">
                                    <span style="margin: 0 8px;">-</span>
                                    <input type="time" class="schedule-input" name="end_time" id="cleaningEnd" aria-label="End time">
                                </div>
                            </div>
                            <div id="cleaningDateContainer" style="display: none;">
                                        <input type="text" id="cleaningDate" class="schedule-input" placeholder="Select date">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="radio-options">
                                <label class="radio-option">
                                    <input type="radio" name="cleaning-frequency" value="daily" checked>
                                    <span class="radio-label">Every day</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="cleaning-frequency" value="once">
                                    <span class="material-icons-round radio-icon">calendar_today</span>
                                    <span class="radio-label">Once</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="cleaning-frequency" value="multiple">
                                    <span class="material-icons-round radio-icon">calendar_today</span>
                                    <span class="radio-label">Multiple</span>
                                </label> 
                            </div>
                        </div>

                        <button type="button" class="schedule-save-btn" id="saveCleaningBtn">
                            <span>Save Schedule</span>
                            <span class="material-icons-round">check_circle</span>
                        </button>
                    </div>

                    <div id="cleaningList"></div>
                </section>
            </div>
        </main>
    </body>
</html>
