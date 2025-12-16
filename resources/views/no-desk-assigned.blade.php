<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/no-desk-assigned.css'])
    <title>VinyDeskline - No Desk Assigned</title>
</head>

<body>
    <x-navbar active="" />

    <main class="no-desk-container">
        <div class="no-desk-card">
            <span class="material-icons-round no-desk-icon">desk</span>

            @if (auth()->user()->is_admin)
                <h1 class="no-desk-title">No Desk Assigned</h1>
                <p class="no-desk-message">
                    As an administrator, you don't currently have a desk assigned to you.
                </p>
                <p class="no-desk-submessage">
                    You can assign yourself a desk from the Desk Management page or continue managing the system without
                    a personal desk assignment.
                </p>
                <div class="no-desk-actions">
                    <a href="{{ route('arrangement') }}" class="btn-primary">
                        <span class="material-icons-round">dashboard</span>
                        <span>Go to Admin Dashboard</span>
                    </a>
                    <a href="{{ route('arrangement') }}" class="btn-secondary">
                        <span class="material-icons-round">settings</span>
                        <span>Desk Management</span>
                    </a>
                </div>
            @else
                <h1 class="no-desk-title">No Desk Assigned</h1>
                <p class="no-desk-message">
                    You don't currently have a desk assigned to your account.
                </p>
                <p class="no-desk-submessage">
                    Please contact your administrator to have a desk assigned to you before you can access the dashboard
                    and desk controls.
                </p>
                <div class="no-desk-actions">
                    <a href="mailto:{{ config('mail.from.address', 'admin@example.com') }}" class="btn-primary">
                        <span class="material-icons-round">email</span>
                        <span>Contact Administrator</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-secondary">
                            <span class="material-icons-round">logout</span>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </main>
</body>

</html>
