<?php
require_once __DIR__ . '/require_auth.php';

/**
 * Middleware to require specific role
 * @param string|array $requiredRoles - Single role or array of roles
 * @return array|false - User data if authorized, false otherwise
 */
function requireRole($requiredRoles) {
    // First check if user is authenticated
    $user = requireAuth();
    if (!$user) {
        return false;
    }
    
    // Convert single role to array for consistency
    if (!is_array($requiredRoles)) {
        $requiredRoles = [$requiredRoles];
    }
    
    // Check if user has required role
    if (!in_array($user['role'], $requiredRoles)) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Access denied. Required role: ' . implode(' or ', $requiredRoles),
            'user_role' => $user['role']
        ]);
        return false;
    }
    
    return $user;
}

/**
 * Check if user has specific role without stopping execution
 * @param string|array $requiredRoles - Single role or array of roles
 * @return array|null - User data if authorized, null otherwise
 */
function checkRole($requiredRoles) {
    // First check if user is authenticated
    $user = checkAuth();
    if (!$user) {
        return null;
    }
    
    // Convert single role to array for consistency
    if (!is_array($requiredRoles)) {
        $requiredRoles = [$requiredRoles];
    }
    
    // Check if user has required role
    if (!in_array($user['role'], $requiredRoles)) {
        return null;
    }
    
    return $user;
}
?>