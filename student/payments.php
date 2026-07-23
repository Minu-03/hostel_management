<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pdo = db();

$student = get_student_by_user($_SESSION['user_id']);
if (!$student) { header('Location: ' . base_url('logout.php')); exit; }
$sid = $student['id'];

$payments = $pdo->prepare("SELECT * FROM payments WHERE student_id=? ORDER BY payment_date DESC");
$payments->execute([$sid]);
$payments = $payments->fetchAll();

$paid = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE student_id=? AND status='paid'");
$paid->execute([$sid]); $paidTotal = $paid->fetchColumn();
$pending = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE student_id=? AND status='pending'");
$pending->execute([$sid]); $pendingTotal = $pending->fetchColumn();

$pageTitle = 'My Payments';
$activePage = 'payments';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon green">&#128176;</div><div class="stat-info"><h4>$<?= number_format($paidTotal,2) ?></h4><p>Total Paid</p></div></div>
    <div class="stat-card"><div class="stat-icon amber">&#9203;</div><div class="stat-info"><h4>$<?= number_format($pendingTotal,2) ?></h4><p>Pending</p></div></div>
</div>

<div class="card">
    <div class="card-header"><h3>Payment History</h3></div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Receipt</th><th>Type</th><th>Amount</th><th>Method</th><th>Date</th><th>Month</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="7"><div class="empty-state"><div class="icon">&#128176;</div><h4>No payments</h4><p>Your payment history will appear here.</p></div></td></tr>
            <?php else: foreach ($payments as $p): ?>
                <tr>
                    <td><?= e($p['receipt_number'] ?: '-') ?></td>
                    <td><?= ucfirst(e(str_replace('_',' ',$p['payment_type']))) ?></td>
                    <td>$<?= number_format($p['amount'],2) ?></td>
                    <td><?= ucfirst(e($p['payment_method'])) ?></td>
                    <td><?= date('M d, Y', strtotime($p['payment_date'])) ?></td>
                    <td><?= e($p['month_for'] ?: '-') ?></td>
                    <td><span class="badge <?= $p['status']==='paid'?'badge-success':($p['status']==='pending'?'badge-warning':'badge-error') ?>"><?= e($p['status']) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
