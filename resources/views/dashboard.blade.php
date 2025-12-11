<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/general-admin.css', 'resources/js/plotly.js'])
        @vite(['resources/css/dashboard.css', 'resources/js/dashboard.js'])
        
        <title>VinyDeskline</title>
    </head>
    <body>
        <x-navbar active="dashboard" />
        
        <main class="dashboard" id="dashboard">
            <section class="section" id="overview">
                <h1 class="section-title">Overview</h1>
                <div class="overview-last-updated">
                    <span id="stat-last-updated">10s</span>
                    <svg id="refresh-icon" class="refresh-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                    </svg>
                </div>
                <ul class="overview-cards">
                        <li class="overview-card connected">
                            <h2>Total Users</h2>
                            <div class="card-divider"></div>
                            <p class="statistic" id="stat-total">—</p>
                        </li>

                        <li class="overview-card sitting">
                            <h2>Lowered</h2>
                            <div class="card-divider"></div>
                            <p class="statistic" id="stat-seated">—</p>
                        </li>

                        <li class="overview-card standing">
                            <h2>Raised</h2>
                            <div class="card-divider"></div>
                            <p class="statistic" id="stat-standing">—</p>
                        </li>

                        <li class="overview-card active">
                            <h2>Active</h2>
                            <div class="card-divider"></div>
                            <p class="statistic" id="stat-active">—</p>
                        </li>

                        <li class="overview-card cleaning">
                            <h2>Cleaning</h2>
                            <div class="card-divider"></div>
                            <p class="statistic" id="stat-cleaning">—</p>
                        </li>

                        <li class="overview-card idle">
                            <h2>Idle</h2>
                            <div class="card-divider"></div>
                            <p class="statistic" id="stat-idle">—</p>
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
                    <h1 class="section-title"><span>T</span>able positions</h1>

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
        </main>
    </body>
</html>