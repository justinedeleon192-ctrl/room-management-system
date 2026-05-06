// ===== Global Variables =====
let currentUser = null;
let rooms = [];
let users = [];

// ===== Initialize Application =====
document.addEventListener('DOMContentLoaded', function() {
    initializeData();
    setupEventListeners();
    checkAuthStatus();
});

// ===== Data Initialization =====
function initializeData() {
    // Initialize users if not exists
    if (!localStorage.getItem('users')) {
        const defaultUsers = [
            {
                id: 1,
                username: 'admin',
                password: 'admin123',
                fullName: 'Administrator',
                email: 'admin@cea.edu',
                role: 'instructor'
            },
            {
                id: 2,
                username: 'student',
                password: 'student123',
                fullName: 'John Student',
                email: 'student@cea.edu',
                role: 'student'
            }
        ];
        localStorage.setItem('users', JSON.stringify(defaultUsers));
    }
    
    // Initialize rooms if not exists
    if (!localStorage.getItem('rooms')) {
        const defaultRooms = [
            {
                id: 1,
                name: 'Room 101',
                building: 'Engineering Building',
                capacity: 30,
                status: 'available'
            },
            {
                id: 2,
                name: 'Room 102',
                building: 'Engineering Building',
                capacity: 25,
                status: 'occupied'
            },
            {
                id: 3,
                name: 'Lab 201',
                building: 'Science Building',
                capacity: 20,
                status: 'available'
            },
            {
                id: 4,
                name: 'Lecture Hall A',
                building: 'Main Building',
                capacity: 100,
                status: 'available'
            },
            {
                id: 5,
                name: 'Computer Lab',
                building: 'IT Building',
                capacity: 40,
                status: 'occupied'
            }
        ];
        localStorage.setItem('rooms', JSON.stringify(defaultRooms));
    }
    
    loadData();
}

function loadData() {
    users = JSON.parse(localStorage.getItem('users')) || [];
    rooms = JSON.parse(localStorage.getItem('rooms')) || [];
    
    // Validate and clean room data
    rooms = validateRoomsData(rooms);
}

function saveData() {
    localStorage.setItem('users', JSON.stringify(users));
    localStorage.setItem('rooms', JSON.stringify(rooms));
}

// ===== Data Validation Functions =====
function validateRoomsData(roomsData) {
    if (!Array.isArray(roomsData)) {
        console.warn('Invalid rooms data format, using empty array');
        return [];
    }
    
    return roomsData.filter(room => {
        // Check if room has required properties
        if (!room || typeof room !== 'object') {
            console.warn('Invalid room object found, skipping');
            return false;
        }
        
        // Validate required fields
        if (!room.id || !room.name || !room.status) {
            console.warn('Room missing required fields, skipping:', room);
            return false;
        }
        
        // Validate status values
        if (!['available', 'occupied'].includes(room.status)) {
            console.warn('Invalid room status, defaulting to available:', room);
            room.status = 'available';
        }
        
        // Validate capacity
        if (!room.capacity || room.capacity <= 0) {
            console.warn('Invalid room capacity, setting default:', room);
            room.capacity = 1;
        }
        
        return true;
    });
}

function validateRoomData(room) {
    if (!room || typeof room !== 'object') {
        return false;
    }
    
    // Required fields validation
    if (!room.name || typeof room.name !== 'string' || room.name.trim() === '') {
        return false;
    }
    
    if (!room.building || typeof room.building !== 'string' || room.building.trim() === '') {
        return false;
    }
    
    if (!room.capacity || isNaN(room.capacity) || room.capacity <= 0) {
        return false;
    }
    
    if (!room.status || !['available', 'occupied'].includes(room.status)) {
        return false;
    }
    
    return true;
}

// ===== Event Listeners =====
function setupEventListeners() {
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Signup form
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', handleSignup);
    }
    
    // Room form
    const roomForm = document.getElementById('roomForm');
    if (roomForm) {
        roomForm.addEventListener('submit', handleRoomSubmit);
    }
    
    // Search and filter
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', handleSearch);
    }
    
    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus) {
        filterStatus.addEventListener('change', handleFilter);
    }
    
    // Forgot password link
    const forgotPassword = document.querySelector('.forgot-password');
    if (forgotPassword) {
        forgotPassword.addEventListener('click', handleForgotPassword);
    }
}

// ===== Authentication Functions =====
function checkAuthStatus() {
    const currentPage = window.location.pathname.split('/').pop();
    
    if (currentPage !== 'index.html' && currentPage !== 'signup.html') {
        currentUser = JSON.parse(localStorage.getItem('currentUser'));
        
        if (!currentUser) {
            window.location.href = 'index.html';
            return;
        }
        
        // Redirect based on role
        if (currentPage === 'admin.html' && currentUser.role === 'student') {
            window.location.href = 'student.html';
            return;
        }
        
        if (currentPage === 'student.html' && currentUser.role === 'instructor') {
            window.location.href = 'admin.html';
            return;
        }
        
        // Update UI with current user
        updateCurrentUserDisplay();
        updateAuthUI();
        
        // Load page-specific content
        if (currentPage === 'admin.html') {
            loadAdminDashboard();
        } else if (currentPage === 'student.html') {
            loadStudentDashboard();
        }
    }
}

function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    const user = users.find(u => u.username === username && u.password === password);
    
    if (user) {
        currentUser = user;
        localStorage.setItem('currentUser', JSON.stringify(user));
        
        showNotification('Login successful!', 'success');
        
        // Redirect based on role
        setTimeout(() => {
            if (user.role === 'instructor') {
                window.location.href = 'admin.html';
            } else {
                window.location.href = 'student.html';
            }
        }, 1000);
    } else {
        showNotification('Invalid username or password', 'error');
    }
}

function handleSignup(e) {
    e.preventDefault();
    
    const fullName = document.getElementById('fullName').value;
    const email = document.getElementById('email').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;
    
    // Check if username already exists
    if (users.find(u => u.username === username)) {
        showNotification('Username already exists', 'error');
        return;
    }
    
    // Create new user
    const newUser = {
        id: users.length + 1,
        fullName,
        email,
        username,
        password,
        role
    };
    
    users.push(newUser);
    saveData();
    
    showNotification('Account created successfully!', 'success');
    
    // Redirect to login after delay
    setTimeout(() => {
        window.location.href = 'index.html';
    }, 1500);
}

function logout() {
    localStorage.removeItem('currentUser');
    currentUser = null;
    window.location.href = 'index.html';
}

function handleForgotPassword(e) {
    e.preventDefault();
    showNotification('Password reset functionality would be implemented here', 'warning');
}

// ===== User Interface Functions =====
function updateCurrentUserDisplay() {
    const currentUserElement = document.getElementById('currentUser');
    if (currentUserElement && currentUser) {
        currentUserElement.textContent = `${currentUser.fullName} (${currentUser.role})`;
    }
}

function updateAuthUI() {
    const currentPage = window.location.pathname.split('/').pop();
    
    // Only show auth elements on authenticated pages
    if (currentPage === 'admin.html' || currentPage === 'student.html') {
        const userElements = document.querySelectorAll('.user-info');
        const logoutButtons = document.querySelectorAll('[onclick="logout()"], .logout-btn');
        
        if (currentUser) {
            // Show user info and logout buttons
            userElements.forEach(el => el.style.display = 'flex');
            logoutButtons.forEach(btn => btn.style.display = 'block');
        } else {
            // Hide user info and logout buttons if not authenticated
            userElements.forEach(el => el.style.display = 'none');
            logoutButtons.forEach(btn => btn.style.display = 'none');
        }
    }
}

// ===== Room Management Functions =====
function loadAdminDashboard() {
    displayRoomsTable();
    updateStats();
    updateAuthUI();
}

function loadStudentDashboard() {
    displayRoomsGrid();
    updateStats();
    updateAuthUI();
}

function displayRoomsTable() {
    const tbody = document.getElementById('roomsTableBody');
    if (!tbody) return;
    
    const filteredRooms = getFilteredRooms();
    
    tbody.innerHTML = '';
    
    filteredRooms.forEach(room => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${room.name}</td>
            <td>${room.building}</td>
            <td>${room.capacity}</td>
            <td>
                <span class="status-badge ${room.status}">
                    ${room.status === 'available' ? '🟢 Available' : '🔴 Occupied'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button onclick="toggleRoomStatus(${room.id})" class="btn btn-sm btn-${room.status === 'available' ? 'success' : 'danger'}">
                        ${room.status === 'available' ? 'Mark Occupied' : 'Mark Available'}
                    </button>
                    <button onclick="editRoom(${room.id})" class="btn btn-sm btn-secondary">Edit</button>
                    <button onclick="deleteRoom(${room.id})" class="btn btn-sm btn-danger">Delete</button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function displayRoomsGrid() {
    const grid = document.getElementById('roomsGrid');
    if (!grid) return;
    
    const filteredRooms = getFilteredRooms();
    
    grid.innerHTML = '';
    
    filteredRooms.forEach(room => {
        const card = document.createElement('div');
        card.className = `room-card ${room.status} fade-in`;
        card.innerHTML = `
            <h3>${room.name}</h3>
            <div class="room-details">
                <p><strong>Building:</strong> ${room.building}</p>
                <p><strong>Capacity:</strong> ${room.capacity} people</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge ${room.status}">
                        ${room.status === 'available' ? '🟢 Available' : '🔴 Occupied'}
                    </span>
                </p>
            </div>
        `;
        grid.appendChild(card);
    });
}

function getFilteredRooms() {
    let filtered = [...rooms];
    
    // Apply search filter
    const searchInput = document.getElementById('searchInput');
    if (searchInput && searchInput.value) {
        const searchTerm = searchInput.value.toLowerCase();
        filtered = filtered.filter(room => 
            room.name.toLowerCase().includes(searchTerm) ||
            room.building.toLowerCase().includes(searchTerm)
        );
    }
    
    // Apply status filter
    const filterStatus = document.getElementById('filterStatus');
    if (filterStatus && filterStatus.value) {
        filtered = filtered.filter(room => room.status === filterStatus.value);
    }
    
    return filtered;
}

function updateStats() {
    const totalRoomsElement = document.getElementById('totalRooms');
    const availableRoomsElement = document.getElementById('availableRooms');
    const occupiedRoomsElement = document.getElementById('occupiedRooms');
    
    // Ensure rooms array is valid and filter out invalid entries
    const validRooms = rooms.filter(room => room && room.id && room.name && room.status);
    
    // Calculate stats with validation
    const totalRooms = validRooms.length;
    const availableRooms = validRooms.filter(r => r.status === 'available').length;
    const occupiedRooms = validRooms.filter(r => r.status === 'occupied').length;
    
    // Update UI with validation
    if (totalRoomsElement) {
        totalRoomsElement.textContent = totalRooms;
    }
    
    if (availableRoomsElement) {
        availableRoomsElement.textContent = availableRooms;
    }
    
    if (occupiedRoomsElement) {
        occupiedRoomsElement.textContent = occupiedRooms;
    }
    
    // Log for debugging (can be removed in production)
    console.log('Stats updated:', { totalRooms, availableRooms, occupiedRooms, validRoomsCount: validRooms.length });
}

// ===== Room CRUD Operations =====
function handleRoomSubmit(e) {
    e.preventDefault();
    
    const roomId = document.getElementById('roomId').value;
    const roomName = document.getElementById('roomName').value.trim();
    const building = document.getElementById('building').value.trim();
    const capacity = parseInt(document.getElementById('capacity').value);
    const status = document.getElementById('status').value;
    
    // Validate form data
    const roomData = {
        name: roomName,
        building,
        capacity,
        status
    };
    
    if (!validateRoomData(roomData)) {
        showNotification('Please fill in all required fields with valid values', 'error');
        return;
    }
    
    if (roomId) {
        // Edit existing room
        const roomIndex = rooms.findIndex(r => r.id == roomId);
        if (roomIndex !== -1) {
            rooms[roomIndex] = {
                id: parseInt(roomId),
                ...roomData
            };
            showNotification('Room updated successfully!', 'success');
        } else {
            showNotification('Room not found', 'error');
            return;
        }
    } else {
        // Add new room
        const newRoom = {
            id: rooms.length > 0 ? Math.max(...rooms.map(r => r.id)) + 1 : 1,
            ...roomData
        };
        rooms.push(newRoom);
        showNotification('Room added successfully!', 'success');
    }
    
    saveData();
    closeRoomModal();
    
    // Update the appropriate dashboard based on current page
    const currentPage = window.location.pathname.split('/').pop();
    if (currentPage === 'admin.html') {
        loadAdminDashboard();
    } else if (currentPage === 'student.html') {
        loadStudentDashboard();
    }
}

function editRoom(roomId) {
    const room = rooms.find(r => r.id === roomId);
    if (!room) return;
    
    document.getElementById('roomId').value = room.id;
    document.getElementById('roomName').value = room.name;
    document.getElementById('building').value = room.building;
    document.getElementById('capacity').value = room.capacity;
    document.getElementById('status').value = room.status;
    document.getElementById('modalTitle').textContent = 'Edit Room';
    
    openRoomModal();
}

function deleteRoom(roomId) {
    if (confirm('Are you sure you want to delete this room?')) {
        rooms = rooms.filter(r => r.id !== roomId);
        saveData();
        
        // Update the appropriate dashboard based on current page
        const currentPage = window.location.pathname.split('/').pop();
        if (currentPage === 'admin.html') {
            loadAdminDashboard();
        } else if (currentPage === 'student.html') {
            loadStudentDashboard();
        }
        
        showNotification('Room deleted successfully!', 'success');
    }
}

function toggleRoomStatus(roomId) {
    const room = rooms.find(r => r.id === roomId);
    if (room) {
        room.status = room.status === 'available' ? 'occupied' : 'available';
        saveData();
        
        // Update the appropriate dashboard based on current page
        const currentPage = window.location.pathname.split('/').pop();
        if (currentPage === 'admin.html') {
            loadAdminDashboard();
        } else if (currentPage === 'student.html') {
            loadStudentDashboard();
        }
        
        showNotification(`Room status changed to ${room.status}`, 'success');
    }
}

// ===== Modal Functions =====
function openRoomModal() {
    const modal = document.getElementById('roomModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeRoomModal() {
    const modal = document.getElementById('roomModal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('roomForm').reset();
        document.getElementById('roomId').value = '';
        document.getElementById('modalTitle').textContent = 'Add Room';
    }
}

function openAddRoomModal() {
    document.getElementById('modalTitle').textContent = 'Add Room';
    openRoomModal();
}

// ===== Search and Filter Functions =====
function handleSearch() {
    const currentPage = window.location.pathname.split('/').pop();
    if (currentPage === 'admin.html') {
        displayRoomsTable();
    } else if (currentPage === 'student.html') {
        displayRoomsGrid();
    }
}

function handleFilter() {
    handleSearch();
}

// ===== Notification System =====
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (!notification) return;
    
    notification.textContent = message;
    notification.className = `notification ${type} show`;
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

// ===== Utility Functions =====
// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('roomModal');
    if (modal && event.target === modal) {
        closeRoomModal();
    }
}

// Prevent form resubmission on page refresh
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
