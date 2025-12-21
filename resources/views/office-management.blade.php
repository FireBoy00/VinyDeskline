<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/general-admin.css'])
    @vite(['resources/css/office-management.css', 'resources/js/office-management.js'])
    <title>VinyDeskline - Office Management</title>
</head>

<body>
    <x-navbar active="office-management" />

    <main class="dashboard">
        <section class="section office-header">
            <h1 class="section-title"><span>O</span>ffice Management</h1>
        </section>

        <div class="office-management-container">
            <!-- Floors Section -->
            <section class="section floors-section">
                <div class="section-header">
                    <h2 class="section-subtitle">
                        Floors
                        <span class="count-badge" id="floors-count">0</span>
                    </h2>
                    <button class="add-btn" id="add-floor-btn">
                        <span class="material-icons-round">add</span>
                        <span>Add Floor</span>
                    </button>
                </div>

                <div class="loading-container" id="floors-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading floors...</p>
                </div>

                <div class="floors-grid" id="floors-grid">
                    <!-- Floors will be loaded dynamically -->
                </div>

                <div class="no-results-message" id="no-floors-message">
                    <span class="material-icons-round">layers</span>
                    <p>No floors defined yet</p>
                    <button class="btn-primary" onclick="document.getElementById('add-floor-btn').click()">
                        Add Your First Floor
                    </button>
                </div>
            </section>

            <!-- Rooms Section -->
            <section class="section rooms-section">
                <div class="section-header">
                    <h2 class="section-subtitle">
                        Rooms
                        <span class="count-badge" id="rooms-count">0</span>
                    </h2>
                    <button class="add-btn" id="add-room-btn">
                        <span class="material-icons-round">add</span>
                        <span>Add Room</span>
                    </button>
                </div>

                <div class="loading-container" id="rooms-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading rooms...</p>
                </div>

                <div class="rooms-grid" id="rooms-grid">
                    <!-- Rooms will be loaded dynamically -->
                </div>

                <div class="no-results-message" id="no-rooms-message">
                    <span class="material-icons-round">meeting_room</span>
                    <p>No rooms defined yet</p>
                    <button class="btn-primary" onclick="document.getElementById('add-room-btn').click()">
                        Add Your First Room
                    </button>
                </div>
            </section>
        </div>
    </main>

    <!-- Floor Modal -->
    <div class="modal" id="floor-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="floor-modal-title">Add Floor</h3>
                <button class="modal-close-btn" id="close-floor-modal">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="error-message" id="floor-error-message">
                    <span class="material-icons-round">error</span>
                    <span id="floor-error-text"></span>
                </div>
                <form id="floor-form">
                    <input type="hidden" id="floor-id">

                    <div class="form-group">
                        <label class="form-label">Floor Name (Optional)</label>
                        <input type="text" class="form-input" id="floor-name"
                            placeholder="Leave empty to auto-generate">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Floor Number</label>
                        <input type="number" class="form-input" id="floor-number" placeholder="e.g., 0, 1, 2" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea class="form-input" id="floor-description" placeholder="Optional description" rows="3"></textarea>
                    </div>

                    <!-- Only show these sections when editing -->
                    <div id="floor-management-sections" style="display: none;">
                        <!-- Rooms on this floor -->
                        <div class="form-group">
                            <label class="form-label">Rooms on this Floor</label>
                            <div class="desk-list" id="floor-rooms-list">
                                <p style="text-align: center; color: rgba(0, 79, 110, 0.6); padding: 20px;">No rooms
                                    assigned</p>
                            </div>
                            <div class="add-desk-section">
                                <button type="button" class="add-desk-btn" id="add-room-to-floor-btn">
                                    <span class="material-icons-round">add</span>
                                    <span>Add Room to Floor</span>
                                </button>
                            </div>
                        </div>

                        <!-- Desks on this floor -->
                        <div class="form-group">
                            <label class="form-label">Desks on this Floor</label>
                            <div class="desk-list" id="floor-desks-list">
                                <p style="text-align: center; color: rgba(0, 79, 110, 0.6); padding: 20px;">No desks
                                    assigned</p>
                            </div>
                            <div class="add-desk-section">
                                <button type="button" class="add-desk-btn" id="add-desk-to-floor-btn">
                                    <span class="material-icons-round">add</span>
                                    <span>Add Desk to Floor</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel" id="cancel-floor-btn">Cancel</button>
                <button class="modal-btn save" id="save-floor-btn">Save</button>
            </div>
        </div>
    </div>

    <!-- Room Modal -->
    <div class="modal" id="room-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="room-modal-title">Add Room</h3>
                <button class="modal-close-btn" id="close-room-modal">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="error-message" id="room-error-message">
                    <span class="material-icons-round">error</span>
                    <span id="room-error-text"></span>
                </div>
                <form id="room-form">
                    <input type="hidden" id="room-id">

                    <div class="form-group">
                        <label class="form-label">Room Name</label>
                        <input type="text" class="form-input" id="room-name"
                            placeholder="e.g., Conference Room A" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Floor (Optional)</label>
                        <div class="custom-select" id="room-floor-select">
                            <div class="select-selected" id="room-floor-selected">
                                No Floor Assignment
                            </div>
                            <div class="select-items hidden" id="room-floor-items">
                                <!-- Will be populated dynamically -->
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea class="form-input" id="room-description" placeholder="Optional description" rows="3"></textarea>
                    </div>

                    <!-- Only show when editing -->
                    <div id="room-management-sections" style="display: none;">
                        <!-- Desks in this room -->
                        <div class="form-group">
                            <label class="form-label">Desks in this Room</label>
                            <div class="desk-list" id="room-desks-list">
                                <p style="text-align: center; color: rgba(0, 79, 110, 0.6); padding: 20px;">No desks
                                    assigned</p>
                            </div>
                            <div class="add-desk-section">
                                <button type="button" class="add-desk-btn" id="add-desk-to-room-btn">
                                    <span class="material-icons-round">add</span>
                                    <span>Add Desk to Room</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel" id="cancel-room-btn">Cancel</button>
                <button class="modal-btn save" id="save-room-btn">Save</button>
            </div>
        </div>
    </div>

    <!-- Desk Selection Modal -->
    <div class="modal" id="desk-selection-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="desk-selection-title">Select Desk</h3>
                <button class="modal-close-btn" id="close-desk-selection-modal">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="error-message" id="desk-selection-error-message">
                    <span class="material-icons-round">error</span>
                    <span id="desk-selection-error-text"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Selected Desks</label>
                    <div class="selected-items empty" id="selected-desks-display">
                        No desks selected
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Available Desks</label>
                    <div class="custom-select" id="desk-select">
                        <div class="select-selected" id="desk-select-selected">
                            Click to select desks...
                        </div>
                        <div class="select-items hidden" id="desk-select-items">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel" id="cancel-desk-selection-btn">Cancel</button>
                <button class="modal-btn save" id="confirm-desk-selection-btn">Add Selected Desks</button>
            </div>
        </div>
    </div>

    <!-- Room Selection Modal -->
    <div class="modal" id="room-selection-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Select Room</h3>
                <button class="modal-close-btn" id="close-room-selection-modal">
                    <span class="material-icons-round">close</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="error-message" id="room-selection-error-message">
                    <span class="material-icons-round">error</span>
                    <span id="room-selection-error-text"></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Selected Rooms</label>
                    <div class="selected-items empty" id="selected-rooms-display">
                        No rooms selected
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Available Rooms</label>
                    <div class="custom-select" id="room-select">
                        <div class="select-selected" id="room-select-selected">
                            Click to select rooms...
                        </div>
                        <div class="select-items hidden" id="room-select-items">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel" id="cancel-room-selection-btn">Cancel</button>
                <button class="modal-btn save" id="confirm-room-selection-btn">Add Selected Rooms</button>
            </div>
        </div>
    </div>
</body>

</html>
