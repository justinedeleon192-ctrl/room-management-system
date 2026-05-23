// admin.js – Admin Dashboard Logic
const API_URL = 'api.php';
let rooms = [];
let instructors = [];
let bookings = [];
let currentDateFilter = 'all';
let currentFilterDate = new Date();

// ==================== LOAD DATA ====================
async function loadData() {
    try {
        const [roomsRes, instructorsRes, bookingsRes] = await Promise.all([
            fetch(`${API_URL}?endpoint=rooms`),
            fetch(`${API_URL}?endpoint=instructors`),
            fetch(`${API_URL}?endpoint=bookings`)
        ]);
        
        const roomsData = await roomsRes.json();
        const instructorsData = await instructorsRes.json();
        const bookingsData = await bookingsRes.json();
        
        if (roomsData.success) rooms = roomsData.rooms;
        if (instructorsData.success) instructors = instructorsData.instructors;
        if (bookingsData.success) {
            bookings = bookingsData.bookings.map(b => ({
                id: b.id.toString(),
                name: b.name,
                instructor_id: b.instructor_id,
                instructor_fullname: b.instructor_fullname,
                instructor_email: b.instructor_email,
                notes: b.notes || '',
                booking_date: b.booking_date,
                start_time: b.start_time,
                end_time: b.end_time,
                capacity: b.capacity
            }));
        }

        updateStats();
        renderTable();
        updateRoomDropdown();
        updateInstructorDropdown();
    } catch (error) {
        console.error('Error:', error);
        showNotification('Failed to load data', 'error');
    }
}

// ==================== STATS ====================
function updateStats() {
    const templateRooms = rooms.filter(r => r.status === 'available' && !r.booking_date);
    const occupiedRooms = rooms.filter(r => r.status === 'occupied');
    
    document.getElementById('totalRooms').innerText = templateRooms.length;
    document.getElementById('availableRooms').innerText = templateRooms.length;
    document.getElementById('occupiedRooms').innerText = occupiedRooms.length;
}

// ==================== TABLE ====================
Table() {
    const tbody = document.getElementById('roomsTableBody');
    const searchTerm = (document.getElementById('searchInput')?.value || '').toLowerCase();
    const filterStatus = document.getElementById('filterStatus')?.value || '';
    
    let displayRooms = getFilteredRooms();
    
    if (searchTerm) {
        displayRooms = displayRooms.filter(r => r.name.toLowerCase().includes(searchTerm));
    }
    if (filterStatus) {
        displayRooms = displayRooms.filter(r => r.status === filterStatus);
    }function renderTable() {
    const tbody = document.getElementById('roomsTableBody');
    const searchTerm = (document.getElementById('searchInput')?.value || '').toLowerCase();
    const filterStatus = document.getElementById('filterStatus')?.value || '';

    // Merge available rooms + bookings
    let displayRooms = [];

    // Available rooms
    const availableRooms = rooms
        .filter(r => r.status === 'available')
        .map(r => ({
            ...r,
            type: 'available'
        }));

    // Occupied rooms from bookings
    const occupiedRooms = bookings.map(b => ({
        id: b.id,
        name: b.name,
        capacity: b.capacity,
        booking_date: b.booking_date,
        start_time: b.start_time,
        end_time: b.end_time,
        instructor_fullname: b.instructor_fullname,
        instructor_id: b.instructor_id,
        notes: b.notes,
        status: 'occupied',
        type: 'booking'
    }));

    displayRooms = [...availableRooms, ...occupiedRooms];

    // Search filter
    if (searchTerm) {
        displayRooms = displayRooms.filter(r =>
            r.name.toLowerCase().includes(searchTerm)
        );
    }

    // Status filter
    if (filterStatus) {
        displayRooms = displayRooms.filter(r =>
            r.status === filterStatus
        );
    }

    if (displayRooms.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-8 text-gray-400">
                    No rooms found
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = displayRooms.map(room => {

        const isBooked = room.type === 'booking';

        const statusBadge = isBooked
            ? '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">Occupied</span>'
            : '<span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">Available</span>';

        const dateDisplay = isBooked
            ? formatDate(room.booking_date)
            : '-';

        const timeDisplay = isBooked
            ? `${formatTime(room.start_time)} - ${formatTime(room.end_time)}`
            : '-';

        const instructorDisplay = isBooked
            ? room.instructor_fullname
            : '-';

        let actionsHTML;

        if (!isBooked) {
            actionsHTML = `
                <button onclick="openBookModal(null, '${escapeHtml(room.name)}')"
                    class="bg-red-800 hover:bg-red-900 text-white px-3 py-1 rounded text-xs font-medium transition">
                    Book
                </button>

                <button onclick="deleteRoom(${room.id})"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded text-xs font-medium transition ml-1">
                    Delete
                </button>
            `;
        } else {
            actionsHTML = `
                <button onclick="openBookModal(${room.id})"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-medium transition">
                    Edit
                </button>

                <button onclick="removeBooking(${room.id})"
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-medium transition ml-1">
                    Remove
                </button>
            `;
        }

        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="py-3 px-4 font-medium">
                    🏢 ${escapeHtml(room.name)}
                </td>

                <td class="py-3 px-4">
                    ${room.capacity}
                </td>

                <td class="py-3 px-4">
                    ${dateDisplay}
                </td>

                <td class="py-3 px-4">
                    ${timeDisplay}
                </td>

                <td class="py-3 px-4">
                    ${escapeHtml(instructorDisplay)}
                </td>

                <td class="py-3 px-4">
                    ${statusBadge}
                </td>

                <td class="py-3 px-4">
                    ${actionsHTML}
                </td>
            </tr>
        `;
    }).join('');
}
    
    tbody.innerHTML = displayRooms.map(room => {
        const booking = room.status === 'occupied' ? room : null;
        
        const statusBadge = room.status === 'available' && !room.booking_date
            ? '<span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-semibold">Available</span>'
            : '<span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs font-semibold">Occupied</span>';
        
        const dateDisplay = booking?.booking_date ? formatDate(booking.booking_date) : '-';
        const timeDisplay = booking ? `${formatTime(booking.start_time)} - ${formatTime(booking.end_time)}` : '-';
        const instructorDisplay = booking?.instructor_fullname || '-';
        
        let actionsHTML;
        if (room.status === 'available' && !room.booking_date) {
            actionsHTML = `
                <button onclick="openBookModal(null, '${escapeHtml(room.name)}')" class="bg-red-800 hover:bg-red-900 text-white px-3 py-1 rounded text-xs font-medium transition">Book</button>
                <button onclick="deleteRoom(${room.id})" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded text-xs font-medium transition ml-1">Delete</button>
            `;
        } else {
            actionsHTML = `
                <button onclick="openBookModal(${room.id})" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-medium transition">Edit</button>
                <button onclick="removeBooking(${room.id})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-medium transition ml-1">Remove</button>
            `;
        }
        
        return `
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="py-3 px-4 font-medium">🏢 ${escapeHtml(room.name)}</td>
                <td class="py-3 px-4">${room.capacity}</td>
                <td class="py-3 px-4">${dateDisplay}</td>
                <td class="py-3 px-4">${timeDisplay}</td>
                <td class="py-3 px-4">${escapeHtml(instructorDisplay)}</td>
                <td class="py-3 px-4">${statusBadge}</td>
                <td class="py-3 px-4">${actionsHTML}</td>
            </tr>
        `;
    }).join('');
}

// ==================== DATE FILTER ====================
function setDateFilter(filter) {
    currentDateFilter = filter;
    
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    document.getElementById(`filter-${filter}`)?.classList.add('active');
    
    const label = document.getElementById('dateFilterLabel');
    const today = new Date();
    
    switch(filter) {
        case 'day':
            currentFilterDate = today;
            label.innerText = `Showing: ${today.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })}`;
            break;
        case 'week':
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay());
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            currentFilterDate = startOfWeek;
            label.innerText = `Showing: ${startOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endOfWeek.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}`;
            break;
        case 'month':
            currentFilterDate = new Date(today.getFullYear(), today.getMonth(), 1);
            label.innerText = `Showing: ${today.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}`;
            break;
        default:
            label.innerText = '';
    }
    
    renderTable();
}

function getFilteredRooms() {
    if (currentDateFilter === 'all') return [...rooms];
    
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    
    const templateRooms = rooms.filter(r => r.status === 'available' && !r.booking_date);
    let filteredBookings = rooms.filter(r => r.status === 'occupied' && r.booking_date);
    
    switch(currentDateFilter) {
        case 'day':
            filteredBookings = filteredBookings.filter(r => r.booking_date === todayStr);
            break;
        case 'week':
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay());
            const startStr = startOfWeek.toISOString().split('T')[0];
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            const endStr = endOfWeek.toISOString().split('T')[0];
            filteredBookings = filteredBookings.filter(r => r.booking_date >= startStr && r.booking_date <= endStr);
            break;
        case 'month':
            const monthStr = today.toISOString().split('T')[0].substring(0, 7);
            filteredBookings = filteredBookings.filter(r => r.booking_date && r.booking_date.startsWith(monthStr));
            break;
    }
    
    return [...templateRooms, ...filteredBookings];
}

// ==================== CONFLICT CHECK ====================
function checkConflict(roomName, date, startTime, endTime, excludeId = null) {
    return bookings.filter(b => {
        if (b.name !== roomName) return false;
        if (b.booking_date !== date) return false;
        if (excludeId && b.id === excludeId) return false;
        return (startTime < b.end_time && endTime > b.start_time);
    });
}

// Real-time conflict detection
document.addEventListener('DOMContentLoaded', () => {
    const startTimeInput = document.getElementById('bookingStartTime');
    const endTimeInput = document.getElementById('bookingEndTime');
    const dateInput = document.getElementById('bookingDate');
    const roomSelect = document.getElementById('roomSelect');
    
    if (startTimeInput && endTimeInput) {
        [startTimeInput, endTimeInput, dateInput].forEach(input => {
            if (input) {
                input.addEventListener('change', () => {
                    const roomName = roomSelect?.value;
                    const date = dateInput?.value;
                    const startTime = startTimeInput?.value;
                    const endTime = endTimeInput?.value;
                    const bookingId = document.getElementById('bookingId')?.value;
                    
                    if (roomName && date && startTime && endTime) {
                        const conflicts = checkConflict(roomName, date, startTime, endTime, bookingId || null);
                        const warningDiv = document.getElementById('conflictWarning');
                        const conflictMsg = document.getElementById('conflictMessage');
                        const saveBtn = document.getElementById('saveBookingBtn');
                        
                        if (conflicts.length > 0) {
                            warningDiv.classList.remove('hidden');
                            conflictMsg.innerText = `This room is already booked at this time by ${conflicts[0].instructor_fullname || 'someone'} (${conflicts[0].start_time} - ${conflicts[0].end_time}). Please choose a different time.`;
                            saveBtn.disabled = true;
                            saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        } else {
                            warningDiv.classList.add('hidden');
                            saveBtn.disabled = false;
                            saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    }
                });
            }
        });
    }
});

// ==================== HELPERS ====================
function formatDate(dateStr) {
    if (!dateStr) return '-';
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatTime(timeStr) {
    if (!timeStr) return '-';
    const [h, m] = timeStr.split(':');
    const hour = parseInt(h);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${m} ${ampm}`;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

// ==================== EVENTS ====================
document.getElementById('searchInput')?.addEventListener('input', renderTable);
document.getElementById('filterStatus')?.addEventListener('change', renderTable);

// ==================== ADD ROOM ====================
function openAddRoomModal() {
    document.getElementById('addRoomForm').reset();
    document.getElementById('addRoomModal').classList.add('active');
}

function closeAddRoomModal() {
    document.getElementById('addRoomModal').classList.remove('active');
}

async function saveRoom(e) {
    e.preventDefault();
    const name = document.getElementById('newRoomName').value.trim();
    const capacity = parseInt(document.getElementById('newRoomCapacity').value) || 0;

    if (!name) return showNotification('Please enter a room name', 'error');
    if (capacity < 1) return showNotification('Capacity must be at least 1', 'error');

    try {
        const res = await fetch(`${API_URL}?endpoint=rooms`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, capacity })
        });
        const data = await res.json();
        
        if (data.success) {
            showNotification('Room added successfully', 'success');
            closeAddRoomModal();
            loadData();
        } else {
            showNotification(data.error || 'Failed to add room', 'error');
        }
    } catch (error) {
        showNotification('Failed to connect to server', 'error');
    }
}

// ==================== DELETE ROOM ====================
async function deleteRoom(id) {
    if (!confirm('Are you sure you want to delete this room?')) return;
    
    try {
        const res = await fetch(`${API_URL}?endpoint=rooms`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        
        if (data.success) {
            showNotification('Room deleted', 'success');
            loadData();
        } else {
            showNotification(data.error || 'Failed to delete room', 'error');
        }
    } catch (error) {
        showNotification('Failed to connect', 'error');
    }
}

// ==================== BOOK ROOM ====================
function openBookModal(bookingId = null, roomName = null) {
    document.getElementById('bookRoomForm').reset();
    document.getElementById('conflictWarning').classList.add('hidden');
    document.getElementById('saveBookingBtn').disabled = false;
    document.getElementById('saveBookingBtn').classList.remove('opacity-50', 'cursor-not-allowed');
    
    updateRoomDropdown(bookingId);
    updateInstructorDropdown();
    
    if (bookingId) {
        const booking = rooms.find(r => r.id == bookingId);
        if (booking) {
            document.getElementById('bookModalTitle').innerText = 'Edit Booking';
            document.getElementById('bookingId').value = booking.id;
            document.getElementById('roomSelect').value = booking.name;
            document.getElementById('instructorSelect').value = booking.instructor_id || '';
            document.getElementById('bookingDate').value = booking.booking_date || '';
            document.getElementById('bookingStartTime').value = booking.start_time || '';
            document.getElementById('bookingEndTime').value = booking.end_time || '';
            document.getElementById('bookingNotes').value = booking.notes || '';
            document.getElementById('removeBookingBtn').classList.remove('hidden');
        }
    } else {
        document.getElementById('bookModalTitle').innerText = 'Book Room';
        document.getElementById('bookingId').value = '';
        document.getElementById('removeBookingBtn').classList.add('hidden');
        
        if (roomName) document.getElementById('roomSelect').value = roomName;
        
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('bookingDate').value = today;
        document.getElementById('bookingStartTime').value = '09:00';
        document.getElementById('bookingEndTime').value = '10:00';
    }
    
    document.getElementById('bookRoomModal').classList.add('active');
}

function closeBookRoomModal() {
    document.getElementById('bookRoomModal').classList.remove('active');
}

function updateRoomDropdown(editBookingId = null) {
    const select = document.getElementById('roomSelect');
    if (!select) return;
    
    const templateRooms = rooms.filter(r => r.status === 'available' && !r.booking_date);
    const editingRoom = editBookingId ? rooms.find(r => r.id == editBookingId) : null;
    
    let options = templateRooms.map(r => 
        `<option value="${r.name}">${r.name} (Cap: ${r.capacity})</option>`
    );
    
    if (editingRoom && editingRoom.status === 'occupied') {
        options.push(`<option value="${editingRoom.name}" selected>${editingRoom.name} (Cap: ${editingRoom.capacity}) - Editing</option>`);
    }
    
    select.innerHTML = options.join('');
}

function updateInstructorDropdown() {
    const select = document.getElementById('instructorSelect');
    if (!select) return;
    
    if (instructors.length === 0) {
        select.innerHTML = '<option value="">No instructors available</option>';
        return;
    }
    
    select.innerHTML = '<option value="">Select Instructor</option>' + 
        instructors.map(i => `<option value="${i.id}">${i.fullname} (${i.email})</option>`).join('');
}

async function saveBooking(e) {
    e.preventDefault();
    
    const id = document.getElementById('bookingId').value;
    const name = document.getElementById('roomSelect').value;
    const instructor_id = document.getElementById('instructorSelect').value;
    const booking_date = document.getElementById('bookingDate').value;
    const start_time = document.getElementById('bookingStartTime').value;
    const end_time = document.getElementById('bookingEndTime').value;
    const notes = document.getElementById('bookingNotes').value;

    if (!name) return showNotification('Please select a room', 'error');
    if (!instructor_id) return showNotification('Please select an instructor', 'error');
    if (!booking_date) return showNotification('Please select a date', 'error');
    if (!start_time || !end_time) return showNotification('Please select times', 'error');
    if (start_time >= end_time) return showNotification('End time must be after start time', 'error');

    const conflicts = checkConflict(name, booking_date, start_time, end_time, id || null);
    if (conflicts.length > 0) {
        showNotification(`Time conflict! Room booked ${conflicts[0].start_time}-${conflicts[0].end_time} by ${conflicts[0].instructor_fullname}`, 'error');
        return;
    }

    const bookingData = { name, instructor_id: parseInt(instructor_id), booking_date, start_time, end_time, notes };

    try {
        let res;
        if (id) {
            bookingData.id = parseInt(id);
            res = await fetch(`${API_URL}?endpoint=bookings`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookingData)
            });
        } else {
            res = await fetch(`${API_URL}?endpoint=bookings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(bookingData)
            });
        }
        
        const data = await res.json();
        if (data.success) {
            showNotification(id ? 'Booking updated' : 'Room booked', 'success');
            closeBookRoomModal();
            loadData();
        } else {
            showNotification(data.error || 'Failed to save booking', 'error');
        }
    } catch (error) {
        showNotification('Failed to connect', 'error');
    }
}

// ==================== REMOVE BOOKING ====================
function removeBookingFromModal() {
    const id = document.getElementById('bookingId').value;
    if (id) removeBooking(parseInt(id));
    closeBookRoomModal();
}

async function removeBooking(id) {
    if (!confirm('Are you sure you want to remove this booking?')) return;
    
    try {
        const res = await fetch(`${API_URL}?endpoint=bookings`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        
        if (data.success) {
            showNotification('Booking removed', 'success');
            loadData();
        } else {
            showNotification(data.error || 'Failed', 'error');
        }
    } catch (error) {
        showNotification('Failed to connect', 'error');
    }
}

// ==================== NOTIFICATIONS ====================
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (!notification) return;
    
    notification.innerText = message;
    notification.className = `notification ${type} show`;
    
    setTimeout(() => notification.classList.remove('show'), 3000);
}

// ==================== LOGOUT ====================
function logout() {
    localStorage.clear();
    sessionStorage.clear();
    window.location.href = './index.html';
}

function loadCurrentUser() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const el = document.getElementById('currentUser');
    if (el && user.fullname) el.innerText = `Welcome, ${user.fullname}`;
}

// ==================== CLOSE MODALS ====================
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
    }
});

// ==================== INIT ====================
document.addEventListener('DOMContentLoaded', function() {
    loadCurrentUser();
    loadData();
    setDateFilter('all');
});