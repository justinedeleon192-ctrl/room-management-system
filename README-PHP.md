# School Management System - PHP/MySQL Version

A comprehensive PHP-based school management system with MySQL database integration for XAMPP. This version replaces the localStorage-based system with a robust server-side architecture.

## 🎯 Features

### 🔐 Authentication System
- **User Registration** with role selection (Student/Instructor)
- **Secure Login** with PHP password hashing
- **Role-based Authentication** checking both students and instructors tables
- **Session Management** with secure PHP sessions
- **Automatic Redirects** based on user role

### 👥 User Management
- **Separate Database Tables** for students and instructors
- **Role-based Table Insertion** during registration
- **Duplicate Prevention** for email and username across both tables
- **Input Validation** with comprehensive error handling
- **Password Security** with PHP's password_hash()

### 🏗️ Database Architecture
- **MySQL Database** with optimized schema
- **Two Separate Tables**: `students` and `instructors`
- **Rooms Table** for room management system
- **Unique Constraints** on email and username
- **Auto-increment Primary Keys** and timestamps

### 🏫 Room Management System
- **CRUD Operations** for room management
- **Role-based Access** (Instructors can manage, Students can view)
- **Search and Filter** functionality
- **Real-time Status Updates**
- **Statistics Dashboard**

### 🎨 Frontend Features
- **Modern Responsive Design** with gradient backgrounds
- **Role-specific Dashboards** for students and instructors
- **Real-time Form Validation** with user feedback
- **AJAX Integration** with PHP APIs
- **Professional UI** with smooth transitions

## 📁 Project Structure

```
school-management-system/
├── config/
│   └── database.php           # Database connection class
├── api/
│   ├── auth.php              # Authentication endpoints
│   └── rooms.php             # Room management endpoints
├── includes/
│   ├── session.php           # Session management
│   └── functions.php         # Helper functions
├── database/
│   └── schema.sql            # Database schema
├── public/                   # Frontend files
│   ├── index.html           # Landing page
│   ├── login.html           # Login page
│   ├── signup.html          # Registration page
│   ├── admin.html          # Instructor dashboard
│   ├── student.html        # Student dashboard
│   ├── styles.css          # Styling
│   ├── script.js           # Original localStorage version
│   └── script-php.js       # PHP API version
├── xampp-setup/             # XAMPP configuration
│   ├── setup.md            # Setup instructions
│   └── vhost.conf          # Apache configuration
└── README-PHP.md            # This documentation
```

## 🚀 Quick Setup (XAMPP)

### Prerequisites
- **XAMPP** installed and running
- **MySQL** service started
- **Apache** service started

### Installation Steps

1. **Database Setup**
   ```bash
   # Import database schema
   mysql -u root -p school_system < database/schema.sql
   ```

2. **Deploy Files**
   ```bash
   # Copy to XAMPP htdocs
   cp -r . /path/to/xampp/htdocs/school-management/
   ```

3. **Access Application**
   - URL: `http://localhost/school-management/`
   - Or with virtual host: `http://school-management.local/`

### Default Credentials
- **Instructor**: Username: `admin`, Password: `password123`
- **Student**: Username: `student`, Password: `password123`

## 🔧 Configuration

### Database Settings
Edit `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'school_system';
private $username = 'root';
private $password = '';
```

### Session Settings
Edit `includes/session.php`:
```php
// Session timeout (30 minutes)
$timeout = 30 * 60;
```

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

### Rooms Table
```sql
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    building VARCHAR(255) NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    status ENUM('available', 'occupied') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## 🛡️ Security Features

### Authentication Security
- **Password Hashing** with PHP's `password_hash()`
- **Session Management** with secure cookies
- **CSRF Protection** tokens
- **SQL Injection Prevention** with prepared statements

### Input Validation
- **Email Format Validation** with PHP filters
- **Username Validation** with regex patterns
- **Password Strength Requirements**
- **XSS Prevention** with output escaping

### Session Security
- **Session Regeneration** on login
- **Session Timeout** (30 minutes)
- **Secure Cookie Configuration**
- **Session Hijacking Prevention**

## 🔌 API Endpoints

### Authentication API (`/api/auth.php`)
- `POST /api/auth.php?action=register` - User registration
- `POST /api/auth.php?action=login` - User login
- `POST /api/auth.php?action=logout` - User logout
- `GET /api/auth.php?action=user` - Get current user
- `GET /api/auth.php?action=check` - Check authentication

### Rooms API (`/api/rooms.php`)
- `GET /api/rooms.php?action=list` - Get all rooms
- `GET /api/rooms.php?action=get&id={id}` - Get specific room
- `POST /api/rooms.php?action=create` - Create room (instructor only)
- `PUT /api/rooms.php?action=update&id={id}` - Update room (instructor only)
- `PUT /api/rooms.php?action=toggle&id={id}` - Toggle room status (instructor only)
- `DELETE /api/rooms.php?action=delete&id={id}` - Delete room (instructor only)
- `GET /api/rooms.php?action=stats` - Get room statistics

## 📱 Usage Instructions

### For Students
1. **Login** with student credentials
2. **View Dashboard** with room statistics
3. **Browse Rooms** with search and filter
4. **View Room Details** and availability

### For Instructors
1. **Login** with instructor credentials
2. **Manage Rooms** (add, edit, delete)
3. **Toggle Room Status** (available/occupied)
4. **View Statistics** and analytics
5. **Search and Filter** rooms

## 🧪 Testing

### Manual Testing
1. **Database Connection**: Verify MySQL connection
2. **User Registration**: Test both roles
3. **Login Authentication**: Test both user types
4. **Room Management**: Test CRUD operations
5. **Session Management**: Test login/logout flow

### API Testing
Use curl or Postman to test endpoints:
```bash
# Test login
curl -X POST "http://localhost/school-management/api/auth.php?action=login" \
     -H "Content-Type: application/json" \
     -d '{"username":"admin","password":"password123"}'

# Test room list
curl -X GET "http://localhost/school-management/api/rooms.php?action=list"
```

## 🔧 Development

### File Structure
- **config/**: Configuration files
- **api/**: API endpoints
- **includes/**: Helper functions and classes
- **database/**: Database schema and migrations
- **public/**: Frontend files
- **xampp-setup/**: XAMPP configuration

### Coding Standards
- **PHP**: PSR-4 autoloading, camelCase functions
- **JavaScript**: ES6+ features, async/await
- **CSS**: BEM methodology, responsive design
- **Security**: Input validation, output escaping

## 🚀 Deployment

### XAMPP Deployment
1. Copy files to `htdocs/school-management/`
2. Import database schema
3. Configure virtual host (optional)
4. Test all functionality

### Production Deployment
1. Update database credentials
2. Configure HTTPS
3. Set proper file permissions
4. Disable error reporting
5. Enable caching

## 🐛 Troubleshooting

### Common Issues
- **Database Connection**: Check MySQL service and credentials
- **404 Errors**: Verify file paths and Apache configuration
- **Session Issues**: Check PHP session configuration
- **CORS Errors**: Verify API endpoint URLs

### Debug Mode
Enable error reporting in development:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 📈 Performance Optimization

### Database Optimization
- **Indexes** on frequently queried columns
- **Prepared Statements** for security and performance
- **Connection Pooling** with persistent connections

### Frontend Optimization
- **Lazy Loading** for large datasets
- **Caching** for static assets
- **Minification** for CSS/JS files

## 🔄 Migration from localStorage

This PHP version provides these improvements over the localStorage version:
- **Multi-user Support** with proper data isolation
- **Enhanced Security** with server-side validation
- **Data Persistence** across browsers and devices
- **Scalability** for larger user bases
- **Role-based Access Control** with database enforcement

## 📞 Support

For technical support:
1. Check the troubleshooting section
2. Review error logs
3. Verify XAMPP configuration
4. Test database connectivity

---

**School Management System - PHP Version**  
*XAMPP Compatible*  
*© 2024 - All rights reserved*
