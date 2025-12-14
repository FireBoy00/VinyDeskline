<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>VinyDeskline</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/home.css', 'resources/js/home.js', 'resources/js/plotly.js'])
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
                                <a href="{{ route('settings') }}" class="dropdown-item">
                                    <span class="material-icons-round">settings</span>
                                    <span>Settings</span>
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

        <main class="layout">

            <!-- DAILY BRIEFING -->
            <section class="card briefing-card">
                <button class="card-help-btn" data-tooltip="View your daily briefing with important updates and information about your desk usage.">
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
                <button class="card-help-btn" data-tooltip="Browse through different sensor readings including temperature, humidity, and light levels.">
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
                <button class="card-help-btn" data-tooltip="Set and save your optimal standing and sitting desk heights for quick access.">
                    <span class="material-icons-round">help_outline</span>
                </button>
                <h2 class="accent-title"><span>Optimal</span> <span>Positions</span></h2>
                <div class="item">
                    <p class="accent"><span>Standing</span></p>
                    <div class="pos-group">
                        <button class="pos-btn">114 CM</button>
                        <i class="material-icons-round save-icon">save</i>
                    </div>
                </div>

                <div class="item">
                    <p class="accent"><span>Sitting</span></p>
                    <div class="pos-group">
                        <button class="pos-btn">70 CM</button>
                        <i class="material-icons-round save-icon">save</i>
                    </div>
                </div>
            </section>

            <!-- CUSTOM POSITIONS -->
            <section class="card custom-card">
                <button class="card-help-btn" data-tooltip="Create custom desk height presets with personalized names for different tasks or preferences.">
                    <span class="material-icons-round">help_outline</span>
                </button>
                <h2 class="accent-title"><span>Custom</span> <span>Positions</span></h2>
                <div class="pos-row">
                    <input type="text" placeholder="Give it a name">
                    <input type="number" placeholder="Height">
                    <i class="material-icons-round save-icon">save</i>
                </div>
                <div class="pos-row">
                    <input type="text" placeholder="Give it a name">
                    <input type="number" placeholder="Height">
                    <i class="material-icons-round save-icon">save</i>
                </div>
            </section>

            <!-- STATISTICS -->
            <section class="card stats-card">
                <button class="card-help-btn" data-tooltip="View your desk usage statistics and track time spent in different positions throughout the day.">
                    <span class="material-icons-round">help_outline</span>
                </button>
                <h2 class="accent-title"><span>Your</span> <span>Statistics</span></h2>

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
                    <div class="legend-item">
                        <span class="legend-dot cleaning"></span>
                        <span class="legend-label">Cleaning</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-dot lowered"></span>
                        <span class="legend-label">Uniform</span>
                    </div>
                </div>
            </section>

            <section class="card stats-card" style="grid-column: 1; grid-row: 9 / span 2;">
                <button class="card-help-btn" data-tooltip="Track the height of your desk over time, showing transitions between standing and sitting positions.">
                    <span class="material-icons-round">help_outline</span>
                </button>
                <h2 class="accent-title"><span>Desk</span> <span>Height</span> <span>Across</span> <span>One</span> <span>Day</span></h2>
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
