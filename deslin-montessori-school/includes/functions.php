<?php
/**
 * Common Functions
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

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
 * Run a read-only query and return the mysqli result.
 * Logs and returns false on failure instead of letting a failed
 * query (which returns false) trigger a fatal "member function on bool" error.
 */
function runReadQuery($sql, $context) {
    $db = Database::getInstance()->getConnection();
    $result = $db->query($sql);
    if ($result === false) {
        Database::logError($context, $db->error . ' -- Query: ' . $sql);
        return false;
    }
    return $result;
}

/**
 * Get total students count
 */
function getTotalStudents() {
    $result = runReadQuery(
        "SELECT COUNT(*) as total FROM students WHERE student_status = 'Active'",
        'getTotalStudents'
    );
    if ($result === false) {
        return 0;
    }
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

/**
 * Get total teachers count
 */
function getTotalTeachers() {
    $result = runReadQuery(
        "SELECT COUNT(*) as total FROM teachers WHERE teacher_status = 'Active'",
        'getTotalTeachers'
    );
    if ($result === false) {
        return 0;
    }
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

/**
 * Get total classes count
 */
function getTotalClasses() {
    $result = runReadQuery(
        "SELECT COUNT(*) as total FROM classes WHERE is_active = 1",
        'getTotalClasses'
    );
    if ($result === false) {
        return 0;
    }
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

/**
 * Get attendance percentage for today
 */
function getTodayAttendancePercentage() {
    $today = date('Y-m-d');
    $result = runReadQuery(
        "SELECT 
            COUNT(*) as total_marked,
            SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present
         FROM attendance 
         WHERE attendance_date = '$today'",
        'getTodayAttendancePercentage'
    );
    if ($result === false) {
        return 0;
    }
    $row = $result->fetch_assoc();

    if (empty($row['total_marked'])) return 0;
    return round(($row['present'] / $row['total_marked']) * 100, 2);
}

/**
 * Get all classes
 */
function getAllClasses() {
    $result = runReadQuery(
        "SELECT * FROM classes WHERE is_active = 1 ORDER BY class_level",
        'getAllClasses'
    );
    if ($result === false) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all subjects
 */
function getAllSubjects() {
    $result = runReadQuery(
        "SELECT * FROM subjects WHERE is_active = 1 ORDER BY subject_name",
        'getAllSubjects'
    );
    if ($result === false) {
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get school settings
 */
function getSchoolSettings() {
    $result = runReadQuery("SELECT * FROM school_settings LIMIT 1", 'getSchoolSettings');
    if ($result === false) {
        return null;
    }
    return $result->fetch_assoc();
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