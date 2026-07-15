<?php
/**
 * Logout Handler
 */

require_once '../config/config.php';
require_once '../config/SecurityHelper.php';
require_once '../config/database.php';

// Log the logout action
if (isset($_SESSION['user_id'])) {
    $db = Database::getInstance()->getConnection();
    $userId = $_SESSION['user_id'];
    $ip = SecurityHelper::getClientIP();
    $userAgent = SecurityHelper::getUserAgent();

    // Update session log. A logging failure must not prevent the user from
    // logging out, but it should be recorded rather than silently swallowed.
    $stmt = $db->prepare(
        "UPDATE session_log SET logout_time = NOW(), is_active = 0 
         WHERE user_id = ? ORDER BY login_time DESC LIMIT 1"
    );
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            Database::logError('logout session_log', $stmt->error);
        }
    } else {
        Database::logError('logout session_log prepare', $db->error);
    }

    // Audit log
    $auditStmt = $db->prepare(
        "INSERT INTO audit_log (user_id, action, ip_address, user_agent) 
         VALUES (?, 'LOGOUT', ?, ?)"
    );
    if ($auditStmt) {
        $auditStmt->bind_param('iss', $userId, $ip, $userAgent);
        if (!$auditStmt->execute()) {
            Database::logError('logout audit_log', $auditStmt->error);
        }
    } else {
        Database::logError('logout audit_log prepare', $db->error);
    }
}

// Destroy session
session_destroy();

// Redirect to login
SecurityHelper::redirect(APP_URL . '/public/login.php?logout=1');
exit();

?>