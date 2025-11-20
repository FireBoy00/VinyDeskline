@props(['active' => 'dashboard'])

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
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}">
                    <span class="material-icons-round nav-icon icon">dashboard</span>
                    <span class="nav-text">Overall Statistics</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.arrangement') }}" class="nav-link {{ $active === 'arrangement' ? 'active' : '' }}">
                    <span class="material-icons-round nav-icon icon">desk</span>
                    <span class="nav-text">Desk Management</span>
                </a>
            </li>

            <li>
                <a href="{{ route('admin.schedules') }}" class="nav-link {{ $active === 'schedules' ? 'active' : '' }}">
                    <span class="material-icons-round nav-icon icon">schedule</span>
                    <span class="nav-text">Schedules</span>
                </a>
            </li>
        </ul>

        <ul class="nav-links nav-bottom">
            <li>
                <a href="{{ route('admin.account') }}" class="nav-link nav-account {{ $active === 'account' ? 'active' : '' }}">
                    <span class="material-icons-round nav-icon icon">account_circle</span>
                    <span class="nav-text">{{ Auth::user()->name ?? 'User' }}</span>
                </a>
            </li>
            <li>
                <a href="{{ route('logout') }}" class="nav-link nav-logout">
                    <span class="material-icons-round nav-icon icon">logout</span>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
