<?php
require_once __DIR__ . '/require_role.php';

/**
 * Middleware to protect admin-only routes
 * @return array|false - User data if admin, false otherwise
 */
function protectAdmin() {
    return requireRole('admin');
}

/**
 * Check if user is admin without stopping execution
 * @return array|null - User data if admin, null otherwise
 */
function checkAdmin() {
    return checkRole('admin');
}

/**
 * Middleware function that can be easily included in routes
 * Usage: $admin = protectAdminRoute(); if (!$admin) exit;
 */
function protectAdminRoute() {
    $user = protectAdmin();
    if (!$user) {
        exit; // Stop execution if not admin
    }
    return $user;
}
?>