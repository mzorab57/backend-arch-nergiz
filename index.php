<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get the request URI and remove the base path
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/api-nergiz';
$path = str_replace($base_path, '', $request_uri);

// Remove query string from path
$path = strtok($path, '?');

// Route dispatcher
switch (true) {
    case $path === '/categories':
        require_once __DIR__ . '/routes/categories.php';
        break;
        
    case $path === '/services':
        require_once __DIR__ . '/routes/services.php';
        break;
        
    case $path === '/portfolio':
        require_once __DIR__ . '/routes/portfolio.php';
        break;
        
    case $path === '/contacts':
        require_once __DIR__ . '/routes/contacts.php';
        break;
        
    case $path === '/portfolio-images':
        require_once __DIR__ . '/routes/portfolio_images.php';
        break;
        
    case preg_match('#^/users(/.*)?$#', $path):
        require_once __DIR__ . '/routes/users.php';
        break;
        
    case $path === '/auth/login':
        require_once __DIR__ . '/auth/login.php';
        break;
        
    case $path === '/auth/logout':
        require_once __DIR__ . '/auth/logout.php';
        break;
        
    case $path === '/auth/me':
        require_once __DIR__ . '/auth/me.php';
        break;
        
    case $path === '/auth/refresh':
        require_once __DIR__ . '/auth/refresh.php';
        break;
        
    case '/':
    case '':
        // API documentation or welcome message
        header("Content-Type: application/json");
        echo json_encode([
            "message" => "Welcome to Nergiz Architecture API",
            "version" => "1.0",
            "endpoints" => [
                "GET /categories" => "Get all categories",
                "POST /categories" => "Create new category",
                "PUT /categories" => "Update category",
                "DELETE /categories" => "Delete category",
                "GET /services" => "Get all services",
                "GET /services?category_id=X" => "Get services by category",
                "POST /services" => "Create new service",
                "PUT /services" => "Update service",
                "DELETE /services" => "Delete service",
                "GET /portfolio" => "Get all portfolio items",
                "GET /portfolio?category_id=X" => "Get portfolio by category",
                "POST /portfolio" => "Create new portfolio item",
                "PUT /portfolio" => "Update portfolio item",
                "DELETE /portfolio" => "Delete portfolio item",
                "GET /contacts" => "Get all contacts",
                "GET /contacts?unread=true" => "Get unread contacts",
                "POST /contacts" => "Create new contact",
                "PUT /contacts" => "Update contact or mark as read",
                "DELETE /contacts" => "Delete contact",
                "POST /auth/login" => "User login",
                "POST /auth/logout" => "User logout",
                "GET /auth/me" => "Get current user info",
                "POST /auth/refresh" => "Refresh JWT token",
                "GET /users" => "Get all users (Admin only)",
                "GET /users/{id}" => "Get specific user (Admin only)",
                "POST /users" => "Create new user (Admin only)",
                "PUT /users/{id}" => "Update user (Admin only)",
                "DELETE /users/{id}" => "Delete user (Admin only)",
                "POST /users/{id}/change-password" => "Change user password"
            ]
        ]);
        break;
        
    default:
        header("Content-Type: application/json");
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
        break;
}