<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/CategoryController.php';

$controller = new CategoryController($pdo);
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);

$controller->handle($method, $data ?? []);