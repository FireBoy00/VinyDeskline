<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @vite(['resources/css/general-admin.css'])
        @vite(['resources/css/desk-management.css', 'resources/js/desk-management.js'])
        
        <title>VinyDeskline - Desk Management</title>
    </head>
    <body>
        <x-navbar active="arrangement" />
        
        <main class="dashboard">
            <section class="section desk-header">
                <h1 class="section-title"><span>D</span>esk Management</h1>
            </section>

            
            <!-- Desk Rows Container -->
            <div class="desk-rows-container" id="desk-rows-container">
                <!-- Action Panel / Table Header -->
                <div class="action-panel">
                    <div class="action-panel-content">
                        <!-- Left: Select Button -->
                        <button class="action-btn" id="select-btn">
                            <span class="material-icons-round">check_box_outline_blank</span>
                            <span>Select</span>
                        </button>
                        
                        <!-- Actions Menu Button (next to select) -->
                        <div class="actions-menu-container">
                            <button class="action-btn hidden" id="actions-btn">
                                <span class="material-icons-round">more_vert</span>
                            </button>
                            <!-- Actions dropdown menu -->
                            <div class="actions-dropdown" id="actions-dropdown">
                                <button class="dropdown-action-item" data-action="assign">
                                    <span class="material-icons-round">person_add</span>
                                    <span>Assign User</span>
                                </button>
                                <button class="dropdown-action-item" data-action="mark-available">
                                    <span class="material-icons-round">check_circle</span>
                                    <span>Mark Available</span>
                                </button>
                                <button class="dropdown-action-item" data-action="mark-cleaning">
                                    <span class="material-icons-round">cleaning_services</span>
                                    <span>Mark for Cleaning</span>
                                </button>
                                <button class="dropdown-action-item danger" data-action="mark-faulty">
                                    <span class="material-icons-round">warning</span>
                                    <span>Mark as Faulty</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Middle: Status Text -->
                        <div class="desk-status-text" id="desk-status-text">
                            Loading desks...
                        </div>
                        
                        <!-- Right: Refresh Button with Timestamp -->
                        <div class="refresh-container" title="Refresh">
                            <span class="last-refresh-text" id="last-refresh-text">Just now</span>
                            <button class="action-btn" id="refresh-btn">
                                <span class="material-icons-round">refresh</span>
                                {{-- <span>Refresh</span> --}}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <p>Loading desks...</p>
                </div>
                <div id="desksList">
                    <!-- Desks will be loaded dynamically via JavaScript -->
                </div>
            </div>

            <!-- Desk Detail Modal -->
            <div class="desk-modal" id="desk-modal">
                <div class="desk-modal-content">
                    <div class="modal-header">
                        <button class="modal-nav-btn hidden" id="modal-prev-desk">
                            <span class="material-icons-round">chevron_left</span>
                        </button>
                        <h2 class="modal-title">Desk Details</h2>
                        <button class="modal-nav-btn hidden" id="modal-next-desk">
                            <span class="material-icons-round">chevron_right</span>
                        </button>
                        <button class="modal-close" id="modal-close">
                            <span class="material-icons-round">close</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="desk-detail-info">
                            <div class="detail-row">
                                <span class="detail-label">Desk ID:</span>
                                <span class="detail-value" id="modal-desk-id">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Name:</span>
                                <span class="detail-value" id="modal-desk-name">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value" id="modal-desk-status">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Position (mm):</span>
                                <span class="detail-value" id="modal-desk-position">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Speed (mm/s):</span>
                                <span class="detail-value" id="modal-desk-speed">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Manufacturer:</span>
                                <span class="detail-value" id="modal-desk-manufacturer">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Activations:</span>
                                <span class="detail-value" id="modal-desk-activations">-</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Sit/Stand Counter:</span>
                                <span class="detail-value" id="modal-desk-sitstand">-</span>
                            </div>
                        </div>
                        <div class="modal-actions">
                            <button class="modal-action-btn secondary" data-action="edit">Edit</button>
                            <button class="modal-action-btn danger" data-action="mark-faulty">Mark as Faulty</button>
                            <button class="modal-action-btn success" data-action="mark-available">Mark as Available</button>
                        </div>
                    </div>
                </div>
            </div>
