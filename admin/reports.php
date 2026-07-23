<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pdo = db();

// ----- Summary stats -----
$totalStudents   = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$checkedIn       = $pdo->query("SELECT COUNT(*) FROM students WHERE status='checked_in'")->fetchColumn();
$checkedOut      = $pdo->query("SELECT COUNT(*) FROM students WHERE status='checked_out'")->fetchColumn();
$totalRooms      = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$availableRooms  = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status='available'")->fetchColumn();
$fullRooms       = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status='full'")->fetchColumn();
$maintenanceRooms= $pdo->query("SELECT COUNT(*) FROM rooms WHERE status='maintenance'")->fetchColumn();

$totalCollected  = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn();
$totalPending    = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='pending'")->fetchColumn();
$totalOverdue    = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='overdue'")->fetchColumn();

$openComplaints  = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status='open'")->fetchColumn();
$inProgressComp  = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status='in_progress'")->fetchColumn();
$resolvedComp    = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status='resolved'")->fetchColumn();

$totalVisitors   = $pdo->query("SELECT COUNT(*) FROM visitors")->fetchColumn();
$insideVisitors  = $pdo->query("SELECT COUNT(*) FROM visitors WHERE status='inside'")->fetchColumn();

// ----- Payment by type -----
$payByType = $pdo->query("SELECT payment_type, COUNT(*) as cnt, COALESCE(SUM(amount),0) as total FROM payments GROUP BY payment_type")->fetchAll();

// ----- Room occupancy -----
$roomOccupancy = $pdo->query("SELECT room_number, capacity, occupied, status FROM rooms ORDER BY room_number")->fetchAll();

$pageTitle = 'Reports';
$activePage = 'reports';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="card-header"><h3>Student Summary</h3></div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon blue">&#128100;</div><div class="stat-info"><h4><?= $totalStudents ?></h4><p>Total Students</p></div></div>
        <div class="stat-card"><div class="stat-icon green">&#9989;</div><div class="stat-info"><h4><?= $checkedIn ?></h4><p>Checked In</p></div></div>
        <div class="stat-card"><div class="stat-icon neutral">&#10060;</div><div class="stat-info"><h4><?= $checkedOut ?></h4><p>Checked Out</p></div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Room Summary</h3></div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon blue">&#127968;</div><div class="stat-info"><h4><?= $totalRooms ?></h4><p>Total Rooms</p></div></div>
        <div class="stat-card"><div class="stat-icon green">&#9989;</div><div class="stat-info"><h4><?= $availableRooms ?></h4><p>Available</p></div></div>
        <div class="stat-card"><div class="stat-icon red">&#10060;</div><div class="stat-info"><h4><?= $fullRooms ?></h4><p>Full</p></div></div>
        <div class="stat-card"><div class="stat-icon amber">&#128737;</div><div class="stat-info"><h4><?= $maintenanceRooms ?></h4><p>Maintenance</p></div></div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Room</th><th>Capacity</th><th>Occupied</th><th>Free</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($roomOccupancy as $r): ?>
                <tr>
                    <td><?= e($r['room_number']) ?></td>
                    <td><?= $r['capacity'] ?></td>
                    <td><?= $r['occupied'] ?></td>
                    <td><?= $r['capacity'] - $r['occupied'] ?></td>
                    <td><span class="badge <?= $r['status']==='available'?'badge-success':($r['status']==='full'?'badge-error':'badge-warning') ?>"><?= e($r['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Financial Summary</h3></div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon green">&#128176;</div><div class="stat-info"><h4>$<?= number_format($totalCollected,2) ?></h4><p>Collected</p></div></div>
        <div class="stat-card"><div class="stat-icon amber">&#9203;</div><div class="stat-info"><h4>$<?= number_format($totalPending,2) ?></h4><p>Pending</p></div></div>
        <div class="stat-card"><div class="stat-icon red">&#9888;</div><div class="stat-info"><h4>$<?= number_format($totalOverdue,2) ?></h4><p>Overdue</p></div></div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Payment Type</th><th>Count</th><th>Total Amount</th></tr></thead>
            <tbody>
            <?php foreach ($payByType as $p): ?>
                <tr><td><?= ucfirst(e(str_replace('_',' ',$p['payment_type']))) ?></td><td><?= $p['cnt'] ?></td><td>$<?= number_format($p['total'],2) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Complaints Summary</h3></div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon amber">&#128172;</div><div class="stat-info"><h4><?= $openComplaints ?></h4><p>Open</p></div></div>
        <div class="stat-card"><div class="stat-icon blue">&#128736;</div><div class="stat-info"><h4><?= $inProgressComp ?></h4><p>In Progress</p></div></div>
        <div class="stat-card"><div class="stat-icon green">&#9989;</div><div class="stat-info"><h4><?= $resolvedComp ?></h4><p>Resolved</p></div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Visitor Summary</h3></div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon blue">&#128101;</div><div class="stat-info"><h4><?= $totalVisitors ?></h4><p>Total Visitors</p></div></div>
        <div class="stat-card"><div class="stat-icon green">&#9989;</div><div class="stat-info"><h4><?= $insideVisitors ?></h4><p>Currently Inside</p></div></div>
    </div>
</div>

<div class="toolbar">
    <div class="toolbar-right">
        <button class="btn btn-outline" onclick="window.print()">&#128424; Print Report</button>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
