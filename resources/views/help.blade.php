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
                    <li><strong>Automated Schedules:</strong> Administrators can configure two types of schedules to
                        control desk behavior during office hours:
                        <ul style="margin-top: 8px; margin-left: 20px;">
                            <li><strong>Cleaning Schedule:</strong> Automatically raises desks to a designated cleaning
                                height at configured times to assist cleaning staff.</li>
                            <li><strong>Uniform Schedule:</strong> Aligns multiple desks to the same height during
                                specified periods, which can be set to repeat daily or occur once.</li>
                        </ul>
                    </li>
                    <li><strong>Manual Override:</strong> Users can always use the physical desk controls to manually
                        adjust desk height at any time. The system will automatically update to reflect the controller's
                        current position.</li>
                </ul>
            </div>

            {{-- 4. Admin features --}}
            <div class="about-section">
                <h3>4. Admin features (for administrators)</h3>
                <ul>
                    <li><strong>Dashboard:</strong> Real-time overview of system statistics including total users, total
                        desks, assigned desks, and desks in different states (sitting, standing, active). Displays next
                        scheduled cleaning and uniform schedule events.</li>
                    <li><strong>Desk Management:</strong> View all desks with their current status. Select and perform
                        bulk actions on desks (assign users, mark available, mark for cleaning, mark as faulty). Inspect
                        individual desk details and metrics.</li>
                    <li><strong>Schedule Management:</strong> Configure and manage two types of schedules:
                        <ul style="margin-top: 8px; margin-left: 20px;">
                            <li><strong>Cleaning Schedule:</strong> Set specific times when desks automatically raise to
                                cleaning height to support cleaning staff.</li>
                            <li><strong>Uniform Schedule:</strong> Align multiple desks to the same height for specific
                                periods (daily or one-time).</li>
                        </ul>
                    </li>
                    <li><strong>User Management:</strong> Create, edit, and delete user accounts. Assign and unassign
                        desks to users.</li>
                    <li><strong>Office Management:</strong> Manage office structure including floors, rooms, and desk
                        locations within the office layout.</li>
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
                        <strong>What admin features are available?</strong><br>
                        Admins have access to a Dashboard (overview statistics), Desk Management (view and manage
                        desks), Schedule Management (create cleaning and uniform schedules), User Management (manage
                        user accounts), and Office Management (manage floors, rooms, and desk locations).
                    </li>
                    <li>
                        <strong>How do I set up a cleaning schedule?</strong><br>
                        Go to Admin > Schedules, then create a new Cleaning Schedule. Specify the time and cleaning
                        height. You can set it to repeat daily or run once on a specific date.
                    </li>
                    <li>
                        <strong>Can I assign multiple desks to a user?</strong><br>
                        Users are typically assigned one desk, but admins can view desk assignments and modify them
                        through Desk Management or User Management pages.
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
