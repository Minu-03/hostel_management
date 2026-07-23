<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pdo = db();

$student = get_student_by_user($_SESSION['user_id']);
if (!$student) { header('Location: ' . base_url('logout.php')); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $guardian_name = trim($_POST['guardian_name'] ?? '');
        $guardian_phone = trim($_POST['guardian_phone'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year = (int)($_POST['year_of_study'] ?? 0);
        $pdo->prepare("UPDATE students SET phone=?, address=?, guardian_name=?, guardian_phone=?, course=?, year_of_study=? WHERE id=?")
            ->execute([$phone, $address, $guardian_name, $guardian_phone, $course, $year, $student['id']]);
        set_flash('success', 'Profile updated successfully.');
        header('Location: ' . base_url('student/profile.php')); exit;
    }
    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        $hash = $stmt->fetchColumn();
        if (!password_verify($current, $hash)) {
            set_flash('error', 'Current password is incorrect.');
        } elseif (strlen($new) < 6) {
            set_flash('error', 'New password must be at least 6 characters.');
        } else {
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['user_id']]);
            set_flash('success', 'Password changed successfully.');
        }
        header('Location: ' . base_url('student/profile.php')); exit;
    }
}

// Re-fetch
$student = get_student_by_user($_SESSION['user_id']);

$pageTitle = 'My Profile';
$activePage = 'profile';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="card-header"><h3>Personal Information</h3></div>
    <form method="POST" action="">
        <input type="hidden" name="action" value="update_profile">
        <div class="form-row">
            <div class="form-group"><label>Student Number</label><input type="text" class="form-control" value="<?= e($student['student_number']) ?>" disabled></div>
            <div class="form-group"><label>Full Name</label><input type="text" class="form-control" value="<?= e($student['full_name']) ?>" disabled></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Gender</label><input type="text" class="form-control" value="<?= ucfirst(e($student['gender'])) ?>" disabled></div>
            <div class="form-group"><label>Email</label><input type="email" class="form-control" value="<?= e($student['email']) ?>" disabled></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Course</label><input type="text" name="course" class="form-control" value="<?= e($student['course'] ?? '') ?>"></div>
            <div class="form-group"><label>Year of Study</label><input type="number" name="year_of_study" class="form-control" min="1" max="6" value="<?= e($student['year_of_study'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?= e($student['phone'] ?? '') ?>"></div>
            <div class="form-group"><label>Address</label><input type="text" name="address" class="form-control" value="<?= e($student['address'] ?? '') ?>"></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Guardian Name</label><input type="text" name="guardian_name" class="form-control" value="<?= e($student['guardian_name'] ?? '') ?>"></div>
            <div class="form-group"><label>Guardian Phone</label><input type="text" name="guardian_phone" class="form-control" value="<?= e($student['guardian_phone'] ?? '') ?>"></div>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h3>Change Password</h3></div>
    <form method="POST" action="" style="max-width:400px;">
        <input type="hidden" name="action" value="change_password">
        <div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
        <div class="form-group"><label>New Password</label><input type="password" name="new_password" class="form-control" required minlength="6"></div>
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
