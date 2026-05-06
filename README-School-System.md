# School Management System - Full Stack Application

A comprehensive full-stack web application with MySQL database integration for managing students and instructors with role-based authentication.

## 🎯 Features

### 🔐 Authentication System
- **User Registration** with role selection (Student/Instructor)
- **Secure Login** with password hashing using bcrypt
- **Role-based Authentication** checking both students and instructors tables
- **Session Management** with secure cookies
- **Automatic Redirects** based on user role

### 👥 User Management
- **Separate Database Tables** for students and instructors
- **Role-based Table Insertion** during registration
- **Duplicate Prevention** for email and username across both tables
- **Input Validation** with comprehensive error handling
- **Password Security** with bcrypt hashing

### 🏗️ Database Architecture
- **MySQL Database** with optimized schema
- **Two Separate Tables**: `students` and `instructors`
- **Unique Constraints** on email and username
- **Auto-increment Primary Keys**
- **Timestamps** for tracking creation/updates

### 🎨 Frontend Features
- **Modern Responsive Design** with gradient backgrounds
- **Role-specific Dashboards** for students and instructors
- **Real-time Form Validation** with user feedback
- **Loading States** and error handling
- **Professional UI** with smooth transitions

## 📁 Project Structure

```
school-management-system/
├── server.js                    # Main Node.js server
├── package.json                 # Dependencies and scripts
├── .env                         # Environment variables
├── database.sql                 # MySQL schema and sample data
├── public/                      # Frontend files
│   ├── index.html              # Landing page
│   ├── login.html              # Login page
│   ├── register.html           # Registration page
│   ├── student-dashboard.html   # Student dashboard
│   └── instructor-dashboard.html # Instructor dashboard
└── README-School-System.md      # This documentation
```

## 🚀 Setup Instructions

### Prerequisites
- **Node.js** (v14 or higher)
- **MySQL** (v5.7 or higher)
- **npm** or **yarn** package manager

### Database Setup
1. **Start MySQL Server**
2. **Create Database**:
   ```sql
   mysql -u root -p
   CREATE DATABASE school_system;
   ```
3. **Import Schema**:
   ```bash
   mysql -u root -p school_system < database.sql
   ```

### Application Setup
1. **Install Dependencies**:
   ```bash
   npm install
   ```

2. **Configure Environment**:
   - Edit `.env` file with your MySQL credentials
   - Update `SESSION_SECRET` for production

3. **Start Server**:
   ```bash
   npm start
   # or for development with auto-reload
   npm run dev
   ```

4. **Access Application**:
   - Open http://localhost:3000 in your browser

## 📊 Database Schema

### Students Table
```sql
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Instructors Table
```sql
CREATE TABLE instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🔧 API Endpoints

### Authentication
- `POST /api/register` - User registration with role selection
- `POST /api/login` - User authentication
- `POST /api/logout` - User logout
- `GET /api/user` - Get current user information

### Protected Routes
- `GET /api/dashboard` - User dashboard data
- `GET /dashboard` - Redirect to role-specific dashboard

### Frontend Routes
- `/` - Landing page
- `/login` - Login page
- `/register` - Registration page
- `/dashboard` - Role-specific dashboard (protected)

## 🛡️ Security Features

### Password Security
- **bcrypt Hashing** with salt rounds (10)
- **Minimum Password Length** (6 characters)
- **Password Validation** on both client and server

### Input Validation
- **Email Format Validation** using regex
- **Username Length Validation** (minimum 3 characters)
- **Full Name Validation** (minimum 2 characters)
- **Role Validation** (only 'student' or 'instructor')

### Session Security
- **Secure Session Cookies** in production
- **Session Expiration** (24 hours)
- **Automatic Session Cleanup** on logout
- **CSRF Protection** with session middleware

### Database Security
- **Prepared Statements** to prevent SQL injection
- **Unique Constraints** for data integrity
- **Input Sanitization** and validation

## 📱 Usage Instructions

### Registration Process
1. Navigate to `/register`
2. Fill in all required fields:
   - Full Name (minimum 2 characters)
   - Valid Email Address
   - Username (minimum 3 characters)
   - Password (minimum 6 characters)
   - Role Selection (Student/Instructor)
3. Submit form and wait for confirmation
4. Redirect to login page after successful registration

### Login Process
1. Navigate to `/login`
2. Enter username and password
3. System checks both students and instructors tables
4. Redirect to appropriate dashboard based on role

### Dashboard Features
- **Student Dashboard**: View academic information, courses, assignments
- **Instructor Dashboard**: Manage courses, students, grading tools

## 🔧 Configuration

### Environment Variables (.env)
```env
# Database Configuration
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_mysql_password
DB_NAME=school_system

# Server Configuration
PORT=3000
SESSION_SECRET=your-secret-key-here

# Application Configuration
NODE_ENV=development
```

### Production Setup
1. **Set NODE_ENV=production**
2. **Use HTTPS** for secure connections
3. **Configure reverse proxy** (nginx/Apache)
4. **Set up SSL certificates**
5. **Configure database backups**
6. **Monitor application logs**

## 🧪 Testing

### Manual Testing Steps
1. **Database Connection**: Verify MySQL connection
2. **Registration**: Test both student and instructor registration
3. **Duplicate Prevention**: Try registering with existing email/username
4. **Login**: Test login for both roles
5. **Session Management**: Verify session persistence and logout
6. **Dashboard Access**: Test role-based redirects

### Sample Test Data
The database includes sample users:
- **Instructor**: johnsmith / password123
- **Student**: janedoe / password123

## 🎯 Future Enhancements

### Backend Features
- **Email Verification** for registration
- **Password Reset** functionality
- **Account Management** (update profile, change password)
- **Advanced Role Management** (admin, moderator, etc.)
- **API Rate Limiting** and throttling

### Frontend Features
- **Dark Mode** toggle
- **Multi-language Support**
- **Advanced Dashboard Analytics**
- **Real-time Notifications**
- **File Upload** capabilities

### Database Features
- **Additional User Roles** and permissions
- **Audit Logging** for user actions
- **Data Export** functionality
- **Backup and Recovery** systems

## 📞 Support

This is a demonstration project showcasing:
- **Node.js/Express** backend development
- **MySQL** database integration
- **bcrypt** password hashing
- **Session-based authentication**
- **Role-based access control**
- **Modern frontend** with vanilla JavaScript

For technical questions, refer to the code documentation and comments within the files.

---

**School Management System**  
*Full Stack Application*  
*© 2024 - All rights reserved*
