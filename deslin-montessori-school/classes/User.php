<?php
/**
 * User Model Class
 * Handles user authentication, profile, and role management
 */

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../config/SecurityHelper.php';

class User extends BaseModel {
    protected $table = 'users';
    protected $primaryKey = 'user_id';

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

        return $this->insertResult($stmt, 'user_id', 'User created successfully');
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
        if (isset($data['password'])) {
            $data['password_hash'] = SecurityHelper::hashPassword($data['password']);
        }

        $allowedFields = ['email', 'full_name', 'role', 'password_hash'];

        return $this->updateRecord(
            $userId,
            $data,
            $allowedFields,
            [],
            [],
            'User updated successfully'
        );
    }

    /**
     * Deactivate user
     */
    public function deactivate($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_active = 0 WHERE user_id = ?");
        $stmt->bind_param('i', $userId);

        return $this->resultFromExecute($stmt, 'User deactivated');
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
        return $this->existsWhere('username', $username);
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeUserId = null) {
        return $this->existsWhere('email', $email, $excludeUserId);
    }
}

?>