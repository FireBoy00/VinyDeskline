<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/admin-pages.css'])
        @vite(['resources/css/dashboard.css'])
        
        <title>VinyDeskline - Account Settings</title>
    </head>
    <body>
        <x-navbar active="account" />
        
        <main class="dashboard">
            <section class="section">
                <h1 class="section-title">Account Settings</h1>
                <p class="coming-soon-message">
                    Account settings coming soon...
                </p>
            </section>
        </main>
    </body>
</html>
