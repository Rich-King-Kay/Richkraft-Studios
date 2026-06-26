<?php
/**
 * Attendance Model Class
 * Handles attendance data operations
 */

require_once __DIR__ . '/../config/database.php';

class Attendance {
    private $db;
    private $table = 'attendance';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Mark attendance for a student
     */
    public function mark($data) {
        // Check if already marked for the day
        $checkStmt = $this->db->prepare(
            "SELECT attendance_id FROM {$this->table} 
             WHERE student_id = ? AND attendance_date = ?"
        );
        $checkStmt->bind_param('is', $data['student_id'], $data['attendance_date']);
        $checkStmt->execute();

        if ($checkStmt->get_result()->num_rows > 0) {
            // Update existing record
            return $this->updateAttendance($data);
        }

        // Insert new record
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
            (student_id, attendance_date, status, marked_by, remarks, academic_year) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            'isssss',
            $data['student_id'],
            $data['attendance_date'],
            $data['status'],
            $data['marked_by'],
            $data['remarks'],
            $data['academic_year']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Attendance marked successfully'];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Update existing attendance record
     */
    private function updateAttendance($data) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} 
             SET status = ?, marked_by = ?, remarks = ?, marked_at = NOW() 
             WHERE student_id = ? AND attendance_date = ?"
        );

        $stmt->bind_param(
            'sisis',
            $data['status'],
            $data['marked_by'],
            $data['remarks'],
            $data['student_id'],
            $data['attendance_date']
        );

        return $stmt->execute() ? 
            ['success' => true, 'message' => 'Attendance updated successfully'] : 
            ['success' => false, 'message' => $stmt->error];
    }

    /**
     * Get attendance for a student on a specific date
     */
    public function getByStudentAndDate($studentId, $date) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE student_id = ? AND attendance_date = ?"
        );

        $stmt->bind_param('is', $studentId, $date);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get attendance records for a class on a specific date
     */
    public function getByClassAndDate($classId, $date) {
        $query = "SELECT a.*, s.student_reg_no, s.first_name, s.last_name 
                  FROM {$this->table} a 
                  JOIN students s ON a.student_id = s.student_id 
                  WHERE s.current_class_id = ? AND a.attendance_date = ? 
                  ORDER BY s.first_name, s.last_name";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('is', $classId, $date);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get student attendance summary
     */
    public function getStudentSummary($studentId, $academicYear = null) {
        $query = "SELECT 
                    COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late,
                    SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused
                  FROM {$this->table} 
                  WHERE student_id = ?";

        if ($academicYear) {
            $query .= " AND academic_year = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('is', $studentId, $academicYear);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('i', $studentId);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get class attendance summary for a date range
     */
    public function getClassSummary($classId, $startDate, $endDate, $academicYear = null) {
        $query = "SELECT 
                    s.student_id,
                    s.student_reg_no,
                    s.first_name,
                    s.last_name,
                    COUNT(a.attendance_id) as total_days,
                    SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) as late,
                    ROUND((SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) / COUNT(a.attendance_id) * 100), 2) as percentage
                  FROM students s
                  LEFT JOIN {$this->table} a ON s.student_id = a.student_id 
                    AND a.attendance_date BETWEEN ? AND ?";

        if ($academicYear) {
            $query .= " AND a.academic_year = ?";
        }

        $query .= " WHERE s.current_class_id = ? AND s.student_status = 'Active'
                  GROUP BY s.student_id
                  ORDER BY s.first_name, s.last_name";

        if ($academicYear) {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('sssi', $startDate, $endDate, $academicYear, $classId);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ssi', $startDate, $endDate, $classId);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get attendance records for a date range
     */
    public function getByDateRange($studentId, $startDate, $endDate) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} 
             WHERE student_id = ? AND attendance_date BETWEEN ? AND ? 
             ORDER BY attendance_date DESC"
        );

        $stmt->bind_param('iss', $studentId, $startDate, $endDate);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get attendance percentage for a student
     */
    public function getAttendancePercentage($studentId, $academicYear = null) {
        $summary = $this->getStudentSummary($studentId, $academicYear);
        
        if ($summary['total_days'] == 0) {
            return 0;
        }

        return round(($summary['present'] / $summary['total_days']) * 100, 2);
    }

    /**
     * Delete attendance record
     */
    public function delete($attendanceId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE attendance_id = ?");
        $stmt->bind_param('i', $attendanceId);

        return $stmt->execute() ? 
            ['success' => true, 'message' => 'Attendance record deleted'] : 
            ['success' => false, 'message' => $stmt->error];
    }
}

?>