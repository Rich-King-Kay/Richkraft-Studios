<?php
/**
 * Login Page
 * Deslin Montessori School Management System
 */

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/SecurityHelper.php';
require_once '../classes/User.php';

// If already logged in, redirect to dashboard
if (SecurityHelper::isLoggedIn()) {
    SecurityHelper::redirect(APP_URL . '/public/index.php');
    exit();
}

$error = '';
$success = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = SecurityHelper::sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } else {
        $userModel = new User();
        $loginResult = $userModel->authenticate($username, $password);

        if ($loginResult['success']) {
            // Set session variables
            $_SESSION['user_id'] = $loginResult['user_id'];
            $_SESSION['user_name'] = $loginResult['full_name'];
            $_SESSION['user_email'] = $loginResult['email'];
            $_SESSION['user_role'] = $loginResult['role'];
            $_SESSION['login_time'] = time();
            $_SESSION['csrf_token'] = SecurityHelper::generateCSRFToken();

            // Log session
            $db = Database::getInstance()->getConnection();
            $sessionToken = SecurityHelper::generateSessionToken();
            $ip = SecurityHelper::getClientIP();
            $userAgent = SecurityHelper::getUserAgent();

            $stmt = $db->prepare(
                "INSERT INTO session_log (user_id, ip_address, user_agent, session_token, is_active) 
                 VALUES (?, ?, ?, ?, 1)"
            );
            $stmt->bind_param('isss', $loginResult['user_id'], $ip, $userAgent, $sessionToken);
            $stmt->execute();

            // Audit log
            SecurityHelper::logAudit($db, $loginResult['user_id'], 'LOGIN');

            SecurityHelper::redirect(APP_URL . '/public/index.php');
            exit();
        } else {
            $error = $loginResult['message'];
        }
    }
}

// Check for expired session
if (isset($_GET['expired'])) {
    $error = 'Your session has expired. Please login again.';
}

$schoolName = SCHOOL_NAME;
$schoolMotto = 'Arise & Shine';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $schoolName; ?> Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #003d7a 0%, #0056b3 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #003d7a 0%, #0056b3 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .school-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 40px;
            font-weight: bold;
            color: #003d7a;
        }

        .school-logo svg {
            width: 70px;
            height: 70px;
        }

        .login-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .login-header p {
            font-size: 13px;
            opacity: 0.9;
            font-style: italic;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #003d7a;
            box-shadow: 0 0 0 3px rgba(0, 61, 122, 0.1);
        }

        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .error-message.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .login-button {
            width: 100%;
            padding: 12px 15px;
            background: linear-gradient(135deg, #003d7a 0%, #0056b3 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 61, 122, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            font-size: 13px;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            cursor: pointer;
            margin: 0;
        }

        .remember-forgot input[type="checkbox"] {
            margin-right: 5px;
        }

        .remember-forgot a {
            color: #003d7a;
            text-decoration: none;
            font-weight: 500;
        }

        .remember-forgot a:hover {
            text-decoration: underline;
        }

        .demo-credentials {
            background: #e7f3ff;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 12px;
            color: #333;
        }

        .demo-credentials strong {
            color: #003d7a;
        }

        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            font-size: 12px;
            color: #666;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .loading {
            display: none;
            text-align: center;
            color: #003d7a;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #003d7a;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 10px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
                box-shadow: none;
                border-radius: 0;
            }

            .login-header {
                padding: 30px 20px;
            }

            .login-body {
                padding: 30px 20px;
            }

            .school-logo {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }

            .login-header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="school-logo">☀️</div>
            <h1><?php echo $schoolName; ?></h1>
            <p><?php echo $schoolMotto; ?></p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="error-message show">
                    <strong>Error!</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember" id="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="login-button" id="submitBtn">
                    Login
                </button>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Logging in...</p>
                </div>
            </form>

            <div class="demo-credentials">
                <strong>Demo Login:</strong><br>
                Username: <code>admin</code><br>
                Password: <code>Admin@12345</code>
            </div>
        </div>

        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo $schoolName; ?>. All rights reserved.</p>
            <p>Developed by Richkraft Studios</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }

            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('loading').style.display = 'block';
        });

        // Remove error message on input
        document.getElementById('username').addEventListener('focus', function() {
            const errorMsg = document.querySelector('.error-message');
            if (errorMsg) errorMsg.classList.remove('show');
        });
    </script>
</body>
</html>