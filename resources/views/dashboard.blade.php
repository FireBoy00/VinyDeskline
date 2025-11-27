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
                <div class="overview-header">
                    <h1 class="section-title">Overview</h1>
                    <div class="overview-last-updated" id="stat-last-updated">Last updated: —</div>
                </div>
                <ul class="overview-cards">
                    <li class="overview-card connected">
                        <h2>Total Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-total">—</p>
                    </li>
                    <li class="overview-card occupied">
                        <h2>Occupied Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-occupied">—</p>
                    </li>
                    <li class="overview-card available">
                        <h2>Available Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-available">—</p>
                    </li>
                    <li class="overview-card raised">
                        <h2>Raised Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-raised">—</p>
                    </li>
                    <li class="overview-card lowered">
                        <h2>Lowered Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-lowered">—</p>
                    </li>
                    <li class="overview-card faulty">
                        <h2>Faulty Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-faulty">—</p>
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