<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pdo = db();

$student = get_student_by_user($_SESSION['user_id']);
if (!$student) {
    set_flash('error', 'Your student record was not found. Contact admin.');
    header('Location: ' . base_url('logout.php')); exit;
}
$sid = $student['id'];

// Allocation
$alloc = $pdo->prepare("SELECT a.*, r.room_number, r.block, r.room_type, r.monthly_fee
                        FROM allocations a JOIN rooms r ON a.room_id = r.id
                        WHERE a.student_id=? AND a.status='active' LIMIT 1");
$alloc->execute([$sid]);
$allocation = $alloc->fetch();

// Payment stats
$paidTotal = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE student_id=? AND status='paid'");
$paidTotal->execute([$sid]);
$paid = $paidTotal->fetchColumn();
$pendingTotal = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE student_id=? AND status='pending'");
$pendingTotal->execute([$sid]);
$pending = $pendingTotal->fetchColumn();

// Complaint stats
$openComp = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE student_id=? AND status IN ('open','in_progress')");
$openComp->execute([$sid]);
$openCount = $openComp->fetchColumn();

// Recent complaints
$recentComp = $pdo->prepare("SELECT * FROM complaints WHERE student_id=? ORDER BY created_at DESC LIMIT 3");
$recentComp->execute([$sid]);
$recentComplaints = $recentComp->fetchAll();

$pageTitle = 'My Dashboard';
$activePage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon blue">&#127968;</div><div class="stat-info"><h4><?= $allocation ? e($allocation['room_number']) : 'None' ?></h4><p>My Room</p></div></div>
    <div class="stat-card"><div class="stat-icon green">&#128176;</div><div class="stat-info"><h4>$<?= number_format($paid,2) ?></h4><p>Total Paid</p></div></div>
    <div class="stat-card"><div class="stat-icon amber">&#9203;</div><div class="stat-info"><h4>$<?= number_format($pending,2) ?></h4><p>Pending</p></div></div>
    <div class="stat-card"><div class="stat-icon red">&#128172;</div><div class="stat-info"><h4><?= $openCount ?></h4><p>Open Complaints</p></div></div>
</div>

<?php if ($allocation): ?>
<div class="card">
    <div class="card-header"><h3>My Room Allocation</h3></div>
    <div class="detail-grid">
        <div class="detail-item"><label>Room Number</label><span><?= e($allocation['room_number']) ?></span></div>
        <div class="detail-item"><label>Block</label><span><?= e($allocation['block'] ?: '-') ?></span></div>
        <div class="detail-item"><label>Room Type</label><span><?= ucfirst(e($allocation['room_type'])) ?></span></div>
        <div class="detail-item"><label>Monthly Fee</label><span>$<?= number_format($allocation['monthly_fee'],2) ?></span></div>
        <div class="detail-item"><label>Allocated Date</label><span><?= date('M d, Y', strtotime($allocation['allocated_date'])) ?></span></div>
        <div class="detail-item"><label>Status</label><span><span class="badge badge-success">Active</span></span></div>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="empty-state"><div class="icon">&#127968;</div><h4>No room allocated</h4><p>Contact the hostel admin to get a room assigned.</p></div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h3>Recent Complaints</h3><a href="<?= base_url('student/complaints.php') ?>" class="btn btn-outline btn-sm">View All</a></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php if (empty($recentComplaints)): ?>
                <tr><td colspan="5"><div class="empty-state"><p>No complaints filed yet.</p></div></td></tr>
            <?php else: foreach ($recentComplaints as $c): ?>
                <tr>
                    <td><?= e($c['subject']) ?></td>
                    <td><?= ucfirst(e($c['category'])) ?></td>
                    <td><span class="badge badge-<?= $c['priority']==='urgent'?'error':($c['priority']==='high'?'warning':'neutral') ?>"><?= e($c['priority']) ?></span></td>
                    <td><span class="badge badge-<?= $c['status']==='resolved'?'success':($c['status']==='open'?'warning':'info') ?>"><?= e(str_replace('_',' ',$c['status'])) ?></span></td>
                    <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
