<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/env.php';

// Protect with a simple token from .env to avoid public misuse
$token = isset($_GET['token']) ? $_GET['token'] : '';
$expected = EnvLoader::get('ADMIN_SEED_TOKEN', null);
if (!$expected || $token !== $expected) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

header('Content-Type: application/json');

try {
    $userModel = new User($pdo);

    // If admin exists, just report
    $existing = $userModel->getByUsername('admin');
    if ($existing) {
        echo json_encode(['message' => 'Admin already exists']);
        exit;
    }

    $data = [
        'username' => 'admin',
        'password' => 'admin123',
        'role' => 'admin'
    ];

    $created = $userModel->create($data);
    unset($created['password']);
    echo json_encode(['message' => 'Admin seeded', 'user' => $created]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}