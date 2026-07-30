<?php
// Setup Script: Run once to set up the default admin account.

require_once __DIR__ . '/config/database.php';

echo "<h2>Hostel Management System - Setup</h2>";

// 1. Connect to Database
try {
    $pdo = db();
    echo "<p style='color: green;'>✓ Database connected successfully.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✕ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please ensure you have imported <code>database/schema.sql</code> first.</p>";
    exit;
}

// 2. Setup Admin Credentials
$adminEmail = 'admin@hostel.com';
$adminPass  = 'admin123';
$hashedPass = password_hash($adminPass, PASSWORD_DEFAULT);

// 3. Create or Update Admin Account
$query = "INSERT INTO users (full_name, email, password, role, status)
          VALUES ('System Administrator', ?, ?, 'admin', 'active')
          ON DUPLICATE KEY UPDATE password = VALUES(password)";

$stmt = $pdo->prepare($query);
$stmt->execute([$adminEmail, $hashedPass]);

echo "<p style='color: green;'>✓ Admin account is ready.</p>";

// 4. Show Login Info & Security Reminder
?>
<h3>Default Login Credentials</h3>
<p><strong>Email:</strong> admin@hostel.com</p>
<p><strong>Password:</strong> admin123</p>

<p style="color: orange;">
    <strong>Warning:</strong> Delete this file (<code>setup.php</code>) immediately after setup for security!
</p>

<p><a href="index.php">Go to Login Page &rarr;</a></p>
