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
    let allDeskCards = [];
    let lastRefreshTime = Date.now();
    let refreshTimerInterval = null;
    let availableUsers = window.deskData?.users || [];
    let currentDeskId = null;
    let totalDesks = 0;
    let roomsByFloor = {}; // Store rooms data
    let allDesksData = []; // Store all desk data for room modal

    // API endpoints
    const DESK_API_ENDPOINT = "/admin/desks";
    const DESKS_DATA_URL =
        window.deskData?.desksApiUrl || "/admin/arrangement/desks";

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

    // Initialize - Load desks via AJAX after page is ready
    loadDesksFromServer();

    async function loadDesksFromServer() {
        try {
            updateStatusText("Loading desks...");
            loadingContainer.classList.remove("hidden");
            selectBtn.disabled = true;
            refreshBtn.disabled = true;

            const response = await fetch(DESKS_DATA_URL);
            if (!response.ok) throw new Error("Failed to fetch desks");

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || "Failed to load desks");
            }

            totalDesks = data.total_desks;
            roomsByFloor = data.rooms_by_floor || {};

            // Store all desks data for room modal
            allDesksData = [];
            Object.values(data.desks_by_floor).forEach((desks) => {
                allDesksData = allDesksData.concat(desks);
            });

            // Render desks by floor progressively (loading will be hidden inside)
            await renderDesksByFloorProgressively(data.desks_by_floor);

            // Collect all desk cards
            allDeskCards = Array.from(document.querySelectorAll(".desk-card"));

            // Attach event listeners
            allDeskCards.forEach((card) => {
                attachCardEventListener(card);
            });

            // Update status
            updateStatusText(
                `${totalDesks} desk${totalDesks !== 1 ? "s" : ""} loaded`
            );

            // Enable buttons
            selectBtn.disabled = false;
            refreshBtn.disabled = false;

            // Start refresh timer
            lastRefreshTime = Date.now();
            startRefreshTimer();
        } catch (error) {
            console.error("Error loading desks:", error);
            loadingContainer.classList.add("hidden");
            showError("Failed to load desks. Please try again.");
            updateStatusText("Failed to load desks");
            refreshBtn.disabled = false;
        }
    }

    async function renderDesksByFloorProgressively(desksByFloor) {
        if (!desksByFloor || Object.keys(desksByFloor).length === 0) {
            deskRowsContainer.innerHTML = `
                <div class="no-results-message active">
                    <span class="material-icons-round">error_outline</span>
                    <p>No desks found</p>
                </div>
            `;
            return;
        }

        // Convert to array then sort (highest to lowest, then unassigned)
        const floors = Object.entries(desksByFloor);
        floors.sort((a, b) => {
            const floorA = a[0];
            const floorB = b[0];
            if (floorA === "unassigned") return 1;
            if (floorB === "unassigned") return -1;
            return parseInt(floorB) - parseInt(floorA);
        });

        // Render floors one by one with slight delay for progressive loading effect
        for (let i = 0; i < floors.length; i++) {
            const [floorKey, desks] = floors[i];

            const floorContainer = createFloorContainer(floorKey);
            const deskItems = floorContainer.querySelector(".desk-items");

            // First, add room cards if this floor has rooms
            const roomsOnFloor = roomsByFloor[floorKey] || [];
            roomsOnFloor.forEach((room) => {
                const roomCard = createRoomCard(room, floorKey);
                deskItems.appendChild(roomCard);
            });

            // Then add desk cards - but only desks that are NOT in a room
            desks.forEach((desk) => {
                // Only show desks that are directly on the floor (not in a room)
                if (!desk.room_id) {
                    const deskCard = createDeskCard(desk);
                    deskItems.appendChild(deskCard);
                }
            });

            deskRowsContainer.appendChild(floorContainer);

            // Hide loading spinner after first floor renders
            if (i === 0) {
                loadingContainer.classList.add("hidden");
            }

            // Delay between floors for progressive visual effect
            if (i < floors.length - 1) {
                await new Promise((resolve) => setTimeout(resolve, 50));
            }
        }
    }

    function createFloorContainer(floorKey) {
        const row = document.createElement("div");
        row.className = "desk-row";
        row.setAttribute("data-floor", floorKey);

        // Add special styling for unassigned section
        if (floorKey === "unassigned") {
            row.classList.add("unassigned-section");
        }

        const deskItems = document.createElement("div");
        deskItems.className = "desk-items";

        const separator = document.createElement("div");
        separator.className = "row-separator";

        const rowHeader = document.createElement("div");
        rowHeader.className = "row-header";

        if (floorKey === "unassigned") {
            rowHeader.classList.add("unassigned");
            rowHeader.innerHTML = `
                <span class="row-number unassigned">N/A</span>
            `;
        } else {
            rowHeader.innerHTML = `
                <span class="row-label">Floor</span>
                <span class="row-number">${floorKey}</span>
            `;
        }

        row.appendChild(deskItems);
        row.appendChild(separator);
        row.appendChild(rowHeader);

        return row;
    }

    function createDeskCard(desk) {
        const card = document.createElement("div");
        card.className = "desk-card";
        card.setAttribute("data-desk-id", desk.desk_id);
        card.setAttribute("data-desk-name", desk.display_name);
        card.setAttribute("data-position", desk.current_position || "");
        card.setAttribute("data-status", desk.current_status);
        card.setAttribute("data-manufacturer", desk.manufacturer);
        card.setAttribute("data-activations", desk.activations);
        card.setAttribute("data-sit-stand", desk.sit_stand);
        card.setAttribute("data-assigned-user", desk.assigned_user_id || "");

        const statusClass = getStatusClass(desk.current_status);

        card.innerHTML = `
            <div class="desk-icon">
                <span class="material-icons-round">desk</span>
            </div>
            <div class="desk-status ${statusClass}"></div>
        `;

        return card;
    }

    function createRoomCard(room, floorKey) {
        const card = document.createElement("div");
        card.className = "room-card";
        card.setAttribute("data-room-id", room.id);
        card.setAttribute("data-room-name", room.name);
        card.setAttribute("data-floor-key", floorKey);

        card.innerHTML = `
            <div class="room-card-content">
                <div class="room-name">${room.name}</div>
                <div class="room-desk-count">
                    <span class="material-icons-round">desk</span>
                    <span>${room.desks_count} desk${
            room.desks_count !== 1 ? "s" : ""
        }</span>
                </div>
            </div>
            <span class="material-icons-round room-icon">meeting_room</span>
        `;

        // Add click event to open room modal
        card.addEventListener("click", function (e) {
            if (selectMode) {
                // In select mode, can't select rooms
                return;
            }
            openRoomModal(room);
        });

        return card;
    }

    function getStatusClass(status) {
        const normalized = status.toLowerCase();
        if (
            normalized.includes("error") ||
            normalized.includes("collision") ||
            normalized.includes("faulty")
        )
            return "faulty";
        if (
            normalized.includes("moving") ||
            normalized.includes("use") ||
            normalized.includes("occupied")
        )
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

    // Refresh button handler - reload desks
    refreshBtn.addEventListener("click", function () {
        if (refreshBtn.disabled) return;

        // Reset selections when refreshing
        if (selectMode) {
            toggleSelectMode();
        }

        // Clear existing desks and show loading
        deskRowsContainer.innerHTML = "";
        loadingContainer.classList.remove("hidden");

        stopRefreshTimer();
        loadDesksFromServer();
    });

    function toggleSelectMode() {
        selectMode = false;
        selectBtn.classList.remove("primary");
        const icon = selectBtn.querySelector(".material-icons-round");
        icon.textContent = "check_box_outline_blank";
        actionsBtn.style.display = "none";
        actionsDropdown.classList.remove("active");
        document
            .querySelectorAll(".desk-card")
            .forEach((card) => card.classList.remove("selected"));
        selectedDesks = [];
        updateStatusText(`${totalDesks} desks loaded`);
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
                updateStatusText(`${totalDesks} desks`);
            } else {
                updateStatusText(
                    `${selectedDesks.length} desk${
                        selectedDesks.length !== 1 ? "s" : ""
                    } selected`
                );
            }
        } else {
            updateStatusText(`${totalDesks} desks loaded`);
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
                    `${totalDesks} desks - Select desks to perform actions`
                );
            } else {
                icon.textContent = "check_box_outline_blank";
                actionsBtn.style.display = "none";
                actionsDropdown.classList.remove("active");
                document
                    .querySelectorAll(".desk-card")
                    .forEach((card) => card.classList.remove("selected"));
                selectedDesks = [];
                updateStatusText(`${totalDesks} desks loaded`);
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
        // Get fresh desk data from the card's data attributes
        const card = document.querySelector(`[data-desk-id="${deskId}"]`);

        if (!card) {
            showNotification("Desk not found");
            return;
        }

        currentDeskId = deskId;

        // Force clear all modal content first to ensure fresh data is visible
        document.getElementById("modal-desk-id").textContent = "";
        document.getElementById("modal-desk-name").textContent = "";
        document.getElementById("modal-desk-status").textContent = "";
        document.getElementById("modal-desk-position").textContent = "";
        document.getElementById("modal-desk-manufacturer").textContent = "";
        document.getElementById("modal-desk-activations").textContent = "";
        document.getElementById("modal-desk-sitstand").textContent = "";

        // Now populate with fresh data from card attributes
        document.getElementById("modal-desk-id").textContent = deskId;
        document.getElementById("modal-desk-name").textContent =
            card.getAttribute("data-desk-name") || "N/A";
        document.getElementById("modal-desk-status").textContent =
            statusMap[card.getAttribute("data-status")] ||
            card.getAttribute("data-status") ||
            "N/A";
        document.getElementById("modal-desk-position").textContent =
            card.getAttribute("data-position") || "N/A";
        document.getElementById("modal-desk-manufacturer").textContent =
            card.getAttribute("data-manufacturer") || "N/A";
        document.getElementById("modal-desk-activations").textContent =
            card.getAttribute("data-activations") || "0";
        document.getElementById("modal-desk-sitstand").textContent =
            card.getAttribute("data-sit-stand") || "0";

        // Populate user assignment with custom select
        const assignedUserId = card.getAttribute("data-assigned-user");
        populateUserAssignmentCustomSelect(assignedUserId || null);

        // Set height controls
        const currentHeight =
            parseInt(card.getAttribute("data-position")) || 700;
        document.getElementById("modal-height-slider").value = currentHeight;
        document.getElementById("modal-height-input").value = currentHeight;

        modal.classList.add("active");
    }

    function populateUserAssignmentCustomSelect(assignedUserId) {
        const selectContainer = document.getElementById(
            "modal-assigned-user-select"
        );
        const selectSelected = document.getElementById(
            "modal-assigned-user-selected"
        );
        const selectItems = document.getElementById(
            "modal-assigned-user-items"
        );

        if (!selectContainer || !selectSelected || !selectItems) return;

        // Clear existing items
        selectItems.innerHTML = "";

        // Add "No User Assigned" option
        const noUserDiv = document.createElement("div");
        noUserDiv.textContent = "No User Assigned";
        noUserDiv.setAttribute("data-value", "");
        noUserDiv.addEventListener("click", function () {
            handleUserSelection("", "No User Assigned");
        });
        selectItems.appendChild(noUserDiv);

        // Set default selected text
        let selectedText = "No User Assigned";
        let selectedFound = false;

        // Add users
        availableUsers.forEach((user) => {
            const userDiv = document.createElement("div");
            const userName = `${user.first_name} ${user.last_name}`;

            // Check if user already has a desk (unless it's this desk)
            if (user.desk_id && user.desk_id !== currentDeskId) {
                userDiv.textContent = `${userName} (Already assigned)`;
                userDiv.classList.add("disabled");
                userDiv.style.opacity = "0.5";
                userDiv.style.cursor = "not-allowed";
            } else {
                userDiv.textContent = userName;
                userDiv.setAttribute("data-value", user.id);
                userDiv.addEventListener("click", function () {
                    if (!this.classList.contains("disabled")) {
                        handleUserSelection(user.id, userName);
                    }
                });
            }

            // Mark as selected if this is the assigned user
            if (user.id == assignedUserId) {
                userDiv.classList.add("selected");
                selectedText = userName;
                selectedFound = true;
            }

            selectItems.appendChild(userDiv);
        });

        // Update selected display
        selectSelected.textContent = selectedText;

        // Mark "No User Assigned" as selected if no user is assigned
        if (!selectedFound && !assignedUserId) {
            noUserDiv.classList.add("selected");
        }

        // Handle custom select dropdown toggle
        selectSelected.onclick = function () {
            selectContainer.classList.toggle("active");
            selectItems.classList.toggle("hidden");
        };

        // Close dropdown when clicking outside
        document.addEventListener("click", function closeDropdown(e) {
            if (!selectContainer.contains(e.target)) {
                selectContainer.classList.remove("active");
                selectItems.classList.add("hidden");
            }
        });
    }

    async function handleUserSelection(userId, userName) {
        const selectContainer = document.getElementById(
            "modal-assigned-user-select"
        );
        const selectSelected = document.getElementById(
            "modal-assigned-user-selected"
        );
        const selectItems = document.getElementById(
            "modal-assigned-user-items"
        );

        // Close dropdown
        selectContainer.classList.remove("active");
        selectItems.classList.add("hidden");

        // Update display
        selectSelected.textContent = userName;

        // Update selected state in dropdown
        selectItems.querySelectorAll("div").forEach((div) => {
            div.classList.remove("selected");
            if (
                div.getAttribute("data-value") == userId ||
                (!userId && div.textContent === "No User Assigned")
            ) {
                div.classList.add("selected");
            }
        });

        const deskId = currentDeskId;

        if (!deskId) {
            showNotification("No desk selected");
            return;
        }

        try {
            let response;
            if (userId) {
                // Assign user to desk
                response = await fetch(
                    `${DESK_API_ENDPOINT}/${deskId}/assign`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": window.deskData.csrfToken,
                        },
                        body: JSON.stringify({ user_id: userId }),
                    }
                );
            } else {
                // Unassign user from desk
                response = await fetch(
                    `${DESK_API_ENDPOINT}/${deskId}/unassign`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": window.deskData.csrfToken,
                        },
                    }
                );
            }

            if (!response.ok) throw new Error("Failed to update assignment");

            const result = await response.json();
            showNotification(result.message || "Assignment updated");

            // Update card attribute
            const card = document.querySelector(`[data-desk-id="${deskId}"]`);
            if (card) {
                card.setAttribute("data-assigned-user", userId || "");
            }

            // Update users list - mark user as having this desk
            const userIndex = availableUsers.findIndex((u) => u.id == userId);
            if (userIndex >= 0) {
                availableUsers[userIndex].desk_id = userId ? deskId : null;
            }
        } catch (error) {
            console.error("Error updating assignment:", error);
            showNotification("Failed to update assignment");

            // Revert selection on error
            populateUserAssignmentCustomSelect(null);
        }
    }

    // Height slider and input synchronization
    const heightSlider = document.getElementById("modal-height-slider");
    const heightInput = document.getElementById("modal-height-input");

    if (heightSlider && heightInput) {
        heightSlider.addEventListener("input", function () {
            heightInput.value = this.value;
        });

        heightInput.addEventListener("input", function () {
            heightSlider.value = this.value;
        });
    }

    // Preset height buttons
    const presetBtns = document.querySelectorAll(".preset-btn");
    presetBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
            const height = this.getAttribute("data-height");
            heightSlider.value = height;
            heightInput.value = height;
        });
    });

    // Apply height button
    const applyHeightBtn = document.getElementById("apply-height-btn");
    if (applyHeightBtn) {
        applyHeightBtn.addEventListener("click", async function () {
            const deskId = currentDeskId;
            const newHeight = heightInput.value;

            if (!deskId) {
                showNotification("No desk selected");
                return;
            }

            if (newHeight < 680 || newHeight > 1320) {
                showNotification("Height must be between 680mm and 1320mm");
                return;
            }

            try {
                applyHeightBtn.disabled = true;
                applyHeightBtn.innerHTML =
                    '<span class="material-icons-round">hourglass_empty</span><span>Applying...</span>';

                const response = await fetch(
                    `${DESK_API_ENDPOINT}/${deskId}/height`,
                    {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": window.deskData.csrfToken,
                        },
                        body: JSON.stringify({
                            position_mm: parseInt(newHeight),
                        }),
                    }
                );

                if (!response.ok) throw new Error("Failed to set height");

                const result = await response.json();
                showNotification(`Height set to ${newHeight}mm`);

                // Update card data attribute
                const card = document.querySelector(
                    `[data-desk-id="${deskId}"]`
                );
                if (card) {
                    card.setAttribute("data-position", newHeight);
                    document.getElementById("modal-desk-position").textContent =
                        newHeight;
                }
            } catch (error) {
                console.error("Error setting height:", error);
                showNotification("Failed to set height");
            } finally {
                applyHeightBtn.disabled = false;
                applyHeightBtn.innerHTML =
                    '<span class="material-icons-round">height</span><span>Apply Height</span>';
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
                loadDesksFromServer();
            });
        }
    }

    function showNotification(message) {
        console.log(`Notification: ${message}`);

        const notification = document.createElement("div");
        notification.className = "notification-toast";
        notification.textContent = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add("closing");
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 2000);
    }

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
        }
    }

    // Clear interval when page is unloaded
    window.addEventListener("beforeunload", function () {
        if (refreshTimerInterval) {
            clearInterval(refreshTimerInterval);
        }
    });

    // Room Modal functionality
    const roomModal = document.getElementById("room-modal");
    const roomModalClose = document.getElementById("room-modal-close");
    const roomModalTitle = document.getElementById("room-modal-title");
    const roomModalSubtitle = document.getElementById("room-modal-subtitle");
    const roomDesksGrid = document.getElementById("room-desks-grid");
    const roomModalEmpty = document.getElementById("room-modal-empty");

    function openRoomModal(room) {
        // Set room title
        roomModalTitle.textContent = room.name;
        roomModalSubtitle.textContent = `${room.desks_count} desk${
            room.desks_count !== 1 ? "s" : ""
        }`;

        // Find all desks in this room
        const desksInRoom = allDesksData.filter(
            (desk) => desk.room_id === room.id
        );

        // Clear previous content
        roomDesksGrid.innerHTML = "";

        if (desksInRoom.length === 0) {
            roomDesksGrid.classList.add("hidden");
            roomModalEmpty.classList.remove("hidden");
        } else {
            roomDesksGrid.classList.remove("hidden");
            roomModalEmpty.classList.add("hidden");

            // Create desk cards for this room
            desksInRoom.forEach((desk) => {
                const deskCard = createDeskCard(desk);
                // Re-attach click event for desk modal
                deskCard.addEventListener("click", function () {
                    // Close room modal first
                    roomModal.classList.remove("active");
                    // Open desk modal
                    selectedDesks = [desk.desk_id];
                    currentViewIndex = 0;
                    openDeskModal(desk.desk_id);
                    updateModalNavigation();
                });
                roomDesksGrid.appendChild(deskCard);
            });
        }

        // Show modal
        roomModal.classList.add("active");
    }

    // Close room modal
    if (roomModalClose) {
        roomModalClose.addEventListener("click", function () {
            roomModal.classList.remove("active");
        });
    }

    // Close room modal when clicking outside
    if (roomModal) {
        roomModal.addEventListener("click", function (e) {
            if (e.target === roomModal) {
                roomModal.classList.remove("active");
            }
        });
    }
});
