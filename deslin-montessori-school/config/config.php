<?php
/**
 * Deslin Montessori School Management System
 * Configuration File
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change to your database password
define('DB_NAME', 'deslin_montessori_school');
define('DB_PORT', 3306);

// Application Configuration
define('APP_NAME', 'Deslin Montessori School Management System');
define('APP_ACRONYM', 'DMSMS');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/Richkraft-Studios/deslin-montessori-school');
define('APP_ENV', 'development'); // development or production

// School Information
define('SCHOOL_NAME', 'Deslin Montessori School');
define('SCHOOL_MOTTO', 'Arise & Shine');
define('SCHOOL_EMAIL', 'info@deslinmontessori.edu');
define('SCHOOL_PHONE', '+234-XXXX-XXXX');
define('ACADEMIC_YEAR', '2025/2026');
define('CURRENCY_SYMBOL', '₦');

// Security Configuration
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes in seconds

// File Upload Configuration
define('UPLOAD_DIR', 'uploads/');
define('UPLOAD_PHOTO_DIR', UPLOAD_DIR . 'photos/');
define('UPLOAD_LOGO_DIR', UPLOAD_DIR . 'logo/');
define('UPLOAD_REPORTS_DIR', UPLOAD_DIR . 'reports/');
define('MAX_UPLOAD_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_LOGO_TYPES', ['image/jpeg', 'image/png', 'image/svg+xml']);

// Pagination
define('RECORDS_PER_PAGE', 20);

// Email Configuration (Optional)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_FROM_ADDRESS', 'noreply@deslinmontessori.edu');
define('MAIL_FROM_NAME', 'Deslin Montessori School');

// Color Scheme (from school logo)
define('PRIMARY_COLOR', '#003d7a'); // Dark Blue
define('SECONDARY_COLOR', '#ffffff'); // White
define('ACCENT_COLOR', '#ffc107'); // Yellow
define('SUCCESS_COLOR', '#28a745'); // Green
define('DANGER_COLOR', '#dc3545'); // Red
define('WARNING_COLOR', '#ffc107'); // Yellow
define('INFO_COLOR', '#17a2b8'); // Cyan

// Date and Time
define('DEFAULT_TIMEZONE', 'Africa/Lagos');
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_ACCOUNTANT', 'accountant');
define('ROLE_STAFF', 'staff');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Set default timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Enable sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>