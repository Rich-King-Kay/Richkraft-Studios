<?php
/**
 * Teacher Model Class
 * Handles teacher data operations
 */

require_once __DIR__ . '/../config/database.php';

class Teacher {
    private $db;
    private $table = 'teachers';

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
            Database::logError('Teacher::prepare', $this->db->error . ' -- Query: ' . $query);
        }
        return $stmt;
    }

    /**
     * Create a new teacher
     */
    public function create($data, $userId) {
        $stmt = $this->prepareStmt(
            "INSERT INTO {$this->table} 
            (user_id, teacher_emp_no, phone_number, residential_address, city, state, 
             postal_code, date_employed, assigned_class_id, qualification, specialization, 
             employment_status, date_of_birth, bank_account_number, bank_name, teacher_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')"
        );

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query preparation failed'];
        }

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

        if ($stmt->execute()) {
            return ['success' => true, 'teacher_id' => $this->db->insert_id, 'message' => 'Teacher added successfully'];
        } else {
            Database::logError('Teacher::create', $stmt->error);
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Get teacher by ID
     */
    public function getById($teacherId) {
        $stmt = $this->prepareStmt(
            "SELECT t.*, u.username, u.email, u.full_name, c.class_name 
             FROM {$this->table} t 
             LEFT JOIN users u ON t.user_id = u.user_id 
             LEFT JOIN classes c ON t.assigned_class_id = c.class_id 
             WHERE t.teacher_id = ?"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result === false ? null : $result->fetch_assoc();
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
        if ($result === false) {
            Database::logError('Teacher::getAll', $this->db->error . ' -- Query: ' . $query);
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get teacher by user ID
     */
    public function getByUserId($userId) {
        $stmt = $this->prepareStmt(
            "SELECT t.*, u.username, u.email, u.full_name, c.class_name 
             FROM {$this->table} t 
             LEFT JOIN users u ON t.user_id = u.user_id 
             LEFT JOIN classes c ON t.assigned_class_id = c.class_id 
             WHERE t.user_id = ?"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result === false ? null : $result->fetch_assoc();
    }

    /**
     * Get teachers by class
     */
    public function getByClass($classId) {
        $stmt = $this->prepareStmt(
            "SELECT t.*, u.username, u.email, u.full_name 
             FROM {$this->table} t 
             LEFT JOIN users u ON t.user_id = u.user_id 
             WHERE t.assigned_class_id = ? AND t.teacher_status = 'Active'"
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
     * Update teacher
     */
    public function update($teacherId, $data) {
        $updateFields = [];
        $types = '';
        $values = [];

        $allowedFields = [
            'phone_number', 'residential_address', 'city', 'state', 'postal_code',
            'assigned_class_id', 'qualification', 'specialization',
            'employment_status', 'date_of_birth', 'bank_account_number',
            'bank_name', 'teacher_status'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $types .= (in_array($field, ['assigned_class_id']) ? 'i' : 's');
                $values[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $types .= 'i';
        $values[] = $teacherId;

        $query = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE teacher_id = ?";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query failed'];
        }

        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Teacher updated successfully'];
        } else {
            Database::logError('Teacher::update', $stmt->error);
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Delete teacher
     */
    public function delete($teacherId) {
        $stmt = $this->prepareStmt(
            "UPDATE {$this->table} SET teacher_status = 'Inactive' WHERE teacher_id = ?"
        );
        if (!$stmt) {
            return ['success' => false, 'message' => 'Query preparation failed'];
        }
        $stmt->bind_param('i', $teacherId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Teacher deleted successfully'];
        }
        Database::logError('Teacher::delete', $stmt->error);
        return ['success' => false, 'message' => $stmt->error];
    }

    /**
     * Get total teachers count
     */
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE teacher_status = 'Active'";
        $result = $this->db->query($query);
        if ($result === false) {
            Database::logError('Teacher::getTotalCount', $this->db->error . ' -- Query: ' . $query);
            return 0;
        }
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    /**
     * Check if employment number exists
     */
    public function empNoExists($empNo, $excludeTeacherId = null) {
        $query = "SELECT teacher_id FROM {$this->table} WHERE teacher_emp_no = ?";
        if ($excludeTeacherId) {
            $query .= " AND teacher_id != ?";
            $stmt = $this->prepareStmt($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('si', $empNo, $excludeTeacherId);
        } else {
            $stmt = $this->prepareStmt($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('s', $empNo);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result !== false && $result->num_rows > 0;
    }
}

?>