<?php
/**
 * Student Model Class
 * Handles student data operations
 */

require_once __DIR__ . '/../config/database.php';

class Student {
    private $db;
    private $table = 'students';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Prepare a statement, logging (rather than silently ignoring) failures.
     * Returns false when preparation fails so callers can bail out safely
     * instead of fatally calling methods on a boolean.
     */
    private function prepareStmt($query) {
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            Database::logError('Student::prepare', $this->db->error . ' -- Query: ' . $query);
        }
        return $stmt;
    }

    /**
     * Create a new student
     */
    public function create($data) {
        $stmt = $this->prepareStmt(
            "INSERT INTO {$this->table} 
            (student_reg_no, first_name, middle_name, last_name, gender, date_of_birth, 
             residential_address, city, state, postal_code, parent_guardian_name, 
             parent_guardian_email, parent_guardian_phone, parent_guardian_address, 
             admission_date, current_class_id, academic_year, medical_notes, allergies, 
             blood_group, emergency_contact_name, emergency_contact_phone, 
             student_status, enrollment_status, passport_photo_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query preparation failed'];
        }

        $stmt->bind_param(
            'ssssssssssssssisssssssss',
            $data['student_reg_no'],
            $data['first_name'],
            $data['middle_name'],
            $data['last_name'],
            $data['gender'],
            $data['date_of_birth'],
            $data['residential_address'],
            $data['city'],
            $data['state'],
            $data['postal_code'],
            $data['parent_guardian_name'],
            $data['parent_guardian_email'],
            $data['parent_guardian_phone'],
            $data['parent_guardian_address'],
            $data['admission_date'],
            $data['current_class_id'],
            $data['academic_year'],
            $data['medical_notes'],
            $data['allergies'],
            $data['blood_group'],
            $data['emergency_contact_name'],
            $data['emergency_contact_phone'],
            $data['student_status'],
            $data['enrollment_status'],
            $data['passport_photo_path']
        );

        if ($stmt->execute()) {
            return ['success' => true, 'student_id' => $this->db->insert_id, 'message' => 'Student added successfully'];
        } else {
            Database::logError('Student::create', $stmt->error);
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Get student by ID
     */
    public function getById($studentId) {
        $stmt = $this->prepareStmt(
            "SELECT s.*, c.class_name FROM {$this->table} s 
             LEFT JOIN classes c ON s.current_class_id = c.class_id 
             WHERE s.student_id = ?"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result === false ? null : $result->fetch_assoc();
    }

    /**
     * Get all students
     */
    public function getAll($limit = null, $offset = 0, $filters = []) {
        $query = "SELECT s.*, c.class_name FROM {$this->table} s 
                  LEFT JOIN classes c ON s.current_class_id = c.class_id 
                  WHERE 1";

        if (isset($filters['class_id'])) {
            $query .= " AND s.current_class_id = " . intval($filters['class_id']);
        }
        if (isset($filters['status'])) {
            $query .= " AND s.student_status = '" . $this->db->real_escape_string($filters['status']) . "'";
        }
        if (isset($filters['academic_year'])) {
            $query .= " AND s.academic_year = '" . $this->db->real_escape_string($filters['academic_year']) . "'";
        }

        $query .= " ORDER BY s.first_name, s.last_name";

        if ($limit) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        $result = $this->db->query($query);
        if ($result === false) {
            Database::logError('Student::getAll', $this->db->error . ' -- Query: ' . $query);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get students by class
     */
    public function getByClass($classId) {
        $stmt = $this->prepareStmt(
            "SELECT s.* FROM {$this->table} s 
             WHERE s.current_class_id = ? AND s.student_status = 'Active' 
             ORDER BY s.first_name, s.last_name"
        );

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('i', $classId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result === false ? [] : $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Search students
     */
    public function search($searchTerm, $limit = 20) {
        $searchTerm = '%' . $this->db->real_escape_string($searchTerm) . '%';
        $query = "SELECT s.*, c.class_name FROM {$this->table} s 
                  LEFT JOIN classes c ON s.current_class_id = c.class_id 
                  WHERE s.first_name LIKE ? OR s.last_name LIKE ? 
                     OR s.student_reg_no LIKE ? OR s.parent_guardian_name LIKE ? 
                  LIMIT ?";

        $stmt = $this->prepareStmt($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('ssssi', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result === false ? [] : $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update student
     */
    public function update($studentId, $data) {
        $updateFields = [];
        $types = '';
        $values = [];

        $allowedFields = [
            'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth',
            'residential_address', 'city', 'state', 'postal_code',
            'parent_guardian_name', 'parent_guardian_email', 'parent_guardian_phone',
            'parent_guardian_address', 'current_class_id', 'medical_notes',
            'allergies', 'blood_group', 'emergency_contact_name',
            'emergency_contact_phone', 'student_status', 'enrollment_status',
            'passport_photo_path'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $types .= (in_array($field, ['current_class_id']) ? 'i' : 's');
                $values[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $types .= 'i';
        $values[] = $studentId;

        $query = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE student_id = ?";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query failed'];
        }

        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Student updated successfully'];
        } else {
            Database::logError('Student::update', $stmt->error);
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Delete student
     */
    public function delete($studentId) {
        $stmt = $this->prepareStmt("DELETE FROM {$this->table} WHERE student_id = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Query preparation failed'];
        }
        $stmt->bind_param('i', $studentId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Student deleted successfully'];
        }
        Database::logError('Student::delete', $stmt->error);
        return ['success' => false, 'message' => $stmt->error];
    }

    /**
     * Get total students count
     */
    public function getTotalCount($filters = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1";

        if (isset($filters['status'])) {
            $query .= " AND student_status = '" . $this->db->real_escape_string($filters['status']) . "'";
        }

        $result = $this->db->query($query);
        if ($result === false) {
            Database::logError('Student::getTotalCount', $this->db->error . ' -- Query: ' . $query);
            return 0;
        }
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Get students by academic year
     */
    public function getByAcademicYear($academicYear) {
        $stmt = $this->prepareStmt(
            "SELECT s.*, c.class_name FROM {$this->table} s 
             LEFT JOIN classes c ON s.current_class_id = c.class_id 
             WHERE s.academic_year = ? 
             ORDER BY s.first_name, s.last_name"
        );

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('s', $academicYear);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result === false ? [] : $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if registration number exists
     */
    public function regNoExists($regNo, $excludeStudentId = null) {
        $query = "SELECT student_id FROM {$this->table} WHERE student_reg_no = ?";
        if ($excludeStudentId) {
            $query .= " AND student_id != ?";
            $stmt = $this->prepareStmt($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('si', $regNo, $excludeStudentId);
        } else {
            $stmt = $this->prepareStmt($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('s', $regNo);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result !== false && $result->num_rows > 0;
    }
}

?>