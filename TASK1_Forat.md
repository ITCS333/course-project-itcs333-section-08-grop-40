# ITCS333 Course Project - Task 1 Implementation Guide

## 🚀 Quick Start - Step by Step Setup

### Step 1: Start XAMPP Servers
1. Open **XAMPP Control Panel**
2. Click **Start** for:
   - ✅ **Apache** (for PHP & web server)
   - ✅ **MySQL** (for database)
3. Wait until both show "Running" status

### Step 2: Setup Database
1. Open your web browser
2. Go to: **http://localhost/phpmyadmin**
3. Click **"SQL"** tab at the top
4. Copy the contents of `src/config/init_db.sql` and paste it
5. Click **"Go"** button
6. You should see: ✅ "Query executed successfully"

### Step 3: Verify Database Created
1. In phpMyAdmin, check left sidebar
2. You should see database: **course_management**
3. Click on it, then click **students** table
4. You should see 3 sample students:
   - John Doe (12345)
   - Jane Smith (67890)
   - Alice Johnson (54321)
5. Default password for all: **password123**

### Step 4: Access the Application
1. Open browser and go to: **http://localhost/course-project-itcs333-section-08-grop-40/index.html**
2. Click **"Get Started"** or **"Login"** button
3. You'll see the login page

### Step 5: Test Login System
**Test with sample student:**
- Email: `john.doe@example.com`
- Password: `password123`
- Should redirect to homepage after login

### Step 6: Access Admin Portal
1. Go to: **http://localhost/course-project-itcs333-section-08-grop-40/src/admin/manage_users.html**
2. You should see:
   - ✅ Password change form
   - ✅ Add new student form (collapsible)
   - ✅ Student table with sample data


