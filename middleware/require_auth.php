<?php
require_once __DIR__ . '/../utils/jwt.php';

/**
 * Middleware to require authentication
 * Returns user data if authenticated, false otherwise
 */
function requireAuth() {
    try {
        $jwt = new JWT();
        $token = JWT::getTokenFromHeader();
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Authorization token required']);
            return false;
        }
        
        $decoded = $jwt->verifyToken($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            return false;
        }
        
        // Return user data from token
        return [
            'user_id' => $decoded->user_id,
            'username' => $decoded->username,
            'role' => $decoded->role
        ];
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Authentication error: ' . $e->getMessage()]);
        return false;
    }
}

/**
 * Check if user is authenticated without stopping execution
 * Returns user data if authenticated, null otherwise
 */
function checkAuth() {
    try {
        $jwt = new JWT();
        $token = JWT::getTokenFromHeader();
        
        if (!$token) {
            return null;
        }
        
        $decoded = $jwt->verifyToken($token);
        if (!$decoded) {
            return null;
        }
        
        return [
            'user_id' => $decoded->user_id,
            'username' => $decoded->username,
            'role' => $decoded->role
        ];
        
    } catch (Exception $e) {
        return null;
    }
}
?>