<?php
// ============================================================
// Setup Script - Run this ONCE after importing schema.sql
// It generates a proper password hash for the default admin
// and lets you verify the database connection.
// ============================================================

require_once __DIR__ . '/config/database.php';

echo "<h2>Hostel Management System - Setup</h2>";

// 1. Test connection
try {
    $pdo = db();
    echo "<p style='color:green;'>Database connection: OK</p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Make sure you have imported <code>database/schema.sql</code> into MySQL first.</p>";
    exit;
}

// 2. Set the admin password hash correctly
$adminEmail = 'admin@hostel.com';
$adminPass  = 'admin123';
$hash = password_hash($adminPass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->execute([$hash, $adminEmail]);

if ($stmt->rowCount() > 0) {
    echo "<p style='color:green;'>Admin password set successfully.</p>";
} else {
    // Admin row might not exist yet - create it
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, status)
                           VALUES ('System Administrator', ?, ?, 'admin', 'active')
                           ON DUPLICATE KEY UPDATE password = VALUES(password)");
    $stmt->execute([$adminEmail, $hash]);
    echo "<p style='color:green;'>Admin account created/updated successfully.</p>";
}

echo "<h3>Default Login Credentials</h3>";
echo "<p><strong>Email:</strong> admin@hostel.com<br><strong>Password:</strong> admin123</p>";
echo "<p style='color:orange;'><strong>Important:</strong> Delete this file (setup.php) after running it once for security.</p>";
echo "<p><a href='index.php'>Go to Login Page</a></p>";
