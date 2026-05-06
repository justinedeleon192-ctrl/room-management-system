// ===== PHP-Based School Management System =====
// This script replaces localStorage with PHP API calls

// ===== Global Variables =====
let currentUser = null;
let rooms = [];
let users = [];

// ===== API Configuration =====
const API_BASE_URL = '/api';
const API_ENDPOINTS = {
    auth: `${API_BASE_URL}/auth.php`,
    rooms: `${API_BASE_URL}/rooms.php`
};

// ===== Initialize Application =====
document.addEventListener('DOMContentLoaded', function() {
    initializeData();
    setupEventListeners();
    checkAuthStatus();
});

// ===== Data Initialization =====
async function initializeData() {
    await loadUserData();
    await loadRoomsData();
}

// ===== User Management Functions =====
async function loadUserData() {
    try {
        const response = await fetch(`${API_ENDPOINTS.auth}?action=user`);
        const result = await response.json();
        
        if (result.success) {
            currentUser = result.data.user;
            updateCurrentUserDisplay();
            updateAuthUI();
        } else {
            currentUser = null;
            updateAuthUI();
        }
    } catch (error) {
        console.error('Error loading user data:', error);
        currentUser = null;
        updateAuthUI();
    }
}

async function loadUsersData() {
    // This would be implemented if we need to load all users
    // For now, we'll keep users array empty as it's not used in the current UI
    users = [];
}

async function saveUserData() {
    // Data is saved automatically through API calls
    // No manual save needed as PHP handles database persistence
}

// ===== Room Management Functions =====
async function loadRoomsData() {
    try {
        const response = await fetch(`${API_ENDPOINTS.rooms}?action=list`);
        const result = await response.json();
        
        if (result.success) {
            rooms = result.data.rooms;
        } else {
            console.error('Error loading rooms:', result.message);
            rooms = [];
        }
    } catch (error) {
        console.error('Error loading rooms data:', error);
        rooms = [];
    }
}

// ===== Authentication Functions =====
async function checkAuthStatus() {
    const currentPage = window.location.pathname.split('/').pop();
    
    if (currentPage !== 'index.html' && currentPage !== 'signup.html') {
        try {
            const response = await fetch(`${API_ENDPOINTS.auth}?action=check`);
            const result = await response.json();
            
            if (!result.success) {
                // Redirect to login if not authenticated
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
            
            // Load page-specific content
            if (currentPage === 'admin.html') {
                await loadAdminDashboard();
            } else if (currentPage === 'student.html') {
                await loadStudentDashboard();
            }
        } catch (error) {
            console.error('Auth check error:', error);
            window.location.href = 'index.html';
        }
    }
}

async function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    try {
        const response = await fetch(`${API_ENDPOINTS.auth}?action=login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username, password })
        });
        
        const result = await response.json();
        
        if (result.success) {
            currentUser = result.data.user;
            updateCurrentUserDisplay();
            updateAuthUI();
            showNotification('Login successful!', 'success');
            
            // Redirect based on role
            setTimeout(() => {
                if (currentUser.role === 'instructor') {
                    window.location.href = 'admin.html';
                } else {
                    window.location.href = 'student.html';
                }
            }, 1000);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

async function handleSignup(e) {
    e.preventDefault();
    
    const fullName = document.getElementById('fullName').value;
    const email = document.getElementById('email').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;
    
    try {
        const response = await fetch(`${API_ENDPOINTS.auth}?action=register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                fullname: fullName,
                email,
                username,
                password,
                role
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            currentUser = result.data.user;
            updateCurrentUserDisplay();
            updateAuthUI();
            showNotification('Account created successfully!', 'success');
            
            // Redirect to login after delay
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Signup error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

async function logout() {
    try {
        const response = await fetch(`${API_ENDPOINTS.auth}?action=logout`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            currentUser = null;
            showNotification('Logout successful!', 'success');
            window.location.href = 'index.html';
        } else {
            showNotification('Logout failed. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Logout error:', error);
        showNotification('Network error during logout.', 'error');
    }
}

// ===== User Interface Functions =====
function updateCurrentUserDisplay() {
    const currentUserElement = document.getElementById('currentUser');
    if (currentUserElement && currentUser) {
        currentUserElement.textContent = `${currentUser.fullname} (${currentUser.role})`;
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
async function loadAdminDashboard() {
    await displayRoomsTable();
    await updateStats();
}

async function loadStudentDashboard() {
    await displayRoomsGrid();
    await updateStats();
}

async function displayRoomsTable() {
    const tbody = document.getElementById('roomsTableBody');
    if (!tbody) return;
    
    try {
        const response = await fetch(`${API_ENDPOINTS.rooms}?action=list`);
        const result = await response.json();
        
        if (result.success) {
            const filteredRooms = getFilteredRooms(result.data.rooms);
            
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
    } catch (error) {
        console.error('Error displaying rooms table:', error);
    }
}

async function displayRoomsGrid() {
    const grid = document.getElementById('roomsGrid');
    if (!grid) return;
    
    try {
        const response = await fetch(`${API_ENDPOINTS.rooms}?action=list`);
        const result = await response.json();
        
        if (result.success) {
            const filteredRooms = getFilteredRooms(result.data.rooms);
            
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
    } catch (error) {
        console.error('Error displaying rooms grid:', error);
    }
}

function getFilteredRooms(roomsData) {
    let filtered = [...roomsData];
    
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

async function updateStats() {
    try {
        const response = await fetch(`${API_ENDPOINTS.rooms}?action=stats`);
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data.stats;
            const totalRoomsElement = document.getElementById('totalRooms');
            const availableRoomsElement = document.getElementById('availableRooms');
            const occupiedRoomsElement = document.getElementById('occupiedRooms');
            
            if (totalRoomsElement) {
                totalRoomsElement.textContent = stats.total_rooms || 0;
            }
            
            if (availableRoomsElement) {
                availableRoomsElement.textContent = stats.available_rooms || 0;
            }
            
            if (occupiedRoomsElement) {
                occupiedRoomsElement.textContent = stats.occupied_rooms || 0;
            }
        }
    } catch (error) {
        console.error('Error updating stats:', error);
    }
}

// ===== Room CRUD Operations =====
async function handleRoomSubmit(e) {
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
    
    try {
        let response;
        if (roomId) {
            // Edit existing room
            response = await fetch(`${API_ENDPOINTS.rooms}?action=update&id=${roomId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(roomData)
            });
        } else {
            // Add new room
            response = await fetch(`${API_ENDPOINTS.rooms}?action=create`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(roomData)
            });
        }
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            closeRoomModal();
            await loadAdminDashboard();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Room submit error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

async function editRoom(roomId) {
    try {
        const response = await fetch(`${API_ENDPOINTS.rooms}?action=get&id=${roomId}`);
        const result = await response.json();
        
        if (result.success) {
            const room = result.data.room;
            document.getElementById('roomId').value = room.id;
            document.getElementById('roomName').value = room.name;
            document.getElementById('building').value = room.building;
            document.getElementById('capacity').value = room.capacity;
            document.getElementById('status').value = room.status;
            document.getElementById('modalTitle').textContent = 'Edit Room';
            
            openRoomModal();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Edit room error:', error);
        showNotification('Network error. Please try again.', 'error');
    }
}

async function deleteRoom(roomId) {
    if (confirm('Are you sure you want to delete this room?')) {
        try {
            const response = await fetch(`${API_ENDPOINTS.rooms}?action=delete&id=${roomId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification(result.message, 'success');
                await loadAdminDashboard();
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Delete room error:', error);
            showNotification('Network error. Please try again.', 'error');
        }
    }
}

async function toggleRoomStatus(roomId) {
    try {
        const response = await fetch(`${API_ENDPOINTS.rooms}?action=toggle&id=${roomId}`, {
            method: 'PUT'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            await loadAdminDashboard();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Toggle room status error:', error);
        showNotification('Network error. Please try again.', 'error');
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
async function handleSearch() {
    const currentPage = window.location.pathname.split('/').pop();
    if (currentPage === 'admin.html') {
        await displayRoomsTable();
    } else if (currentPage === 'student.html') {
        await displayRoomsGrid();
    }
}

async function handleFilter() {
    await handleSearch();
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

function handleForgotPassword(e) {
    e.preventDefault();
    showNotification('Password reset functionality would be implemented here', 'warning');
}
