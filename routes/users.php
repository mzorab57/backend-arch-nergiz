<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/corse.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../controllers/UserController.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $controller = new UserController($pdo);
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    
    // Parse the URI to get the path and ID
    $path = parse_url($uri, PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    // Remove 'api-nergiz' and 'users' from path parts
    $pathParts = array_values(array_filter($pathParts));
    $relevantParts = array_slice($pathParts, array_search('users', $pathParts) + 1);
    
    switch ($method) {
        case 'GET':
            if (empty($relevantParts)) {
                // GET /users - Get all users
                $controller->handleGet();
            } else {
                $id = $relevantParts[0];
                if (is_numeric($id)) {
                    // GET /users/{id} - Get specific user
                    $controller->handleGet($id);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid user ID']);
                }
            }
            break;
            
        case 'POST':
            if (empty($relevantParts)) {
                // POST /users - Create new user
                $controller->handlePost();
            } elseif (count($relevantParts) === 1 && $relevantParts[0] === 'login') {
                // POST /users/login - User login
                $controller->login();
            } elseif (count($relevantParts) === 2 && is_numeric($relevantParts[0]) && $relevantParts[1] === 'change-password') {
                // POST /users/{id}/change-password - Change password
                $controller->changePassword($relevantParts[0]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'PUT':
            if (count($relevantParts) === 1 && is_numeric($relevantParts[0])) {
                // PUT /users/{id} - Update user
                $controller->handlePut($relevantParts[0]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid user ID']);
            }
            break;
            
        case 'DELETE':
            if (count($relevantParts) === 1 && is_numeric($relevantParts[0])) {
                // DELETE /users/{id} - Delete user by path param
                $controller->handleDelete($relevantParts[0]);
            } else {
                // Support JSON body: { "id": N }
                $input = json_decode(file_get_contents('php://input'), true);
                if (isset($input['id']) && is_numeric($input['id'])) {
                    $controller->handleDelete($input['id']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid user ID']);
                }
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>