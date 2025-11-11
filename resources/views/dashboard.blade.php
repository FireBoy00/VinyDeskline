<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/dashboard.css'])

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
                        <a href="{{ route('admin') }}" class="nav-link active">
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
                        <a href="#" class="nav-link">
                            <span class="material-symbols-rounded nav-icon icon">schedule</span>
                            <span>Schedules</span>
                        </a>
                    </li>
                    <ul class="nav-links nav-bottom">
                                <li>
                                    <a href="#" class="nav-link nav-account">
                                        <span class="material-symbols-rounded nav-icon icon">account_circle</span>
                                        <span>Account</span>
                                    </a>
                                </li>
                    </ul>
                
                </ul>
                
            </div>
        </nav>
        <main class="dashboard" id="dashboard">
            <section class="section" id="overview">
                <h1 class="section-title">Overview</h1>
                <ul class="overview-cards">
                    <li class="overview-card connected">
                        <p class="statistic">150</p>
                        <h2>Total Desks</h2>
                    </li>
                    <li class="overview-card occupied">
                        <p class="statistic">120</p>
                        <h2>Occupied Desks</h2>
                    </li>
                    <li class="overview-card available">
                        <p class="statistic">30</p>
                        <h2>Available Desks</h2>
                    </li>
                    <li class="overview-card raised">
                        <p class="statistic">140</p>
                        <h2>Raised Desks</h2>
                    </li>
                    <li class="overview-card lowered">
                        <p class="statistic">10</p>
                        <h2>Lowered Desks</h2>
                    </li>
                    <li class="overview-card faulty">
                        <p class="statistic">5</p>
                        <h2>Faulty Desks</h2>
                    </li>
                </ul>
            </section>
            <section class="section" id="positions">
  <div class="positions-wrapper">
    <div class="position-box">
      <span class="position-label">Cleaning</span>
      <div class="position-badges">
        <span class="badge">in 3h</span>
        <span class="badge">17–19 pm</span>
      </div>
    </div>

    <div class="position-box">
      <span class="position-label">Uniform</span>
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
                            <span class="legend-dot unknown"></span>
                            <span class="legend-label">Unknown</span>
                        </div>
                    </div>
                </section>
                <section class="section" id="pie-graph">
                    <h1 class="section-title">Table positions</h1>

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
                                <span class="legend-dot unknown"></span>
                                <span class="legend-label">Unknown</span>
                            </div>
                        </div>
                    </div>
                </section>

                <script>
                    const xArray = [50,60,70,80,90,100,110,120,130,140,150];
                    const yArray = [7,8,8,9,9,9,10,11,14,14,15];

                    Plotly.newPlot("myPlot", [{
                        x: xArray,
                        y: yArray,
                        mode: "lines",
                        line: { color: '#004F6E' }
                    }], {
                        autosize: true,
                        xaxis: { title: "Square Meters" },
                        yaxis: { title: "Price in Millions" },
                        margin: { t: 20, b: 40, l: 60, r: 20 },
                        plot_bgcolor: 'transparent',
                        paper_bgcolor: 'transparent',
                        showlegend: false
                    }, {responsive: true});

                    const pieValues = [25, 25, 25, 25];
                    const pieLabels = ['Sitting', 'Standing', 'Cleaning', 'Unknown'];
                    const pieColors = ['#0485B9', '#004F6E', '#66B2D0', '#C6DAE2'];

                    Plotly.newPlot('piePlot', [{
                        values: pieValues,
                        labels: pieLabels,
                        type: 'pie',
                        marker: { colors: pieColors, line: { color: '#ffffff', width: 2 } },
                        hoverinfo: 'label+percent'
                    }], {
                        height: 300,
                        margin: { t: 20, b: 20, l: 20, r: 20 },
                        showlegend: false,
                        paper_bgcolor: 'transparent',
                        plot_bgcolor: 'transparent'
                    }, { responsive: true });
                </script>
            </div>
        </main>
    </body>
</html>