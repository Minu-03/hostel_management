<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $receipt = generate_receipt_number();
        $stmt = $pdo->prepare("INSERT INTO payments
            (student_id, amount, payment_type, payment_method, payment_date, month_for, status, receipt_number, notes)
            VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            (int)($_POST['student_id'] ?? 0),
            (float)($_POST['amount'] ?? 0),
            $_POST['payment_type'] ?? 'hostel_fee',
            $_POST['payment_method'] ?? 'cash',
            $_POST['payment_date'] ?? date('Y-m-d'),
            trim($_POST['month_for'] ?? ''),
            $_POST['status'] ?? 'paid',
            $receipt,
            trim($_POST['notes'] ?? '')
        ]);
        set_flash('success', 'Payment recorded. Receipt: ' . $receipt);
        header('Location: ' . base_url('admin/payments.php')); exit;
    }

    if ($action === 'update') {
        $stmt = $pdo->prepare("UPDATE payments SET student_id=?, amount=?, payment_type=?, payment_method=?, payment_date=?, month_for=?, status=?, notes=? WHERE id=?");
        $stmt->execute([
            (int)($_POST['student_id'] ?? 0),
            (float)($_POST['amount'] ?? 0),
            $_POST['payment_type'] ?? 'hostel_fee',
            $_POST['payment_method'] ?? 'cash',
            $_POST['payment_date'] ?? date('Y-m-d'),
            trim($_POST['month_for'] ?? ''),
            $_POST['status'] ?? 'paid',
            trim($_POST['notes'] ?? ''),
            (int)($_POST['id'] ?? 0)
        ]);
        set_flash('success', 'Payment updated.');
        header('Location: ' . base_url('admin/payments.php')); exit;
    }

    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM payments WHERE id=?")->execute([(int)($_POST['id'] ?? 0)]);
        set_flash('success', 'Payment deleted.');
        header('Location: ' . base_url('admin/payments.php')); exit;
    }
}

$payments = $pdo->query("SELECT p.*, s.full_name, s.student_number
                         FROM payments p
                         JOIN students s ON p.student_id = s.id
                         ORDER BY p.payment_date DESC")->fetchAll();
$students = $pdo->query("SELECT id, student_number, full_name FROM students ORDER BY full_name")->fetchAll();
$totalCollected = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'")->fetchColumn();
$totalPending   = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='pending'")->fetchColumn();

$pageTitle = 'Payment Management';
$activePage = 'payments';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon green">&#128176;</div><div class="stat-info"><h4>$<?= number_format($totalCollected,2) ?></h4><p>Total Collected</p></div></div>
    <div class="stat-card"><div class="stat-icon amber">&#9203;</div><div class="stat-info"><h4>$<?= number_format($totalPending,2) ?></h4><p>Pending</p></div></div>
</div>

<div class="toolbar">
    <div class="toolbar-left"><div class="search-box"><input type="text" placeholder="Search payments..." data-table-search="payTable"></div></div>
    <div class="toolbar-right"><button class="btn btn-primary" onclick="openModal('addModal')">+ Record Payment</button></div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table" id="payTable">
            <thead><tr><th>Receipt</th><th>Student</th><th>Amount</th><th>Type</th><th>Method</th><th>Date</th><th>Month</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="9"><div class="empty-state"><div class="icon">&#128176;</div><h4>No payments yet</h4><p>Record a payment to get started.</p></div></td></tr>
            <?php else: foreach ($payments as $p): ?>
                <tr>
                    <td><?= e($p['receipt_number'] ?: '-') ?></td>
                    <td><?= e($p['full_name']) ?></td>
                    <td>$<?= number_format($p['amount'],2) ?></td>
                    <td><?= ucfirst(e(str_replace('_',' ',$p['payment_type']))) ?></td>
                    <td><?= ucfirst(e($p['payment_method'])) ?></td>
                    <td><?= date('M d, Y', strtotime($p['payment_date'])) ?></td>
                    <td><?= e($p['month_for'] ?: '-') ?></td>
                    <td><span class="badge <?= $p['status']==='paid'?'badge-success':($p['status']==='pending'?'badge-warning':'badge-error') ?>"><?= e($p['status']) ?></span></td>
                    <td class="actions">
                        <button class="btn btn-outline btn-sm" onclick='editPayment(<?= json_encode($p, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>Edit</button>
                        <form method="POST" style="display:inline" data-confirm="Delete this payment?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal modal-lg">
        <div class="modal-header"><h3>Record Payment</h3><button class="modal-close" onclick="closeModal('addModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="create">
                <div class="form-group"><label>Student *</label><select name="student_id" required><option value="">Select...</option><?php foreach ($students as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['student_number'].' - '.$s['full_name']) ?></option><?php endforeach; ?></select></div>
                <div class="form-row">
                    <div class="form-group"><label>Amount *</label><input type="number" name="amount" class="form-control" step="0.01" required></div>
                    <div class="form-group"><label>Payment Date *</label><input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                </div>
                <div class="form-row-3">
                    <div class="form-group"><label>Type</label><select name="payment_type"><option value="hostel_fee">Hostel Fee</option><option value="security_deposit">Security Deposit</option><option value="utility">Utility</option><option value="fine">Fine</option><option value="other">Other</option></select></div>
                    <div class="form-group"><label>Method</label><select name="payment_method"><option value="cash">Cash</option><option value="card">Card</option><option value="bank_transfer">Bank Transfer</option><option value="online">Online</option></select></div>
                    <div class="form-group"><label>Status</label><select name="status"><option value="paid">Paid</option><option value="pending">Pending</option><option value="overdue">Overdue</option></select></div>
                </div>
                <div class="form-group"><label>Month For</label><input type="text" name="month_for" class="form-control" placeholder="e.g. January 2025"></div>
                <div class="form-group"><label>Notes</label><textarea name="notes" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button><button type="submit" class="btn btn-primary">Record</button></div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal modal-lg">
        <div class="modal-header"><h3>Edit Payment</h3><button class="modal-close" onclick="closeModal('editModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group"><label>Student *</label><select name="student_id" id="edit_student_id" required><option value="">Select...</option><?php foreach ($students as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['student_number'].' - '.$s['full_name']) ?></option><?php endforeach; ?></select></div>
                <div class="form-row">
                    <div class="form-group"><label>Amount *</label><input type="number" name="amount" id="edit_amount" class="form-control" step="0.01" required></div>
                    <div class="form-group"><label>Payment Date *</label><input type="date" name="payment_date" id="edit_payment_date" class="form-control" required></div>
                </div>
                <div class="form-row-3">
                    <div class="form-group"><label>Type</label><select name="payment_type" id="edit_payment_type"><option value="hostel_fee">Hostel Fee</option><option value="security_deposit">Security Deposit</option><option value="utility">Utility</option><option value="fine">Fine</option><option value="other">Other</option></select></div>
                    <div class="form-group"><label>Method</label><select name="payment_method" id="edit_payment_method"><option value="cash">Cash</option><option value="card">Card</option><option value="bank_transfer">Bank Transfer</option><option value="online">Online</option></select></div>
                    <div class="form-group"><label>Status</label><select name="status" id="edit_status"><option value="paid">Paid</option><option value="pending">Pending</option><option value="overdue">Overdue</option></select></div>
                </div>
                <div class="form-group"><label>Month For</label><input type="text" name="month_for" id="edit_month_for" class="form-control"></div>
                <div class="form-group"><label>Notes</label><textarea name="notes" id="edit_notes" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

<script>
function editPayment(p) {
    document.getElementById('edit_id').value = p.id;
    document.getElementById('edit_student_id').value = p.student_id;
    document.getElementById('edit_amount').value = p.amount;
    document.getElementById('edit_payment_date').value = p.payment_date;
    document.getElementById('edit_payment_type').value = p.payment_type;
    document.getElementById('edit_payment_method').value = p.payment_method;
    document.getElementById('edit_status').value = p.status;
    document.getElementById('edit_month_for').value = p.month_for || '';
    document.getElementById('edit_notes').value = p.notes || '';
    openModal('editModal');
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
