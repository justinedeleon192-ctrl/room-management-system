-- School Management System Database Schema
-- Compatible with XAMPP MySQL
-- Created for PHP migration from localStorage system

-- Create database
CREATE DATABASE IF NOT EXISTS school_system;
USE school_system;

-- Set character set and collation
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if they exist (for fresh setup)
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS instructors;
DROP TABLE IF EXISTS students;

-- Create students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_students_email (email),
    INDEX idx_students_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create instructors table
CREATE TABLE instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_instructors_email (email),
    INDEX idx_instructors_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create rooms table
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    building VARCHAR(255) NOT NULL,
    capacity INT NOT NULL DEFAULT 1,
    status ENUM('available', 'occupied') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rooms_status (status),
    INDEX idx_rooms_building (building),
    CONSTRAINT chk_capacity CHECK (capacity > 0),
    CONSTRAINT chk_status CHECK (status IN ('available', 'occupied'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing
-- Note: Passwords are hashed using PHP's password_hash() with 'password123'

-- Sample Instructor
INSERT INTO instructors (fullname, email, username, password) VALUES 
('Administrator', 'admin@cea.edu', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample Student
INSERT INTO students (fullname, email, username, password) VALUES 
('John Student', 'student@cea.edu', 'student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Sample rooms data
INSERT INTO rooms (name, building, capacity, status) VALUES 
('Room 101', 'Engineering Building', 30, 'available'),
('Room 102', 'Engineering Building', 25, 'occupied'),
('Lab 201', 'Science Building', 20, 'available'),
('Lecture Hall A', 'Main Building', 100, 'available'),
('Computer Lab', 'IT Building', 40, 'occupied'),
('Conference Room B', 'Admin Building', 15, 'available'),
('Study Hall C', 'Library Building', 50, 'available'),
('Workshop D', 'Technical Building', 35, 'occupied');

-- Create view for unified user management (optional)
CREATE OR REPLACE VIEW all_users AS
SELECT 
    id, fullname, email, username, password, 'student' as role, created_at, updated_at
FROM students
UNION ALL
SELECT 
    id, fullname, email, username, password, 'instructor' as role, created_at, updated_at
FROM instructors;

-- Create stored procedures for common operations

DELIMITER //

-- Procedure to authenticate user (checks both tables)
CREATE PROCEDURE AuthenticateUser(IN p_username VARCHAR(255), IN p_password VARCHAR(255))
BEGIN
    DECLARE user_found INT DEFAULT 0;
    DECLARE user_id INT;
    DECLARE user_fullname VARCHAR(255);
    DECLARE user_email VARCHAR(255);
    DECLARE user_role VARCHAR(20);
    DECLARE user_password VARCHAR(255);
    
    -- Check in students table first
    SELECT id, fullname, email, username, password, 'student'
    INTO user_id, user_fullname, user_email, p_username, user_password, user_role
    FROM students WHERE username = p_username LIMIT 1;
    
    -- If not found in students, check instructors
    IF user_id IS NULL THEN
        SELECT id, fullname, email, username, password, 'instructor'
        INTO user_id, user_fullname, user_email, p_username, user_password, user_role
        FROM instructors WHERE username = p_username LIMIT 1;
    END IF;
    
    -- Return result
    IF user_id IS NOT NULL THEN
        SELECT user_id as id, user_fullname as fullname, user_email as email, p_username as username, user_role as role;
    ELSE
        SELECT NULL as id, NULL as fullname, NULL as email, NULL as username, NULL as role;
    END IF;
END //

-- Procedure to check if email or username exists
CREATE PROCEDURE CheckUserExists(IN p_email VARCHAR(255), IN p_username VARCHAR(255))
BEGIN
    DECLARE email_count INT DEFAULT 0;
    DECLARE username_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO email_count
    FROM (
        SELECT email FROM students WHERE email = p_email
        UNION ALL
        SELECT email FROM instructors WHERE email = p_email
    ) as email_check;
    
    SELECT COUNT(*) INTO username_count
    FROM (
        SELECT username FROM students WHERE username = p_username
        UNION ALL
        SELECT username FROM instructors WHERE username = p_username
    ) as username_check;
    
    SELECT email_count, username_count;
END //

DELIMITER ;

-- Set foreign key checks back on
SET FOREIGN_KEY_CHECKS = 1;

-- Grant permissions (adjust as needed for XAMPP)
-- GRANT ALL PRIVILEGES ON school_system.* TO 'root'@'localhost' WITH GRANT OPTION;
-- FLUSH PRIVILEGES;

-- Display success message
SELECT 'School Management System database created successfully!' as message,
       (SELECT COUNT(*) FROM students) as students_count,
       (SELECT COUNT(*) FROM instructors) as instructors_count,
       (SELECT COUNT(*) FROM rooms) as rooms_count;
