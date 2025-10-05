<?php
require_once __DIR__ . '/../controllers/PortfolioController.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/corse.php';


// Get the request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Remove query string from URI
$uri = strtok($uri, '?');

// Parse the URI to get the ID if present
$path_parts = explode('/', trim($uri, '/'));
$id = null;

// Check if there's an ID in the path (e.g., /api/portfolio/1)
if (count($path_parts) >= 3 && is_numeric($path_parts[2])) {
    $id = (int)$path_parts[2];
}

try {
    // Database connection is already available from db.php as $pdo
    
    // Create controller instance
    $controller = new PortfolioController($pdo);
    
    switch ($method) {
        case 'GET':
            $controller->handleGet($id);
            break;
            
        case 'POST':
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                exit;
            }
            $controller->handlePost($input);
            break;
            
        case 'PUT':
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                exit;
            }
            // Allow ID from path or JSON body
            if (!$id) {
                if (isset($input['id']) && is_numeric($input['id'])) {
                    $id = (int)$input['id'];
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Portfolio ID is required for update']);
                    exit;
                }
            }
            $controller->handlePut($id, $input);
            break;
            
        case 'DELETE':
             // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input']);
                exit;
            }
            
            // Allow ID from path or JSON body
            if (!$id) {
                if (isset($input['id']) && is_numeric($input['id'])) {
                    $id = (int)$input['id'];
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Portfolio ID is required for update']);
                    exit;
                }
            }
            $controller->handleDelete($id);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>