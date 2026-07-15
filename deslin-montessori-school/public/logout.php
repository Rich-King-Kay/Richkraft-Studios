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

    // Update session log
    $stmt = $db->prepare(
        "UPDATE session_log SET logout_time = NOW(), is_active = 0 
         WHERE user_id = ? ORDER BY login_time DESC LIMIT 1"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();

    // Audit log
    SecurityHelper::logAudit($db, $userId, 'LOGOUT');
}

// Destroy session
session_destroy();

// Redirect to login
SecurityHelper::redirect(APP_URL . '/public/login.php?logout=1');
exit();

?>