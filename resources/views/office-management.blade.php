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
                <form id="floor-form">
                    <input type="hidden" id="floor-id">
                    
                    <div class="form-group">
                        <label class="form-label">Floor Name</label>
                        <input type="text" class="form-input" id="floor-name" placeholder="e.g., Ground Floor" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Floor Number</label>
                        <input type="number" class="form-input" id="floor-number" placeholder="e.g., 0, 1, 2" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea class="form-input" id="floor-description" placeholder="Optional description" rows="3"></textarea>
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
                <form id="room-form">
                    <input type="hidden" id="room-id">
                    
                    <div class="form-group">
                        <label class="form-label">Room Name</label>
                        <input type="text" class="form-input" id="room-name" placeholder="e.g., Conference Room A" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Floor (Optional)</label>
                        <select class="form-input" id="room-floor">
                            <option value="">No Floor Assignment</option>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description (Optional)</label>
                        <textarea class="form-input" id="room-description" placeholder="Optional description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-actions">
                <button class="modal-btn cancel" id="cancel-room-btn">Cancel</button>
                <button class="modal-btn save" id="save-room-btn">Save</button>
            </div>
        </div>
    </div>
</body>
</html>
