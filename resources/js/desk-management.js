// Desk Management Page JavaScript

document.addEventListener('DOMContentLoaded', function () {
    const deskRowsContainer = document.getElementById('desksList');
    const loadingContainer = document.querySelector('.loading-container');
    const deskStatusText = document.getElementById('desk-status-text');
    const modal = document.getElementById('desk-modal');
    const modalClose = document.getElementById('modal-close');
    const selectBtn = document.getElementById('select-btn');
    const actionsBtn = document.getElementById('actions-btn');
    const actionsDropdown = document.getElementById('actions-dropdown');
    const modalPrevBtn = document.getElementById('modal-prev-desk');
    const modalNextBtn = document.getElementById('modal-next-desk');
    const refreshBtn = document.getElementById('refresh-btn');
    const lastRefreshText = document.getElementById('last-refresh-text');
    
    let selectedDesks = [];
    let selectMode = false;
    let currentViewIndex = 0;
    let allDesks = [];
    let deskDetailsCache = {};
    let loadedDesksCount = 0;
    let lastRefreshTime = Date.now();
    let refreshTimerInterval = null;

    // API endpoints - adjust these based on your actual routes
    const API_BASE = '/admin/desks';

    // Status mapping from API to display
    const statusMap = {
        'Normal': 'Normal',
        'normal': 'Normal',
        'Moving': 'In Use',
        'moving': 'In Use',
        'Collision': 'Faulty',
        'collision': 'Faulty',
        'Occupied': 'Occupied',
        'occupied': 'Occupied'
    };

    // Initialize - Load desks from API
    loadDesks();
    
    // Refresh button handler
    refreshBtn.addEventListener('click', function() {
        if (refreshBtn.disabled) return;
        
        // Reset selections when refreshing
        if (selectMode) {
            toggleSelectMode();
        }
        
        stopRefreshTimer();
        loadDesks();
    });

    async function loadDesks() {
        try {
            updateStatusText(`Loading desks...`);
            loadingContainer.style.display = 'flex';
            selectBtn.disabled = true;
            actionsBtn.disabled = true;
            refreshBtn.disabled = true;
            
            const response = await fetch(API_BASE);
            if (!response.ok) throw new Error('Failed to fetch desks');
            
            const data = await response.json();
            allDesks = data.desks || [];
            loadingContainer.style.display = 'none';
            
            if (allDesks.length === 0) {
                showError('No desks found');
                updateStatusText('No desks found');
                return;
            }
            
            // Reset counter and clear container for progressive rendering
            loadedDesksCount = 0;
            deskRowsContainer.innerHTML = '';
            updateStatusText(`Loading ${allDesks.length} desks...`);
            
            // Load details progressively - render as each desk loads
            await loadAllDeskDetailsProgressively();
            
            updateStatusText(`${loadedDesksCount} desks loaded`);
            selectBtn.disabled = false;
            actionsBtn.disabled = false;
            refreshBtn.disabled = false;
            
            // Start refresh timer
            lastRefreshTime = Date.now();
            startRefreshTimer();
            
        } catch (error) {
            console.error('Error loading desks:', error);
            showError('Failed to load desks. Please try again.');
            updateStatusText('Failed to load desks');
            refreshBtn.disabled = false;
        }
    }

    async function loadAllDeskDetailsProgressively() {
        // Assign desks to floors randomly
        const deskFloorAssignments = {};
        const totalFloors = 10;
        
        // Pre-assign each desk to a random floor
        allDesks.forEach(deskId => {
            const floorNum = Math.floor(Math.random() * totalFloors) + 1;
            deskFloorAssignments[deskId] = floorNum;
        });
        
        // Load each desk and render progressively
        for (let i = 0; i < allDesks.length; i++) {
            const deskId = allDesks[i];
            
            // Load desk details
            const deskData = await loadDeskDetails(deskId);
            
            // Only proceed if desk data loaded successfully
            if (!deskData) {
                console.warn(`Skipping desk ${deskId} - failed to load details`);
                continue;
            }
            
            loadedDesksCount++;
            
            // Update status text
            updateStatusText(`Loading desks... ${loadedDesksCount}/${allDesks.length}`);
            
            // Get the floor for this desk
            const floorNum = deskFloorAssignments[deskId];
            
            // Check if floor container exists, if not create it
            let floorContainer = document.querySelector(`[data-floor="${floorNum}"]`);
            if (!floorContainer) {
                floorContainer = createFloorContainer(floorNum);
                // Insert floor in sorted order (highest to lowest)
                insertFloorInOrder(floorContainer, floorNum);
            }
            
            const deskItems = floorContainer.querySelector('.desk-items');
            const deskCard = createDeskCard(deskId);
            deskItems.appendChild(deskCard);
            
            // Attach event listener to this card
            attachCardEventListener(deskCard);
        }
    }

    function insertFloorInOrder(floorContainer, floorNum) {
        const existingFloors = Array.from(deskRowsContainer.querySelectorAll('.desk-row'));
        
        if (existingFloors.length === 0) {
            deskRowsContainer.appendChild(floorContainer);
            return;
        }
        
        // Find the correct position (floors sorted descending)
        let inserted = false;
        for (let existingFloor of existingFloors) {
            const existingFloorNum = parseInt(existingFloor.getAttribute('data-floor'));
            if (floorNum > existingFloorNum) {
                deskRowsContainer.insertBefore(floorContainer, existingFloor);
                inserted = true;
                break;
            }
        }
        
        if (!inserted) {
            deskRowsContainer.appendChild(floorContainer);
        }
    }

    function createFloorContainer(floorNum) {
        const row = document.createElement('div');
        row.className = 'desk-row';
        row.setAttribute('data-floor', floorNum);
        
        // Desk items container (initially empty)
        const deskItems = document.createElement('div');
        deskItems.className = 'desk-items';
        
        // Separator
        const separator = document.createElement('div');
        separator.className = 'row-separator';
        
        // Row header
        const rowHeader = document.createElement('div');
        rowHeader.className = 'row-header';
        rowHeader.innerHTML = `
            <span class=\"row-label\">Floor</span>
            <span class=\"row-number\">${floorNum}</span>
        `;
        
        row.appendChild(deskItems);
        row.appendChild(separator);
        row.appendChild(rowHeader);
        
        return row;
    }

    async function loadDeskDetails(deskId) {
        try {
            const response = await fetch(`${API_BASE}/${deskId}`);
            if (!response.ok) throw new Error(`Failed to fetch desk ${deskId}`);
            
            const data = await response.json();
            deskDetailsCache[deskId] = data;
            return data;
        } catch (error) {
            console.error(`Error loading desk ${deskId}:`, error);
            return null;
        }
    }

    function createDeskCard(deskId) {
        const card = document.createElement('div');
        card.className = 'desk-card';
        card.setAttribute('data-desk-id', deskId);
        
        const deskData = deskDetailsCache[deskId];
        const status = deskData?.state?.status || 'Normal';
        const statusClass = getStatusClass(status);
        
        card.innerHTML = `
            <div class="desk-icon">
                <span class="material-icons-round">desk</span>
            </div>
            <div class="desk-status ${statusClass}"></div>
        `;
        
        return card;
    }

    function getStatusClass(status) {
        const normalized = status.toLowerCase();
        if (normalized.includes('error') || normalized.includes('collision')) return 'faulty';
        if (normalized.includes('moving') || normalized.includes('use')) return 'occupied';
        if (normalized.includes('cleaning')) return 'cleaning';
        return 'available';
    }

    function attachCardEventListener(card) {
        card.addEventListener('click', function () {
            const deskId = this.getAttribute('data-desk-id');
            
            if (selectMode) {
                this.classList.toggle('selected');
                updateSelectedDesks();
            } else {
                selectedDesks = [deskId];
                currentViewIndex = 0;
                openDeskModal(deskId);
                updateModalNavigation();
            }
        });
    }

    function updateStatusText(text) {
        if (deskStatusText) {
            deskStatusText.textContent = text;
        }
    }

    function updateSelectedDesks() {
        selectedDesks = Array.from(document.querySelectorAll('.desk-card.selected'))
            .map(card => card.getAttribute('data-desk-id'));
        
        // Update status text based on selection
        if (selectMode) {
            if (selectedDesks.length === 0) {
                updateStatusText(`${loadedDesksCount} desks`);
            } else {
                updateStatusText(`${selectedDesks.length} desk${selectedDesks.length !== 1 ? 's' : ''} selected`);
            }
        } else {
            updateStatusText(`${loadedDesksCount} desks`);
        }
    }

    // Modal navigation buttons
    if (modalPrevBtn) {
        modalPrevBtn.addEventListener('click', function () {
            if (currentViewIndex > 0) {
                currentViewIndex--;
                openDeskModal(selectedDesks[currentViewIndex]);
                updateModalNavigation();
            }
        });
    }

    if (modalNextBtn) {
        modalNextBtn.addEventListener('click', function () {
            if (currentViewIndex < selectedDesks.length - 1) {
                currentViewIndex++;
                openDeskModal(selectedDesks[currentViewIndex]);
                updateModalNavigation();
            }
        });
    }

    function updateModalNavigation() {
        if (selectedDesks.length > 1) {
            modalPrevBtn.style.display = 'flex';
            modalNextBtn.style.display = 'flex';
            
            modalPrevBtn.disabled = currentViewIndex === 0;
            modalNextBtn.disabled = currentViewIndex === selectedDesks.length - 1;
        } else {
            modalPrevBtn.style.display = 'none';
            modalNextBtn.style.display = 'none';
        }
    }

    // Select button - toggles selection mode
    if (selectBtn) {
        selectBtn.addEventListener('click', function () {
            selectMode = !selectMode;
            this.classList.toggle('primary');
            
            // Update icon
            const icon = this.querySelector('.material-icons-round');
            if (selectMode) {
                icon.textContent = 'check_box';
                actionsBtn.style.display = 'flex';
                updateStatusText(`${loadedDesksCount} desks - Select desks to perform actions`);
            } else {
                icon.textContent = 'check_box_outline_blank';
                actionsBtn.style.display = 'none';
                actionsDropdown.classList.remove('active');
                document.querySelectorAll('.desk-card').forEach(card => card.classList.remove('selected'));
                selectedDesks = [];
                updateStatusText(`${loadedDesksCount} desks loaded`);
            }
            
            showNotification(selectMode ? 'Selection mode enabled' : 'Selection mode disabled');
        });
    }

    // Actions button - toggle dropdown
    if (actionsBtn) {
        actionsBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            actionsDropdown.classList.toggle('active');
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (actionsDropdown && !actionsBtn.contains(e.target) && !actionsDropdown.contains(e.target)) {
            actionsDropdown.classList.remove('active');
        }
    });

    // Dropdown action items
    const dropdownActions = document.querySelectorAll('.dropdown-action-item');
    dropdownActions.forEach(item => {
        item.addEventListener('click', function () {
            const action = this.getAttribute('data-action');
            const selectedCount = selectedDesks.length;
            
            if (selectedCount > 0) {
                // TODO: Implement actual action via API
                showNotification(`${action.replace('-', ' ')} will be applied to ${selectedCount} desk(s)`);
                actionsDropdown.classList.remove('active');
                console.log(`TODO: Implement ${action} for desks:`, selectedDesks);
            } else {
                showNotification('Please select desks first');
            }
        });
    });

    async function openDeskModal(deskId) {
        const deskData = deskDetailsCache[deskId];
        
        if (!deskData) {
            showNotification('Desk data not available');
            return;
        }

        // Populate modal with desk data from API
        document.getElementById('modal-desk-id').textContent = deskId;
        document.getElementById('modal-desk-name').textContent = deskData.config?.name || 'N/A';
        document.getElementById('modal-desk-status').textContent = statusMap[deskData.state?.status] || deskData.state?.status || 'N/A';
        document.getElementById('modal-desk-position').textContent = deskData.state?.position_mm || 'N/A';
        document.getElementById('modal-desk-speed').textContent = deskData.state?.speed_mms || '0';
        document.getElementById('modal-desk-manufacturer').textContent = deskData.config?.manufacturer || 'N/A';
        document.getElementById('modal-desk-activations').textContent = deskData.usage?.activationsCounter || '0';
        document.getElementById('modal-desk-sitstand').textContent = deskData.usage?.sitStandCounter || '0';

        modal.classList.add('active');
    }

    // Close modal
    if (modalClose) {
        modalClose.addEventListener('click', function () {
            modal.classList.remove('active');
        });
    }

    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    // Modal action buttons
    const modalActionBtns = document.querySelectorAll('.modal-action-btn');
    modalActionBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const action = this.getAttribute('data-action');
            const deskId = document.getElementById('modal-desk-id').textContent;
            
            // TODO: Implement actual actions via API
            showNotification(`${action.replace('-', ' ')} action triggered for ${deskId}`);
            console.log(`TODO: Implement ${action} for desk:`, deskId);
        });
    });

    function showLoading() {
        deskRowsContainer.innerHTML = `
            <div class="loading-container">
                <div class="loading-spinner"></div>
                <p>Loading desks...</p>
            </div>
        `;
    }

    function showError(message) {
        deskRowsContainer.innerHTML = `
            <div class="error-container">
                <span class="material-icons-round">error_outline</span>
                <p>${message}</p>
                <button class="retry-btn">Retry</button>
            </div>
        `;
        // Add event listener for retry button
        const retryBtn = deskRowsContainer.querySelector('.retry-btn');
        if (retryBtn) {
            retryBtn.addEventListener('click', function () {
                location.reload();
            });
        }
    }

    function showNotification(message) {
        console.log(`Notification: ${message}`);
        
        const notification = document.createElement('div');
        notification.className = 'notification-toast';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--btn-primary-hover);
            color: white;
            padding: 16px 24px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 2000;
            animation: slideInRight 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 2000);
    }

    // Add notification animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Refresh timer functions
    function startRefreshTimer() {
        // Update immediately
        updateRefreshText();
        
        // Update every second
        refreshTimerInterval = setInterval(updateRefreshText, 1000);
    }

    function stopRefreshTimer() {
        lastRefreshTime = Date.now();
        updateRefreshText();
        if (refreshTimerInterval) {
            clearInterval(refreshTimerInterval);
            refreshTimerInterval = null;
        }
    }
    
    function updateRefreshText() {
        const now = Date.now();
        const secondsAgo = Math.floor((now - lastRefreshTime) / 1000);
        
        if (secondsAgo < 60) {
            lastRefreshText.textContent = secondsAgo === 0 ? 'Just now' : `${secondsAgo}s ago`;
        } else if (secondsAgo < 3600) {
            const minutesAgo = Math.floor(secondsAgo / 60);
            lastRefreshText.textContent = `${minutesAgo}m ago`;
        } else {
            const hoursAgo = Math.floor(secondsAgo / 3600);
            lastRefreshText.textContent = `${hoursAgo}h ago`;
        }
    }
    
    // Clear interval when page is unloaded
    window.addEventListener('beforeunload', function() {
        if (refreshTimerInterval) {
            clearInterval(refreshTimerInterval);
        }
    });
});
