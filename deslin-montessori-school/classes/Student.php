<?php
/**
 * Student Model Class
 * Handles student data operations
 */

require_once __DIR__ . '/BaseModel.php';

class Student extends BaseModel {
    protected $table = 'students';
    protected $primaryKey = 'student_id';

    /**
     * Create a new student
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
            (student_reg_no, first_name, middle_name, last_name, gender, date_of_birth, 
             residential_address, city, state, postal_code, parent_guardian_name, 
             parent_guardian_email, parent_guardian_phone, parent_guardian_address, 
             admission_date, current_class_id, academic_year, medical_notes, allergies, 
             blood_group, emergency_contact_name, emergency_contact_phone, 
             student_status, enrollment_status, passport_photo_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

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

        return $this->insertResult($stmt, 'student_id', 'Student added successfully');
    }

    /**
     * Get student by ID
     */
    public function getById($studentId) {
        $stmt = $this->db->prepare(
            "SELECT s.*, c.class_name FROM {$this->table} s 
             LEFT JOIN classes c ON s.current_class_id = c.class_id 
             WHERE s.student_id = ?"
        );

        $stmt->bind_param('i', $studentId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
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
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get students by class
     */
    public function getByClass($classId) {
        $stmt = $this->db->prepare(
            "SELECT s.* FROM {$this->table} s 
             WHERE s.current_class_id = ? AND s.student_status = 'Active' 
             ORDER BY s.first_name, s.last_name"
        );

        $stmt->bind_param('i', $classId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssssi', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update student
     */
    public function update($studentId, $data) {
        $allowedFields = [
            'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth',
            'residential_address', 'city', 'state', 'postal_code',
            'parent_guardian_name', 'parent_guardian_email', 'parent_guardian_phone',
            'parent_guardian_address', 'current_class_id', 'medical_notes',
            'allergies', 'blood_group', 'emergency_contact_name',
            'emergency_contact_phone', 'student_status', 'enrollment_status',
            'passport_photo_path'
        ];

        return $this->updateRecord(
            $studentId,
            $data,
            $allowedFields,
            ['current_class_id' => 'i'],
            [],
            'Student updated successfully'
        );
    }

    /**
     * Delete student
     */
    public function delete($studentId) {
        return $this->deleteRecord($studentId, 'Student deleted successfully');
    }

    /**
     * Get total students count
     */
    public function getTotalCount($filters = []) {
        $where = '1';
        if (isset($filters['status'])) {
            $where .= " AND student_status = '" . $this->db->real_escape_string($filters['status']) . "'";
        }
        return $this->countRecords($where);
    }

    /**
     * Get students by academic year
     */
    public function getByAcademicYear($academicYear) {
        $stmt = $this->db->prepare(
            "SELECT s.*, c.class_name FROM {$this->table} s 
             LEFT JOIN classes c ON s.current_class_id = c.class_id 
             WHERE s.academic_year = ? 
             ORDER BY s.first_name, s.last_name"
        );

        $stmt->bind_param('s', $academicYear);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Check if registration number exists
     */
    public function regNoExists($regNo, $excludeStudentId = null) {
        return $this->existsWhere('student_reg_no', $regNo, $excludeStudentId);
    }
}

?>