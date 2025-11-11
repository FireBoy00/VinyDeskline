<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/dashboard.css'])
        
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
        </main>
    </body>
</html>