<?php
/**
 * Common Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Get the shared database connection
 */
function dbConnection() {
    return Database::getInstance()->getConnection();
}

/**
 * Count rows in a table, optionally filtered by a raw WHERE clause
 */
function dbCount($table, $where = '1') {
    $result = dbConnection()->query("SELECT COUNT(*) as total FROM $table WHERE $where");
    return $result->fetch_assoc()['total'];
}

/**
 * Run a query and return all rows as an associative array
 */
function dbFetchAll($query) {
    return dbConnection()->query($query)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Run a query and return the first row as an associative array
 */
function dbFetchOne($query) {
    return dbConnection()->query($query)->fetch_assoc();
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('d M Y', strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime) {
    return date('d M Y H:i', strtotime($datetime));
}

/**
 * Get total students count
 */
function getTotalStudents() {
    return dbCount('students', "student_status = 'Active'");
}

/**
 * Get total teachers count
 */
function getTotalTeachers() {
    return dbCount('teachers', "teacher_status = 'Active'");
}

/**
 * Get total classes count
 */
function getTotalClasses() {
    return dbCount('classes', 'is_active = 1');
}

/**
 * Get attendance percentage for today
 */
function getTodayAttendancePercentage() {
    $today = date('Y-m-d');
    $row = dbFetchOne(
        "SELECT 
            COUNT(*) as total_marked,
            SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
         FROM attendance 
         WHERE attendance_date = '$today'"
    );

    if ($row['total_marked'] == 0) return 0;
    return round(($row['present'] / $row['total_marked']) * 100, 2);
}

/**
 * Get all classes
 */
function getAllClasses() {
    return dbFetchAll("SELECT * FROM classes WHERE is_active = 1 ORDER BY class_level");
}

/**
 * Get all subjects
 */
function getAllSubjects() {
    return dbFetchAll("SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name");
}

/**
 * Get school settings
 */
function getSchoolSettings() {
    return dbFetchOne("SELECT * FROM school_settings LIMIT 1");
}

/**
 * Get academic year
 */
function getCurrentAcademicYear() {
    return ACADEMIC_YEAR;
}

/**
 * Check user permission
 */
function checkPermission($requiredRole) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    return $_SESSION['user_role'] === $requiredRole;
}

/**
 * Check any permission
 */
function checkAnyPermission($requiredRoles) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    return in_array($_SESSION['user_role'], $requiredRoles);
}

/**
 * Get user role display name
 */
function getRoleDisplayName($role) {
    $roles = [
        'admin' => 'Administrator',
        'teacher' => 'Teacher',
        'accountant' => 'Accountant',
        'staff' => 'Staff'
    ];
    return $roles[$role] ?? ucfirst($role);
}

/**
 * Get status badge color
 */
function getStatusBadgeClass($status) {
    $classes = [
        'Active' => 'badge-success',
        'Inactive' => 'badge-secondary',
        'Present' => 'badge-success',
        'Absent' => 'badge-danger',
        'Late' => 'badge-warning',
        'Excused' => 'badge-info',
        'On Leave' => 'badge-warning',
        'Suspended' => 'badge-danger',
        'Resigned' => 'badge-secondary'
    ];
    return $classes[$status] ?? 'badge-secondary';
}

/**
 * Truncate text
 */
function truncateText($text, $length = 50) {
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

/**
 * Get color from string (for avatars)
 */
function getAvatarColor($string) {
    $colors = ['#003d7a', '#0056b3', '#007bff', '#17a2b8', '#20c997'];
    $hash = ord(substr($string, 0, 1));
    return $colors[$hash % count($colors)];
}

?>