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
        <div class="dashboard-container">
            <section class="section" id="overview">
                <h1 class="section-title">Overview</h1>
                <div class="overview-last-updated">
                    <span id="stat-last-updated">10s</span>
                    <svg id="refresh-icon" class="refresh-icon" xmlns="http://www.w3.org/2000/svg" width="14"
                        height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2" />
                    </svg>
                </div>
                <ul class="overview-cards">
                    <li class="overview-card connected">
                        <h2>Total Users</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-total-users">—</p>
                    </li>

                    <li class="overview-card connected">
                        <h2>Total Desks</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-total-desks">—</p>
                    </li>

                    <li class="overview-card idle">
                        <h2>Assigned</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-assigned">—</p>
                    </li>

                    <li class="overview-card sitting">
                        <h2>Sitting</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-sitting">—</p>
                    </li>

                    <li class="overview-card standing">
                        <h2>Standing</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-standing">—</p>
                    </li>

                    <li class="overview-card active">
                        <h2>Active</h2>
                        <div class="card-divider"></div>
                        <p class="statistic" id="stat-active">—</p>
                    </li>
                </ul>
            </section>
            <section class="section" id="positions">
                <div class="positions-wrapper">
                    <div class="position-box">
                        <span class="position-label">Cleaning Schedule</span>
                        <div class="position-badges">
                            <span class="badge" id="cleaning_date">--</span>
                            <span class="badge" id="cleaning_time">--</span>
                        </div>
                    </div>

                    <div class="position-box">
                        <span class="position-label">Uniform Schedule</span>
                        <div class="position-badges">
                            <span class="badge" id="uniform_date">--</span>
                            <span class="badge" id="uniform_time">--</span>
                        </div>
                    </div>
                </div>
            </section>
            <section class="section" id="timeline-card">
                <h1 class="section-title"><span>Sit</span>/<span>Stand</span> <span>Timeline</span></h1>
                <div class="plot-wrap">
                    <div id="timelinePlot"></div>
                </div>
            </section>
            <section class="graphs-row">
                <section class="section" id="standing-percentage-card">
                    <h1 class="section-title"><span>S</span>it/<span>Stand</span> Percentage</h1>
                    <div class="pie-wrap">
                        <div id="piePlot"></div>
                    </div>
                </section>

                <section class="section" id="desk-state-card">
                    <h1 class="section-title"><span>D</span>esk <span>S</span>tate <span>O</span>verview</h1>
                    <div class="pie-wrap">
                        <div id="deskStatePlot"></div>
                    </div>
                </section>
            </section>
            <section class="section" id="daily-usage-card">
                <h1 class="section-title">Daily Desk Usage Duration</h1>
                <div class="plot-wrap">
                    <div id="dailyUsagePlot"></div>
                </div>
            </section>
            <section class="section" id="environment-card">
                <h1 class="section-title">Environmental Data Overview</h1>

                <div class="chart-navigation" style="padding: 10px 20px 0;">
                    <button class="nav-button active" data-chart="tempPlot">Temperature</button>
                    <button class="nav-button" data-chart="lightPlot">Light</button>
                    <button class="nav-button" data-chart="humidityPlot">Humidity</button>
                </div>

                <div class="plot-container" style="height: 400px; padding: 20px;">
                    <div id="tempPlot" class="chart-plot active-chart"></div>
                    <div id="lightPlot" class="chart-plot hidden-chart"></div>
                    <div id="humidityPlot" class="chart-plot hidden-chart"></div>
                </div>
            </section>
        </div>
    </main>
</body>

</html>
