<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VinyDeskline</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/general-user.css'])
    @vite(['resources/css/home.css', 'resources/js/home.js', 'resources/js/plotly.js'])
    @vite(['resources/css/about.css'])
</head>

<body>
    <header class="page-header">
        <h1 class="accent-title">
            <span>Viny</span>
            <span>Deskline</span>
        </h1>

        <x-user-dropdown />
    </header>

    <main class="layout about-layout">

        <section class="card about-card">

            <h2 class="accent-title">
                <span>About</span>
                <span>Viny Deskline</span>
            </h2>

            <p class="about-tagline">
                Smart desk management for healthier, more ergonomic offices.
            </p>

            <div class="about-section">
                <h3>Project background & aim</h3>
                <p>
                    Viny Deskline is developed in collaboration with LINAK as part of the
                    <strong>“Distributed Software Systems with Embedded Elements”</strong> semester project
                    in the BSc Software Engineering programme at the University of Southern Denmark (Sønderborg).
                    The goal is to design a smart system that controls height-adjustable desks and supports
                    better ergonomics, cleaning workflows, and uniform desk arrangements.
                </p>
            </div>

            <div class="about-section">
                <h3>What Viny Deskline does</h3>
                <p>
                    Traditional desks restrict movement and are hard to adapt to different needs.
                    Viny Deskline combines a web application with an embedded controller to provide:
                </p>
                <ul>
                    <li><strong>Cleaning support</strong> – temporarily raising desks to make cleaning easier.</li>
                    <li><strong>Ergonomic assistance</strong> – storing user height profiles and suggesting when to sit
                        or stand.</li>
                    <li><strong>Uniform desk arrangement</strong> – aligning multiple desks to the same height when
                        needed.</li>
                </ul>
            </div>

            <div class="about-section">
                <h3>High-level design & tech stack</h3>
                <ul>
                    <li><strong>Frontend:</strong> Blade templates with Vite-powered JavaScript and custom CSS.</li>
                    <li><strong>Backend:</strong> Laravel (PHP) web application exposing routes for users and admins.
                    </li>
                    <li><strong>Database:</strong> SQLite/MySQL with Eloquent ORM for users, desks, and usage data.</li>
                    <li><strong>Embedded & tools:</strong> Pico-based desk controller, GitHub, DevDb, Docker, Figma,
                        Discord.</li>
                </ul>
            </div>

            <div class="about-section">
                <h3>Project information</h3>
                <p>
                    This system is a third-semester project deliverable for
                    <strong>SE3-PRO-3: Semester Project 3 – Distributed Software Systems with Embedded
                        Elements</strong>.
                    The project demonstrates a distributed architecture with an embedded element, analytics dashboard,
                    and a web-based control interface for LINAK desks.
                </p>
            </div>

            <div class="about-section">
                <h3>Developer team</h3>
                <p>
                    <strong>Group 14 – Viny Deskline</strong><br>
                    Adrian Cristian Stancu<br>
                    Aleksandra Kwiatkowska<br>
                    Dorina Petra Nagy<br>
                    Gabija Staskeviciute<br>
                    Jakub Cuninka<br>
                    Tomass Zarins
                </p>
            </div>

        </section>

    </main>

</body>

</html>
