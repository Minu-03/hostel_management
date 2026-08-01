<?php
// ============================================================
// Database Configuration
// Update these values to match your MySQL server
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hostel_management');

// ============================================================
// Create and return a PDO connection
// ============================================================
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

// ============================================================
// Start a session if not already started
// ============================================================
function init_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// ============================================================
// Get the base URL of the application
// ============================================================
function base_url($path = '') {
    // Adjust this if the app is in a subfolder
    $base = '/hostel_management_final';
    return $base . '/' . ltrim($path, '/');
}
