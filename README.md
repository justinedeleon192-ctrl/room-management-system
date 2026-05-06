# Room Availability System

A modern, responsive Room Availability System built for the College of Engineering and Architecture. This frontend-only application uses HTML, CSS, and vanilla JavaScript with a beautiful maroon and white academic theme.

## 🎯 Features

### 🔐 Authentication System
- **Login Page** with username/password fields
- **Signup Page** with role selection (Student/Instructor)
- Role-based redirects (Admin → Admin Dashboard, Student → Student View)
- Session management using localStorage

### 👥 User Roles & Permissions

#### 🧑‍💼 Admin / Instructor
- Full CRUD operations on rooms
- Add, edit, delete rooms
- Change room availability status
- View comprehensive room statistics
- Search and filter functionality

#### 🎓 Student
- View-only access to room information
- See room availability status
- Search and filter rooms
- Clean, intuitive interface

### 🏫 Room Management System
- **Room List Display** in table (Admin) and card (Student) layouts
- **Color-coded Status Indicators**:
  - 🟢 Green = Available
  - 🔴 Red = Occupied
- **Room Information**: Name, Building, Capacity, Status
- **Real-time Updates** with localStorage persistence

### 🎨 Design Features
- **Maroon/White Academic Theme** matching College of Engineering and Architecture
- **Responsive Design** for mobile and desktop
- **Modern UI Elements**: Soft shadows, rounded buttons, clean typography
- **Professional Layout** with header and footer bars
- **Logo Placement** on right side (login/signup pages)

### ⚙️ Technical Implementation
- **HTML5** semantic structure
- **CSS3** with Flexbox/Grid layouts
- **Vanilla JavaScript** (no frameworks)
- **localStorage** for data persistence
- **Modular Code Structure** with clear functions

### ⭐ Bonus Features
- **Search Functionality** - Filter rooms by name or building
- **Status Filtering** - View available/occupied rooms only
- **Toast Notifications** - User-friendly feedback messages
- **Statistics Dashboard** - Real-time room counts
- **Modal Forms** - Modern add/edit room interface
- **Responsive Tables** - Mobile-friendly data display

## 📁 Project Structure

```
room-availability-system/
├── index.html          # Login page
├── signup.html         # User registration
├── admin.html          # Admin dashboard
├── student.html        # Student view
├── styles.css          # Complete styling with academic theme
├── script.js           # All JavaScript functionality
└── README.md           # Project documentation
```

## 🚀 Getting Started

1. **Download/Clone** the project files
2. **Open `index.html`** in your web browser
3. **Default Login Credentials**:
   - **Admin**: Username: `admin`, Password: `admin123`
   - **Student**: Username: `student`, Password: `student123`

## 📱 Usage Instructions

### For Admin/Instructors:
1. Login with admin credentials
2. View the dashboard with room statistics
3. **Add Rooms**: Click "➕ Add Room" button
4. **Edit Rooms**: Click "Edit" button in room table
5. **Delete Rooms**: Click "Delete" button (with confirmation)
6. **Toggle Status**: Click "Mark Available/Occupied" buttons
7. **Search**: Use the search bar to find specific rooms
8. **Filter**: Use dropdown to filter by status

### For Students:
1. Login with student credentials
2. View available rooms in card layout
3. **Search**: Use search bar to find rooms
4. **Filter**: Filter by availability status
5. **View Details**: See room information and availability

## 🎨 Design System

### Color Palette
- **Primary**: Maroon (#8B0000)
- **Secondary**: White (#FFFFFF)
- **Accent**: Light Gray (#F5F5F5)
- **Success**: Green (#28A745)
- **Danger**: Red (#DC3545)

### Typography
- **Font Family**: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
- **Headings**: Bold, maroon color scheme
- **Body Text**: Clean, readable dark gray

### Responsive Breakpoints
- **Desktop**: 1200px+ (full layout)
- **Tablet**: 768px-1199px (adapted layout)
- **Mobile**: <768px (stacked layout)

## 🔧 Technical Details

### Data Storage
- **localStorage** for user accounts and room data
- **JSON format** for structured data
- **Automatic data persistence** across sessions

### Security Features
- **Role-based access control**
- **Session management**
- **Input validation**
- **Confirmation dialogs** for destructive actions

### Browser Compatibility
- **Modern browsers** (Chrome, Firefox, Safari, Edge)
- **Mobile responsive** design
- **No external dependencies**

## 📊 Sample Data

The system comes pre-loaded with:
- **2 Default Users** (admin and student)
- **5 Sample Rooms** across different buildings
- **Mixed availability status** for demonstration

## 🎯 Future Enhancements

- Real-time room booking system
- Calendar integration
- Advanced reporting
- Email notifications
- Dark mode toggle
- Room scheduling
- Multi-building management

## 📞 Support

This is a demonstration project showcasing modern web development techniques with vanilla JavaScript. For questions or support, refer to the code comments and documentation within the files.

---

**College of Engineering and Architecture**  
*Room Availability System*  
*© 2024 - All rights reserved*
