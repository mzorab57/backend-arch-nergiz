<?php
require_once __DIR__ . '/../controllers/PortfolioImageController.php';
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

// Check if there's an ID in the path (e.g., /api/portfolio-images/1)
if (count($path_parts) >= 3 && is_numeric($path_parts[2])) {
    $id = (int)$path_parts[2];
}

try {
    // Create controller instance
    $controller = new PortfolioImageController($pdo);

    switch ($method) {
        case 'GET':
            $controller->handleGet($id);
            break;

        case 'POST':
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                // Handle file upload for single or multiple images
                $portfolio_id = isset($_POST['portfolio_id']) ? (int)$_POST['portfolio_id'] : null;
                if (!$portfolio_id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'portfolio_id is required']);
                    exit;
                }

                $uploadDir = __DIR__ . '/../uploads/portfolioImages/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $savedPaths = [];
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                // Helper to process a single file array
                $processFile = function($file) use ($uploadDir, $allowedExt, $allowedMimes) {
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        return null;
                    }
                    $tmpName = $file['tmp_name'];
                    $originalName = basename($file['name']);
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowedExt, true)) {
                        return null;
                    }
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);
                    if (!in_array($mime, $allowedMimes, true)) {
                        return null;
                    }
                    $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
                    $filename = $safeBase . '_' . uniqid('', true) . '.' . $ext;
                    $targetPath = $uploadDir . $filename;
                    if (!move_uploaded_file($tmpName, $targetPath)) {
                        return null;
                    }
                    return 'uploads/portfolioImages/' . $filename;
                };

                // Support single file under 'image'
                if (isset($_FILES['image'])) {
                    $path = $processFile($_FILES['image']);
                    if ($path) {
                        $savedPaths[] = $path;
                    }
                }
                // Support multiple files under 'images'
                if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                    $count = count($_FILES['images']['name']);
                    for ($i = 0; $i < $count; $i++) {
                        $file = [
                            'name' => $_FILES['images']['name'][$i],
                            'type' => $_FILES['images']['type'][$i],
                            'tmp_name' => $_FILES['images']['tmp_name'][$i],
                            'error' => $_FILES['images']['error'][$i],
                            'size' => $_FILES['images']['size'][$i],
                        ];
                        $path = $processFile($file);
                        if ($path) {
                            $savedPaths[] = $path;
                        }
                    }
                }

                if (empty($savedPaths)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'No valid image files uploaded']);
                    exit;
                }

                // Determine primary image among uploaded ones (optional)
                $primaryIndex = null;
                if (isset($_POST['primary_index']) && is_numeric($_POST['primary_index'])) {
                    $idx = (int)$_POST['primary_index'];
                    if ($idx >= 0 && $idx < count($savedPaths)) {
                        $primaryIndex = $idx;
                    }
                } elseif (isset($_POST['is_primary'])) {
                    // If only one image uploaded, allow is_primary flag
                    $isPrimaryFlag = filter_var($_POST['is_primary'], FILTER_VALIDATE_BOOLEAN);
                    if ($isPrimaryFlag && count($savedPaths) === 1) {
                        $primaryIndex = 0;
                    }
                }

                $input = [
                    'portfolio_id' => $portfolio_id,
                    'images' => $savedPaths,
                ];
                if ($primaryIndex !== null) {
                    $input['primary_index'] = $primaryIndex;
                }

                $controller->handlePost($input);
            } else {
                // Expect JSON (support single image string or multiple images array)
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid JSON input']);
                    exit;
                }
                $controller->handlePost($input);
            }
            break;

        case 'PUT':
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                // Handle file upload update (single image)
                $input = [];
                if (isset($_POST['portfolio_id']) && is_numeric($_POST['portfolio_id'])) {
                    $input['portfolio_id'] = (int)$_POST['portfolio_id'];
                }

                // Get ID from form-data if not in path
                if (!$id && isset($_POST['id']) && is_numeric($_POST['id'])) {
                    $id = (int)$_POST['id'];
                }

                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Portfolio image ID is required for update']);
                    exit;
                }

                // Optional is_primary flag
                if (isset($_POST['is_primary'])) {
                    $input['is_primary'] = filter_var($_POST['is_primary'], FILTER_VALIDATE_BOOLEAN);
                }

                // Process uploaded file under 'image'
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/portfolioImages/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $tmpName = $_FILES['image']['tmp_name'];
                    $originalName = basename($_FILES['image']['name']);
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($ext, $allowedExt, true)) {
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

                    // Store relative path in input
                    $input['image'] = 'uploads/portfolioImages/' . $filename;
                }

                // Delegate to controller
                $controller->handlePut($id, $input);
            } else {
                // Expect JSON
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
                        echo json_encode(['error' => 'Portfolio image ID is required for update']);
                        exit;
                    }
                }
                $controller->handlePut($id, $input);
            }
            break;

        case 'DELETE':
            // Support both path param and JSON body
            if (!$id) {
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() === JSON_ERROR_NONE && isset($input['id']) && is_numeric($input['id'])) {
                    $id = (int)$input['id'];
                }
            }
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Portfolio image ID is required for deletion']);
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