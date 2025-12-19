<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="desk-id" content="{{ $user->desk_id ?? '' }}">
    <title>VinyDeskline</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/general-user.css'])
    @vite(['resources/css/home.css', 'resources/js/home.js', 'resources/js/plotly.js'])
</head>

<body>
    <header class="page-header">
        <h1 class="accent-title">
            <span>Viny</span>
            <span>Deskline</span>
        </h1>

        <x-user-dropdown />
    </header>

    <main class="layout">

        <!-- DAILY BRIEFING -->
        <section class="card briefing-card">
            <button class="card-help-btn"
                data-tooltip="View your daily briefing with important updates and information about your desk usage.">
                <span class="material-icons-round">help_outline</span>
            </button>
            <h2 class="accent-title"><span>Daily</span> <span>Briefing</span></h2>

            <div class="brief-row">
                <span class="brief-text">
                    This is
                    lalalallalalalallalalalalalalalalalalalalalalalalalalalalallalalallalalalaalallalalalallallalal.
                    This is
                    lalalallalalalallalalalalalalalalalalalalalalalalalalalallalalallalalalaalallalalalallallalal.
                </span>
            </div>
        </section>

        <!-- TEMPERATURE CARD -->
        <section class="card carousel-card">
            <button class="card-help-btn"
                data-tooltip="Browse through different sensor readings including temperature, humidity, and light levels.">
                <span class="material-icons-round">help_outline</span>
            </button>
            <h2 class="accent-title"><span id="sensor-title">Temperature</span></h2>
            <div class="carousel-controls">
                <i class="material-icons-round chevron-btn" id="prev-btn">chevron_left</i>
                <div class="carousel-value" id="sensor-value">19°C</div>
                <i class="material-icons-round chevron-btn" id="next-btn">chevron_right</i>
            </div>

            <div id="pagination-dots" class="carousel-pagination"></div>
        </section>

        <!-- TABLE -->
        <section class="card table-card">
            <button class="card-help-btn" data-tooltip="Visual representation of your desk.">
                <span class="material-icons-round">help_outline</span>
            </button>
            <h2 class="accent-title"><span>Your</span> <span>Table</span></h2>
            <img src="{{ asset('assets/user_main_page_icons/table.png') }}" alt="Work Desk Layout">
        </section>

        <!-- OPTIMAL POSITIONS -->
        <section class="card optimal-card">
            <button class="card-help-btn"
                data-tooltip="Set and save your optimal standing and sitting desk heights for quick access.">
                <span class="material-icons-round">help_outline</span>
            </button>
            <h2 class="accent-title"><span>Optimal</span> <span>Positions</span></h2>
            <div class="item">
                <p class="accent"><span>Standing</span></p>
                <div class="pos-group">
                    <button class="pos-btn"
                        data-height={{ $user->optimal_standing_height }}>{{ $user->optimal_standing_height ?? '—' }}
                        CM</button>
                    <i class="material-icons-round save-icon" data-height={{ $user->optimal_standing_height }}>save</i>
                </div>
            </div>

            <div class="item">
                <p class="accent"><span>Sitting</span></p>
                <div class="pos-group">
                    <button class="pos-btn"
                        data-height={{ $user->optimal_sitting_height }}>{{ $user->optimal_sitting_height ?? '—' }}
                        CM</button>
                    <i class="material-icons-round save-icon" data-height={{ $user->optimal_sitting_height }}>save</i>
                </div>
            </div>
        </section>

        <!-- CUSTOM POSITIONS -->
        <section class="card custom-card">
            <button class="card-help-btn"
                data-tooltip="Create custom desk height presets with personalized names for different tasks or preferences.">
                <span class="material-icons-round">help_outline</span>
            </button>
            <h2 class="accent-title"><span>Custom</span> <span>Positions</span></h2>
            <form class="pos-row">
                <input type="text" class="custom-name" placeholder= 'Give it a name'
                    value={{ $user->custom_name_1 }}>
                <input type="number" class="custom-height" placeholder='Height in cm'
                    value={{ $user->custom_height_1 / 10 ? $user->custom_height_1 / 10 : '' }}>
                <i data-position="1" class="material-icons-round save-icon"
                    data-height={{ $user->custom_height_1 }}>save</i>
            </form>
            <form class="pos-row">
                <input type="text" class="custom-name" placeholder="Give it a name"
                    value={{ $user->custom_name_2 }}>
                <input type="number" class="custom-height" placeholder='Height in cm'
                    value={{ $user->custom_height_2 / 10 ? $user->custom_height_2 / 10 : '' }}>
                <i data-position="2" class="material-icons-round save-icon"
                    data-height={{ $user->custom_height_2 }}>save</i>
            </form>
        </section>

        <!-- STATISTICS -->
        <section class="card stats-card">
            <button class="card-help-btn"
                data-tooltip="Track your daily sitting and standing time to maintain a healthy desk posture balance.">
                <span class="material-icons-round">help_outline</span>
            </button>
            <h2 class="accent-title"><span>Weekly</span> <span>Sit</span> <span>vs</span> <span>Stand</span>
                <span>(7d)</span>
            </h2>

            <div id="myPlot"></div>

            <div class="stats-legend">
                <div class="legend-item">
                    <span class="legend-dot sitting"></span>
                    <span class="legend-label">Sitting</span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot standing"></span>
                    <span class="legend-label">Standing</span>
                </div>
            </div>
        </section>

        <section class="card stats-card" style="grid-column: 1; grid-row: 9 / span 2;">
            <button class="card-help-btn"
                data-tooltip="Track the height of your desk over time, showing transitions between standing and sitting positions.">
                <span class="material-icons-round">help_outline</span>
            </button>
            <h2 class="accent-title"><span>Desk</span> <span>Height</span> <span>Across</span> <span>One</span>
                <span>Day</span>
            </h2>
            <div id="heightPlot"></div>
        </section>

        <!-- FEEDBACK -->
        <section class="card feedback-card">
            <button class="card-help-btn" data-tooltip="Get a feedback recommendation based on your desk usage.">
                <span class="material-icons-round">help_outline</span>
            </button>
            <h2 class="accent-title"><span>Feedback</span></h2>
            <div class="feedback-dot"></div>
            <div class="feedback-dot"></div>
            <div class="feedback-dot"></div>
        </section>
    </main>
</body>

</html>
