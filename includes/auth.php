<?php
// ============================================================
// Authentication & Session Helpers
// ============================================================

require_once __DIR__ . '/../config/database.php';

init_session();

// -----------------------------------------------------------
// Check if a user is logged in
// -----------------------------------------------------------
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// -----------------------------------------------------------
// Require login - redirect to login page if not logged in
// -----------------------------------------------------------
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . base_url('index.php'));
        exit;
    }
}

// -----------------------------------------------------------
// Require a specific role (admin or student)
// -----------------------------------------------------------
function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        // Redirect to their own dashboard
        if ($_SESSION['role'] === 'admin') {
            header('Location: ' . base_url('admin/dashboard.php'));
        } else {
            header('Location: ' . base_url('student/dashboard.php'));
        }
        exit;
    }
}

// -----------------------------------------------------------
// Get current user info from session
// -----------------------------------------------------------
function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id'        => $_SESSION['user_id'],
        'name'      => $_SESSION['full_name'],
        'email'     => $_SESSION['email'],
        'role'      => $_SESSION['role'],
    ];
}

// -----------------------------------------------------------
// Attempt to log in a user
// -----------------------------------------------------------
function attempt_login($email, $password) {
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND status = "active" LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) return false;
    if (!password_verify($password, $user['password'])) return false;

    $_SESSION['user_id']    = $user['id'];
    $_SESSION['full_name']  = $user['full_name'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['role']      = $user['role'];

    // Update last login timestamp if you wish
    return true;
}

// -----------------------------------------------------------
// Log out the current user
// -----------------------------------------------------------
function logout() {
    $_SESSION = [];
    session_destroy();
}

// -----------------------------------------------------------
// Generate a random receipt number
// -----------------------------------------------------------
function generate_receipt_number() {
    return 'RCP-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
}

// -----------------------------------------------------------
// Flash message helpers
// -----------------------------------------------------------
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// -----------------------------------------------------------
// Sanitize output
// -----------------------------------------------------------
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// -----------------------------------------------------------
// Get the student record linked to a user id
// -----------------------------------------------------------
function get_student_by_user($user_id) {
    $stmt = db()->prepare('SELECT * FROM students WHERE user_id = ? LIMIT 1');
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}
