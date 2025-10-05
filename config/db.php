<?php
// Load environment variables
require_once __DIR__ . '/env.php';

$host = EnvLoader::get('DB_HOST', 'localhost');
$user = EnvLoader::get('DB_USER', 'root');
$pass = EnvLoader::get('DB_PASS', '');
$dbname = EnvLoader::get('DB_NAME', 'arch-nergiz');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => $e->getMessage()]));
}