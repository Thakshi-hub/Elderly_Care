# Elderly Care Management System

A web application for managing elderly care — including medications, daily checklists, appointments, and emergency contacts.

Built with: **HTML · CSS · JavaScript · PHP · MySQL**

---

## Project Structure
```
elderly_care/
├── index.php               # Home page (protected)
├── dashboard.php           # Main dashboard (protected)
├── contacts.php            # Emergency contacts (protected)
├── contact.php             # Contact/support form
├── styles.css              # Global stylesheet
├── app.js                  # Frontend JavaScript
├── database.sql            # MySQL database dump
│
├── auth/
│   ├── login.php           # User login
│   ├── register.php        # User registration
│   └── logout.php          # Session destroy & logout
│
├── includes/
│   ├── db.php              # Database connection
│   └── functions.php       # Helper functions
│
└── assets/                 # Images used in CSS backgrounds
```

---

## Setup Instructions

### 1. Requirements
- WAMP or XAMPP installed and running
- PHP 7.4+
- MySQL 5.7+

### 2. Import the Database
1. Open `http://localhost/phpmyadmin`
2. Click **New** → name it `elderly_care` → **Create**
3. Click the `elderly_care` database → go to **Import** tab
4. Click **Choose File** → select `database.sql` → **Go**

### 3. Configure Database Connection
Open `includes/db.php` and update if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // your MySQL username
define('DB_PASS', '');       // your MySQL password (blank by default in WAMP)
define('DB_NAME', 'elderly_care');
```

### 4. Copy Project to WAMP
Copy the entire `elderly_care/` folder to:
```
C:\wamp64\www\elderly_care\
```

### 5. Run the Project
Open your browser and visit:
```
http://localhost/elderly_care/auth/register.php
```
- Register a new account
- Login → redirected to dashboard ✅

---

## Features

| Feature | Description |
|---|---|
| User Registration | Sign up with username, email, hashed password |
| User Login / Logout | Session-based authentication |
| Daily Checklist | Add and toggle daily health tasks |
| Medication Reminders | Add medications, mark as taken |
| Appointments | Schedule and view upcoming appointments |
| Emergency Contacts | Add/delete contacts with POA support |
| Contact Form | Submit support messages stored in DB |

---

## Security
- Passwords hashed with `password_hash()` (bcrypt)
- All inputs sanitized with `htmlspecialchars()` and `strip_tags()`
- Prepared statements used for all DB queries (prevents SQL injection)
- Session-based authentication guards all protected pages

---

## Evaluation Checklist (Phase 3)
- [x] MySQL database set up locally
- [x] Required tables created
- [x] User registration with hashed passwords
- [x] User login with session handling
- [x] Logout functionality
- [x] Contact form with database storage
- [x] Frontend connected to PHP backend
- [x] Proper folder structure
- [x] database.sql export included

---

© 2024 Elderly Care Management Systems — Rajarata University of Sri Lanka