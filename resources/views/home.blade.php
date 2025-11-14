<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VinyDeskline</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/home.css', 'resources/js/home.js'])
    
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
</head>

<body>
<header class="page-header">
    <h1 class="accent-title">
        <span>Viny</span><br>
        <span>Deskline</span>
    </h1>

    <i class="material-icons account-box">account_box</i>
</header>

<main class="layout">

            <!-- DAILY BRIEFING -->
    <section class="card briefing-card">
        <h2 class="accent-title"><span>Daily</span> <span>Briefing</span></h2>

        <div class="brief-row">
            <span class="brief-text">
                This is lalalallalalalallalalalalalalalalalalalalalalalalalalalalallalalallalalalaalallalalalallallalal.
                This is lalalallalalalallalalalalalalalalalalalalalalalalalalalallalalallalalalaalallalalalallallalal.
            </span>
        </div>
    </section>

    <!-- TEMPERATURE CARD -->
    <section class="card carousel-card">
        <h2 class="accent-title"><span id="sensor-title">Temperature</span></h2>
        <div class="carousel-controls">
            <i class="material-icons chevron-btn" id="prev-btn">chevron_left</i>
            <div class="carousel-value" id="sensor-value">19°C</div>
            <i class="material-icons chevron-btn" id="next-btn">chevron_right</i>
        </div>
        
        <div id="pagination-dots" class="carousel-pagination"></div>
    </section>

            <!-- TABLE -->
    <section class="card table-card">
        <h2 class="accent-title"><span>Your</span> <span>Table</span></h2>
                <img src="{{ asset('assets/user_main_page_icons/table.png') }}" alt="Work Desk Layout">
    </section>

            <!-- OPTIMAL POSITIONS -->
    <section class="card optimal-card">
        <h2 class="accent-title"><span>Optimal</span> <span>Positions</span></h2>
        <div class="item">
            <p class="accent"><span>Standing</span></p>
            <div class="pos-group">
                <button class="pos-btn">114 CM</button>
                <i class="material-icons save-icon">save</i>
            </div>
        </div>

        <div class="item">
            <p class="accent"><span>Sitting</span></p>
            <div class="pos-group">
                <button class="pos-btn">70 CM</button>
                <i class="material-icons save-icon">save</i>
            </div>
        </div>
    </section>

            <!-- CUSTOM POSITIONS -->
    <section class="card custom-card">
        <h2 class="accent-title"><span>Custom</span> <span>Positions</span></h2>
        <div class="pos-row">
            <input type="text" placeholder="Give it a name">
            <input type="number" placeholder="Height">
            <i class="material-icons save-icon">save</i>
        </div>
        <div class="pos-row">
            <input type="text" placeholder="Give it a name">
            <input type="number" placeholder="Height">
            <i class="material-icons save-icon">save</i>
        </div>
    </section>

            <!-- STATISTICS -->
    <section class="card stats-card">
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
                <span class="legend-dot unknown"></span>
                <span class="legend-label">Unknown</span>
            </div>
        </div>

        <script>
            const xArray = [50,60,70,80,90,100,110,120,130,140,150];
            const yArray = [7,8,8,9,9,9,10,11,14,14,15];

            Plotly.newPlot("myPlot", [{
                x: xArray,
                y: yArray,
                mode: "lines",
                line: { color: '#004F6E' }
            }], {
                xaxis: { title: "Square Meters" },
                yaxis: { title: "Price in Millions" },
                margin: { t: 20, b: 40, l: 60, r: 20 },
                plot_bgcolor: 'transparent',
                paper_bgcolor: 'transparent',
                showlegend: false
            });
        </script>
    </section>

            <!-- FEEDBACK -->
    <section class="card feedback-card">
        <h2 class="accent-title"><span>Feedback</span></h2>
        <div class="feedback-dot"></div>
        <div class="feedback-dot"></div>
        <div class="feedback-dot"></div>
    </section>
</main>

        <!-- FOOTER -->
<footer class="page-footer">
    <h1 class="accent-title">
        <span>Viny</span><br>
        <span>Deskline</span>
    </h1>

    <h3 class="accent-title">
        <span>Admins</span><br>
        <span>Developers</span>
    </h3>

    <h3 class="accent-title">
        <span>Features</span>
    </h3>

    <h3 class="accent-title">
        <span>Support</span>
    </h3>
</footer>
</body>
</html>