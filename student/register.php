<?php
// 1. Connection Configuration
$host = 'localhost';
$dbname = 'hostel_management';
$db_user = 'root';
$db_pass = '';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Collect form inputs
    $student_number = trim($_POST['student_number']);
    $full_name      = trim($_POST['full_name']);
    $email          = trim($_POST['email']);
    $password       = trim($_POST['password']);
    $gender         = $_POST['gender'];
    $phone          = trim($_POST['phone']);
    $course         = trim($_POST['course']);
    $year_of_study  = intval($_POST['year_of_study']);

    if (empty($student_number) || empty($full_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if email or student number already exists
            $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkEmail->execute([$email]);

            $checkNum = $conn->prepare("SELECT id FROM students WHERE student_number = ?");
            $checkNum->execute([$student_number]);

            if ($checkEmail->fetch()) {
                $error = "This email address is already registered.";
            } elseif ($checkNum->fetch()) {
                $error = "This student number is already registered.";
            } else {
                // Begin Transaction to ensure both database tables insert successfully
                $conn->beginTransaction();

                // Hash password for security
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert into 'users' table (Auth details)
                $userSql = "INSERT INTO users (full_name, email, password, role, phone, status) VALUES (?, ?, ?, 'student', ?, 'active')";
                $userStmt = $conn->prepare($userSql);
                $userStmt->execute([$full_name, $email, $hashedPassword, $phone]);

                // Retrieve the newly created user's ID
                $userId = $conn->lastInsertId();

                // Insert into 'students' table (Profile details linked via user_id)
                $studentSql = "INSERT INTO students (user_id, student_number, full_name, gender, course, year_of_study, phone, email, status)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'checked_out')";
                $studentStmt = $conn->prepare($studentSql);
                $studentStmt->execute([$userId, $student_number, $full_name, $gender, $course, $year_of_study, $phone, $email]);

                // Commit both database changes
                $conn->commit();

                $message = "Registration successful! You can now log in.";
            }
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        button { width: 100%; padding: 12px; background-color: #007bff; border: none; color: white; font-size: 16px; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        button:hover { background-color: #0056b3; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="login-page">
    <div class="login-card">
        <h1>Student Registration</h1>

        <?php if ($message): ?>
            <div class="alert success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="student_number">Student Number *</label>
                    <input type="text" name="student_number" id="student_number" placeholder="e.g. ST12345" required>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" name="full_name" id="full_name" placeholder="John Doe" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" name="email" id="email" placeholder="student@example.com" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" name="phone" id="phone" placeholder="e.g. 0771234567">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select name="gender" id="gender" required>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year_of_study">Year of Study</label>
                    <input type="number" name="year_of_study" id="year_of_study" placeholder="e.g. 1" min="1" max="6">
                </div>
            </div>

            <div class="form-group">
                <label for="course">Course / Degree</label>
                <input type="text" name="course" id="course" placeholder="e.g. Computer Science">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" name="password" id="password" required>
                </div>
            </div>

            <button type="submit">Register Account</button>
        </form>

        <a href="login.php" class="login-link">Already have an account? Log In</a>
    </div>
</div>

</body>
</html>