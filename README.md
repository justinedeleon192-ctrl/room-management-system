# CEA Room Management System

A PHP and MySQL room availability system for the College of Engineering and Architecture. The app supports student and instructor accounts, role-based dashboards, and room availability management through a XAMPP-friendly backend.

## Features

- Student and instructor registration
- Secure login using PHP password hashing
- PHP session-based authentication
- Role-based redirects after login
- Instructor room management: add, edit, delete, and update room status
- Student room availability view
- Room search, filtering, and dashboard statistics
- MySQL persistence for users and rooms

## Tech Stack

- HTML, CSS, and vanilla JavaScript
- PHP 8+
- MySQL / MariaDB
- PDO database access
- XAMPP Apache and MySQL

## Project Structure

```text
rms/
├── api/
│   ├── auth.php
│   └── rooms.php
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   ├── functions.php
│   └── session.php
├── public/
│   └── script-php.js
├── admin.html
├── index.html
├── signup.html
├── student.html
├── styles.css
└── README.md
```

## Setup

1. Copy or clone the project into your XAMPP `htdocs` folder:

```text
C:\xampp\htdocs\rms
```

2. Start Apache and MySQL in XAMPP.

3. Create/import the database using phpMyAdmin:

- Open `http://localhost/phpmyadmin`
- Create a database named `school_system`
- Import `database/schema.sql`

4. Check the database settings in `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'school_system';
private $username = 'root';
private $password = '';
```

5. Open the app:

```text
http://localhost/rms/index.html
```

## Default Accounts

Use these accounts after importing the schema:

```text
Instructor
Username: admin
Password: password

Student
Username: student
Password: password
```

New accounts created through `signup.html` are saved in either the `students` or `instructors` table depending on the selected role.

## Main Pages

- `index.html` - login page
- `signup.html` - registration page
- `admin.html` - instructor dashboard
- `student.html` - student room availability view

## API Endpoints

Authentication:

```text
POST /rms/api/auth.php?action=register
POST /rms/api/auth.php?action=login
POST /rms/api/auth.php?action=logout
GET  /rms/api/auth.php?action=user
GET  /rms/api/auth.php?action=check
```

Rooms:

```text
GET    /rms/api/rooms.php?action=list
GET    /rms/api/rooms.php?action=get&id=1
GET    /rms/api/rooms.php?action=stats
POST   /rms/api/rooms.php?action=create
PUT    /rms/api/rooms.php?action=update&id=1
PUT    /rms/api/rooms.php?action=toggle&id=1
DELETE /rms/api/rooms.php?action=delete&id=1
```

## Notes

- Instructors can manage rooms.
- Students can view and filter rooms.
- The frontend uses `public/script-php.js`, which calls the PHP API and supports running the project under `localhost/rms`.
- PHP sessions must be enabled for login and protected features to work.

## Recent Auth Fix

Signup and login are now connected to the PHP/MySQL backend. Login verifies the stored password hash correctly, and JSON responses preserve PHP session cookies so authenticated features continue working after login.
