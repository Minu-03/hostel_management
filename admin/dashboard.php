<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

// ----- Stats -----
$pdo = db();
$totalStudents   = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$checkedIn       = $pdo->query("SELECT COUNT(*) FROM students WHERE status='checked_in'")->fetchColumn();
$totalRooms      = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$availableRooms  = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status='available'")->fetchColumn();
$activeAlloc     = $pdo->query("SELECT COUNT(*) FROM allocations WHERE status='active'")->fetchColumn();
$openComplaints  = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status IN ('open','in_progress')")->fetchColumn();
$pendingPayments = $pdo->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();
$insideVisitors  = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status='inside'")->fetchColumn();

// ----- Recent students -----
$recentStudents = $pdo->query("SELECT student_number, full_name, status, created_at
                               FROM students ORDER BY created_at DESC LIMIT 5")->fetchAll();

// ----- Recent complaints -----
$recentComplaints = $pdo->query("SELECT c.id, c.subject, c.status, c.priority, c.created_at, s.full_name
                                 FROM complaints c
                                 JOIN students s ON c.student_id = s.id
                                 ORDER BY c.created_at DESC LIMIT 5")->fetchAll();

$pageTitle = 'Admin Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">&#128100;</div>
        <div class="stat-info"><h4><?= $totalStudents ?></h4><p>Total Students</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">&#127968;</div>
        <div class="stat-info"><h4><?= $availableRooms ?>/<?= $totalRooms ?></h4><p>Available Rooms</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber">&#128203;</div>
        <div class="stat-info"><h4><?= $activeAlloc ?></h4><p>Active Allocations</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">&#128172;</div>
        <div class="stat-info"><h4><?= $openComplaints ?></h4><p>Open Complaints</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">&#128176;</div>
        <div class="stat-info"><h4><?= $pendingPayments ?></h4><p>Pending Payments</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">&#128101;</div>
        <div class="stat-info"><h4><?= $insideVisitors ?></h4><p>Visitors Inside</p></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recently Added Students</h3>
        <a href="<?= base_url('admin/students.php') ?>" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Student No.</th><th>Name</th><th>Status</th><th>Added</th></tr></thead>
            <tbody>
            <?php if (empty($recentStudents)): ?>
                <tr><td colspan="4" class="empty-state">No students yet.</td></tr>
            <?php else: foreach ($recentStudents as $s): ?>
                <tr>
                    <td><?= e($s['student_number']) ?></td>
                    <td><?= e($s['full_name']) ?></td>
                    <td><span class="badge <?= $s['status']==='checked_in'?'badge-success':'badge-neutral' ?>"><?= e($s['status']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Complaints</h3>
        <a href="<?= base_url('admin/complaints.php') ?>" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Student</th><th>Subject</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (empty($recentComplaints)): ?>
                <tr><td colspan="5" class="empty-state">No complaints yet.</td></tr>
            <?php else: foreach ($recentComplaints as $c): ?>
                <tr>
                    <td><?= e($c['full_name']) ?></td>
                    <td><?= e($c['subject']) ?></td>
                    <td><span class="badge badge-<?= $c['priority']==='urgent'?'error':($c['priority']==='high'?'warning':'neutral') ?>"><?= e($c['priority']) ?></span></td>
                    <td><span class="badge badge-<?= $c['status']==='resolved'?'success':($c['status']==='open'?'warning':'info') ?>"><?= e($c['status']) ?></span></td>
                    <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>



