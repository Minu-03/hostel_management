<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pdo = db();

// ----- Handle form actions -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'create') {
        $full_name = trim($_POST['full_name']);
        $email = trim($_POST['email']);

        $password = $_POST['password'];
        if ($password === '') {
            $password = 'student123';
        }

        $student_number = trim($_POST['student_number']);
        $gender = $_POST['gender'];
        $course = trim($_POST['course']);
        $year = (int)$_POST['year_of_study'];
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $guardian_name = trim($_POST['guardian_name']);
        $guardian_phone = trim($_POST['guardian_phone']);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Step 1: create the login account in "users"
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, phone, status)
                                VALUES (?, ?, ?, 'student', ?, 'active')");
        $stmt->execute([$full_name, $email, $hashed_password, $phone]);

        // Grab the new user's id so we can link the student record to it
        $user_id = $pdo->lastInsertId();

        // Step 2: create the student profile linked to that user
        $stmt = $pdo->prepare("INSERT INTO students
            (user_id, student_number, full_name, gender, course, year_of_study, phone, email, address, guardian_name, guardian_phone, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'checked_out')");
        $stmt->execute([$user_id, $student_number, $full_name, $gender, $course, $year, $phone, $email, $address, $guardian_name, $guardian_phone]);

        set_flash('success', 'Student added successfully.');
        header('Location: ' . base_url('admin/students.php'));
        exit;
    }

    if ($action === 'update') {
        $id = (int)$_POST['id'];
        $full_name = trim($_POST['full_name']);
        $student_number = trim($_POST['student_number']);
        $gender = $_POST['gender'];
        $course = trim($_POST['course']);
        $year = (int)$_POST['year_of_study'];
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $guardian_name = trim($_POST['guardian_name']);
        $guardian_phone = trim($_POST['guardian_phone']);
        $status = $_POST['status'];

        $stmt = $pdo->prepare("UPDATE students SET
            full_name=?, student_number=?, gender=?, course=?, year_of_study=?, phone=?, email=?, address=?, guardian_name=?, guardian_phone=?, status=?
            WHERE id=?");
        $stmt->execute([$full_name, $student_number, $gender, $course, $year, $phone, $email, $address, $guardian_name, $guardian_phone, $status, $id]);

        set_flash('success', 'Student updated successfully.');
        header('Location: ' . base_url('admin/students.php'));
        exit;
    }

    if ($action === 'checkin') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE students SET status='checked_in', check_in_date=CURDATE(), check_out_date=NULL WHERE id=?");
        $stmt->execute([$id]);
        set_flash('success', 'Student checked in.');
        header('Location: ' . base_url('admin/students.php'));
        exit;
    }

    if ($action === 'checkout') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE students SET status='checked_out', check_out_date=CURDATE() WHERE id=?");
        $stmt->execute([$id]);
        set_flash('success', 'Student checked out.');
        header('Location: ' . base_url('admin/students.php'));
        exit;
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];

        // Find the linked user_id first
        $stmt = $pdo->prepare("SELECT user_id FROM students WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row) {
            $user_id = $row['user_id'];
            // Deleting the user also removes the student row (foreign key cascade)
            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$user_id]);
        }

        set_flash('success', 'Student deleted.');
        header('Location: ' . base_url('admin/students.php'));
        exit;
    }
}

// ----- Get all students for the table -----
$students = array();
$result = $pdo->query("SELECT * FROM students ORDER BY created_at DESC");
while ($row = $result->fetch()) {
    $students[] = $row;
}

$pageTitle = 'Student Management';
$activePage = 'students';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box">
            <input type="text" placeholder="Search students..." data-table-search="studentsTable">
        </div>
    </div>
    <div class="toolbar-right">
        <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Student</button>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table" id="studentsTable">
            <thead><tr><th>Student No.</th><th>Name</th><th>Gender</th><th>Course</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (count($students) === 0): ?>
                <tr><td colspan="7"><div class="empty-state"><div class="icon">&#128100;</div><h4>No students yet</h4><p>Click "Add Student" to create one.</p></div></td></tr>
            <?php else: ?>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= e($s['student_number']) ?></td>
                    <td><?= e($s['full_name']) ?></td>
                    <td><?= ucfirst(e($s['gender'])) ?></td>
                    <td>
                        <?php if ($s['course']) { echo e($s['course']); } else { echo '-'; } ?>
                    </td>
                    <td>
                        <?php if ($s['phone']) { echo e($s['phone']); } else { echo '-'; } ?>
                    </td>
                    <td>
                        <?php if ($s['status'] === 'checked_in'): ?>
                            <span class="badge badge-success"><?= e(str_replace('_', ' ', $s['status'])) ?></span>
                        <?php else: ?>
                            <span class="badge badge-neutral"><?= e(str_replace('_', ' ', $s['status'])) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <button class="btn btn-outline btn-sm" onclick="editStudent(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)">Edit</button>
                        <?php if ($s['status'] === 'checked_out'): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="checkin">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button class="btn btn-success btn-sm">Check-In</button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action" value="checkout">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button class="btn btn-warning btn-sm">Check-Out</button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" style="display:inline" data-confirm="Delete this student?">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal modal-lg">
        <div class="modal-header"><h3>Add New Student</h3><button class="modal-close" onclick="closeModal('addModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="create">
                <div class="form-row">
                    <div class="form-group"><label>Student Number *</label><input type="text" name="student_number" class="form-control" required></div>
                    <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Email (login) *</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Password</label><input type="text" name="password" class="form-control" value="student123"></div>
                </div>
                <div class="form-row-3">
                    <div class="form-group"><label>Gender</label><select name="gender"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
                    <div class="form-group"><label>Course</label><input type="text" name="course" class="form-control"></div>
                    <div class="form-group"><label>Year of Study</label><input type="number" name="year_of_study" class="form-control" min="1" max="6"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group"><label>Address</label><input type="text" name="address" class="form-control"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Guardian Name</label><input type="text" name="guardian_name" class="form-control"></div>
                    <div class="form-group"><label>Guardian Phone</label><input type="text" name="guardian_phone" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Student</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal modal-lg">
        <div class="modal-header"><h3>Edit Student</h3><button class="modal-close" onclick="closeModal('editModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <div class="form-group"><label>Student Number *</label><input type="text" name="student_number" id="edit_student_number" class="form-control" required></div>
                    <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" id="edit_full_name" class="form-control" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Gender</label><select name="gender" id="edit_gender"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></div>
                    <div class="form-group"><label>Status</label><select name="status" id="edit_status"><option value="checked_out">Checked Out</option><option value="checked_in">Checked In</option></select></div>
                </div>
                <div class="form-row-3">
                    <div class="form-group"><label>Course</label><input type="text" name="course" id="edit_course" class="form-control"></div>
                    <div class="form-group"><label>Year</label><input type="number" name="year_of_study" id="edit_year_of_study" class="form-control" min="1" max="6"></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone" id="edit_phone" class="form-control"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control"></div>
                    <div class="form-group"><label>Address</label><input type="text" name="address" id="edit_address" class="form-control"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Guardian Name</label><input type="text" name="guardian_name" id="edit_guardian_name" class="form-control"></div>
                    <div class="form-group"><label>Guardian Phone</label><input type="text" name="guardian_phone" id="edit_guardian_phone" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editStudent(s) {
    document.getElementById('edit_id').value = s.id;
    document.getElementById('edit_student_number').value = s.student_number;
    document.getElementById('edit_full_name').value = s.full_name;
    document.getElementById('edit_gender').value = s.gender;
    document.getElementById('edit_status').value = s.status;
    document.getElementById('edit_course').value = s.course || '';
    document.getElementById('edit_year_of_study').value = s.year_of_study || '';
    document.getElementById('edit_phone').value = s.phone || '';
    document.getElementById('edit_email').value = s.email || '';
    document.getElementById('edit_address').value = s.address || '';
    document.getElementById('edit_guardian_name').value = s.guardian_name || '';
    document.getElementById('edit_guardian_phone').value = s.guardian_phone || '';
    openModal('editModal');
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>