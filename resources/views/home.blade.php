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
    </head>

    <body>  
        <header class="page-header">
            <h1 class="accent-title">
                <span>Viny</span><br>
                <span>Deskline</span>
            </h1>

            <img class="account-box" src="{{ asset('assets/user_main_page_icons/account_box.png') }}" alt="User Icon">
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
            <section class="card temp-card">
                <h2 class="accent-title"><span>Temperature</span></h2>

                <div class="temp-controls">
                    <button class="left_arrow-btn">←</button>
                    <div class="temp-value">19°C</div>
                    <button class="right_arrow-btn">→</button>
                </div>

                <div class="temp-pagination">
                    <span></span><span></span><span></span>
                </div>
            </section>

            <!-- TABLE -->
            <section class="card table-card">
                <h2 class="accent-title"><span>Your</span> <span>Table</span></h2>
                <img src="{{ asset('assets/user_main_page_icons/table.png') }}" alt="Work Desk Layout">
            </section>

            <!-- OPTIMAL POSITIONS -->
            <section class="card optimal-card">
                <div class="item">
                    <p class="accent"><span>Optimal</span> <span>Standing</span> <span>Position</span></p>
                    <div class="pos-group">
                        <button class="pos-btn">114 CM</button>
                        <img src="{{ asset('assets/user_main_page_icons/save.png') }}" alt="Save Icon" class="save-icon">
                    </div>
                </div>

                <div class="item">
                    <p class="accent"><span>Optimal</span> <span>Sitting</span> <span>Position</span></p>
                    <div class="pos-group">
                        <button class="pos-btn">70 CM</button>
                        <img src="{{ asset('assets/user_main_page_icons/save.png') }}" alt="Save Icon" class="save-icon">
                    </div>
                </div>
            </section>

            <!-- CUSTOM POSITIONS -->
            <section class="card custom-card">
                <h2 class="accent-title">
                    <span>Make</span> <span>Your</span> <span>Custom</span> <span>Positions</span>
                </h2>

                <div class="pos-row">
                    <input type="text" placeholder="Give it a name">
                    <input type="number" placeholder="Height">
                    <img src="{{ asset('assets/user_main_page_icons/save.png') }}" alt="Save Icon" class="save-icon">
                </div>

                <div class="pos-row">
                    <input type="text" placeholder="Give it a name">
                    <input type="number" placeholder="Height">
                    <img src="{{ asset('assets/user_main_page_icons/save.png') }}" alt="Save Icon" class="save-icon">
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
                        <span class="legend-dot lowered"></span>
                        <span class="legend-label">Lowered</span>
                    </div>
                </div>
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