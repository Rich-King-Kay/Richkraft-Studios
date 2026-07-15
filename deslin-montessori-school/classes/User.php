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
     * Prepare a statement, logging (rather than silently ignoring) failures.
     * Returns false when preparation fails so callers can bail out safely
     * instead of fatally calling methods on a boolean.
     */
    private function prepareStmt($query) {
        $stmt = $this->db->prepare($query);
        if ($stmt === false) {
            Database::logError('User::prepare', $this->db->error . ' -- Query: ' . $query);
        }
        return $stmt;
    }

    /**
     * Create a new user
     */
    public function create($data) {
        $stmt = $this->prepareStmt(
            "INSERT INTO {$this->table} 
            (username, email, password_hash, full_name, role, is_active) 
            VALUES (?, ?, ?, ?, ?, 1)"
        );

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query preparation failed'];
        }

        $passwordHash = SecurityHelper::hashPassword($data['password'] ?? '');
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
            Database::logError('User::create', $stmt->error);
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $stmt = $this->prepareStmt(
            "SELECT user_id, username, email, password_hash, full_name, role, is_active 
             FROM {$this->table} WHERE username = ? AND is_active = 1"
        );

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query failed'];
        }

        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            Database::logError('User::authenticate', $this->db->error);
            return ['success' => false, 'message' => 'Authentication failed'];
        }

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
        $stmt = $this->prepareStmt(
            "SELECT user_id, username, email, full_name, role, is_active, last_login, created_at 
             FROM {$this->table} WHERE user_id = ?"
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
     * Get all users
     */
    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT user_id, username, email, full_name, role, is_active, last_login, created_at 
                  FROM {$this->table} WHERE is_active = 1 ORDER BY created_at DESC";
        
        if ($limit) {
            $query .= " LIMIT ? OFFSET ?";
            $stmt = $this->prepareStmt($query);
            if (!$stmt) {
                return [];
            }
            $stmt->bind_param('ii', $limit, $offset);
        } else {
            $stmt = $this->prepareStmt($query);
            if (!$stmt) {
                return [];
            }
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result === false ? [] : $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get users by role
     */
    public function getByRole($role) {
        $stmt = $this->prepareStmt(
            "SELECT user_id, username, email, full_name, role, is_active 
             FROM {$this->table} WHERE role = ? AND is_active = 1 ORDER BY full_name"
        );

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param('s', $role);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result === false ? [] : $result->fetch_all(MYSQLI_ASSOC);
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
        $stmt = $this->prepareStmt($query);

        if (!$stmt) {
            return ['success' => false, 'message' => 'Query failed'];
        }

        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User updated successfully'];
        } else {
            Database::logError('User::update', $stmt->error);
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    /**
     * Deactivate user
     */
    public function deactivate($userId) {
        $stmt = $this->prepareStmt("UPDATE {$this->table} SET is_active = 0 WHERE user_id = ?");
        if (!$stmt) {
            return ['success' => false, 'message' => 'Query preparation failed'];
        }
        $stmt->bind_param('i', $userId);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User deactivated'];
        }
        Database::logError('User::deactivate', $stmt->error);
        return ['success' => false, 'message' => $stmt->error];
    }

    /**
     * Update last login
     */
    private function updateLastLogin($userId) {
        $stmt = $this->prepareStmt(
            "UPDATE {$this->table} SET last_login = NOW() WHERE user_id = ?"
        );
        if (!$stmt) {
            return;
        }
        $stmt->bind_param('i', $userId);
        if (!$stmt->execute()) {
            Database::logError('User::updateLastLogin', $stmt->error);
        }
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username) {
        $stmt = $this->prepareStmt("SELECT user_id FROM {$this->table} WHERE username = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result !== false && $result->num_rows > 0;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeUserId = null) {
        $query = "SELECT user_id FROM {$this->table} WHERE email = ?";
        if ($excludeUserId) {
            $query .= " AND user_id != ?";
            $stmt = $this->prepareStmt($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('si', $email, $excludeUserId);
        } else {
            $stmt = $this->prepareStmt($query);
            if (!$stmt) {
                return false;
            }
            $stmt->bind_param('s', $email);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result !== false && $result->num_rows > 0;
    }
}

?>