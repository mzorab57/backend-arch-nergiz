<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../middleware/protect_admin.php';
require_once __DIR__ . '/../helpers/response.php';

class UserController {
    private $user;
    private $jwt;
    
    public function __construct($pdo) {
        $this->user = new User($pdo);
        $this->jwt = new JWT();
    }
    
    /**
     * Handle GET requests
     */
    public function handleGet($id = null) {
        // Require admin access for user management
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            if ($id) {
                $userData = $this->user->getById($id);
                if (!$userData) {
                    http_response_code(404);
                    echo json_encode(['error' => 'User not found']);
                    return;
                }
                
                // Remove password from response
                unset($userData['password']);
                
                http_response_code(200);
                echo json_encode($userData);
            } else {
                $users = $this->user->all();
                http_response_code(200);
                echo json_encode($users);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle POST requests (Create user)
     */
    public function handlePost() {
        // Require admin access for creating users
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validation
            if (!isset($input['username']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Username and password are required']);
                return;
            }
            
            $username = trim($input['username']);
            $password = $input['password'];
            $role = $input['role'] ?? 'admin';
            
            // Validate input
            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode(['error' => 'Username and password cannot be empty']);
                return;
            }
            
            if (strlen($username) < 3) {
                http_response_code(400);
                echo json_encode(['error' => 'Username must be at least 3 characters long']);
                return;
            }
            
            if (strlen($password) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'Password must be at least 6 characters long']);
                return;
            }
            
            // Check if username already exists
            if ($this->user->usernameExists($username)) {
                http_response_code(409);
                echo json_encode(['error' => 'Username already exists']);
                return;
            }
            
            // Create user
            $userData = $this->user->create([
                'username' => $username,
                'password' => $password,
                'role' => $role
            ]);
            
            // Remove password from response
            unset($userData['password']);
            
            http_response_code(201);
            echo json_encode([
                'message' => 'User created successfully',
                'user' => $userData
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle PUT requests (Update user)
     */
    public function handlePut($id) {
        // Require admin access for updating users
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input)) {
                http_response_code(400);
                echo json_encode(['error' => 'No data provided']);
                return;
            }
            
            // Validate username if provided
            if (isset($input['username'])) {
                $username = trim($input['username']);
                if (empty($username)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Username cannot be empty']);
                    return;
                }
                
                if (strlen($username) < 3) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Username must be at least 3 characters long']);
                    return;
                }
                
                // Check if username already exists (excluding current user)
                if ($this->user->usernameExists($username, $id)) {
                    http_response_code(409);
                    echo json_encode(['error' => 'Username already exists']);
                    return;
                }
                
                $input['username'] = $username;
            }
            
            // Validate password if provided
            if (isset($input['password'])) {
                if (strlen($input['password']) < 6) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Password must be at least 6 characters long']);
                    return;
                }
            }
            
            // Update user
            $userData = $this->user->update($id, $input);
            
            if (!$userData) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                return;
            }
            
            // Remove password from response
            unset($userData['password']);
            
            http_response_code(200);
            echo json_encode([
                'message' => 'User updated successfully',
                'user' => $userData
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle DELETE requests
     */
    public function handleDelete($id) {
        // Require admin access for deleting users
        $admin = protectAdmin();
        if (!$admin) {
            return;
        }
        
        try {
            // Check if user exists
            $userData = $this->user->getById($id);
            if (!$userData) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                return;
            }
            
            // Prevent admin from deleting themselves
            if ($admin['user_id'] == $id) {
                http_response_code(400);
                echo json_encode(['error' => 'Cannot delete your own account']);
                return;
            }
            
            $this->user->delete($id);
            
            http_response_code(200);
            echo json_encode(['message' => 'User deleted successfully']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle login
     */
    public function login() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['username']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Username and password are required']);
                return;
            }
            
            $username = trim($input['username']);
            $password = $input['password'];
            
            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode(['error' => 'Username and password cannot be empty']);
                return;
            }
            
            // Authenticate user
            $userData = $this->user->authenticate($username, $password);
            
            if (!$userData) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid username or password']);
                return;
            }
            
            // Generate JWT tokens
            $accessToken = $this->jwt->generateAccessToken($userData['id'], $userData['username'], $userData['role']);
            $refreshToken = $this->jwt->generateRefreshToken($userData['id']);
            
            http_response_code(200);
            echo json_encode([
                'message' => 'Login successful',
                'user' => $userData,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => 3600 // 1 hour
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle password change
     */
    public function changePassword($id) {
        // Users can change their own password, or admin can change any password
        $user = requireAuth();
        if (!$user) {
            return;
        }
        
        // Check if user is changing their own password or is admin
        if ($user['user_id'] != $id && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied. You can only change your own password']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['current_password']) || !isset($input['new_password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Current password and new password are required']);
                return;
            }
            
            $currentPassword = $input['current_password'];
            $newPassword = $input['new_password'];
            
            if (strlen($newPassword) < 6) {
                http_response_code(400);
                echo json_encode(['error' => 'New password must be at least 6 characters long']);
                return;
            }
            
            // Admin can skip current password verification
            if ($user['role'] === 'admin' && $user['user_id'] != $id) {
                $userData = $this->user->getById($id);
                if (!$userData) {
                    http_response_code(404);
                    echo json_encode(['error' => 'User not found']);
                    return;
                }
                
                // Update password directly for admin
                $this->user->update($id, ['password' => $newPassword]);
            } else {
                // Regular password change with current password verification
                $this->user->changePassword($id, $currentPassword, $newPassword);
            }
            
            http_response_code(200);
            echo json_encode(['message' => 'Password changed successfully']);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>