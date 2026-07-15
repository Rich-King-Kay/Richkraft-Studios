<?php
/**
 * Authentication Check
 * Verify user is logged in and has proper session
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/SecurityHelper.php';

// Check if user is logged in
if (!SecurityHelper::isLoggedIn()) {
    SecurityHelper::redirect(APP_URL . '/public/login.php');
    exit();
}

// Check session timeout
$lastActivity = $_SESSION['login_time'] ?? 0;
if (($lastActivity === 0) || ((time() - $lastActivity) > SESSION_TIMEOUT)) {
    $_SESSION = [];
    session_destroy();
    SecurityHelper::redirect(APP_URL . '/public/login.php?expired=1');
    exit();
}

// Update last activity time
$_SESSION['login_time'] = time();

// Get user information
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];

?>