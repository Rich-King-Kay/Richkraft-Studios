-- Deslin Montessori School Management System
-- Complete Database Schema
-- Created: 2026-06-25

-- ============================================
-- 1. USERS TABLE (Authentication)
-- ============================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'teacher', 'accountant', 'staff') NOT NULL DEFAULT 'staff',
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. SCHOOL SETTINGS TABLE
-- ============================================
CREATE TABLE school_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    school_name VARCHAR(150) NOT NULL DEFAULT 'Deslin Montessori School',
    school_motto VARCHAR(150) DEFAULT 'Arise & Shine',
    school_email VARCHAR(100),
    school_phone VARCHAR(20),
    school_address TEXT,
    school_city VARCHAR(50),
    school_state VARCHAR(50),
    school_postal_code VARCHAR(10),
    school_logo_path VARCHAR(255),
    academic_year_start DATE,
    academic_year_end DATE,
    currency_symbol VARCHAR(5) DEFAULT '$',
    timezone VARCHAR(50) DEFAULT 'UTC',
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(user_id),
    INDEX idx_academic_year (academic_year_start, academic_year_end)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. CLASSES TABLE
-- ============================================
CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL UNIQUE,
    class_level INT,
    form_tutor_id INT,
    capacity INT DEFAULT 40,
    academic_year VARCHAR(9) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (form_tutor_id) REFERENCES users(user_id),
    INDEX idx_class_name (class_name),
    INDEX idx_academic_year (academic_year),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. SUBJECTS TABLE
-- ============================================
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subject_code (subject_code),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. CLASS_SUBJECTS (Many-to-Many)
-- ============================================
CREATE TABLE class_subjects (
    class_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT,
    academic_year VARCHAR(9),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id),
    UNIQUE KEY unique_class_subject (class_id, subject_id, academic_year),
    INDEX idx_teacher_id (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. STUDENTS TABLE (Core Student Data)
-- ============================================
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_reg_no VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('M', 'F', 'Other') NOT NULL,
    date_of_birth DATE NOT NULL,
    passport_photo_path VARCHAR(255),
    residential_address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(10),
    parent_guardian_name VARCHAR(100) NOT NULL,
    parent_guardian_email VARCHAR(100),
    parent_guardian_phone VARCHAR(20) NOT NULL,
    parent_guardian_address TEXT,
    admission_date DATE NOT NULL,
    current_class_id INT,
    academic_year VARCHAR(9) NOT NULL,
    medical_notes TEXT,
    allergies TEXT,
    blood_group VARCHAR(5),
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    student_status ENUM('Active', 'Inactive', 'Transferred', 'Graduated') DEFAULT 'Active',
    enrollment_status ENUM('current', 'graduated', 'transferred', 'withdrawn') DEFAULT 'current',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_class_id) REFERENCES classes(class_id),
    INDEX idx_student_reg_no (student_reg_no),
    INDEX idx_current_class_id (current_class_id),
    INDEX idx_academic_year (academic_year),
    INDEX idx_student_status (student_status),
    INDEX idx_enrollment_status (enrollment_status),
    FULLTEXT INDEX ft_search (first_name, last_name, parent_guardian_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. TEACHERS TABLE (Staff Teaching)
-- ============================================
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    teacher_emp_no VARCHAR(20) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    residential_address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    postal_code VARCHAR(10),
    date_employed DATE NOT NULL,
    assigned_class_id INT,
    qualification TEXT,
    specialization VARCHAR(100),
    employment_status ENUM('Active', 'On Leave', 'Suspended', 'Resigned') DEFAULT 'Active',
    date_of_birth DATE,
    bank_account_number VARCHAR(30),
    bank_name VARCHAR(100),
    teacher_status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_class_id) REFERENCES classes(class_id),
    INDEX idx_teacher_emp_no (teacher_emp_no),
    INDEX idx_assigned_class_id (assigned_class_id),
    INDEX idx_employment_status (employment_status),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. ATTENDANCE TABLE
-- ============================================
CREATE TABLE attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late', 'Excused') DEFAULT 'Present',
    marked_by INT,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    academic_year VARCHAR(9) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(user_id),
    UNIQUE KEY unique_student_date (student_id, attendance_date),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_academic_year (academic_year),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. EXAMINATION TERMS TABLE
-- ============================================
CREATE TABLE examination_terms (
    term_id INT AUTO_INCREMENT PRIMARY KEY,
    term_name VARCHAR(50) NOT NULL,
    term_number INT,
    academic_year VARCHAR(9) NOT NULL,
    start_date DATE,
    end_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_term (academic_year, term_number),
    INDEX idx_academic_year (academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. GRADES/RESULTS TABLE
-- ============================================
CREATE TABLE grades (
    grade_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    term_id INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    test_score DECIMAL(5,2),
    exam_score DECIMAL(5,2),
    assignment_score DECIMAL(5,2),
    class_work_score DECIMAL(5,2),
    total_score DECIMAL(5,2),
    grade_letter VARCHAR(2),
    grade_point DECIMAL(3,2),
    remarks TEXT,
    teacher_id INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id),
    FOREIGN KEY (term_id) REFERENCES examination_terms(term_id),
    FOREIGN KEY (teacher_id) REFERENCES users(user_id),
    UNIQUE KEY unique_grade_record (student_id, subject_id, class_id, term_id),
    INDEX idx_student_id (student_id),
    INDEX idx_academic_year (academic_year),
    INDEX idx_term_id (term_id),
    INDEX idx_class_id (class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. REPORT CARDS TABLE
-- ============================================
CREATE TABLE report_cards (
    report_card_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    term_id INT NOT NULL,
    academic_year VARCHAR(9) NOT NULL,
    class_id INT NOT NULL,
    total_marks DECIMAL(6,2),
    average_mark DECIMAL(5,2),
    class_position INT,
    total_students_in_class INT,
    principal_remarks TEXT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    generated_by INT,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES examination_terms(term_id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id),
    FOREIGN KEY (generated_by) REFERENCES users(user_id),
    UNIQUE KEY unique_report_card (student_id, term_id, academic_year),
    INDEX idx_academic_year (academic_year),
    INDEX idx_class_id (class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. AUDIT LOG TABLE
-- ============================================
CREATE TABLE audit_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. SESSION LOG TABLE
-- ============================================
CREATE TABLE session_log (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_token VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_login_time (login_time),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    notification_type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    read_at TIMESTAMP NULL,
    reference_table VARCHAR(50),
    reference_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT DATA
-- ============================================

-- Insert school settings
INSERT INTO school_settings (
    school_name, 
    school_motto, 
    school_email, 
    school_phone, 
    school_address,
    school_city,
    school_state,
    currency_symbol,
    timezone
) VALUES (
    'Deslin Montessori School',
    'Arise & Shine',
    'info@deslinmontessori.edu',
    '+234-XXXX-XXXX',
    '123 Education Street',
    'Lagos',
    'Lagos State',
    '₦',
    'Africa/Lagos'
) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Insert default subjects
INSERT INTO subjects (subject_name, subject_code, is_active) VALUES
('English Language', 'ENG', 1),
('Mathematics', 'MATH', 1),
('Science', 'SCI', 1),
('Social Studies', 'SS', 1),
('Physical Education', 'PE', 1),
('Information Technology', 'IT', 1),
('Creative Arts', 'ART', 1),
('Music', 'MUS', 1),
('French Language', 'FRE', 1),
('Religious Studies', 'RS', 1)
ON DUPLICATE KEY UPDATE is_active = 1;

-- Insert default classes
INSERT INTO classes (class_name, class_level, capacity, academic_year, is_active) VALUES
('Nursery', 1, 35, '2025/2026', 1),
('Kindergarten', 2, 40, '2025/2026', 1),
('Primary 1', 3, 40, '2025/2026', 1),
('Primary 2', 4, 40, '2025/2026', 1),
('Primary 3', 5, 40, '2025/2026', 1),
('Primary 4', 6, 40, '2025/2026', 1),
('Primary 5', 7, 40, '2025/2026', 1),
('Primary 6', 8, 40, '2025/2026', 1),
('JSS 1', 9, 45, '2025/2026', 1),
('JSS 2', 10, 45, '2025/2026', 1),
('JSS 3', 11, 45, '2025/2026', 1)
ON DUPLICATE KEY UPDATE is_active = 1;

-- Insert examination terms
INSERT INTO examination_terms (term_name, term_number, academic_year, start_date, end_date, is_active) VALUES
('First Term', 1, '2025/2026', '2025-09-01', '2025-12-15', 1),
('Second Term', 2, '2025/2026', '2026-01-12', '2026-03-31', 1),
('Third Term', 3, '2025/2026', '2026-04-15', '2026-07-10', 1)
ON DUPLICATE KEY UPDATE is_active = 1;
