<div class="user-account-card">
    <div class="user-account-trigger">
        <span class="user-name">{{ Auth::user()->full_name }}</span>
        <div class="user-avatar">
            <span class="material-icons-round">person</span>
        </div>
    </div>
    <div class="user-dropdown">
        <div class="dropdown-header">
            <div class="user-avatar-large">
                <span class="material-icons-round">person</span>
            </div>
            <div class="user-info">
                <span class="dropdown-user-name">{{ Auth::user()->full_name }}</span>
                <span class="dropdown-user-email">{{ Auth::user()->email }}</span>
            </div>
        </div>
        <div class="dropdown-divider"></div>
        <ul class="dropdown-menu">
            <li>
                <a href="{{ route('home') }}" class="dropdown-item">
                    <span class="material-icons-round">home</span>
                    <span>Home</span>
                </a>
            </li>
            <li>
                <a href="{{ route('settings') }}" class="dropdown-item">
                    <span class="material-icons-round">settings</span>
                    <span>Settings</span>
                </a>
            </li>
            <li class="dropdown-divider-small"></li>
            <li>
                <a href="{{ route('help') }}" class="dropdown-item">
                    <span class="material-icons-round">help_outline</span>
                    <span>Help</span>
                </a>
            </li>
            <li>
                <a href="{{ route('about') }}" class="dropdown-item">
                    <span class="material-icons-round">info</span>
                    <span>About</span>
                </a>
            </li>
            @if (Auth::user()->is_admin)
                <li class="dropdown-divider-small"></li>
                <li>
                    <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                        <span class="material-icons-round">dashboard</span>
                        <span>Admin Dashboard</span>
                    </a>
                </li>
            @endif
            <li class="dropdown-divider-small"></li>
            <li>
                <a href="{{ route('logout') }}" class="dropdown-item logout-item">
                    <span class="material-icons-round">logout</span>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</div>
