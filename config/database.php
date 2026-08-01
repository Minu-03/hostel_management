<?php
// ============================================================
// Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'hostel_management');   // Your database name
define('DB_USER', 'root');                // MySQL username
define('DB_PASS', '');                    // MySQL password

// ============================================================
// Database Connection
// ============================================================

function db()
{
    static $pdo = null;

    if ($pdo === null) {

        try {

            $dsn = "mysql:host=" . DB_HOST .
                   ";dbname=" . DB_NAME .
                   ";charset=utf8mb4";

            $pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

        } catch (PDOException $e) {

            die("Database Connection Failed!<br>" . $e->getMessage());

        }

    }

    return $pdo;
}

// ============================================================
// Start Session
// ============================================================

function init_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// ============================================================
// Base URL
// ============================================================

function base_url($path = '')
{
    $base = "http://localhost/hostel_management_final";

    return rtrim($base, '/') . '/' . ltrim($path, '/');
}
