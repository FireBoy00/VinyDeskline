// Office Management JavaScript
// This file handles floor and room management functionality

document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // State
    let floors = [];
    let rooms = [];
    let editingFloor = null;
    let editingRoom = null;
    
    // Load initial data
    loadFloors();
    loadRooms();
    
    // Floor Modal Controls
    const floorModal = document.getElementById('floor-modal');
    const addFloorBtn = document.getElementById('add-floor-btn');
    const closeFloorModal = document.getElementById('close-floor-modal');
    const cancelFloorBtn = document.getElementById('cancel-floor-btn');
    const saveFloorBtn = document.getElementById('save-floor-btn');
    const floorForm = document.getElementById('floor-form');
    
    // Room Modal Controls
    const roomModal = document.getElementById('room-modal');
    const addRoomBtn = document.getElementById('add-room-btn');
    const closeRoomModal = document.getElementById('close-room-modal');
    const cancelRoomBtn = document.getElementById('cancel-room-btn');
    const saveRoomBtn = document.getElementById('save-room-btn');
    const roomForm = document.getElementById('room-form');
    
    // Floor Modal Event Listeners
    addFloorBtn.addEventListener('click', () => openFloorModal());
    closeFloorModal.addEventListener('click', () => closeModal(floorModal));
    cancelFloorBtn.addEventListener('click', () => closeModal(floorModal));
    saveFloorBtn.addEventListener('click', saveFloor);
    
    // Room Modal Event Listeners
    addRoomBtn.addEventListener('click', () => openRoomModal());
    closeRoomModal.addEventListener('click', () => closeModal(roomModal));
    cancelRoomBtn.addEventListener('click', () => closeModal(roomModal));
    saveRoomBtn.addEventListener('click', saveRoom);
    
    // Functions
    async function loadFloors() {
        try {
            const response = await fetch('/api/floors');
            const data = await response.json();
            
            if (data.success) {
                floors = data.floors;
                renderFloors();
                populateFloorDropdown();
            }
        } catch (error) {
            console.error('Error loading floors:', error);
        }
    }
    
    async function loadRooms() {
        try {
            const response = await fetch('/api/rooms');
            const data = await response.json();
            
            if (data.success) {
                rooms = data.rooms;
                renderRooms();
            }
        } catch (error) {
            console.error('Error loading rooms:', error);
        }
    }
    
    function renderFloors() {
        const floorsGrid = document.getElementById('floors-grid');
        const floorsCount = document.getElementById('floors-count');
        const noFloorsMessage = document.getElementById('no-floors-message');
        const floorsLoading = document.getElementById('floors-loading');
        
        floorsLoading.style.display = 'none';
        floorsCount.textContent = floors.length;
        
        if (floors.length === 0) {
            floorsGrid.style.display = 'none';
            noFloorsMessage.style.display = 'block';
            return;
        }
        
        floorsGrid.style.display = 'grid';
        noFloorsMessage.style.display = 'none';
        
        floorsGrid.innerHTML = floors.map(floor => `
            <div class="floor-card" data-id="${floor.id}">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${floor.name}</h3>
                        <p class="card-subtitle">Floor ${floor.floor_number}</p>
                    </div>
                    <div class="card-actions">
                        <button class="card-action-btn edit" onclick="editFloor(${floor.id})">
                            <span class="material-icons-round">edit</span>
                        </button>
                        <button class="card-action-btn delete" onclick="deleteFloor(${floor.id})">
                            <span class="material-icons-round">delete</span>
                        </button>
                    </div>
                </div>
                ${floor.description ? `<p class="card-description">${floor.description}</p>` : ''}
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
        `).join('');
    }
    
    function renderRooms() {
        const roomsGrid = document.getElementById('rooms-grid');
        const roomsCount = document.getElementById('rooms-count');
        const noRoomsMessage = document.getElementById('no-rooms-message');
        const roomsLoading = document.getElementById('rooms-loading');
        
        roomsLoading.style.display = 'none';
        roomsCount.textContent = rooms.length;
        
        if (rooms.length === 0) {
            roomsGrid.style.display = 'none';
            noRoomsMessage.style.display = 'block';
            return;
        }
        
        roomsGrid.style.display = 'grid';
        noRoomsMessage.style.display = 'none';
        
        roomsGrid.innerHTML = rooms.map(room => `
            <div class="room-card" data-id="${room.id}">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${room.name}</h3>
                        <p class="card-subtitle">${room.floor ? `Floor ${room.floor.floor_number}` : 'No floor assigned'}</p>
                    </div>
                    <div class="card-actions">
                        <button class="card-action-btn edit" onclick="editRoom(${room.id})">
                            <span class="material-icons-round">edit</span>
                        </button>
                        <button class="card-action-btn delete" onclick="deleteRoom(${room.id})">
                            <span class="material-icons-round">delete</span>
                        </button>
                    </div>
                </div>
                ${room.description ? `<p class="card-description">${room.description}</p>` : ''}
                <div class="card-stats">
                    <div class="card-stat">
                        <span class="material-icons-round">desk</span>
                        <span>${room.desks_count || 0} desks</span>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    function populateFloorDropdown() {
        const roomFloorSelect = document.getElementById('room-floor');
        roomFloorSelect.innerHTML = '<option value="">No Floor Assignment</option>' +
            floors.map(floor => `<option value="${floor.id}">Floor ${floor.floor_number} - ${floor.name}</option>`).join('');
    }
    
    function openFloorModal(floor = null) {
        editingFloor = floor;
        const title = document.getElementById('floor-modal-title');
        
        if (floor) {
            title.textContent = 'Edit Floor';
            document.getElementById('floor-id').value = floor.id;
            document.getElementById('floor-name').value = floor.name;
            document.getElementById('floor-number').value = floor.floor_number;
            document.getElementById('floor-description').value = floor.description || '';
        } else {
            title.textContent = 'Add Floor';
            floorForm.reset();
            document.getElementById('floor-id').value = '';
        }
        
        floorModal.classList.add('active');
    }
    
    function openRoomModal(room = null) {
        editingRoom = room;
        const title = document.getElementById('room-modal-title');
        
        if (room) {
            title.textContent = 'Edit Room';
            document.getElementById('room-id').value = room.id;
            document.getElementById('room-name').value = room.name;
            document.getElementById('room-floor').value = room.floor_id || '';
            document.getElementById('room-description').value = room.description || '';
        } else {
            title.textContent = 'Add Room';
            roomForm.reset();
            document.getElementById('room-id').value = '';
        }
        
        roomModal.classList.add('active');
    }
    
    function closeModal(modal) {
        modal.classList.remove('active');
    }
    
    async function saveFloor() {
        const id = document.getElementById('floor-id').value;
        const name = document.getElementById('floor-name').value;
        const floorNumber = document.getElementById('floor-number').value;
        const description = document.getElementById('floor-description').value;
        
        if (!name || !floorNumber) {
            alert('Please fill in all required fields');
            return;
        }
        
        try {
            const url = id ? `/api/floors/${id}` : '/api/floors';
            const method = id ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ name, floor_number: floorNumber, description })
            });
            
            const data = await response.json();
            
            if (data.success) {
                closeModal(floorModal);
                loadFloors();
            } else {
                alert(data.message || 'Error saving floor');
            }
        } catch (error) {
            console.error('Error saving floor:', error);
            alert('Error saving floor');
        }
    }
    
    async function saveRoom() {
        const id = document.getElementById('room-id').value;
        const name = document.getElementById('room-name').value;
        const floorId = document.getElementById('room-floor').value || null;
        const description = document.getElementById('room-description').value;
        
        if (!name) {
            alert('Please fill in all required fields');
            return;
        }
        
        try {
            const url = id ? `/api/rooms/${id}` : '/api/rooms';
            const method = id ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ name, floor_id: floorId, description })
            });
            
            const data = await response.json();
            
            if (data.success) {
                closeModal(roomModal);
                loadRooms();
            } else {
                alert(data.message || 'Error saving room');
            }
        } catch (error) {
            console.error('Error saving room:', error);
            alert('Error saving room');
        }
    }
    
    // Global functions for inline onclick handlers
    window.editFloor = function(id) {
        const floor = floors.find(f => f.id === id);
        if (floor) openFloorModal(floor);
    };
    
    window.deleteFloor = async function(id) {
        if (!confirm('Are you sure you want to delete this floor?')) return;
        
        try {
            const response = await fetch(`/api/floors/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                loadFloors();
                loadRooms();
            } else {
                alert(data.message || 'Error deleting floor');
            }
        } catch (error) {
            console.error('Error deleting floor:', error);
            alert('Error deleting floor');
        }
    };
    
    window.editRoom = function(id) {
        const room = rooms.find(r => r.id === id);
        if (room) openRoomModal(room);
    };
    
    window.deleteRoom = async function(id) {
        if (!confirm('Are you sure you want to delete this room?')) return;
        
        try {
            const response = await fetch(`/api/rooms/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                loadRooms();
            } else {
                alert(data.message || 'Error deleting room');
            }
        } catch (error) {
            console.error('Error deleting room:', error);
            alert('Error deleting room');
        }
    };
});
