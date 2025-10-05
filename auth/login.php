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
    
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password are required']);
        exit;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password cannot be empty']);
        exit;
    }
    
    $user = new User($pdo);
    $userData = $user->getByUsername($username);
    
    if (!$userData || !$user->verifyPassword($password, $userData['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password']);
        exit;
    }
    
    // Generate JWT tokens
    $jwt = new JWT();
    $accessToken = $jwt->generateAccessToken($userData['id'], $userData['username'], $userData['role']);
    $refreshToken = $jwt->generateRefreshToken($userData['id']);
    
    // Remove password from response
    unset($userData['password']);
    
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
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>