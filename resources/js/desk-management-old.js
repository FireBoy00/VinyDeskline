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

    // API endpoints
    const DESK_API_ENDPOINT = "/admin/desks";
    const DESKS_DATA_URL = window.deskData?.desksApiUrl || "/admin/arrangement/desks";

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

    function initializePage() {
        // Collect all desk cards that were rendered server-side
        allDeskCards = Array.from(document.querySelectorAll(".desk-card"));
        
        // Attach click event listeners to all desk cards
        allDeskCards.forEach(card => {
            attachCardEventListener(card);
        });
        
        // Update status text
        updateStatusText(`${totalDesks} desk${totalDesks !== 1 ? 's' : ''} loaded`);
        
        // Enable buttons
        selectBtn.disabled = false;
        refreshBtn.disabled = false;
        
        // Start refresh timer
        lastRefreshTime = Date.now();
        startRefreshTimer();
    }

    // Refresh button handler - reload page to get fresh data
    refreshBtn.addEventListener("click", function () {
        if (refreshBtn.disabled) return;

        // Simple page reload to get fresh server-side data
        window.location.reload();
    });

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
        // Get desk data from the card's data attributes
        const card = document.querySelector(`[data-desk-id="${deskId}"]`);
        
        if (!card) {
            showNotification("Desk not found");
            return;
        }

        currentDeskId = deskId;

        // Populate modal with desk data from card attributes
        document.getElementById("modal-desk-id").textContent = deskId;
        document.getElementById("modal-desk-name").textContent = 
            card.getAttribute("data-desk-name") || "N/A";
        document.getElementById("modal-desk-status").textContent =
            statusMap[card.getAttribute("data-status")] ||
            card.getAttribute("data-status") || "N/A";
        document.getElementById("modal-desk-position").textContent =
            card.getAttribute("data-position") || "N/A";
        document.getElementById("modal-desk-manufacturer").textContent =
            card.getAttribute("data-manufacturer") || "N/A";
        document.getElementById("modal-desk-activations").textContent =
            card.getAttribute("data-activations") || "0";
        document.getElementById("modal-desk-sitstand").textContent =
            card.getAttribute("data-sit-stand") || "0";

        // Populate user assignment
        const assignedUserId = card.getAttribute("data-assigned-user");
        populateUserAssignment(assignedUserId || null);

        // Set height controls
        const currentHeight = parseInt(card.getAttribute("data-position")) || 700;
        document.getElementById("modal-height-slider").value = currentHeight;
        document.getElementById("modal-height-input").value = currentHeight;

        modal.classList.add("active");
    }

    function populateUserAssignment(assignedUserId) {
        const select = document.getElementById("modal-assigned-user");
        select.innerHTML = '<option value="">No User Assigned</option>';

        availableUsers.forEach((user) => {
            const option = document.createElement("option");
            option.value = user.id;
            option.textContent = `${user.first_name} ${user.last_name}`;

            // Disable if user already has a desk (unless it's this desk)
            if (user.desk_id && user.desk_id !== currentDeskId) {
                option.disabled = true;
                option.textContent += " (Already assigned)";
            }

            if (user.id == assignedUserId) {
                option.selected = true;
            }

            select.appendChild(option);
        });
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

            if (newHeight < 620 || newHeight > 1270) {
                showNotification("Height must be between 620mm and 1270mm");
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
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                        },
                        body: JSON.stringify({ position_mm: newHeight }),
                    }
                );

                if (!response.ok) throw new Error("Failed to set height");

                const result = await response.json();
                showNotification(`Height set to ${newHeight}mm`);

                // Update card data attribute
                const card = document.querySelector(`[data-desk-id="${deskId}"]`);
                if (card) {
                    card.setAttribute("data-position", newHeight);
                    document.getElementById("modal-desk-position").textContent = newHeight;
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

    // User assignment change handler
    const userSelect = document.getElementById("modal-assigned-user");
    if (userSelect) {
        userSelect.addEventListener("change", async function () {
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
                    response = await fetch(
                        `${DESK_API_ENDPOINT}/${deskId}/assign`,
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
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
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                            },
                        }
                    );
                }

                if (!response.ok)
                    throw new Error("Failed to update assignment");

                const result = await response.json();
                showNotification(result.message || "Assignment updated");

                // Update card attribute
                const card = document.querySelector(`[data-desk-id="${deskId}"]`);
                if (card) {
                    card.setAttribute("data-assigned-user", userId || "");
                }
                
                // Update users list - mark user as having this desk
                const userIndex = availableUsers.findIndex(u => u.id == userId);
                if (userIndex >= 0) {
                    availableUsers[userIndex].desk_id = userId ? deskId : null;
                }
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
