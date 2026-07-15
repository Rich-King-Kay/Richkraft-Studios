<?php
/**
 * Teacher Model Class
 * Handles teacher data operations
 */

require_once __DIR__ . '/BaseModel.php';

class Teacher extends BaseModel {
    protected $table = 'teachers';
    protected $primaryKey = 'teacher_id';

    /**
     * Create a new teacher
     */
    public function create($data, $userId) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
            (user_id, teacher_emp_no, phone_number, residential_address, city, state, 
             postal_code, date_employed, assigned_class_id, qualification, specialization, 
             employment_status, date_of_birth, bank_account_number, bank_name, teacher_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')"
        );

        $stmt->bind_param(
            'issssssisssssss',
            $userId,
            $data['teacher_emp_no'],
            $data['phone_number'],
            $data['residential_address'],
            $data['city'],
            $data['state'],
            $data['postal_code'],
            $data['date_employed'],
            $data['assigned_class_id'],
            $data['qualification'],
            $data['specialization'],
            $data['employment_status'],
            $data['date_of_birth'],
            $data['bank_account_number'],
            $data['bank_name']
        );

        return $this->insertResult($stmt, 'teacher_id', 'Teacher added successfully');
    }

    /**
     * Get teacher by ID
     */
    public function getById($teacherId) {
        $stmt = $this->db->prepare(
            "SELECT t.*, u.username, u.email, u.full_name, c.class_name 
             FROM {$this->table} t 
             LEFT JOIN users u ON t.user_id = u.user_id 
             LEFT JOIN classes c ON t.assigned_class_id = c.class_id 
             WHERE t.teacher_id = ?"
        );

        $stmt->bind_param('i', $teacherId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all teachers
     */
    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT t.*, u.username, u.email, u.full_name, c.class_name 
                  FROM {$this->table} t 
                  LEFT JOIN users u ON t.user_id = u.user_id 
                  LEFT JOIN classes c ON t.assigned_class_id = c.class_id 
                  WHERE t.teacher_status = 'Active' 
                  ORDER BY u.full_name";

        if ($limit) {
            $query .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        }

        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get teacher by user ID
     */
    public function getByUserId($userId) {
        $stmt = $this->db->prepare(
            "SELECT t.*, u.username, u.email, u.full_name, c.class_name 
             FROM {$this->table} t 
             LEFT JOIN users u ON t.user_id = u.user_id 
             LEFT JOIN classes c ON t.assigned_class_id = c.class_id 
             WHERE t.user_id = ?"
        );

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get teachers by class
     */
    public function getByClass($classId) {
        $stmt = $this->db->prepare(
            "SELECT t.*, u.username, u.email, u.full_name 
             FROM {$this->table} t 
             LEFT JOIN users u ON t.user_id = u.user_id 
             WHERE t.assigned_class_id = ? AND t.teacher_status = 'Active'"
        );

        $stmt->bind_param('i', $classId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Search teachers
     */
    public function search($searchTerm, $limit = 20) {
        $searchTerm = '%' . $this->db->real_escape_string($searchTerm) . '%';
        $query = "SELECT t.*, u.username, u.email, u.full_name, c.class_name 
                  FROM {$this->table} t 
                  LEFT JOIN users u ON t.user_id = u.user_id 
                  LEFT JOIN classes c ON t.assigned_class_id = c.class_id 
                  WHERE u.full_name LIKE ? OR t.teacher_emp_no LIKE ? 
                     OR u.email LIKE ? OR t.phone_number LIKE ? 
                  LIMIT ?";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ssssi', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update teacher
     */
    public function update($teacherId, $data) {
        $allowedFields = [
            'phone_number', 'residential_address', 'city', 'state', 'postal_code',
            'assigned_class_id', 'qualification', 'specialization',
            'employment_status', 'date_of_birth', 'bank_account_number',
            'bank_name', 'teacher_status'
        ];

        return $this->updateRecord(
            $teacherId,
            $data,
            $allowedFields,
            ['assigned_class_id' => 'i'],
            [],
            'Teacher updated successfully'
        );
    }

    /**
     * Delete teacher
     */
    public function delete($teacherId) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET teacher_status = 'Inactive' WHERE teacher_id = ?"
        );
        $stmt->bind_param('i', $teacherId);

        return $this->resultFromExecute($stmt, 'Teacher deleted successfully');
    }

    /**
     * Get total teachers count
     */
    public function getTotalCount() {
        return $this->countRecords("teacher_status = 'Active'");
    }

    /**
     * Check if employment number exists
     */
    public function empNoExists($empNo, $excludeTeacherId = null) {
        return $this->existsWhere('teacher_emp_no', $empNo, $excludeTeacherId);
    }
}

?>