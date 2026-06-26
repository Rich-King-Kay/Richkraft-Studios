# Deslin Montessori School Management System

## Project Overview
A comprehensive web-based school management system designed for Deslin Montessori School. The system enables administrators, teachers, and staff to efficiently manage student records, attendance, grades, and school operations.

## Features

### 1. Authentication & Authorization
- Secure login system with password hashing (bcrypt)
- Role-based access control (Admin, Teacher, Accountant, Staff)
- Session management with timeout
- Audit logging for security events

### 2. Student Management
- Add, edit, delete, and search students
- Complete student profiles with:
  - Passport photos
  - Personal information
  - Parent/Guardian details
  - Medical notes
  - Enrollment status
- Student directory with filters

### 3. Teacher Management
- Add and manage teacher profiles
- Assign teachers to classes
- Track employment information
- Manage teaching assignments

### 4. Class Management
- Manage 11 class levels (Nursery to JSS 3)
- Assign form tutors
- Track class capacity
- Academic year configuration

### 5. Attendance Management
- Mark daily attendance (Present, Absent, Late, Excused)
- Generate attendance reports
- Track attendance statistics
- Identify patterns and trends

### 6. Examination & Results
- Add and manage subjects
- Enter student scores
- Automatic grade calculation
- Generate report cards
- Position ranking
- Printable results

### 7. Dashboard
- Real-time statistics
- Total students count
- Total teachers count
- Attendance overview
- Recent activities feed
- Quick action buttons

### 8. School Settings
- Configure school information
- Upload school logo
- Manage academic year settings
- Contact information management

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla JS)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Server**: Apache with mod_rewrite
- **Color Scheme**: Dark Blue (#003d7a), White (#ffffff), Yellow (#ffc107)

## Project Structure

```
deslin-montessori-school/
├── config/
│   ├── config.php                 # Main configuration file
│   ├── database.php               # Database connection class
│   └── SecurityHelper.php         # Security utilities
├── database/
│   └── schema.sql                 # MySQL database schema
├── public/
│   ├── index.php                  # Dashboard
│   ├── login.php                  # Login page
│   ├── logout.php                 # Logout handler
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css          # Main stylesheet
│   │   │   └── responsive.css     # Mobile responsive styles
│   │   ├── js/
│   │   │   ├── main.js            # Main JavaScript
│   │   │   └── utils.js           # Utility functions
│   │   ├── images/
│   │   └── fonts/
│   ├── students/
│   │   ├── index.php              # Student management
│   │   ├── add.php                # Add student form
│   │   ├── edit.php               # Edit student form
│   │   ├── view.php               # Student profile
│   │   └── delete.php             # Delete student handler
│   ├── teachers/
│   │   ├── index.php              # Teacher management
│   │   ├── add.php                # Add teacher form
│   │   ├── edit.php               # Edit teacher form
│   │   └── delete.php             # Delete teacher handler
│   ├── attendance/
│   │   ├── index.php              # Attendance marking
│   │   ├── report.php             # Attendance report
│   │   └── api.php                # AJAX endpoints
│   ├── grades/
│   │   ├── index.php              # Grade management
│   │   ├── entry.php              # Enter grades
│   │   ├── report.php             # Grade reports
│   │   └── report-card.php        # Generate report cards
│   ├── settings/
│   │   ├── school.php             # School settings
│   │   └── profile.php            # User profile settings
│   └── uploads/
│       ├── photos/                # Student/Teacher photos
│       ├── logo/                  # School logo
│       └── reports/               # Generated reports
├── classes/
│   ├── User.php                   # User model
│   ├── Student.php                # Student model
│   ├── Teacher.php                # Teacher model
│   ├── Attendance.php             # Attendance model
│   ├── Grade.php                  # Grade model
│   └── SchoolSettings.php         # School settings model
├── includes/
│   ├── header.php                 # Header template
│   ├── footer.php                 # Footer template
│   ├── sidebar.php                # Sidebar navigation
│   ├── functions.php              # Common functions
│   └── auth.php                   # Authentication check
├── logs/
│   └── error.log                  # Error logging
├── .htaccess                      # Apache rewrite rules
└── INSTALLATION.md                # Installation guide
```

## Installation Guide

### Prerequisites
- XAMPP (or similar Apache + MySQL + PHP server)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled

### Step-by-Step Installation

1. **Clone or Download the Project**
   ```bash
   git clone https://github.com/Rich-King-Kay/Richkraft-Studios.git
   cd Richkraft-Studios
   git checkout deslin-montessori-school
   ```

2. **Place in XAMPP Directory**
   - Copy the `deslin-montessori-school` folder to `C:\xampp\htdocs\Richkraft-Studios\` (Windows)
   - Or `/Applications/XAMPP/htdocs/Richkraft-Studios/` (macOS)
   - Or `/opt/lampp/htdocs/Richkraft-Studios/` (Linux)

3. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database: `deslin_montessori_school`
   - Import `database/schema.sql`

4. **Configure Database Connection**
   - Edit `config/config.php`
   - Update database credentials if needed

5. **Set File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 logs/
   ```

6. **Create Upload Directories**
   ```bash
   mkdir -p public/uploads/photos
   mkdir -p public/uploads/logo
   mkdir -p public/uploads/reports
   mkdir -p logs
   ```

7. **Access the Application**
   - Open http://localhost/Richkraft-Studios/deslin-montessori-school/public/login.php

### Default Login Credentials
- **Username**: admin
- **Password**: Admin@12345

*Note: Change the default password on first login*

## Database Schema

The system includes 14 main tables:
1. **users** - User accounts and authentication
2. **school_settings** - School configuration
3. **classes** - Class levels and information
4. **subjects** - Subject list
5. **class_subjects** - Many-to-many relationship for class and subjects
6. **students** - Student information and enrollment
7. **teachers** - Teacher profiles and assignments
8. **attendance** - Daily attendance records
9. **examination_terms** - Academic terms
10. **grades** - Student grades and scores
11. **report_cards** - Generated report cards
12. **audit_log** - Activity logging
13. **session_log** - User session tracking
14. **notifications** - System notifications

## Security Features

- **Password Hashing**: Bcrypt with cost factor 12
- **Session Management**: Automatic timeout after 1 hour
- **CSRF Protection**: Token-based CSRF prevention
- **SQL Injection Prevention**: Prepared statements for all queries
- **Input Validation**: Sanitization on all user inputs
- **File Upload Security**: Type and size validation
- **Audit Logging**: Track all administrative actions
- **Role-Based Access**: Granular permission control

## Color Scheme

Based on Deslin Montessori School Logo:
- **Primary**: Dark Blue (#003d7a)
- **Secondary**: White (#ffffff)
- **Accent**: Yellow (#ffc107)
- **Success**: Green (#28a745)
- **Danger**: Red (#dc3545)
- **Warning**: Yellow (#ffc107)
- **Info**: Cyan (#17a2b8)

## User Roles & Permissions

### Administrator
- Full system access
- Manage all users
- Configure school settings
- View all reports
- Manage backup and restore

### Teacher
- View assigned students
- Mark attendance
- Enter grades
- View class reports
- Update profile

### Accountant
- View financial reports
- Manage fees (if implemented)
- Generate payment reports

### Staff
- Limited access
- View school information
- Update own profile

## API Endpoints

For AJAX requests:
- `/public/attendance/api.php?action=mark` - Mark attendance
- `/public/students/api.php?action=search` - Search students
- `/public/grades/api.php?action=calculate` - Calculate grades

## Troubleshooting

### Database Connection Error
- Verify database credentials in `config/config.php`
- Ensure MySQL server is running
- Check if database exists

### Permission Denied Errors
- Set proper file permissions: `chmod 755 uploads/ logs/`
- Ensure web server has write access

### Blank Pages
- Check `logs/error.log` for PHP errors
- Enable error display in `config/config.php` (development mode)
- Verify mod_rewrite is enabled in Apache

### Session Issues
- Clear browser cookies
- Verify session save path in php.ini
- Check session timeout settings

## Maintenance

### Regular Tasks
- Monitor disk space for uploads
- Backup database regularly
- Review audit logs monthly
- Update PHP and MySQL
- Clear old session files

### Database Maintenance
```sql
-- Optimize tables
OPTIMIZE TABLE users, students, teachers, attendance, grades;

-- Check for errors
CHECK TABLE users, students, teachers, attendance, grades;

-- Backup
mysqldump -u root -p deslin_montessori_school > backup.sql
```

## Support & Contact

For issues or questions, contact:
- Email: info@deslinmontessori.edu
- Technical Support: support@richkraftstudios.com

## License

This project is licensed for use by Deslin Montessori School only.

## Version History

### v1.0.0 (2026-06-25)
- Initial release
- Core features implemented
- Security features added
- Database schema created

---

**Last Updated**: June 25, 2026
**Developed by**: Richkraft Studios
