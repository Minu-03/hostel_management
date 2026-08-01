<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pdo = db();

// ----- Total students -----
$result = $pdo->query("SELECT COUNT(*) AS total FROM students");
$row = $result->fetch();
$totalStudents = $row['total'];

// ----- Checked-in students -----
$result = $pdo->query("SELECT COUNT(*) AS total FROM students WHERE status='checked_in'");
$row = $result->fetch();
$checkedInStudents = $row['total'];

// ----- Total rooms -----
$result = $pdo->query("SELECT COUNT(*) AS total FROM rooms");
$row = $result->fetch();
$totalRooms = $row['total'];

// ----- Available rooms -----
$result = $pdo->query("SELECT COUNT(*) AS total FROM rooms WHERE status='available'");
$row = $result->fetch();
$availableRooms = $row['total'];

// ----- Active allocations -----
$result = $pdo->query("SELECT COUNT(*) AS total FROM allocations WHERE status='active'");
$row = $result->fetch();
$activeAllocations = $row['total'];

// ----- Open complaints -----
$result = $pdo->query("SELECT COUNT(*) AS total FROM complaints WHERE status='open'");
$row = $result->fetch();
$openComplaints = $row['total'];

// ----- Pending payments -----
$result = $pdo->query("SELECT COUNT(*) AS total FROM payments WHERE status='pending'");
$row = $result->fetch();
$pendingPayments = $row['total'];

// ----- Visitors currently inside -----
$result = $pdo->query("SELECT COUNT(*) AS total FROM visitors WHERE status='checked_in'");
$row = $result->fetch();
$visitorsInside = $row['total'];

// ----- Recently added students (last 5) -----
$recentStudents = array();
$result = $pdo->query("SELECT student_number, full_name, status, created_at
                        FROM students ORDER BY created_at DESC LIMIT 5");
while ($row = $result->fetch()) {
    $recentStudents[] = $row;
}

// ----- Recent complaints (last 5), with the student's name -----
$recentComplaints = array();
$result = $pdo->query("SELECT c.id, c.subject, c.status, c.priority, c.created_at, s.full_name
                        FROM complaints c
                        JOIN students s ON c.student_id = s.id
                        ORDER BY c.created_at DESC LIMIT 5");
while ($row = $result->fetch()) {
    $recentComplaints[] = $row;
}

$pageTitle = 'Admin Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">&#128100;</div><div class="stat-info"><h3><?= $totalStudents ?></h3><p>Total Students</p></div></div>
    <div class="stat-card"><div class="stat-icon">&#9989;</div><div class="stat-info"><h3><?= $checkedInStudents ?></h3><p>Checked In</p></div></div>
    <div class="stat-card"><div class="stat-icon">&#127968;</div><div class="stat-info"><h3><?= $totalRooms ?></h3><p>Total Rooms</p></div></div>
    <div class="stat-card"><div class="stat-icon">&#128274;</div><div class="stat-info"><h3><?= $availableRooms ?></h3><p>Available Rooms</p></div></div>
    <div class="stat-card"><div class="stat-icon">&#128203;</div><div class="stat-info"><h3><?= $activeAllocations ?></h3><p>Active Allocations</p></div></div>
    <div class="stat-card"><div class="stat-icon">&#128172;</div><div class="stat-info"><h3><?= $openComplaints ?></h3><p>Open Complaints</p></div></div>
    <div class="stat-card"><div class="stat-icon">&#128176;</div><div class="stat-info"><h3><?= $pendingPayments ?></h3><p>Pending Payments</p></div></div>
    <div class="stat-card"><div class="stat-icon">&#128101;</div><div class="stat-info"><h3><?= $visitorsInside ?></h3><p>Visitors Inside</p></div></div>
</div>

<div class="card">
    <div class="card-header"><h3>Recently Added Students</h3></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Student No.</th><th>Name</th><th>Status</th><th>Added On</th></tr></thead>
            <tbody>
            <?php if (count($recentStudents) === 0): ?>
                <tr><td colspan="4">No students yet</td></tr>
            <?php else: ?>
                <?php foreach ($recentStudents as $s): ?>
                <tr>
                    <td><?= e($s['student_number']) ?></td>
                    <td><?= e($s['full_name']) ?></td>
                    <td>
                        <?php if ($s['status'] === 'checked_in'): ?>
                            <span class="badge badge-success"><?= e($s['status']) ?></span>
                        <?php else: ?>
                            <span class="badge badge-neutral"><?= e($s['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Recent Complaints</h3></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Subject</th><th>Student</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (count($recentComplaints) === 0): ?>
                <tr><td colspan="5">No complaints yet</td></tr>
            <?php else: ?>
                <?php foreach ($recentComplaints as $c): ?>
                <tr>
                    <td><?= e($c['subject']) ?></td>
                    <td><?= e($c['full_name']) ?></td>
                    <td>
                        <?php
                        if ($c['priority'] === 'urgent') {
                            $priorityClass = 'badge-error';
                        } elseif ($c['priority'] === 'high') {
                            $priorityClass = 'badge-warning';
                        } else {
                            $priorityClass = 'badge-neutral';
                        }
                        ?>
                        <span class="badge <?= $priorityClass ?>"><?= e($c['priority']) ?></span>
                    </td>
                    <td>
                        <?php
                        if ($c['status'] === 'resolved') {
                            $statusClass = 'badge-success';
                        } elseif ($c['status'] === 'open') {
                            $statusClass = 'badge-error';
                        } else {
                            $statusClass = 'badge-neutral';
                        }
                        ?>
                        <span class="badge <?= $statusClass ?>"><?= e($c['status']) ?></span>
                    </td>
                    <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
