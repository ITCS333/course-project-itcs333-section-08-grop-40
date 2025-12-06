# ITCS333 Course Project - Task 1 Implementation Guide

## Setup Instructions

### 1. Database Setup
1. Start your XAMPP Apache and MySQL servers
2. Navigate to: `http://localhost/course-project-itcs333-section-08-grop-40/src/config/init_db.php`
3. This will create the database and add sample users

### 2. Default Login Credentials
- **Admin**: admin@uob.edu.bh / admin123
- **Student**: sara.m@stu.uob.bh / student123

## Files Created for Task 1

### Core Files
- `index.html` - Homepage
- `src/config/db.php` - Database connection
- `src/config/init_db.php` - Database setup script
- `src/auth/login.php` - Login page
- `src/auth/logout.php` - Logout functionality
- `src/admin/dashboard.php` - Admin portal with student management
- `src/utils/auth.php` - Helper functions for teammates

### CSS Files
- `src/common/styles.css` - Shared styles
- `home.css` - Homepage styles
- `src/auth/auth.css` - Login page styles
- `src/admin/admin.css` - Admin dashboard styles

## For Teammates: Using the Auth System

Include this at the top of your PHP pages:

```php
<?php
session_start();
require_once '../config/db.php';
require_once '../utils/auth.php';

// For pages that need login
requireLogin();

// For admin-only pages
requireAdmin();

// Check if user is logged in
if (isLoggedIn()) {
    // User is logged in
}

// Check if user is admin
if (isAdmin()) {
    // User is admin
}

// Get user info
$name = getUserName();
$role = getUserRole();
?>
```

## Database Table Structure

### users table
- `id` - Auto increment primary key
- `student_id` - Unique student ID
- `name` - Full name
- `email` - Email (unique)
- `password` - Hashed password
- `role` - Either 'admin' or 'student'
- `created_at` - Timestamp

### For Teammates: Adding Your Own Tables

**Option 1: Using phpMyAdmin (Manual)**
1. Navigate to `http://localhost/phpmyadmin`
2. Select the `course_db` database
3. Create your tables manually using the GUI

**Option 2: Using the Init Script (Recommended)**
1. Open `src/config/init_db.php`
2. Add your table creation code before the closing PHP tag:
```php
$pdo->exec("CREATE TABLE IF NOT EXISTS your_table_name (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");
```
3. Run the script again to create your tables

## Features Implemented

### Homepage
- Simple landing page with course information
- Link to login page

### Login System
- Email and password authentication
- Password hashing with PHP password_hash()
- Session management
- Redirects admin to dashboard, students to homepage

### Admin Portal
- Change admin password
- Add new students (CRUD - Create)
- View all students (CRUD - Read)
- Edit student information (CRUD - Update)
- Delete students (CRUD - Delete)

## Session Variables Available
- `$_SESSION['user_id']` - User ID
- `$_SESSION['user_name']` - Full name
- `$_SESSION['user_email']` - Email
- `$_SESSION['user_role']` - 'admin' or 'student'
- `$_SESSION['student_id']` - Student ID

## Testing Your Pages
1. Make sure XAMPP is running
2. Navigate to your page
3. If not logged in, you'll be redirected to login
4. Use the helper functions to check permissions
