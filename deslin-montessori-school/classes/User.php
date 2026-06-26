<?php
/**
 * User Model Class
 * Handles user authentication, profile, and role management
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/SecurityHelper.php';

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new user
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->table} 
            (username, email, password_hash, full_name, role, is_active) 
            VALUES (?, ?, ?, ?, ?, 1)"
        );

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query preparation failed'];
        }

        $passwordHash = SecurityHelper::hashPassword($data['password']);
        $role = $data['role'] ?? 'staff';

        $stmt->bind_param(
            'sssss',
            $data['username'],
            $data['email'],
            $passwordHash,
            $data['full_name'],
            $role
        );

        if ($stmt->execute()) {
            return ['success' => true, 'user_id' => $this->db->insert_id, 'message' => 'User created successfully'];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $stmt = $this->db->prepare(
            "SELECT user_id, username, email, password_hash, full_name, role, is_active 
             FROM {$this->table} WHERE username = ? AND is_active = 1"
        );

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query failed'];
        }

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid username'];
        }

        $user = $result->fetch_assoc();

        if (!SecurityHelper::verifyPassword($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid password'];
        }

        // Update last login
        $this->updateLastLogin($user['user_id']);

        return [
            'success' => true,
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
            'message' => 'Login successful'
        ];
    }

    /**
     * Get user by ID
     */
    public function getById($userId) {
        $stmt = $this->db->prepare(
            "SELECT user_id, username, email, full_name, role, is_active, last_login, created_at 
             FROM {$this->table} WHERE user_id = ?"
        );

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Get all users
     */
    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT user_id, username, email, full_name, role, is_active, last_login, created_at 
                  FROM {$this->table} WHERE is_active = 1 ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('ii', $limit, $offset);
        } else {
            $stmt = $this->db->prepare($query);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get users by role
     */
    public function getByRole($role) {
        $stmt = $this->db->prepare(
            "SELECT user_id, username, email, full_name, role, is_active 
             FROM {$this->table} WHERE role = ? AND is_active = 1 ORDER BY full_name"
        );

        $stmt->bind_param('s', $role);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update user
     */
    public function update($userId, $data) {
        $fields = [];
        $types = '';
        $values = [];

        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $types .= 's';
            $values[] = $data['email'];
        }
        if (isset($data['full_name'])) {
            $fields[] = 'full_name = ?';
            $types .= 's';
            $values[] = $data['full_name'];
        }
        if (isset($data['role'])) {
            $fields[] = 'role = ?';
            $types .= 's';
            $values[] = $data['role'];
        }
        if (isset($data['password'])) {
            $fields[] = 'password_hash = ?';
            $types .= 's';
            $passwordHash = SecurityHelper::hashPassword($data['password']);
            $values[] = $passwordHash;
        }

        if (empty($fields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }

        $types .= 'i';
        $values[] = $userId;

        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE user_id = ?";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query failed'];
        }

        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User updated successfully'];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Deactivate user
     */
    public function deactivate($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_active = 0 WHERE user_id = ?");
        $stmt->bind_param('i', $userId);

        return $stmt->execute() ? 
            ['success' => true, 'message' => 'User deactivated'] : 
            ['success' => false, 'message' => $stmt->error];
    }

    /**
     * Update last login
     */
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET last_login = NOW() WHERE user_id = ?"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT user_id FROM {$this->table} WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeUserId = null) {
        $query = "SELECT user_id FROM {$this->table} WHERE email = ?";
        if ($excludeUserId) {
            $query .= " AND user_id != ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('si', $email, $excludeUserId);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param('s', $email);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}

?>