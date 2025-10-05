<?php
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../config/corse.php';

// header('Content-Type: application/json');
// header('Access-Control-Allow-Origin: *');
// header('Access-Control-Allow-Methods: POST');
// header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    // In a real application, you might want to blacklist the token
    // For now, we'll just return a success message
    
    http_response_code(200);
    echo json_encode([
        'message' => 'Logout successful',
        'logged_out_at' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>