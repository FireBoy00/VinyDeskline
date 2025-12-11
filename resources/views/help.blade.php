<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VinyDeskline</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/general-user.css'])
    @vite(['resources/css/about.css']) {{-- reuse about layout/styles --}}
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
                <span>Help &</span>
                <span>User Guide</span>
            </h2>

            <p class="about-tagline">
                A quick guide to using Viny Deskline as an employee or administrator.
            </p>

            {{-- 1. Getting started --}}
            <div class="about-section">
                <h3>1. Getting started</h3>
                <ul>
                    <li><strong>Log in:</strong> Use your assigned Viny Deskline account (email and password).</li>
                    <li><strong>Personalization:</strong> If prompted, enter your height so the system can suggest
                        ergonomic sit/stand heights.</li>
                    <li><strong>Skip personalization:</strong> You can skip it, but recommendations will be less
                        accurate.</li>
                </ul>
            </div>

            {{-- 2. Home page overview --}}
            <div class="about-section">
                <h3>2. Home page overview</h3>
                <ul>
                    <li><strong>Daily Briefing:</strong> Short messages and updates about your desk usage or office
                        information.</li>
                    <li><strong>Temperature carousel:</strong> Browse sensor readings (e.g. temperature) using the
                        left/right arrows.</li>
                    <li><strong>Your Table:</strong> Visual representation of your desk.</li>
                    <li><strong>Optimal Positions:</strong> Suggested standing and sitting heights based on your
                        profile.</li>
                    <li><strong>Custom Positions:</strong> Create and save your own named height presets.</li>
                    <li><strong>Your Statistics:</strong> Graph showing how your desk has been used (sitting, standing,
                        cleaning, lowered).</li>
                    <li><strong>Feedback:</strong> Simple indicator based on your recent desk usage.</li>
                </ul>
            </div>

            {{-- 3. Desk behaviour & schedules --}}
            <div class="about-section">
                <h3>3. Desk behaviour & schedules</h3>
                <ul>
                    <li><strong>Cleaning schedule:</strong> At configured times, desks can be raised to a cleaning
                        height to support cleaning staff.</li>
                    <li><strong>Uniform schedule:</strong> Admins can align multiple desks to the same height for
                        specific periods.</li>
                    <li><strong>Manual control:</strong> You can still use the physical desk controls; the system will
                        update its state based on the controller.</li>
                </ul>
            </div>

            {{-- 4. Admin features --}}
            <div class="about-section">
                <h3>4. Admin features (for administrators)</h3>
                <ul>
                    <li><strong>Admin Dashboard:</strong> Overview of total, occupied, available, raised, lowered, and
                        faulty desks.</li>
                    <li><strong>Desk Management:</strong> Inspect individual desks and their current state.</li>
                    <li><strong>Schedules:</strong> Configure and manage cleaning and uniform height schedules.</li>
                </ul>
            </div>

            {{-- 5. FAQ --}}
            <div class="about-section">
                <h3>5. FAQ</h3>
                <ul>
                    <li>
                        <strong>Why can’t I access the Admin Dashboard?</strong><br>
                        Only users with admin permissions can access admin pages. Regular users will see a 403
                        (Forbidden) page.
                    </li>
                    <li>
                        <strong>Why do I keep seeing the personalization page?</strong><br>
                        Your account is marked as needing personalization. Complete the form once or choose “Skip” to go
                        directly to the home page.
                    </li>
                    <li>
                        <strong>My desk height or status looks wrong — what should I do?</strong><br>
                        Ensure the desk controller is powered and connected. If it still looks incorrect, contact an
                        administrator.
                    </li>
                </ul>
            </div>

            {{-- 6. Troubleshooting --}}
            <div class="about-section">
                <h3>6. Troubleshooting</h3>
                <ul>
                    <li>Refresh the page.</li>
                    <li>Log out and log back in.</li>
                    <li>Try another browser if possible.</li>
                    <li>Check that the desk controller is powered and connected.</li>
                    <li>Contact your system administrator if the issue continues.</li>
                </ul>
            </div>

            {{-- 7. Contact --}}
            <div class="about-section">
                <h3>Need more help?</h3>
                <p>
                    For further assistance, please contact your system administrator or project supervisor.
                </p>
            </div>

        </section>

    </main>
</body>

</html>
