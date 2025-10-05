<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../config/corse.php';

// header('Content-Type: application/json');
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST');
// header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['refresh_token'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Refresh token is required']);
        exit;
    }
    
    $refreshToken = $input['refresh_token'];
    $jwt = new JWT();
    
    // Verify refresh token
    $decoded = $jwt->verifyRefreshToken($refreshToken);
    if (!$decoded) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired refresh token']);
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
    
    // Generate new tokens
    $newAccessToken = $jwt->generateAccessToken($userData['id'], $userData['username'], $userData['role']);
    $newRefreshToken = $jwt->generateRefreshToken($userData['id']);
    
    // Remove password from response
    unset($userData['password']);
    
    http_response_code(200);
    echo json_encode([
        'message' => 'Token refreshed successfully',
        'user' => $userData,
        'access_token' => $newAccessToken,
        'refresh_token' => $newRefreshToken,
        'expires_in' => 3600 // 1 hour
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>