// Desk Management Page JavaScript

document.addEventListener("DOMContentLoaded", function () {
    const deskRowsContainer = document.getElementById("desksList");
    const loadingContainer = document.querySelector(".loading-container");
    const deskStatusText = document.getElementById("desk-status-text");
    const modal = document.getElementById("desk-modal");
    const modalClose = document.getElementById("modal-close");
    const selectBtn = document.getElementById("select-btn");
    const actionsBtn = document.getElementById("actions-btn");
    const actionsDropdown = document.getElementById("actions-dropdown");
    const modalPrevBtn = document.getElementById("modal-prev-desk");
    const modalNextBtn = document.getElementById("modal-next-desk");
    const refreshBtn = document.getElementById("refresh-btn");
    const lastRefreshText = document.getElementById("last-refresh-text");

    let selectedDesks = [];
    let selectMode = false;
    let currentViewIndex = 0;
    let allDesks = [];
    let deskDetailsCache = {};
    let loadedDesksCount = 0;
    let lastRefreshTime = Date.now();
    let refreshTimerInterval = null;
    let availableUsers = [];
    let currentDeskId = null;

    // API endpoints - adjust these based on your actual routes
    const DESK_API_ENDPOINT = "/admin/desks";
    const USER_API_ENDPOINT = "/admin/users";

    // Status mapping from API to display
    const statusMap = {
        Normal: "Normal",
        normal: "Normal",
        Moving: "In Use",
        moving: "In Use",
        Collision: "Faulty",
        collision: "Faulty",
        Occupied: "Occupied",
        occupied: "Occupied",
    };

    // Initialize - Load desks from API
    loadDesks();
    loadAvailableUsers();

    // Refresh button handler
    refreshBtn.addEventListener("click", function () {
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
            loadingContainer.classList.remove("hidden");
            selectBtn.disabled = true;
            actionsBtn.disabled = true;
            refreshBtn.disabled = true;

            const response = await fetch(DESK_API_ENDPOINT);
            if (!response.ok) throw new Error("Failed to fetch desks");

            const data = await response.json();
            allDesks = data.desks || [];
            loadingContainer.classList.add("hidden");

            if (allDesks.length === 0) {
                showError("No desks found");
                updateStatusText("No desks found");
                return;
            }

            // Reset counter and clear container for progressive rendering
            loadedDesksCount = 0;
            deskRowsContainer.innerHTML = "";
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
            loadingContainer.classList.add("hidden");
            console.error("Error loading desks:", error);
            showError("Failed to load desks. Please try again.");
            updateStatusText("Failed to load desks");
            refreshBtn.disabled = false;
        }
    }

    async function loadAllDeskDetailsProgressively() {
        // Assign desks to floors randomly
        const deskFloorAssignments = {};
        const totalFloors = 10;

        // Pre-assign each desk to a random floor
        allDesks.forEach((deskId) => {
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
                console.warn(
                    `Skipping desk ${deskId} - failed to load details`
                );
                continue;
            }

            loadedDesksCount++;

            // Update status text
            updateStatusText(
                `Loading desks... ${loadedDesksCount}/${allDesks.length}`
            );

            // Get the floor for this desk
            const floorNum = deskFloorAssignments[deskId];

            // Check if floor container exists, if not create it
            let floorContainer = document.querySelector(
                `[data-floor="${floorNum}"]`
            );
            if (!floorContainer) {
                floorContainer = createFloorContainer(floorNum);
                // Insert floor in sorted order (highest to lowest)
                insertFloorInOrder(floorContainer, floorNum);
            }

            const deskItems = floorContainer.querySelector(".desk-items");
            const deskCard = createDeskCard(deskId);
            deskItems.appendChild(deskCard);

            // Attach event listener to this card
            attachCardEventListener(deskCard);
        }
    }

    function insertFloorInOrder(floorContainer, floorNum) {
        const existingFloors = Array.from(
            deskRowsContainer.querySelectorAll(".desk-row")
        );

        if (existingFloors.length === 0) {
            deskRowsContainer.appendChild(floorContainer);
            return;
        }

        // Find the correct position (floors sorted descending)
        let inserted = false;
        for (let existingFloor of existingFloors) {
            const existingFloorNum = parseInt(
                existingFloor.getAttribute("data-floor")
            );
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
        const row = document.createElement("div");
        row.className = "desk-row";
        row.setAttribute("data-floor", floorNum);

        // Desk items container (initially empty)
        const deskItems = document.createElement("div");
        deskItems.className = "desk-items";

        // Separator
        const separator = document.createElement("div");
        separator.className = "row-separator";

        // Row header
        const rowHeader = document.createElement("div");
        rowHeader.className = "row-header";
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
            const response = await fetch(`${DESK_API_ENDPOINT}/${deskId}`);
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
        const card = document.createElement("div");
        card.className = "desk-card";
        card.setAttribute("data-desk-id", deskId);

        const deskData = deskDetailsCache[deskId];
        const status = deskData?.state?.status || "Normal";
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
        if (normalized.includes("error") || normalized.includes("collision"))
            return "faulty";
        if (normalized.includes("moving") || normalized.includes("use"))
            return "occupied";
        if (normalized.includes("cleaning")) return "cleaning";
        return "available";
    }

    function attachCardEventListener(card) {
        card.addEventListener("click", function () {
            const deskId = this.getAttribute("data-desk-id");

            if (selectMode) {
                this.classList.toggle("selected");
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
        selectedDesks = Array.from(
            document.querySelectorAll(".desk-card.selected")
        ).map((card) => card.getAttribute("data-desk-id"));

        // Update status text based on selection
        if (selectMode) {
            if (selectedDesks.length === 0) {
                updateStatusText(`${loadedDesksCount} desks`);
            } else {
                updateStatusText(
                    `${selectedDesks.length} desk${
                        selectedDesks.length !== 1 ? "s" : ""
                    } selected`
                );
            }
        } else {
            updateStatusText(`${loadedDesksCount} desks`);
        }
    }

    // Modal navigation buttons
    if (modalPrevBtn) {
        modalPrevBtn.addEventListener("click", function () {
            if (currentViewIndex > 0) {
                currentViewIndex--;
                openDeskModal(selectedDesks[currentViewIndex]);
                updateModalNavigation();
            }
        });
    }

    if (modalNextBtn) {
        modalNextBtn.addEventListener("click", function () {
            if (currentViewIndex < selectedDesks.length - 1) {
                currentViewIndex++;
                openDeskModal(selectedDesks[currentViewIndex]);
                updateModalNavigation();
            }
        });
    }

    function updateModalNavigation() {
        if (selectedDesks.length > 1) {
            modalPrevBtn.style.display = "flex";
            modalNextBtn.style.display = "flex";

            modalPrevBtn.disabled = currentViewIndex === 0;
            modalNextBtn.disabled =
                currentViewIndex === selectedDesks.length - 1;
        } else {
            modalPrevBtn.style.display = "none";
            modalNextBtn.style.display = "none";
        }
    }

    // Select button - toggles selection mode
    if (selectBtn) {
        selectBtn.addEventListener("click", function () {
            selectMode = !selectMode;
            this.classList.toggle("primary");

            // Update icon
            const icon = this.querySelector(".material-icons-round");
            if (selectMode) {
                icon.textContent = "check_box";
                actionsBtn.style.display = "flex";
                updateStatusText(
                    `${loadedDesksCount} desks - Select desks to perform actions`
                );
            } else {
                icon.textContent = "check_box_outline_blank";
                actionsBtn.style.display = "none";
                actionsDropdown.classList.remove("active");
                document
                    .querySelectorAll(".desk-card")
                    .forEach((card) => card.classList.remove("selected"));
                selectedDesks = [];
                updateStatusText(`${loadedDesksCount} desks loaded`);
            }

            showNotification(
                selectMode
                    ? "Selection mode enabled"
                    : "Selection mode disabled"
            );
        });
    }

    // Actions button - toggle dropdown
    if (actionsBtn) {
        actionsBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            actionsDropdown.classList.toggle("active");
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener("click", function (e) {
        if (
            actionsDropdown &&
            !actionsBtn.contains(e.target) &&
            !actionsDropdown.contains(e.target)
        ) {
            actionsDropdown.classList.remove("active");
        }
    });

    // Dropdown action items
    const dropdownActions = document.querySelectorAll(".dropdown-action-item");
    dropdownActions.forEach((item) => {
        item.addEventListener("click", function () {
            const action = this.getAttribute("data-action");
            const selectedCount = selectedDesks.length;

            if (selectedCount > 0) {
                // TODO: Implement actual action via API
                showNotification(
                    `${action.replace(
                        "-",
                        " "
                    )} will be applied to ${selectedCount} desk(s)`
                );
                actionsDropdown.classList.remove("active");
                console.log(
                    `TODO: Implement ${action} for desks:`,
                    selectedDesks
                );
            } else {
                showNotification("Please select desks first");
            }
        });
    });

    async function openDeskModal(deskId) {
        const deskData = deskDetailsCache[deskId];

        if (!deskData) {
            showNotification("Desk data not available");
            return;
        }

        currentDeskId = deskId;

        // Populate modal with desk data from API
        document.getElementById("modal-desk-id").textContent = deskId;
        document.getElementById("modal-desk-name").textContent =
            deskData.config?.name || "N/A";
        document.getElementById("modal-desk-status").textContent =
            statusMap[deskData.state?.status] ||
            deskData.state?.status ||
            "N/A";
        document.getElementById("modal-desk-position").textContent =
            deskData.state?.position_mm || "N/A";
        document.getElementById("modal-desk-manufacturer").textContent =
            deskData.config?.manufacturer || "N/A";
        document.getElementById("modal-desk-activations").textContent =
            deskData.usage?.activationsCounter || "0";
        document.getElementById("modal-desk-sitstand").textContent =
            deskData.usage?.sitStandCounter || "0";

        // Load desk's database record for user assignment
        try {
            const dbResponse = await fetch(`${DESK_API_ENDPOINT}/${deskId}`);
            if (dbResponse.ok) {
                const dbData = await dbResponse.json();
                populateUserAssignment(dbData.assigned_user);
            }
        } catch (error) {
            console.error("Error loading desk database record:", error);
        }

        // Set height controls
        const currentHeight = deskData.state?.position_mm || 700;
        document.getElementById("modal-height-slider").value = currentHeight;
        document.getElementById("modal-height-input").value = currentHeight;

        modal.classList.add("active");
    }

    async function loadAvailableUsers() {
        try {
            const response = await fetch(USER_API_ENDPOINT);
            if (!response.ok) throw new Error("Failed to fetch users");

            const data = await response.json();
            availableUsers = data.users || [];
        } catch (error) {
            console.error("Error loading users:", error);
            showNotification("Failed to load users");
        }
    }

    function populateUserAssignment(assignedUserId) {
        const select = document.getElementById("modal-assigned-user");
        select.innerHTML = '<option value="">No User Assigned</option>';

        availableUsers.forEach(user => {
            const option = document.createElement("option");
            option.value = user.id;
            option.textContent = `${user.first_name} ${user.last_name}`;
            
            // Disable if user already has a desk (unless it's this desk)
            if (user.desk_id && user.desk_id !== currentDeskId) {
                option.disabled = true;
                option.textContent += " (Already assigned)";
            }
            
            if (user.id === assignedUserId) {
                option.selected = true;
            }
            
            select.appendChild(option);
        });
    }

    // Height slider and input synchronization
    const heightSlider = document.getElementById("modal-height-slider");
    const heightInput = document.getElementById("modal-height-input");

    if (heightSlider && heightInput) {
        heightSlider.addEventListener("input", function() {
            heightInput.value = this.value;
        });

        heightInput.addEventListener("input", function() {
            heightSlider.value = this.value;
        });
    }

    // Preset height buttons
    const presetBtns = document.querySelectorAll(".preset-btn");
    presetBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            const height = this.getAttribute("data-height");
            heightSlider.value = height;
            heightInput.value = height;
        });
    });

    // Apply height button
    const applyHeightBtn = document.getElementById("apply-height-btn");
    if (applyHeightBtn) {
        applyHeightBtn.addEventListener("click", async function() {
            const deskId = currentDeskId;
            const newHeight = heightInput.value;

            if (!deskId) {
                showNotification("No desk selected");
                return;
            }

            if (newHeight < 620 || newHeight > 1270) {
                showNotification("Height must be between 620mm and 1270mm");
                return;
            }

            try {
                applyHeightBtn.disabled = true;
                applyHeightBtn.innerHTML = '<span class="material-icons-round">hourglass_empty</span><span>Applying...</span>';

                const response = await fetch(`${DESK_API_ENDPOINT}/${deskId}/height`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ position_mm: newHeight })
                });

                if (!response.ok) throw new Error("Failed to set height");

                const result = await response.json();
                showNotification(`Height set to ${newHeight}mm`);

                // Update cached data
                if (deskDetailsCache[deskId]) {
                    deskDetailsCache[deskId].state.position_mm = newHeight;
                    document.getElementById("modal-desk-position").textContent = newHeight;
                }
            } catch (error) {
                console.error("Error setting height:", error);
                showNotification("Failed to set height");
            } finally {
                applyHeightBtn.disabled = false;
                applyHeightBtn.innerHTML = '<span class="material-icons-round">height</span><span>Apply Height</span>';
            }
        });
    }

    // User assignment change handler
    const userSelect = document.getElementById("modal-assigned-user");
    if (userSelect) {
        userSelect.addEventListener("change", async function() {
            const userId = this.value;
            const deskId = currentDeskId;

            if (!deskId) {
                showNotification("No desk selected");
                return;
            }

            try {
                userSelect.disabled = true;
                
                let response;
                if (userId) {
                    // Assign user to desk
                    response = await fetch(`${DESK_API_ENDPOINT}/${deskId}/assign`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                } else {
                    // Unassign user from desk
                    response = await fetch(`${DESK_API_ENDPOINT}/${deskId}/unassign`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                }

                if (!response.ok) throw new Error("Failed to update assignment");

                const result = await response.json();
                showNotification(result.message || "Assignment updated");

                // Reload users to update availability
                await loadAvailableUsers();
            } catch (error) {
                console.error("Error updating assignment:", error);
                showNotification("Failed to update assignment");
                
                // Revert selection on error
                populateUserAssignment(null);
            } finally {
                userSelect.disabled = false;
            }
        });
    }

    // Close modal
    if (modalClose) {
        modalClose.addEventListener("click", function () {
            modal.classList.remove("active");
        });
    }

    // Close modal when clicking outside
    if (modal) {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("active");
            }
        });
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
        const retryBtn = deskRowsContainer.querySelector(".retry-btn");
        if (retryBtn) {
            retryBtn.addEventListener("click", function () {
                location.reload();
            });
        }
    }

    function showNotification(message) {
        console.log(`Notification: ${message}`);

        const notification = document.createElement("div");
        notification.className = "notification-toast";
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
            notification.style.animation = "slideOutRight 0.3s ease";
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 2000);
    }

    // Add notification animations
    const style = document.createElement("style");
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
            if (secondsAgo === 0) {
                lastRefreshText.textContent = "Just now";
            } else if (secondsAgo === 1) {
                lastRefreshText.textContent = "1 second ago";
            } else {
                lastRefreshText.textContent = `${secondsAgo} seconds ago`;
            }
        } else if (secondsAgo < 3600) {
            const minutesAgo = Math.floor(secondsAgo / 60);
            if (minutesAgo === 1) {
                lastRefreshText.textContent = "1 minute ago";
            } else {
                lastRefreshText.textContent = `${minutesAgo} minutes ago`;
            }
        } else {
            const hoursAgo = Math.floor(secondsAgo / 3600);
            if (hoursAgo === 1) {
                lastRefreshText.textContent = "1 hour ago";
            } else {
                lastRefreshText.textContent = `${hoursAgo} hours ago`;
            }
            lastRefreshText.textContent =
                hoursAgo === 1 ? "1 hour ago" : `${hoursAgo} hours ago`;
        }
    }

    // Clear interval when page is unloaded
    window.addEventListener("beforeunload", function () {
        if (refreshTimerInterval) {
            clearInterval(refreshTimerInterval);
        }
    });
});
