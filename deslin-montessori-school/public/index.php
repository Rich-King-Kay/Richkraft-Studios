<?php
/**
 * Dashboard - Main Page
 */

require_once '../config/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../classes/Student.php';
require_once '../classes/Teacher.php';

$pageTitle = 'Dashboard';

// Get statistics
$totalStudents = getTotalStudents();
$totalTeachers = getTotalTeachers();
$totalClasses = getTotalClasses();
$todayAttendance = getTodayAttendancePercentage();

include_once '../includes/header.php';
include_once '../includes/sidebar.php';
?>

            <!-- Dashboard Content -->
            <div class="content-wrapper">
                <div class="page-header">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>!</h1>
                    <p>Dashboard Overview - <?php echo getCurrentAcademicYear(); ?></p>
                </div>

                <!-- Statistics Section -->
                <section class="stats-section">
                    <div class="stats-grid">
                        <!-- Total Students Card -->
                        <div class="stat-card">
                            <div class="stat-icon students">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo $totalStudents; ?></div>
                                <div class="stat-label">Total Students</div>
                                <p class="stat-change"><i class="fas fa-arrow-up"></i> Up from last month</p>
                            </div>
                        </div>

                        <!-- Total Teachers Card -->
                        <div class="stat-card">
                            <div class="stat-icon teachers">
                                <i class="fas fa-chalkboard-user"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo $totalTeachers; ?></div>
                                <div class="stat-label">Total Teachers</div>
                                <p class="stat-change"><i class="fas fa-arrow-up"></i> Active staff</p>
                            </div>
                        </div>

                        <!-- Total Classes Card -->
                        <div class="stat-card">
                            <div class="stat-icon classes">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo $totalClasses; ?></div>
                                <div class="stat-label">Total Classes</div>
                                <p class="stat-change"><i class="fas fa-check"></i> All active</p>
                            </div>
                        </div>

                        <!-- Attendance Card -->
                        <div class="stat-card">
                            <div class="stat-icon attendance">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo $todayAttendance; ?>%</div>
                                <div class="stat-label">Today's Attendance</div>
                                <p class="stat-change"><i class="fas fa-chart-line"></i> <?php echo $todayAttendance >= 85 ? 'Excellent' : 'Good'; ?></p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Main Content Grid -->
                <div class="dashboard-grid">
                    <!-- Quick Actions -->
                    <section class="quick-actions">
                        <h2><i class="fas fa-lightning-bolt"></i> Quick Actions</h2>
                        <div class="actions-grid">
                            <?php if (checkPermission(ROLE_ADMIN)): ?>
                                <a href="<?php echo APP_URL; ?>/public/students/add.php" class="action-btn">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Add Student</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/public/teachers/add.php" class="action-btn">
                                    <i class="fas fa-user-tie"></i>
                                    <span>Add Teacher</span>
                                </a>
                            <?php endif; ?>
                            <?php if (checkAnyPermission([ROLE_ADMIN, ROLE_TEACHER])): ?>
                                <a href="<?php echo APP_URL; ?>/public/attendance/index.php" class="action-btn">
                                    <i class="fas fa-clipboard-check"></i>
                                    <span>Mark Attendance</span>
                                </a>
                                <a href="<?php echo APP_URL; ?>/public/grades/entry.php" class="action-btn">
                                    <i class="fas fa-pencil-alt"></i>
                                    <span>Enter Grades</span>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo APP_URL; ?>/public/students/index.php" class="action-btn">
                                <i class="fas fa-search"></i>
                                <span>Search Students</span>
                            </a>
                        </div>
                    </section>

                    <!-- Recent Activities -->
                    <section class="recent-activities">
                        <h2><i class="fas fa-history"></i> Recent Activities</h2>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-title">Attendance marked for Primary 3</p>
                                    <p class="activity-time">Today at 8:30 AM</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon info">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-title">New student enrolled: John Doe</p>
                                    <p class="activity-time">Yesterday at 2:15 PM</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-title">Grades entered for Mathematics - Primary 4</p>
                                    <p class="activity-time">2 days ago</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon info">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-title">New teacher added: Mrs. Sarah Smith</p>
                                    <p class="activity-time">3 days ago</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Chart Section -->
                <section class="charts-section">
                    <div class="chart-card">
                        <h3>Student Enrollment Trend</h3>
                        <div class="chart-placeholder">
                            <p>Chart visualization would be displayed here</p>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3>Class Distribution</h3>
                        <div class="chart-placeholder">
                            <p>Chart visualization would be displayed here</p>
                        </div>
                    </div>
                </section>
            </div>

<?php include_once '../includes/footer.php'; ?>