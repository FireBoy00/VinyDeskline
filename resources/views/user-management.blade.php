<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/general-admin.css'])
    @vite(['resources/css/user-management.css', 'resources/js/user-management.js'])
    <title>VinyDeskline - User Management</title>
</head>

<body>
    <x-navbar active="user-management" />

    <main class="dashboard">
        <section class="section user-header">
            <h1 class="section-title"><span>U</span>ser Management</h1>
        </section>

        <div class="user-management-container">
            <section class="section user-list-section">
                <div class="user-list-header">
                    <h2 class="user-list-title">
                        Registered Users
                        <span class="user-count-badge" id="user-count">{{ $users->total() }}</span>
                    </h2>
                    <div class="header-actions">
                        <button class="toggle-filters-btn" id="toggle-filters-btn">
                            <span class="material-icons-round">filter_list</span>
                            <span>Filters</span>
                        </button>
                        <button class="add-user-btn" id="add-user-btn">
                            <span class="material-icons-round">person_add</span>
                            <span>Add User</span>
                        </button>
                    </div>
                </div>

                <!-- Search and Filter Section -->
                <div class="search-filter-section">
                    <div class="search-container">
                        <input type="text" class="search-input" id="search-input"
                            placeholder="Search by name or email..." value="{{ request('search', '') }}">
                    </div>
                    <div class="filter-controls hidden" id="filter-controls">
                        <div class="filter-group">
                            <label class="filter-label">User Type:</label>
                            <div class="custom-select" data-name="filter-user-type">
                                <div class="select-selected">
                                    @if (request('user_type') === 'admin')
                                        Admins Only
                                    @elseif(request('user_type') === 'regular')
                                        Regular Users
                                    @else
                                        All Users
                                    @endif
                                </div>
                                <div class="select-items hidden">
                                    <div data-value="all"
                                        class="{{ request('user_type', 'all') === 'all' ? 'selected' : '' }}">All Users
                                    </div>
                                    <div data-value="admin"
                                        class="{{ request('user_type') === 'admin' ? 'selected' : '' }}">Admins Only
                                    </div>
                                    <div data-value="regular"
                                        class="{{ request('user_type') === 'regular' ? 'selected' : '' }}">Regular Users
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Personalization:</label>
                            <div class="custom-select" data-name="filter-personalization">
                                <div class="select-selected">
                                    @if (request('personalization') === 'completed')
                                        Completed
                                    @elseif(request('personalization') === 'needs')
                                        Needs Personalization
                                    @else
                                        All
                                    @endif
                                </div>
                                <div class="select-items hidden">
                                    <div data-value="all"
                                        class="{{ request('personalization', 'all') === 'all' ? 'selected' : '' }}">All
                                    </div>
                                    <div data-value="completed"
                                        class="{{ request('personalization') === 'completed' ? 'selected' : '' }}">
                                        Completed</div>
                                    <div data-value="needs"
                                        class="{{ request('personalization') === 'needs' ? 'selected' : '' }}">Needs
                                        Personalization</div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Age:</label>
                            <div class="custom-select" data-name="filter-age-comparison">
                                <div class="select-selected">
                                    @if (request('age_comparison') === 'equal')
                                        Equal to
                                    @elseif(request('age_comparison') === 'above')
                                        Above
                                    @elseif(request('age_comparison') === 'below')
                                        Below
                                    @else
                                        Any
                                    @endif
                                </div>
                                <div class="select-items hidden">
                                    <div data-value="any"
                                        class="{{ request('age_comparison', 'any') === 'any' ? 'selected' : '' }}">Any
                                    </div>
                                    <div data-value="equal"
                                        class="{{ request('age_comparison') === 'equal' ? 'selected' : '' }}">Equal to
                                    </div>
                                    <div data-value="above"
                                        class="{{ request('age_comparison') === 'above' ? 'selected' : '' }}">Above
                                    </div>
                                    <div data-value="below"
                                        class="{{ request('age_comparison') === 'below' ? 'selected' : '' }}">Below
                                    </div>
                                </div>
                            </div>
                            <input type="number" class="filter-input" id="filter-age-value" placeholder="Age"
                                min="0" value="{{ request('age_value', '') }}"
                                {{ request('age_comparison', 'any') === 'any' ? 'disabled' : '' }}>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Height:</label>
                            <div class="custom-select" data-name="filter-height-comparison">
                                <div class="select-selected">
                                    @if (request('height_comparison') === 'equal')
                                        Equal to
                                    @elseif(request('height_comparison') === 'above')
                                        Above
                                    @elseif(request('height_comparison') === 'below')
                                        Below
                                    @else
                                        Any
                                    @endif
                                </div>
                                <div class="select-items hidden">
                                    <div data-value="any"
                                        class="{{ request('height_comparison', 'any') === 'any' ? 'selected' : '' }}">
                                        Any</div>
                                    <div data-value="equal"
                                        class="{{ request('height_comparison') === 'equal' ? 'selected' : '' }}">Equal
                                        to</div>
                                    <div data-value="above"
                                        class="{{ request('height_comparison') === 'above' ? 'selected' : '' }}">Above
                                    </div>
                                    <div data-value="below"
                                        class="{{ request('height_comparison') === 'below' ? 'selected' : '' }}">Below
                                    </div>
                                </div>
                            </div>
                            <input type="number" class="filter-input" id="filter-height-value" placeholder="cm"
                                min="0" value="{{ request('height_value', '') }}"
                                {{ request('height_comparison', 'any') === 'any' ? 'disabled' : '' }}>
                        </div>
                        <button class="clear-filters-btn" id="clear-filters-btn">Clear Filters</button>
                    </div>
                </div>

                <div class="user-rows" id="user-rows">
                    @forelse ($users as $user)
                        <div class="user-row" data-id="{{ $user->id }}"
                            data-name="{{ strtolower($user->full_name) }}"
                            data-email="{{ strtolower($user->email) }}"
                            data-is-admin="{{ $user->is_admin ? 'true' : 'false' }}"
                            data-needs-personalization="{{ $user->needs_personalization ? 'true' : 'false' }}"
                            data-age="{{ $user->age ?? '' }}" data-height="{{ $user->height ?? '' }}"
                            data-is-current-user="{{ $user->id === $currentUserId ? 'true' : 'false' }}">
                            <div class="user-info-main">
                                <div class="user-name-container">
                                    <span class="user-name">{{ $user->full_name }}</span>
                                    <span class="user-type {{ $user->is_admin ? 'admin' : 'regular' }}">
                                        {{ $user->is_admin ? 'Admin' : 'User' }}
                                    </span>
                                </div>
                                <div class="user-email">{{ $user->email }}</div>
                            </div>
                            <div class="user-divider"></div>
                            <div class="user-pill-group">
                                @if ($user->desk_id)
                                    <span class="user-pill desk">
                                        <span class="material-icons-round">desk</span>
                                        Desk: {{ $user->desk->name ?? $user->desk_id }}
                                    </span>
                                @endif
                                @if ($user->needs_personalization)
                                    <span class="user-pill needs-personalization">Needs Personalization</span>
                                @elseif (!$user->height && !$user->age)
                                    <span class="user-pill skipped-personalization">Skipped Personalization</span>
                                @endif
                                @if ($user->height)
                                    <span class="user-pill height">Height: {{ $user->height }}cm</span>
                                @endif
                                @if ($user->age)
                                    <span class="user-pill age">Age: {{ $user->age }}</span>
                                @endif
                            </div>
                            <button class="user-actions-btn" data-user-id="{{ $user->id }}">
                                <span class="material-icons-round">more_vert</span>
                            </button>
                            <div class="user-actions-menu" id="user-actions-{{ $user->id }}">
                                <button class="user-action-item edit" data-user-id="{{ $user->id }}">
                                    <span class="material-icons-round">edit</span>
                                    <span>Edit User</span>
                                </button>
                                <button class="user-action-item delete danger" data-user-id="{{ $user->id }}">
                                    <span class="material-icons-round">delete</span>
                                    <span>Delete User</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="no-results-message active">
                            No users match the current filters.
                        </div>
                    @endforelse
                </div>

                <!-- Pagination Controls -->
                @if ($users->hasPages())
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of
                            {{ $users->total() }} users
                        </div>
                        <div class="pagination-controls">
                            {{-- Previous Button --}}
                            @if ($users->onFirstPage())
                                <button class="pagination-btn" disabled>
                                    <span class="material-icons-round">chevron_left</span>
                                </button>
                            @else
                                <a href="{{ $users->previousPageUrl() }}" class="pagination-btn">
                                    <span class="material-icons-round">chevron_left</span>
                                </a>
                            @endif

                            {{-- Page Numbers --}}
                            <div class="pagination-pages">
                                @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                    @if ($page == $users->currentPage())
                                        <span class="pagination-page active">{{ $page }}</span>
                                    @else
                                        <a href="{{ $url }}"
                                            class="pagination-page">{{ $page }}</a>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Next Button --}}
                            @if ($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}" class="pagination-btn">
                                    <span class="material-icons-round">chevron_right</span>
                                </a>
                            @else
                                <button class="pagination-btn" disabled>
                                    <span class="material-icons-round">chevron_right</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
    </main>

    <!-- Edit/Add User Modal -->
    <div class="user-edit-modal" id="edit-user-modal">
        <div class="user-edit-modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-title">Edit User</h3>
                <button class="modal-close-btn" id="close-modal-btn">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div class="modal-loading" id="modal-loading">
                <div class="loading-spinner"></div>
                <p>Loading user data...</p>
            </div>
            <div class="modal-body" id="modal-body">
                <form id="edit-user-form">
                    <input type="hidden" id="edit-user-id">

                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-input" id="edit-first-name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-input" id="edit-last-name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" id="edit-email" required>
                    </div>

                    <div class="form-group" id="password-group">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-input" id="edit-password" placeholder="Enter password">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Height (cm)</label>
                        <input type="number" class="form-input" id="edit-height" min="0"
                            placeholder="Optional">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Age</label>
                        <input type="number" class="form-input" id="edit-age" min="0"
                            placeholder="Optional">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Assigned Desk</label>
                        <select class="form-input" id="edit-desk">
                            <option value="">No Desk Assigned</option>
                            <!-- Will be populated dynamically via JavaScript -->
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="custom-checkbox-group">
                            <input type="checkbox" class="custom-checkbox-input" id="edit-is-admin">
                            <label class="custom-checkbox-label" for="edit-is-admin">
                                <span class="checkbox-box"></span>
                                <span class="checkbox-text">Administrator</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="custom-checkbox-group">
                            <input type="checkbox" class="custom-checkbox-input" id="edit-needs-personalization">
                            <label class="custom-checkbox-label" for="edit-needs-personalization">
                                <span class="checkbox-box"></span>
                                <span class="checkbox-text">Needs Personalization</span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-actions" id="modal-actions">
                <button class="modal-btn cancel" id="cancel-edit-btn">Cancel</button>
                <button class="modal-btn save" id="save-user-btn">Save Changes</button>
            </div>
        </div>
    </div>
</body>

</html>
