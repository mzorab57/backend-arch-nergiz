<?php
require_once __DIR__ . '/../controllers/ServiceController.php';
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

// Check if there's an ID in the path (e.g., /api/services/1)
if (count($path_parts) >= 3 && is_numeric($path_parts[2])) {
    $id = (int)$path_parts[2];
}

try {
    // Database connection is already available from db.php as $pdo
    
    // Create controller instance
    $controller = new ServiceController($pdo);
    
    switch ($method) {
        case 'GET':
            $controller->handleGet($id);
            break;
            
        case 'POST':
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                // Handle file upload
                $input = [
                    'name' => isset($_POST['name']) ? trim($_POST['name']) : null,
                    'description' => isset($_POST['description']) ? trim($_POST['description']) : null,
                ];
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/services/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $tmpName = $_FILES['image']['tmp_name'];
                    $originalName = basename($_FILES['image']['name']);
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (!in_array($ext, $allowed, true)) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Unsupported image format']);
                        exit;
                    }
                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($mime, $allowedMimes, true)) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid image MIME type']);
                        exit;
                    }
                    
                    $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                    $filename = $safeBase . '_' . uniqid('', true) . '.' . $ext;
                    $targetPath = $uploadDir . $filename;
                    
                    if (!move_uploaded_file($tmpName, $targetPath)) {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to save uploaded file']);
                        exit;
                    }
                    
                    // Store relative path
                    $input['image'] = 'uploads/services/' . $filename;
                }
                
                // Method override: treat POST as UPDATE when _method=PUT
                if (isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
                    // Resolve ID from form-data or query
                    if (!$id && isset($_POST['id']) && is_numeric($_POST['id'])) {
                        $id = (int)$_POST['id'];
                    }
                    if (!$id && isset($_GET['id']) && is_numeric($_GET['id'])) {
                        $id = (int)$_GET['id'];
                    }
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Service ID is required for update']);
                        exit;
                    }
                    $controller->handlePut($id, $input);
                    break;
                }
                
                $controller->handlePost($input);
            } else {
                // Expect JSON
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid JSON input']);
                    exit;
                }
                // Method override for JSON
                if (isset($input['_method']) && strtoupper($input['_method']) === 'PUT') {
                    if (!$id && isset($input['id']) && is_numeric($input['id'])) {
                        $id = (int)$input['id'];
                    }
                    if (!$id && isset($_GET['id']) && is_numeric($_GET['id'])) {
                        $id = (int)$_GET['id'];
                    }
                    if (!$id) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Service ID is required for update']);
                        exit;
                    }
                    $controller->handlePut($id, $input);
                    break;
                }
                $controller->handlePost($input);
            }
            break;
            
        case 'PUT':
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                // Handle file upload update
                $input = [
                    'name' => isset($_POST['name']) ? trim($_POST['name']) : null,
                    'description' => isset($_POST['description']) ? trim($_POST['description']) : null,
                ];
                
                // Get ID from form-data if not in path
                if (!$id && isset($_POST['id']) && is_numeric($_POST['id'])) {
                    $id = (int)$_POST['id'];
                }
                // Also allow ID from query string for PUT with multipart
                if (!$id && isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $id = (int)$_GET['id'];
                }
                
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Service ID is required for update']);
                    exit;
                }
                
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/services/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $tmpName = $_FILES['image']['tmp_name'];
                    $originalName = basename($_FILES['image']['name']);
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (!in_array($ext, $allowed, true)) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Unsupported image format']);
                        exit;
                    }
                    
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($mime, $allowedMimes, true)) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Invalid image MIME type']);
                        exit;
                    }
                    
                    $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                    $filename = $safeBase . '_' . uniqid('', true) . '.' . $ext;
                    $targetPath = $uploadDir . $filename;
                    
                    if (!move_uploaded_file($tmpName, $targetPath)) {
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to save uploaded file']);
                        exit;
                    }
                    
                    // Store relative path
                    $input['image'] = 'uploads/services/' . $filename;
                }
                
                $controller->handlePut($id, $input);
            } else {
                // Expect JSON
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid JSON input']);
                    exit;
                }
                
                // Get ID from JSON body if not in path
                if (!$id && isset($input['id']) && is_numeric($input['id'])) {
                    $id = (int)$input['id'];
                }
                // Also allow ID from query string
                if (!$id && isset($_GET['id']) && is_numeric($_GET['id'])) {
                    $id = (int)$_GET['id'];
                }
                
                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Service ID is required for update']);
                    exit;
                }
                
                $controller->handlePut($id, $input);
            }
            break;
            
        case 'DELETE':
            // Support both path param (/services/{id}) and JSON body {"id": N}
            if (!$id) {
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() === JSON_ERROR_NONE && isset($input['id']) && is_numeric($input['id'])) {
                    $id = (int)$input['id'];
                }
            }
            
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Service ID is required for deletion']);
                exit;
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