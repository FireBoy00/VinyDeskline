<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])

        <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
        <title>VinyDeskline</title>
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
                        <a href="#overall-statistics" class="nav-link active" data-target="overall">
                            <span class="material-symbols-rounded nav-icon icon">dashboard</span>
                            <span class="nav-text">Overall Statistics</span>
                        </a>
                    </li>
                    <li>
                        <a href="#arrangement" class="nav-link" data-target="arrangement">
                            <span class="material-symbols-rounded nav-icon icon">desk</span>
                            <span class="nav-text">Desk Arrangement</span>
                        </a>
                    </li>
                    <li>
                        <a href="#schedules" class="nav-link" data-target="schedules">
                            <span class="material-symbols-rounded nav-icon icon">schedule</span>
                            <span class="nav-text">Schedules</span>
                        </a>
                    </li>
                </ul>

                <ul class="nav-links nav-bottom">
                    <li>
                        <a href="#account" class="nav-link nav-account" data-target="account">
                            <span class="material-symbols-rounded nav-icon icon">account_circle</span>
                            <span class="nav-text">John Doe</span>
                        </a>
                    </li>
                </ul>
                
            </div>
        </nav>
        <main class="dashboard" id="dashboard">
            <div id="overall-container">
            <section class="section" id="overview">
                <h1 class="section-title">Overview</h1>
                <ul class="overview-cards">
                    <li class="overview-card connected">
                        <h2>Total Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic">150</p>
                    </li>
                    <li class="overview-card occupied">
                        <h2>Occupied Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic">120</p>
                    </li>
                    <li class="overview-card available">
                        <h2>Available Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic">30</p>
                    </li>
                    <li class="overview-card raised">
                        <h2>Raised Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic">140</p>
                    </li>
                    <li class="overview-card lowered">
                        <h2>Lowered Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic">10</p>
                    </li>
                    <li class="overview-card faulty">
                        <h2>Faulty Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic">5</p>
                    </li>
                </ul>
            </section>
            <section class="section" id="positions">
                <div class="positions-wrapper">
                    <div class="position-box">
                        <span class="position-label">Cleaning Schedule</span>
                        <div class="position-badges">
                            <span class="badge">in 3h</span>
                            <span class="badge">17–19 pm</span>
                        </div>
                    </div>

                    <div class="position-box">
                        <span class="position-label">Uniform Schedule</span>
                        <div class="position-badges">
                            <span class="badge">in 3h</span>
                            <span class="badge">17–19 pm</span>
                        </div>
                    </div>
                </div>
            </section>

            <div class="graphs-row">
                <section class="section" id="line-graph">
                    <h1 class="section-title"><span>Overall</span> <span>Statistics</span></h1>

                    <div class="plot-wrap">
                        <div id="myPlot"></div>
                    </div>
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
                            <span class="legend-label">Lowered</span>
                        </div>
                    </div>
                </section>
                <section class="section" id="pie-graph">
                    <h1 class="section-title"><p><span>T</span>able positions</p> </h1>

                    <div class="pie-wrap">
                        <div id="piePlot"></div>

                        <div class="pie-legend card-legend">
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
                                <span class="legend-label">Lowered</span>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
            </div>

            <!-- Desk Arrangement section (hidden by default) -->
            <section id="arrangement" style="display: none;">
                <section class="section">
                  <h1 class="section-title">Desk Arrangement</h1>  
                </section>
            </section>

            <!-- Schedules section (hidden by default) -->
            <section  id="schedules" style="display: none;">
                <section class="section">
                  <h1 class="section-title">Schedules</h1>  
                </section>
            </section>

            <!-- Account section (hidden by default) -->
            <section id="account" style="display: none;">
                <section class="section">
                  <h1 class="section-title">Account</h1>  
                </section>
                
            </section>
        </main>
    </body>
</html>