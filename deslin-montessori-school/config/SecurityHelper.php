<?php
/**
 * Security Helper Class
 * Handles password hashing, CSRF tokens, input validation, etc.
 */

require_once __DIR__ . '/config.php';

class SecurityHelper {

    /**
     * Hash a password using bcrypt
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify a password against a hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Generate a CSRF token
     */
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate a secure random token
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }

    /**
     * Sanitize input
     */
    public static function sanitizeInput($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }

    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number
     */
    public static function validatePhoneNumber($phone) {
        $phone = preg_replace('/[^0-9+\-\s()]/', '', $phone);
        return strlen($phone) >= 10;
    }

    /**
     * Validate password strength
     */
    public static function validatePasswordStrength($password) {
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['valid' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long'];
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one uppercase letter'];
        }
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one lowercase letter'];
        }
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one number'];
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            return ['valid' => false, 'message' => 'Password must contain at least one special character'];
        }
        return ['valid' => true, 'message' => 'Password is strong'];
    }

    /**
     * Prevent SQL Injection by using prepared statements
     * This is handled by the Database class
     */

    /**
     * Get client IP address
     */
    public static function getClientIP() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    /**
     * Get user agent
     */
    public static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    /**
     * Redirect to a page
     */
    public static function redirect($url) {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Check if user has required role
     */
    public static function hasRole($role) {
        return self::isLoggedIn() && $_SESSION['user_role'] === $role;
    }

    /**
     * Check if user has any of the required roles
     */
    public static function hasAnyRole($roles) {
        if (!self::isLoggedIn()) return false;
        return in_array($_SESSION['user_role'], $roles);
    }

    /**
     * Log out user
     */
    public static function logout() {
        session_destroy();
        self::redirect(APP_URL . '/public/login.php');
    }

    /**
     * Generate session token
     */
    public static function generateSessionToken() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes, $maxSize) {
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'File upload error'];
        }

        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'File size exceeds maximum allowed'];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return ['valid' => false, 'message' => 'File type not allowed'];
        }

        return ['valid' => true, 'message' => 'File is valid'];
    }

    /**
     * Save uploaded file
     */
    public static function saveUploadedFile($file, $destination) {
        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $destination . $fileName;

        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            error_log('[DMSMS] saveUploadedFile: failed to create directory ' . $destination);
            return ['success' => false, 'message' => 'Failed to create upload directory'];
        }

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => true, 'filePath' => $filePath, 'fileName' => $fileName];
        } else {
            return ['success' => false, 'message' => 'Failed to save file'];
        }
    }
}

?>