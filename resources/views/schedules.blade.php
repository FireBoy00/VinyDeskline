<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/admin-pages.css'])
        @vite(['resources/css/dashboard.css'])
        @vite(['resources/css/schedules.css'])
        <title>Schedules</title>
    </head>

    <body>
        <x-navbar active="schedules" />
        
        <main class="dashboard">

            <div class="schedule-section">

                <div class="box-title">UNIFORM</div>

                <div class="form-group1">
                    <span class="material-icons-round">desk</span>
                    <label class="form-label">Set new uniform time</label>
                </div>

                <div class="set-time-row">

                    <div class="big-card">
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input class="form-input" type="text">
                        </div>
                        <div class="form-group">

                            <label class="form-label">Time</label>
                            <input class="form-input time-input" type="text" placeholder="00:00 - 00:00">
                        </div>
                    </div>

                    <div class="grouping-radios">
                        <div class="form-group">
                            <label class="radio-group"><input type="radio">Every day</label>
                        </div>
                        <div class="form-group">
                            <span class="material-icons-round">calendar_today</span>
                            <label class="radio-group"><input type="radio">Once</label>
                        </div>
                        <button class="save-btn">SAVE</button>
                    </div>
                </div>

                <div class="current-tag">Regular</div>

                <div class="current-time-block">
                    <span class="material-icons-round">schedule</span>
                    <span>Current scheduled time</span>
                    <span class="background">Every day from 18:00-7:30</span>
                </div>

                <div class="box-title">CLEANING</div>

                <div class="form-group1">
                    <span class="material-icons-round">cleaning_services</span>
                    <label class="form-label">Set new cleaning time</label>
                </div>

                <div class="set-time-row">

                    <div class="big-card">
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input class="form-input" type="text">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Time</label>
                            <input class="form-input time-input" type="text" placeholder="00:00 - 00:00">
                        </div>
                    </div>

                    <div class="grouping-radios">
                        <div class="form-group">
                            <label class="radio-group">
                                <input type="radio" name="uniform"> Every day
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="radio-group">
                                <span class="material-icons-round">calendar_today</span>
                                <input type="radio" name="uniform"> Once
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="radio-group">
                                <span class="material-icons-round">calendar_today</span>
                                <input type="radio" name="uniform"> Multiple
                            </label>
                        </div>

                        <button class="save-btn">SAVE</button>
                    </div>
                </div>

                <div class="current-tag">Regular</div>

                <div class="current-time-block">
                    <span class="material-icons-round">schedule</span>
                    <span>Current scheduled time</span>
                    <span class="background">Every day from 18:00-7:30</span>
                </div>
            </div>

        </main>
    </body>
</html>
