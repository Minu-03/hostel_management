# Hostel Management System

A web-based hostel management system built with **PHP, MySQL, HTML, CSS, and JavaScript**.

## Features

1. **User Authentication** - Secure login/logout with admin and student roles
2. **Student Management** - Add, edit, delete, check-in, and check-out students
3. **Room Management** - Manage rooms with capacity, type, and fee tracking
4. **Room Allocation** - Allocate and vacate rooms with automatic occupancy tracking
5. **Payment Management** - Record payments with auto-generated receipt numbers
6. **Complaint Management** - Students file complaints; admins respond and update status
7. **Visitor Management** - Check-in/check-out visitors with purpose tracking
8. **Report Generation** - Summary reports for students, rooms, finances, complaints, and visitors
9. **Help Section** - FAQ and quick guides for both admins and students

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.3+)
- A web server (Apache, Nginx) or local environment like XAMPP/WAMP/MAMP

## Installation

### Step 1: Copy files to your web server
Copy all files to your web server's document root (e.g., `htdocs` for XAMPP, `www` for WAMP).

### Step 2: Create the database
1. Open phpMyAdmin or your MySQL client
2. Import the file `database/schema.sql` - this creates the database and all tables

### Step 3: Configure the database connection
Edit `config/database.php` and update these constants to match your MySQL server:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'hostel_management');
```

### Step 4: Set the base URL
Edit `config/database.php` and adjust the `base_url()` function to match your folder name:
```php
function base_url($path = '') {
    $base = '/hostel_management';  // change this if you use a different folder name
    return $base . '/' . ltrim($path, '/');
}
```

### Step 5: Run the setup script
Open your browser and visit: `http://localhost/hostel_management/setup.php`

This sets the default admin password hash correctly. **Delete `setup.php` after running it.**

### Step 6: Log in
- **Admin Email:** admin@hostel.com
- **Admin Password:** admin123

## Directory Structure

```
hostel_management/
├── config/
│   └── database.php          # Database connection configuration
├── database/
│   └── schema.sql             # MySQL database schema
├── includes/
│   ├── auth.php               # Authentication helpers
│   ├── header.php             # Shared layout header (sidebar, topbar)
│   └── footer.php             # Shared layout footer
├── assets/
│   ├── css/
│   │   └── style.css          # All styles
│   └── js/
│       └── app.js             # Client-side JavaScript
├── admin/
│   ├── dashboard.php          # Admin overview
│   ├── students.php           # Student CRUD + check-in/out
│   ├── rooms.php              # Room CRUD
│   ├── allocations.php        # Room allocation management
│   ├── payments.php           # Payment CRUD
│   ├── complaints.php         # Complaint response management
│   ├── visitors.php           # Visitor management
│   └── reports.php            # Summary reports
├── student/
│   ├── dashboard.php          # Student overview
│   ├── profile.php            # Profile + password change
│   ├── payments.php           # View payment history
│   ├── complaints.php         # File & view complaints
│   └── visitors.php           # Visitor check-in/out
├── index.php                  # Login page
├── logout.php                 # Logout handler
├── help.php                   # Help & FAQ page
├── setup.php                  # One-time setup script (delete after use)
└── README.md                  # This file
```

## Security Notes

- Passwords are hashed using PHP's `password_hash()` with bcrypt
- All database queries use PDO prepared statements to prevent SQL injection
- All output is escaped with `htmlspecialchars()` to prevent XSS
- Role-based access control separates admin and student permissions
- **Delete `setup.php` after running it**

## Technologies Used

| Technology | Purpose |
|------------|---------|
| HTML       | Page structure |
| CSS        | Styling and responsive layout |
| JavaScript | Interactive UI (modals, search, sidebar) |
| PHP        | Server-side logic and database operations |
| MySQL      | Database (SQL) |

## Usage Guide

### For Admins
1. Log in with admin credentials
2. Add students (a login account is created automatically)
3. Add rooms with capacity and fees
4. Allocate rooms to students
5. Record payments as students pay
6. Respond to student complaints
7. Manage visitor check-in/check-out
8. View reports for an overview of the hostel

### For Students
1. Log in with credentials provided by the admin
2. View your room allocation on the dashboard
3. Check your payment history
4. File complaints for maintenance or other issues
5. Check in visitors when they arrive
6. Update your profile and change your password


## Test Update

This is a test commit to verify GitHub push.