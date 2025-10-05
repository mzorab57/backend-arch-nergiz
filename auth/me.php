<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../config/corse.php';

// header('Content-Type: application/json');
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: GET');
// header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get authorization header
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization token required']);
        exit;
    }
    
    $token = $matches[1];
    $jwt = new JWT();
    
    // Verify token
    $decoded = $jwt->verifyToken($token);
    if (!$decoded) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }
    
    // Get user data from database
    $user = new User($pdo);
    $userData = $user->getById($decoded->user_id);
    
    if (!$userData) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    // Remove password from response
    unset($userData['password']);
    
    http_response_code(200);
    echo json_encode([
        'user' => $userData,
        'token_info' => [
            'user_id' => $decoded->user_id,
            'username' => $decoded->username,
            'role' => $decoded->role,
            'issued_at' => date('Y-m-d H:i:s', $decoded->iat),
            'expires_at' => date('Y-m-d H:i:s', $decoded->exp)
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>