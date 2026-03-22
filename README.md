# Elderly Care Management System

A web application for managing elderly care — including medications, daily checklists, appointments, and emergency contacts.

Built with: **HTML · CSS · JavaScript · PHP · MariaDB**

---

## Project Structure
```
elderly_care/
├── index.php
├── dashboard.php
├── contacts.php
├── contact.php
├── styles.css
├── app.js
├── database.sql
│
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── includes/
│   ├── db.php
│   └── functions.php
│
└── assets/
```

---

## Setup Instructions

### 1. What You Need
- WAMP installed and running
- PHP 8.0+
- MariaDB 11.4+

### 2. Import the Database
1. Open `http://localhost/phpmyadmin`
2. Log in with username `root` and no password, choose **MariaDB** as server
3. Click **New** → name it `elderly_care` → **Create**
4. Click the `elderly_care` database → go to **Import** tab
5. Click **Choose File** → select `database.sql` → **Go**

### 3. Configure Database Connection
Open `includes/db.php` and update with your details:
```php
define('DB_HOST', '127.0.0.1:3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'elderly_care');
```

### 4. Copy Project to WAMP
Copy the entire project folder to:
```
C:\wamp64\www\elderly_care\
```

### 5. Run the Project
Open your browser and go to:
```
http://localhost/elderly_care/auth/register.php
```
Register an account, log in, and you will be taken to the dashboard.

---

## Features

| Feature | Description |
|---|---|
| User Registration | Sign up with username, email and a securely hashed password |
| User Login / Logout | Session-based authentication |
| Daily Checklist | Add and check off daily health tasks |
| Medication Reminders | Add medications and mark them as taken |
| Appointments | Schedule and view upcoming doctor visits |
| Emergency Contacts | Add and delete contacts with POA support |
| Contact Form | Send support messages which are saved to the database |

---

## Security

- Passwords are hashed using bcrypt via `password_hash()`
- All user inputs are sanitized to prevent XSS attacks
- Prepared statements are used throughout to prevent SQL injection
- All protected pages require an active login session

---

## Evaluation Checklist

- [x] MariaDB database set up locally using WAMP
- [x] All required tables created
- [x] User registration with hashed passwords
- [x] User login with session handling
- [x] Logout functionality
- [x] Contact form with database storage
- [x] Frontend connected to PHP backend
- [x] Proper folder structure followed
- [x] database.sql export included

---

© 2024 Elderly Care Management Systems — Rajarata University of Sri Lanka
