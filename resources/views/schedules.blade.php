<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/dashboard.css'])
    @vite(['resources/css/schedules.css']) 
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <title>Schedules</title>
</head>

<body style="background-color: var(--bg-page);">

<nav class="navbar" id="navbar">
    <h1 class="nav-logo">
        <a href="{{ route('home') }}">
            <p><span>V</span>iny</p>
            <p><span>D</span>eskline</p>
        </a>
    </h1>

    <div class="nav-sections">
        <ul class="nav-links">

            <li>
                <a href="{{ route('admin') }}" class="nav-link">
                    <span class="material-symbols-rounded nav-icon icon">dashboard</span>
                    <span>Overall Statistics</span>
                </a>
            </li>

            <li>
                <a href="#" class="nav-link">
                    <span class="material-symbols-rounded nav-icon icon">desk</span>
                    <span>Desk Arrangement</span>
                </a>
            </li>

            <li>
                <a href="{{ route('schedules') }}" class="nav-link active">
                    <span class="material-symbols-rounded nav-icon icon">schedule</span>
                    <span>Schedules</span>
                </a>
            </li>

            <ul class="nav-links nav-bottom">
                <li>
                    <a href="#" class="nav-link nav-account">
                        <span class="material-symbols-rounded nav-icon icon">account_circle</span>
                        <span>John Doe</span>
                    </a>
                </li>
            </ul>

        </ul>
    </div>
</nav>
    <main class="dashboard">

        <div class="schedule-section">

                <div class="box-title">UNIFORM</div>

                <div class="form-group1">
                    <span class="material-symbols-outlined">desk</span>
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
                            <span class="material-symbols-outlined">calendar_today</span>
                            <label class="radio-group"><input type="radio">Once</label>
                        </div>
                        <button class="save-btn">SAVE</button>
                    </div>
                </div>

                <div class="current-tag">Regular</div>

                <div class="current-time-block">
                    <span class="material-symbols-outlined">schedule</span>
                    <span>Current scheduled time</span>
                    <span class="background">Every day from 18:00-7:30</span>
                </div>

                <div class="box-title">CLEANING</div>

                <div class="form-group1">
                        <span class="material-symbols-outlined">cleaning_services</span>
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
                            <span class="material-symbols-outlined">calendar_today</span>
                            <input type="radio" name="uniform"> Once
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="radio-group">
                            <span class="material-symbols-outlined">calendar_today</span>
                            <input type="radio" name="uniform"> Multiple
                        </label>
                    </div>

                <button class="save-btn">SAVE</button></div></div>

                <div class="current-tag">Regular</div>

                <div class="current-time-block">
                    <span class="material-symbols-outlined">schedule</span>
                    <span>Current scheduled time</span>
                    <span class="background">Every day from 18:00-7:30</span>
                </div>
        </div>

    </main>
</body>
</html>
