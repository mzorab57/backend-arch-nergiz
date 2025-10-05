<?php
require_once __DIR__ . '/../config/env.php';

class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all users
     */
    public function all() {
        try {
            $stmt = $this->pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching users: " . $e->getMessage());
        }
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, password, role, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching user: " . $e->getMessage());
        }
    }
    
    /**
     * Get user by username
     */
    public function getByUsername($username) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, password, role, created_at FROM users WHERE username = ?");
            $stmt->execute([$username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error fetching user: " . $e->getMessage());
        }
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        try {
            // Hash password
            $hashedPassword = $this->hashPassword($data['password']);
            
            $stmt = $this->pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([
                $data['username'],
                $hashedPassword,
                $data['role'] ?? 'admin'
            ]);
            
            $userId = $this->pdo->lastInsertId();
            return $this->getById($userId);
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                throw new Exception("Username already exists");
            }
            throw new Exception("Error creating user: " . $e->getMessage());
        }
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $values = [];
            
            if (isset($data['username'])) {
                $fields[] = "username = ?";
                $values[] = $data['username'];
            }
            
            if (isset($data['password'])) {
                $fields[] = "password = ?";
                $values[] = $this->hashPassword($data['password']);
            }
            
            if (isset($data['role'])) {
                $fields[] = "role = ?";
                $values[] = $data['role'];
            }
            
            if (empty($fields)) {
                throw new Exception("No fields to update");
            }
            
            $values[] = $id;
            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);
            
            return $this->getById($id);
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                throw new Exception("Username already exists");
            }
            throw new Exception("Error updating user: " . $e->getMessage());
        }
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Error deleting user: " . $e->getMessage());
        }
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
            $params = [$username];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new Exception("Error checking username: " . $e->getMessage());
        }
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password, $hashedPassword) {
        return password_verify($password, $hashedPassword);
    }
    
    /**
     * Hash password
     */
    private function hashPassword($password) {
        $rounds = EnvLoader::get('BCRYPT_ROUNDS', 12);
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $rounds]);
    }
    
    /**
     * Change user password
     */
    public function changePassword($id, $currentPassword, $newPassword) {
        try {
            // Get current user data
            $user = $this->getById($id);
            if (!$user) {
                throw new Exception("User not found");
            }
            
            // Verify current password
            if (!$this->verifyPassword($currentPassword, $user['password'])) {
                throw new Exception("Current password is incorrect");
            }
            
            // Update password
            $hashedPassword = $this->hashPassword($newPassword);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $id]);
            
            return true;
            
        } catch (PDOException $e) {
            throw new Exception("Error changing password: " . $e->getMessage());
        }
    }
    
    /**
     * Authenticate user (for login)
     */
    public function authenticate($username, $password) {
        try {
            $user = $this->getByUsername($username);
            
            if (!$user || !$this->verifyPassword($password, $user['password'])) {
                return false;
            }
            
            // Remove password from returned data
            unset($user['password']);
            return $user;
            
        } catch (Exception $e) {
            throw new Exception("Authentication error: " . $e->getMessage());
        }
    }
}
?>