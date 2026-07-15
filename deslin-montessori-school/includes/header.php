<?php
/**
 * Header Template
 */

$schoolSettings = getSchoolSettings();
$userName = $_SESSION['user_name'] ?? 'User';
$userRole = getRoleDisplayName($_SESSION['user_role'] ?? 'staff');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SCHOOL_NAME : SCHOOL_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/public/assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-wrapper">
        <!-- Top Navigation Bar -->
        <nav class="navbar">
            <div class="navbar-container">
                <!-- Logo -->
                <div class="navbar-logo">
                    <a href="<?php echo APP_URL; ?>/public/index.php">
                        <span class="logo-icon">🌞</span>
                        <span class="logo-text"><?php echo SCHOOL_ACRONYM; ?></span>
                    </a>
                </div>

                <!-- Center - School Name -->
                <div class="navbar-school-info">
                    <h1><?php echo SCHOOL_NAME; ?></h1>
                    <p><?php echo SCHOOL_MOTTO; ?></p>
                </div>

                <!-- Right Side Menu -->
                <div class="navbar-menu">
                    <!-- Notifications -->
                    <div class="navbar-item">
                        <button class="notification-btn" id="notificationBtn">
                            <i class="fas fa-bell"></i>
                            <span class="badge">3</span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="dropdown-header">Notifications</div>
                            <div class="notification-item">
                                <i class="fas fa-info-circle"></i>
                                <div>
                                    <p>System maintenance scheduled</p>
                                    <small>2 hours ago</small>
                                </div>
                            </div>
                            <div class="notification-item">
                                <i class="fas fa-user-plus"></i>
                                <div>
                                    <p>New student enrolled</p>
                                    <small>5 hours ago</small>
                                </div>
                            </div>
                            <div class="notification-item">
                                <i class="fas fa-check-circle"></i>
                                <div>
                                    <p>Attendance marked for today</p>
                                    <small>1 day ago</small>
                                </div>
                            </div>
                            <div class="dropdown-footer">
                                <a href="#">View All Notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="navbar-item">
                        <button class="user-menu-btn" id="userMenuBtn">
                            <div class="user-avatar"><?php echo htmlspecialchars(substr($userName, 0, 1), ENT_QUOTES, 'UTF-8'); ?></div>
                            <span class="user-name"><?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="dropdown-header">User Menu</div>
                            <a href="<?php echo APP_URL; ?>/public/settings/profile.php" class="dropdown-item">
                                <i class="fas fa-user-circle"></i> My Profile
                            </a>
                            <a href="<?php echo APP_URL; ?>/public/settings/profile.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <?php if (checkPermission(ROLE_ADMIN)): ?>
                                <a href="<?php echo APP_URL; ?>/public/settings/school.php" class="dropdown-item">
                                    <i class="fas fa-school"></i> School Settings
                                </a>
                            <?php endif; ?>
                            <hr>
                            <a href="<?php echo APP_URL; ?>/public/logout.php" class="dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>

                    <!-- Mobile Menu Toggle -->
                    <div class="navbar-item mobile-only">
                        <button class="menu-toggle" id="menuToggle">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Container -->
        <div class="main-container">
