<?php
require_once 'includes/auth.php';

// If already logged in, redirect to the appropriate dashboard
if (is_logged_in()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ' . base_url('admin/dashboard.php'));
    } else {
        header('Location: ' . base_url('student/dashboard.php'));
    }
    exit;
}

$error = '';
$typedEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = '';
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }
    $typedEmail = $email;

    $password = '';
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
    }

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } elseif (attempt_login($email, $password)) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: ' . base_url('admin/dashboard.php'));
        } else {
            header('Location: ' . base_url('student/dashboard.php'));
        }
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hostel Management System</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <h1>HostelMS</h1>
            <p>Hostel Management System</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="you@example.com" required autofocus
                       value="<?= e($typedEmail) ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>

        <div class="login-hint">
            Default admin: <code>admin@hostel.com</code> / <code>admin123</code><br>
            Student accounts are created by the admin.
        </div>
    </div>
</div>
</body>
</html>
