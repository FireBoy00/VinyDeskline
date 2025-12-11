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
                        <span class="user-count-badge" id="user-count">{{ count($users) }}</span>
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
                            placeholder="Search by name or email...">
                    </div>
                    <div class="filter-controls hidden" id="filter-controls">
                        <div class="filter-group">
                            <label class="filter-label">User Type:</label>
                            <div class="custom-select" data-name="filter-user-type">
                                <div class="select-selected">All Users</div>
                                <div class="select-items hidden">
                                    <div data-value="all" class="selected">All Users</div>
                                    <div data-value="admin">Admins Only</div>
                                    <div data-value="regular">Regular Users</div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Personalization:</label>
                            <div class="custom-select" data-name="filter-personalization">
                                <div class="select-selected">All</div>
                                <div class="select-items hidden">
                                    <div data-value="all" class="selected">All</div>
                                    <div data-value="completed">Completed</div>
                                    <div data-value="needs">Needs Personalization</div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Age:</label>
                            <div class="custom-select" data-name="filter-age-comparison">
                                <div class="select-selected">Any</div>
                                <div class="select-items hidden">
                                    <div data-value="any" class="selected">Any</div>
                                    <div data-value="equal">Equal to</div>
                                    <div data-value="above">Above</div>
                                    <div data-value="below">Below</div>
                                </div>
                            </div>
                            <input type="number" class="filter-input" id="filter-age-value" placeholder="Age"
                                min="0" disabled>
                        </div>
                        <div class="filter-group">
                            <label class="filter-label">Height:</label>
                            <div class="custom-select" data-name="filter-height-comparison">
                                <div class="select-selected">Any</div>
                                <div class="select-items hidden">
                                    <div data-value="any" class="selected">Any</div>
                                    <div data-value="equal">Equal to</div>
                                    <div data-value="above">Above</div>
                                    <div data-value="below">Below</div>
                                </div>
                            </div>
                            <input type="number" class="filter-input" id="filter-height-value" placeholder="cm"
                                min="0" disabled>
                        </div>
                        <button class="clear-filters-btn" id="clear-filters-btn">Clear Filters</button>
                    </div>
                </div>

                <div class="no-results-message" id="no-results-message">No users match the current filters.</div>

                <div class="user-rows" id="user-rows">
                    @foreach ($users as $user)
                        <div class="user-row" data-id="{{ $user->id }}"
                            data-name="{{ strtolower($user->full_name) }}" data-email="{{ strtolower($user->email) }}"
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
                    @endforeach
                </div>
            </section>
        </div>
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
