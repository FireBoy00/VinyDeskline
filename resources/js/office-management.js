// Office Management JavaScript
// This file handles floor and room management functionality

document.addEventListener("DOMContentLoaded", function () {
    // Get CSRF token
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    // State
    let floors = [];
    let rooms = [];
    let desks = [];
    let editingFloor = null;
    let editingRoom = null;
    let pendingDeskAssignment = null;
    let pendingRoomAssignment = null;
    let selectedDesksForAssignment = [];
    let selectedRoomsForAssignment = [];

    // Load initial data
    loadFloors();
    loadRooms();
    loadDesks();

    // Floor Modal Controls
    const floorModal = document.getElementById("floor-modal");
    const addFloorBtn = document.getElementById("add-floor-btn");
    const closeFloorModal = document.getElementById("close-floor-modal");
    const cancelFloorBtn = document.getElementById("cancel-floor-btn");
    const saveFloorBtn = document.getElementById("save-floor-btn");
    const floorForm = document.getElementById("floor-form");

    // Room Modal Controls
    const roomModal = document.getElementById("room-modal");
    const addRoomBtn = document.getElementById("add-room-btn");
    const closeRoomModal = document.getElementById("close-room-modal");
    const cancelRoomBtn = document.getElementById("cancel-room-btn");
    const saveRoomBtn = document.getElementById("save-room-btn");
    const roomForm = document.getElementById("room-form");

    // Desk Selection Modal Controls
    const deskSelectionModal = document.getElementById("desk-selection-modal");
    const closeDeskSelectionModal = document.getElementById(
        "close-desk-selection-modal"
    );
    const cancelDeskSelectionBtn = document.getElementById(
        "cancel-desk-selection-btn"
    );
    const confirmDeskSelectionBtn = document.getElementById(
        "confirm-desk-selection-btn"
    );

    // Room Selection Modal Controls
    const roomSelectionModal = document.getElementById("room-selection-modal");
    const closeRoomSelectionModal = document.getElementById(
        "close-room-selection-modal"
    );
    const cancelRoomSelectionBtn = document.getElementById(
        "cancel-room-selection-btn"
    );
    const confirmRoomSelectionBtn = document.getElementById(
        "confirm-room-selection-btn"
    );

    // Floor Modal Event Listeners
    addFloorBtn.addEventListener("click", () => openFloorModal());
    closeFloorModal.addEventListener("click", () => closeModal(floorModal));
    cancelFloorBtn.addEventListener("click", () => closeModal(floorModal));
    saveFloorBtn.addEventListener("click", saveFloor);

    // Room Modal Event Listeners
    addRoomBtn.addEventListener("click", () => openRoomModal());
    closeRoomModal.addEventListener("click", () => closeModal(roomModal));
    cancelRoomBtn.addEventListener("click", () => closeModal(roomModal));
    saveRoomBtn.addEventListener("click", saveRoom);

    // Desk Selection Modal Event Listeners
    closeDeskSelectionModal.addEventListener("click", () =>
        closeModal(deskSelectionModal)
    );
    cancelDeskSelectionBtn.addEventListener("click", () =>
        closeModal(deskSelectionModal)
    );
    confirmDeskSelectionBtn.addEventListener("click", confirmDeskSelection);

    // Room Selection Modal Event Listeners
    closeRoomSelectionModal.addEventListener("click", () =>
        closeModal(roomSelectionModal)
    );
    cancelRoomSelectionBtn.addEventListener("click", () =>
        closeModal(roomSelectionModal)
    );
    confirmRoomSelectionBtn.addEventListener("click", confirmRoomSelection);

    // Add desk/room to floor buttons
    document
        .getElementById("add-desk-to-floor-btn")
        ?.addEventListener("click", () => {
            openDeskSelectionModal("floor", editingFloor.id);
        });

    document
        .getElementById("add-room-to-floor-btn")
        ?.addEventListener("click", () => {
            openRoomSelectionModal(editingFloor.id);
        });

    // Add desk to room button
    document
        .getElementById("add-desk-to-room-btn")
        ?.addEventListener("click", () => {
            openDeskSelectionModal("room", editingRoom.id);
        });

    // Functions
    async function loadFloors() {
        try {
            const response = await fetch("/api/floors");
            const data = await response.json();

            if (data.success) {
                floors = data.floors;
                renderFloors();
                populateFloorDropdown();
            }
        } catch (error) {
            console.error("Error loading floors:", error);
        }
    }

    async function loadRooms() {
        try {
            const response = await fetch("/api/rooms");
            const data = await response.json();

            if (data.success) {
                rooms = data.rooms;
                renderRooms();
            }
        } catch (error) {
            console.error("Error loading rooms:", error);
        }
    }

    async function loadDesks() {
        try {
            const response = await fetch("/api/desks");
            const data = await response.json();

            if (data.success) {
                desks = data.desks;
            }
        } catch (error) {
            console.error("Error loading desks:", error);
        }
    }

    function renderFloors() {
        const floorsGrid = document.getElementById("floors-grid");
        const floorsCount = document.getElementById("floors-count");
        const noFloorsMessage = document.getElementById("no-floors-message");
        const floorsLoading = document.getElementById("floors-loading");

        floorsLoading.style.display = "none";
        floorsCount.textContent = floors.length;

        if (floors.length === 0) {
            floorsGrid.style.display = "none";
            noFloorsMessage.style.display = "block";
            return;
        }

        floorsGrid.style.display = "grid";
        noFloorsMessage.style.display = "none";

        floorsGrid.innerHTML = floors
            .map(
                (floor) => `
            <div class="floor-card" data-id="${floor.id}">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${floor.name}</h3>
                        <p class="card-subtitle">Floor ${floor.floor_number}</p>
                    </div>
                    <div class="card-actions">
                        <button class="card-action-btn edit" onclick="editFloor(${
                            floor.id
                        })">
                            <span class="material-icons-round">edit</span>
                        </button>
                        <button class="card-action-btn delete" onclick="deleteFloor(${
                            floor.id
                        })">
                            <span class="material-icons-round">delete</span>
                        </button>
                    </div>
                </div>
                ${
                    floor.description
                        ? `<p class="card-description">${floor.description}</p>`
                        : ""
                }
                <div class="card-stats">
                    <div class="card-stat">
                        <span class="material-icons-round">meeting_room</span>
                        <span>${floor.rooms_count || 0} rooms</span>
                    </div>
                    <div class="card-stat">
                        <span class="material-icons-round">desk</span>
                        <span>${floor.desks_count || 0} desks</span>
                    </div>
                </div>
            </div>
        `
            )
            .join("");
    }

    function renderRooms() {
        const roomsGrid = document.getElementById("rooms-grid");
        const roomsCount = document.getElementById("rooms-count");
        const noRoomsMessage = document.getElementById("no-rooms-message");
        const roomsLoading = document.getElementById("rooms-loading");

        roomsLoading.style.display = "none";
        roomsCount.textContent = rooms.length;

        if (rooms.length === 0) {
            roomsGrid.style.display = "none";
            noRoomsMessage.style.display = "block";
            return;
        }

        roomsGrid.style.display = "grid";
        noRoomsMessage.style.display = "none";

        roomsGrid.innerHTML = rooms
            .map(
                (room) => `
            <div class="room-card" data-id="${room.id}">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${room.name}</h3>
                        <p class="card-subtitle">${
                            room.floor
                                ? `Floor ${room.floor.floor_number}`
                                : "No floor assigned"
                        }</p>
                    </div>
                    <div class="card-actions">
                        <button class="card-action-btn edit" onclick="editRoom(${
                            room.id
                        })">
                            <span class="material-icons-round">edit</span>
                        </button>
                        <button class="card-action-btn delete" onclick="deleteRoom(${
                            room.id
                        })">
                            <span class="material-icons-round">delete</span>
                        </button>
                    </div>
                </div>
                ${
                    room.description
                        ? `<p class="card-description">${room.description}</p>`
                        : ""
                }
                <div class="card-stats">
                    <div class="card-stat">
                        <span class="material-icons-round">desk</span>
                        <span>${room.desks_count || 0} desks</span>
                    </div>
                </div>
            </div>
        `
            )
            .join("");
    }

    function populateFloorDropdown() {
        const roomFloorItems = document.getElementById("room-floor-items");

        roomFloorItems.innerHTML =
            '<div data-value="">No Floor Assignment</div>' +
            floors
                .map(
                    (floor) =>
                        `<div data-value="${floor.id}">Floor ${floor.floor_number} - ${floor.name}</div>`
                )
                .join("");

        // Reinitialize the custom select
        initializeCustomSelect(document.getElementById("room-floor-select"));
    }

    function openFloorModal(floor = null) {
        editingFloor = floor;
        const title = document.getElementById("floor-modal-title");
        const managementSections = document.getElementById(
            "floor-management-sections"
        );

        if (floor) {
            title.textContent = "Edit Floor";
            document.getElementById("floor-id").value = floor.id;
            document.getElementById("floor-name").value = floor.name;
            document.getElementById("floor-number").value = floor.floor_number;
            document.getElementById("floor-description").value =
                floor.description || "";

            // Show management sections when editing
            managementSections.style.display = "block";

            // Load rooms and desks for this floor
            loadFloorRooms(floor.id);
            loadFloorDesks(floor.id);
        } else {
            title.textContent = "Add Floor";
            floorForm.reset();
            document.getElementById("floor-id").value = "";
            managementSections.style.display = "none";
        }

        floorModal.classList.add("active");
    }

    function openRoomModal(room = null) {
        editingRoom = room;
        const title = document.getElementById("room-modal-title");
        const managementSections = document.getElementById(
            "room-management-sections"
        );

        if (room) {
            title.textContent = "Edit Room";
            document.getElementById("room-id").value = room.id;
            document.getElementById("room-name").value = room.name;
            document.getElementById("room-description").value =
                room.description || "";

            // Set floor dropdown
            const floorSelect = document.getElementById("room-floor-select");
            const floorSelected = document.getElementById(
                "room-floor-selected"
            );
            if (room.floor_id) {
                const floor = floors.find((f) => f.id === room.floor_id);
                floorSelected.textContent = floor
                    ? `Floor ${floor.floor_number} - ${floor.name}`
                    : "No Floor Assignment";
                floorSelected.dataset.value = room.floor_id || "";
            } else {
                floorSelected.textContent = "No Floor Assignment";
                floorSelected.dataset.value = "";
            }

            // Show management sections when editing
            managementSections.style.display = "block";

            // Load desks for this room
            loadRoomDesks(room.id);
        } else {
            title.textContent = "Add Room";
            roomForm.reset();
            document.getElementById("room-id").value = "";
            document.getElementById("room-floor-selected").textContent =
                "No Floor Assignment";
            document.getElementById("room-floor-selected").dataset.value = "";
            managementSections.style.display = "none";
        }

        roomModal.classList.add("active");
    }

    function closeModal(modal) {
        modal.classList.remove("active");
    }

    async function saveFloor() {
        const id = document.getElementById("floor-id").value;
        let name = document.getElementById("floor-name").value.trim();
        const floorNumber = document.getElementById("floor-number").value;
        const description = document.getElementById("floor-description").value;
        const errorMsg = document.getElementById("floor-error-message");
        const errorText = document.getElementById("floor-error-text");

        // Hide previous errors
        errorMsg.classList.remove("active");

        // Validate floor number
        if (!floorNumber) {
            errorText.textContent = "Floor number is required";
            errorMsg.classList.add("active");
            return;
        }

        // Auto-generate floor name if not provided
        if (!name) {
            name = `Floor ${floorNumber}`;
        }

        try {
            const url = id ? `/api/floors/${id}` : "/api/floors";
            const method = id ? "PUT" : "POST";

            const response = await fetch(url, {
                method: method,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    name,
                    floor_number: floorNumber,
                    description,
                }),
            });

            const data = await response.json();

            if (data.success) {
                closeModal(floorModal);
                loadFloors();
            } else {
                errorText.textContent = data.message || "Error saving floor";
                errorMsg.classList.add("active");
            }
        } catch (error) {
            console.error("Error saving floor:", error);
            errorText.textContent = "Network error. Please try again.";
            errorMsg.classList.add("active");
        }
    }

    async function saveRoom() {
        const id = document.getElementById("room-id").value;
        const name = document.getElementById("room-name").value;
        const floorId =
            document.getElementById("room-floor-selected").dataset.value ||
            null;
        const description = document.getElementById("room-description").value;
        const errorMsg = document.getElementById("room-error-message");
        const errorText = document.getElementById("room-error-text");

        // Hide previous errors
        errorMsg.classList.remove("active");

        if (!name) {
            errorText.textContent = "Room name is required";
            errorMsg.classList.add("active");
            return;
        }

        try {
            const url = id ? `/api/rooms/${id}` : "/api/rooms";
            const method = id ? "PUT" : "POST";

            const response = await fetch(url, {
                method: method,
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ name, floor_id: floorId, description }),
            });

            const data = await response.json();

            if (data.success) {
                closeModal(roomModal);
                loadRooms();
                loadFloors();
            } else {
                errorText.textContent = data.message || "Error saving room";
                errorMsg.classList.add("active");
            }
        } catch (error) {
            console.error("Error saving room:", error);
            errorText.textContent = "Network error. Please try again.";
            errorMsg.classList.add("active");
        }
    }

    // Load rooms for a specific floor
    async function loadFloorRooms(floorId) {
        const roomsList = document.getElementById("floor-rooms-list");
        const floorRooms = rooms.filter((r) => r.floor_id === floorId);

        if (floorRooms.length === 0) {
            roomsList.innerHTML =
                '<p style="text-align: center; color: rgba(0, 79, 110, 0.6); padding: 20px;">No rooms assigned</p>';
            return;
        }

        roomsList.innerHTML = floorRooms
            .map(
                (room) => `
            <div class="desk-item">
                <div class="desk-item-info">
                    <span class="material-icons-round">meeting_room</span>
                    <div>
                        <div class="desk-item-name">${room.name}</div>
                        <div class="desk-item-details">${
                            room.desks_count || 0
                        } desks</div>
                    </div>
                </div>
                <div class="desk-item-actions">
                    <button class="desk-item-btn remove" onclick="removeRoomFromFloor(${
                        room.id
                    })">
                        <span class="material-icons-round" style="font-size: 16px;">close</span>
                        Remove
                    </button>
                </div>
            </div>
        `
            )
            .join("");
    }

    // Load desks for a specific floor
    async function loadFloorDesks(floorId) {
        const desksList = document.getElementById("floor-desks-list");
        const floorDesks = desks.filter(
            (d) => d.floor_id === floorId && !d.room_id
        );

        if (floorDesks.length === 0) {
            desksList.innerHTML =
                '<p style="text-align: center; color: rgba(0, 79, 110, 0.6); padding: 20px;">No desks assigned directly to floor</p>';
            return;
        }

        desksList.innerHTML = floorDesks
            .map(
                (desk) => `
            <div class="desk-item">
                <div class="desk-item-info">
                    <span class="material-icons-round">desk</span>
                    <div>
                        <div class="desk-item-name">${
                            desk.name || desk.desk_id
                        }</div>
                        <div class="desk-item-details">ID: ${desk.desk_id}</div>
                    </div>
                </div>
                <div class="desk-item-actions">
                    <button class="desk-item-btn remove" onclick="removeDeskFromFloor('${
                        desk.desk_id
                    }')">
                        <span class="material-icons-round" style="font-size: 16px;">close</span>
                        Remove
                    </button>
                </div>
            </div>
        `
            )
            .join("");
    }

    // Load desks for a specific room
    async function loadRoomDesks(roomId) {
        const desksList = document.getElementById("room-desks-list");
        const roomDesks = desks.filter((d) => d.room_id === roomId);

        if (roomDesks.length === 0) {
            desksList.innerHTML =
                '<p style="text-align: center; color: rgba(0, 79, 110, 0.6); padding: 20px;">No desks assigned</p>';
            return;
        }

        desksList.innerHTML = roomDesks
            .map(
                (desk) => `
            <div class="desk-item">
                <div class="desk-item-info">
                    <span class="material-icons-round">desk</span>
                    <div>
                        <div class="desk-item-name">${
                            desk.name || desk.desk_id
                        }</div>
                        <div class="desk-item-details">ID: ${desk.desk_id}</div>
                    </div>
                </div>
                <div class="desk-item-actions">
                    <button class="desk-item-btn remove" onclick="removeDeskFromRoom('${
                        desk.desk_id
                    }')">
                        <span class="material-icons-round" style="font-size: 16px;">close</span>
                        Remove
                    </button>
                </div>
            </div>
        `
            )
            .join("");
    }

    // Open desk selection modal
    function openDeskSelectionModal(assignType, targetId) {
        pendingDeskAssignment = { type: assignType, id: targetId };
        selectedDesksForAssignment = [];

        const deskSelectItems = document.getElementById("desk-select-items");
        const title = document.getElementById("desk-selection-title");
        const selectedDisplay = document.getElementById(
            "selected-desks-display"
        );
        const errorMsg = document.getElementById(
            "desk-selection-error-message"
        );

        // Hide errors and reset display
        errorMsg.classList.remove("active");
        selectedDisplay.classList.add("empty");
        selectedDisplay.innerHTML = "No desks selected";

        title.textContent =
            assignType === "floor" ? "Add Desks to Floor" : "Add Desks to Room";

        // Filter available desks based on context
        let availableDesks = desks;
        if (assignType === "floor") {
            // For floors, exclude desks that are in rooms on different floors
            availableDesks = desks.filter(
                (d) => !d.room_id || d.floor_id === targetId
            );
        }

        if (availableDesks.length === 0) {
            deskSelectItems.innerHTML =
                "<div disabled>No desks available</div>";
        } else {
            deskSelectItems.innerHTML = availableDesks
                .map((desk) => {
                    const currentLocation = desk.room_id
                        ? `Room: ${
                              rooms.find((r) => r.id === desk.room_id)?.name
                          }`
                        : desk.floor_id
                        ? `Floor: ${
                              floors.find((f) => f.id === desk.floor_id)?.name
                          }`
                        : "Unassigned";

                    return `<div data-value="${desk.desk_id}" data-name="${
                        desk.name || desk.desk_id
                    }">${desk.name || desk.desk_id} - ${currentLocation}</div>`;
                })
                .join("");
        }

        initializeMultiSelect(
            document.getElementById("desk-select"),
            updateSelectedDesksDisplay
        );
        deskSelectionModal.classList.add("active");
    }

    // Update selected desks display
    function updateSelectedDesksDisplay() {
        const selectedDisplay = document.getElementById(
            "selected-desks-display"
        );

        if (selectedDesksForAssignment.length === 0) {
            selectedDisplay.classList.add("empty");
            selectedDisplay.innerHTML = "No desks selected";
        } else {
            selectedDisplay.classList.remove("empty");
            selectedDisplay.innerHTML = selectedDesksForAssignment
                .map(
                    (desk) => `
                    <div class="selected-item-pill">
                        <span>${desk.name}</span>
                        <span class="material-icons-round remove-icon" onclick="removeSelectedDesk('${desk.id}')">close</span>
                    </div>
                `
                )
                .join("");
        }
    }

    // Remove selected desk
    window.removeSelectedDesk = function (deskId) {
        selectedDesksForAssignment = selectedDesksForAssignment.filter(
            (d) => d.id !== deskId
        );
        updateSelectedDesksDisplay();

        // Update the select items to reflect the change
        const items = document.querySelectorAll(
            "#desk-select-items div[data-value]"
        );
        items.forEach((item) => {
            if (item.dataset.value === deskId) {
                item.classList.remove("selected-multi");
            }
        });
    };

    // Open room selection modal
    function openRoomSelectionModal(floorId) {
        pendingRoomAssignment = floorId;
        selectedRoomsForAssignment = [];

        const roomSelectItems = document.getElementById("room-select-items");
        const selectedDisplay = document.getElementById(
            "selected-rooms-display"
        );
        const errorMsg = document.getElementById(
            "room-selection-error-message"
        );

        // Hide errors and reset display
        errorMsg.classList.remove("active");
        selectedDisplay.classList.add("empty");
        selectedDisplay.innerHTML = "No rooms selected";

        // Only show rooms that are not on this floor or unassigned
        const availableRooms = rooms.filter(
            (r) => !r.floor_id || r.floor_id !== floorId
        );

        if (availableRooms.length === 0) {
            roomSelectItems.innerHTML =
                "<div disabled>No rooms available</div>";
        } else {
            roomSelectItems.innerHTML = availableRooms
                .map((room) => {
                    const currentLocation = room.floor_id
                        ? `Floor: ${
                              floors.find((f) => f.id === room.floor_id)?.name
                          }`
                        : "Unassigned";

                    return `<div data-value="${room.id}" data-name="${room.name}">${room.name} - ${currentLocation}</div>`;
                })
                .join("");
        }

        initializeMultiSelect(
            document.getElementById("room-select"),
            updateSelectedRoomsDisplay
        );
        roomSelectionModal.classList.add("active");
    }

    // Update selected rooms display
    function updateSelectedRoomsDisplay() {
        const selectedDisplay = document.getElementById(
            "selected-rooms-display"
        );

        if (selectedRoomsForAssignment.length === 0) {
            selectedDisplay.classList.add("empty");
            selectedDisplay.innerHTML = "No rooms selected";
        } else {
            selectedDisplay.classList.remove("empty");
            selectedDisplay.innerHTML = selectedRoomsForAssignment
                .map(
                    (room) => `
                    <div class="selected-item-pill">
                        <span>${room.name}</span>
                        <span class="material-icons-round remove-icon" onclick="removeSelectedRoom('${room.id}')">close</span>
                    </div>
                `
                )
                .join("");
        }
    }

    // Remove selected room
    window.removeSelectedRoom = function (roomId) {
        selectedRoomsForAssignment = selectedRoomsForAssignment.filter(
            (r) => r.id !== roomId
        );
        updateSelectedRoomsDisplay();

        // Update the select items to reflect the change
        const items = document.querySelectorAll(
            "#room-select-items div[data-value]"
        );
        items.forEach((item) => {
            if (item.dataset.value === roomId) {
                item.classList.remove("selected-multi");
            }
        });
    };

    // Confirm desk selection
    async function confirmDeskSelection() {
        const errorMsg = document.getElementById(
            "desk-selection-error-message"
        );
        const errorText = document.getElementById("desk-selection-error-text");

        // Hide previous errors
        errorMsg.classList.remove("active");

        if (selectedDesksForAssignment.length === 0) {
            errorText.textContent = "Please select at least one desk";
            errorMsg.classList.add("active");
            return;
        }

        if (!pendingDeskAssignment) {
            errorText.textContent = "Assignment configuration error";
            errorMsg.classList.add("active");
            return;
        }

        const { type, id } = pendingDeskAssignment;

        // Process each desk assignment
        let successCount = 0;
        let errorCount = 0;

        for (const selectedDeskInfo of selectedDesksForAssignment) {
            const desk = desks.find((d) => d.desk_id === selectedDeskInfo.id);

            if (!desk) continue;

            // Check if desk needs confirmation (skip if already confirmed by user)
            let needsConfirmation = false;
            if (
                (type === "floor" && desk.floor_id && desk.floor_id !== id) ||
                (type === "room" && desk.room_id && desk.room_id !== id) ||
                (type === "floor" && desk.room_id) ||
                (type === "room" && desk.floor_id && !desk.room_id)
            ) {
                needsConfirmation = true;
                const currentLocation = desk.room_id
                    ? `room "${rooms.find((r) => r.id === desk.room_id)?.name}"`
                    : desk.floor_id
                    ? `floor "${
                          floors.find((f) => f.id === desk.floor_id)?.name
                      }"`
                    : "unassigned";

                if (
                    !confirm(
                        `Desk "${
                            desk.name || desk.desk_id
                        }" is currently in ${currentLocation}. Do you want to move it?`
                    )
                ) {
                    continue;
                }
            }

            try {
                const payload =
                    type === "floor"
                        ? { floor_id: id, room_id: null }
                        : {
                              room_id: id,
                              floor_id:
                                  rooms.find((r) => r.id === id)?.floor_id ||
                                  null,
                          };

                const response = await fetch(
                    `/api/desks/${selectedDeskInfo.id}/location`,
                    {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        body: JSON.stringify(payload),
                    }
                );

                const data = await response.json();

                if (data.success) {
                    successCount++;
                } else {
                    errorCount++;
                }
            } catch (error) {
                console.error("Error assigning desk:", error);
                errorCount++;
            }
        }

        if (errorCount > 0) {
            errorText.textContent = `${successCount} desk(s) assigned successfully, ${errorCount} failed`;
            errorMsg.classList.add("active");
        }

        if (successCount > 0) {
            await loadDesks();

            if (type === "floor") {
                loadFloorDesks(id);
            } else {
                loadRoomDesks(id);
            }

            loadFloors();
            loadRooms();
        }

        if (errorCount === 0) {
            closeModal(deskSelectionModal);
        }
    }

    // Confirm room selection
    async function confirmRoomSelection() {
        const errorMsg = document.getElementById(
            "room-selection-error-message"
        );
        const errorText = document.getElementById("room-selection-error-text");

        // Hide previous errors
        errorMsg.classList.remove("active");

        if (selectedRoomsForAssignment.length === 0) {
            errorText.textContent = "Please select at least one room";
            errorMsg.classList.add("active");
            return;
        }

        if (!pendingRoomAssignment) {
            errorText.textContent = "Assignment configuration error";
            errorMsg.classList.add("active");
            return;
        }

        // Process each room assignment
        let successCount = 0;
        let errorCount = 0;

        for (const selectedRoomInfo of selectedRoomsForAssignment) {
            const room = rooms.find(
                (r) => r.id === parseInt(selectedRoomInfo.id)
            );

            if (!room) continue;

            // Check if room is already assigned to another floor
            if (room.floor_id && room.floor_id !== pendingRoomAssignment) {
                const currentFloor = floors.find((f) => f.id === room.floor_id);
                if (
                    !confirm(
                        `Room "${room.name}" is currently on "${currentFloor?.name}". Do you want to move it?`
                    )
                ) {
                    continue;
                }
            }

            try {
                const response = await fetch(
                    `/api/rooms/${selectedRoomInfo.id}`,
                    {
                        method: "PUT",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrfToken,
                        },
                        body: JSON.stringify({
                            name: room.name,
                            floor_id: pendingRoomAssignment,
                            description: room.description,
                        }),
                    }
                );

                const data = await response.json();

                if (data.success) {
                    successCount++;
                } else {
                    errorCount++;
                }
            } catch (error) {
                console.error("Error assigning room:", error);
                errorCount++;
            }
        }

        if (errorCount > 0) {
            errorText.textContent = `${successCount} room(s) assigned successfully, ${errorCount} failed`;
            errorMsg.classList.add("active");
        }

        if (successCount > 0) {
            await loadRooms();
            loadFloorRooms(pendingRoomAssignment);
            loadFloors();
        }

        if (errorCount === 0) {
            closeModal(roomSelectionModal);
        }
    }

    // Initialize multi-select dropdown
    function initializeMultiSelect(selectElement, updateCallback) {
        const selected = selectElement.querySelector(".select-selected");
        const items = selectElement.querySelector(".select-items");

        // Remove existing listeners by cloning
        const newSelected = selected.cloneNode(true);
        selected.parentNode.replaceChild(newSelected, selected);

        const newItems = items.cloneNode(true);
        items.parentNode.replaceChild(newItems, items);

        // Add new listeners
        newSelected.addEventListener("click", (e) => {
            e.stopPropagation();
            selectElement.classList.toggle("active");
            newItems.classList.toggle("hidden");

            // Toggle modal size when dropdown opens/closes
            const modal = selectElement.closest(".modal-content");
            if (modal) {
                if (!newItems.classList.contains("hidden")) {
                    modal.classList.add("dropdown-open");
                } else {
                    modal.classList.remove("dropdown-open");
                }
            }
        });

        // Handle item selection (multi-select)
        newItems.querySelectorAll("div[data-value]").forEach((item) => {
            item.addEventListener("click", (e) => {
                e.stopPropagation();
                if (
                    item.hasAttribute("disabled") ||
                    item.classList.contains("disabled")
                ) {
                    return;
                }

                const value = item.dataset.value;
                const name = item.dataset.name;

                // Toggle selection
                if (item.classList.contains("selected-multi")) {
                    item.classList.remove("selected-multi");
                    // Remove from selected array
                    if (selectElement.id === "desk-select") {
                        selectedDesksForAssignment =
                            selectedDesksForAssignment.filter(
                                (d) => d.id !== value
                            );
                    } else if (selectElement.id === "room-select") {
                        selectedRoomsForAssignment =
                            selectedRoomsForAssignment.filter(
                                (r) => r.id !== value
                            );
                    }
                } else {
                    item.classList.add("selected-multi");
                    // Add to selected array
                    if (selectElement.id === "desk-select") {
                        selectedDesksForAssignment.push({
                            id: value,
                            name: name,
                        });
                    } else if (selectElement.id === "room-select") {
                        selectedRoomsForAssignment.push({
                            id: value,
                            name: name,
                        });
                    }
                }

                // Update display
                if (updateCallback) {
                    updateCallback();
                }

                // Don't close dropdown - keep it open for multi-select
            });
        });

        // Close when clicking outside
        const closeHandler = (event) => {
            if (!selectElement.contains(event.target)) {
                selectElement.classList.remove("active");
                newItems.classList.add("hidden");

                // Remove modal expansion class
                const modal = selectElement.closest(".modal-content");
                if (modal) {
                    modal.classList.remove("dropdown-open");
                }
            }
        };

        document.addEventListener("click", closeHandler);
    }

    // Global functions for inline onclick handlers
    window.editFloor = function (id) {
        const floor = floors.find((f) => f.id === id);
        if (floor) openFloorModal(floor);
    };

    window.deleteFloor = async function (id) {
        if (
            !confirm(
                "Are you sure you want to delete this floor? All rooms and desks on this floor will be unassigned."
            )
        )
            return;

        try {
            const response = await fetch(`/api/floors/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
            });

            const data = await response.json();

            if (data.success) {
                loadFloors();
                loadRooms();
            } else {
                alert(data.message || "Error deleting floor");
            }
        } catch (error) {
            console.error("Error deleting floor:", error);
            alert("Error deleting floor");
        }
    };

    window.editRoom = function (id) {
        const room = rooms.find((r) => r.id === id);
        if (room) openRoomModal(room);
    };

    window.deleteRoom = async function (id) {
        if (
            !confirm(
                "Are you sure you want to delete this room? All desks in this room will be unassigned."
            )
        )
            return;

        try {
            const response = await fetch(`/api/rooms/${id}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
            });

            const data = await response.json();

            if (data.success) {
                loadRooms();
                loadFloors();
            } else {
                alert(data.message || "Error deleting room");
            }
        } catch (error) {
            console.error("Error deleting room:", error);
            alert("Error deleting room");
        }
    };

    window.removeDeskFromFloor = async function (deskId) {
        if (!confirm("Remove this desk from the floor?")) return;

        try {
            const response = await fetch(`/api/desks/${deskId}/location`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({ floor_id: null, room_id: null }),
            });

            const data = await response.json();

            if (data.success) {
                await loadDesks();
                loadFloorDesks(editingFloor.id);
                loadFloors();
            } else {
                alert(data.message || "Error removing desk");
            }
        } catch (error) {
            console.error("Error removing desk:", error);
            alert("Error removing desk");
        }
    };

    window.removeDeskFromRoom = async function (deskId) {
        if (!confirm("Remove this desk from the room?")) return;

        try {
            const desk = desks.find((d) => d.desk_id === deskId);
            const response = await fetch(`/api/desks/${deskId}/location`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    floor_id: desk.floor_id,
                    room_id: null,
                }),
            });

            const data = await response.json();

            if (data.success) {
                await loadDesks();
                loadRoomDesks(editingRoom.id);
                loadRooms();
                loadFloors();
            } else {
                alert(data.message || "Error removing desk");
            }
        } catch (error) {
            console.error("Error removing desk:", error);
            alert("Error removing desk");
        }
    };

    window.removeRoomFromFloor = async function (roomId) {
        if (!confirm("Remove this room from the floor?")) return;

        try {
            const room = rooms.find((r) => r.id === roomId);
            const response = await fetch(`/api/rooms/${roomId}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    name: room.name,
                    floor_id: null,
                    description: room.description,
                }),
            });

            const data = await response.json();

            if (data.success) {
                await loadRooms();
                loadFloorRooms(editingFloor.id);
                loadFloors();
            } else {
                alert(data.message || "Error removing room");
            }
        } catch (error) {
            console.error("Error removing room:", error);
            alert("Error removing room");
        }
    };

    // Initialize custom selects
    function initializeCustomSelect(selectElement) {
        const selected = selectElement.querySelector(".select-selected");
        const items = selectElement.querySelector(".select-items");

        // Remove existing listeners by cloning
        const newSelected = selected.cloneNode(true);
        selected.parentNode.replaceChild(newSelected, selected);

        const newItems = items.cloneNode(true);
        items.parentNode.replaceChild(newItems, items);

        // Add new listeners
        newSelected.addEventListener("click", (e) => {
            e.stopPropagation();
            selectElement.classList.toggle("active");
            newItems.classList.toggle("hidden");

            // Toggle modal size when dropdown opens/closes
            const modal = selectElement.closest(".modal-content");
            if (modal) {
                if (!newItems.classList.contains("hidden")) {
                    modal.classList.add("dropdown-open");
                } else {
                    modal.classList.remove("dropdown-open");
                }
            }
        });

        newItems.querySelectorAll("div").forEach((item) => {
            item.addEventListener("click", (e) => {
                e.stopPropagation();
                if (
                    item.hasAttribute("disabled") ||
                    item.classList.contains("disabled")
                ) {
                    return;
                }

                const value = item.dataset.value;
                newSelected.textContent = item.textContent;
                newSelected.dataset.value = value;

                newItems
                    .querySelectorAll("div")
                    .forEach((i) => i.classList.remove("selected"));
                item.classList.add("selected");

                selectElement.classList.remove("active");
                newItems.classList.add("hidden");
            });
        });

        // Close when clicking outside
        document.addEventListener("click", () => {
            selectElement.classList.remove("active");
            newItems.classList.add("hidden");

            // Remove modal expansion class
            const modal = selectElement.closest(".modal-content");
            if (modal) {
                modal.classList.remove("dropdown-open");
            }
        });
    }

    // Initialize custom selects on load
    populateFloorDropdown();
});
