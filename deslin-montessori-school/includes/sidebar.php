<?php
/**
 * Sidebar Navigation Template
 */
?>
            <!-- Sidebar Navigation -->
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-content">
                    <nav class="nav-menu">
                        <!-- Dashboard -->
                        <div class="nav-section">
                            <a href="<?php echo APP_URL; ?>/public/index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                                <i class="fas fa-home"></i>
                                <span>Dashboard</span>
                            </a>
                        </div>

                        <!-- Admin Only Sections -->
                        <?php if (checkPermission(ROLE_ADMIN)): ?>
                            <!-- Student Management -->
                            <div class="nav-section">
                                <div class="nav-section-title">Student Management</div>
                                <a href="<?php echo APP_URL; ?>/public/students/index.php" class="nav-link">
                                    <i class="fas fa-users"></i>
                                    <span>Students</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/public/students/add.php" class="nav-link">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Add Student</span>
                                </a>
                            </div>

                            <!-- Teacher Management -->
                            <div class="nav-section">
                                <div class="nav-section-title">Teacher Management</div>
                                <a href="<?php echo APP_URL; ?>/public/teachers/index.php" class="nav-link">
                                    <i class="fas fa-chalkboard-user"></i>
                                    <span>Teachers</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/public/teachers/add.php" class="nav-link">
                                    <i class="fas fa-user-tie"></i>
                                    <span>Add Teacher</span>
                                </a>
                            </div>

                            <!-- Class Management -->
                            <div class="nav-section">
                                <div class="nav-section-title">Classes</div>
                                <a href="<?php echo APP_URL; ?>/public/classes/index.php" class="nav-link">
                                    <i class="fas fa-door-open"></i>
                                    <span>All Classes</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Attendance Management (Admin & Teacher) -->
                        <?php if (checkAnyPermission([ROLE_ADMIN, ROLE_TEACHER])): ?>
                            <div class="nav-section">
                                <div class="nav-section-title">Attendance</div>
                                <a href="<?php echo APP_URL; ?>/public/attendance/index.php" class="nav-link">
                                    <i class="fas fa-clipboard-check"></i>
                                    <span>Mark Attendance</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/public/attendance/report.php" class="nav-link">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Attendance Report</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Grades & Results (Admin & Teacher) -->
                        <?php if (checkAnyPermission([ROLE_ADMIN, ROLE_TEACHER])): ?>
                            <div class="nav-section">
                                <div class="nav-section-title">Grades & Results</div>
                                <a href="<?php echo APP_URL; ?>/public/grades/index.php" class="nav-link">
                                    <i class="fas fa-list-check"></i>
                                    <span>Grades</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/public/grades/entry.php" class="nav-link">
                                    <i class="fas fa-pencil-alt"></i>
                                    <span>Enter Grades</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/public/grades/report-card.php" class="nav-link">
                                    <i class="fas fa-file-pdf"></i>
                                    <span>Report Cards</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Settings (Admin Only) -->
                        <?php if (checkPermission(ROLE_ADMIN)): ?>
                            <div class="nav-section">
                                <div class="nav-section-title">System</div>
                                <a href="<?php echo APP_URL; ?>/public/settings/school.php" class="nav-link">
                                    <i class="fas fa-cog"></i>
                                    <span>School Settings</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/public/settings/users.php" class="nav-link">
                                    <i class="fas fa-users-cog"></i>
                                    <span>Users</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </nav>
                </div>

                <!-- Sidebar Footer -->
                <div class="sidebar-footer">
                    <div class="role-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span><?php echo getRoleDisplayName($_SESSION['user_role']); ?></span>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="main-content">
