# Ashford University Portal

Ashford University Portal is a PHP and MySQL university management system based on the Ashford University System Manual.

## Features

### Student Portal
- Student registration and login
- Dashboard
- Course registration
- Prerequisite checking
- Course schedule
- Add and drop courses
- Unofficial transcript
- Degree audit
- Holds
- Advisor information
- Majors and minors
- Course catalog
- Buildings
- Student information

### Faculty Portal
- Faculty dashboard
- Faculty schedule
- Master schedule
- Course rosters
- Grade entry
- Attendance entry
- Attendance history
- Advisee information
- Course catalog
- Faculty profile

### Admin Portal
- Admin dashboard
- User search
- User information updates
- Full-Access and Read-Only permissions
- User update logging
- Department and course information
- Course section information

### Statistics Portal
- Read-only statistics access
- Student totals
- Undergraduate and graduate totals
- Enrollment totals
- Anonymous course enrollment reports
- Anonymous grade summaries by department and course
- Reports do not display student names or student IDs

## Technologies Used

- PHP
- MySQL / MariaDB
- HTML
- CSS
- JavaScript
- XAMPP
- phpMyAdmin

## Database

The final working database export is located at:

`database/ashford_university_test.sql`

This file contains the database structure and sample university data used by the project.

## Run with XAMPP on Windows

1. Install and open XAMPP.
2. Put the `Ashford-University` folder inside:

   `C:\xampp\htdocs\`

3. Start Apache and MySQL in XAMPP.
4. Open phpMyAdmin.
5. Create a database named:

   `ashford_university`

6. Import:

   `database/ashford_university_test.sql`

7. Copy `.env.example` and rename the copy to `.env`.
8. Check the database settings inside `.env`.

Example:

DB_HOST=127.0.0.1  
DB_PORT=3306  
DB_NAME=ashford_university  
DB_USER=root  
DB_PASS=  
APP_ENV=local

9. Open the website:

   `http://localhost/Ashford-University/`

## Security

The `.env` file is excluded from GitHub using `.gitignore`.

Database configuration should be stored in `.env` and should not be committed to the repository.

The login system supports hashed passwords created with PHP `password_hash()`.

## Project Structure

- `admin/` - Admin portal
- `assets/` - CSS and JavaScript
- `config/` - Database configuration
- `database/` - Database SQL files
- `faculty/` - Faculty portal
- `includes/` - Shared PHP files
- `statistics/` - Statistics portal
- `student/` - Student portal
- `index.php` - Home page
- `login.php` - Login page
- `logout.php` - Logout
- `register.php` - Student registration

## Ashford University

This project was created as a university database and web application project demonstrating role-based access and MySQL database integration.