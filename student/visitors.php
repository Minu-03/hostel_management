<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pdo = db();

/*$student = get_student_by_user($_SESSION['user_id']);
if (!$student) { header('Location: ' . base_url('logout.php')); exit; }
$sid = $student['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $pdo->prepare("INSERT INTO visitors (student_id, visitor_name, visitor_phone, relation, purpose, check_in, status)
                       VALUES (?,?,?,?,?,?,'inside')")
            ->execute([
                $sid,
                trim($_POST['visitor_name'] ?? ''),
                trim($_POST['visitor_phone'] ?? ''),
                trim($_POST['relation'] ?? ''),
                trim($_POST['purpose'] ?? ''),
                $_POST['check_in'] ?? date('Y-m-d H:i:s')
            ]);
        set_flash('success', 'Visitor checked in.');
        header('Location: ' . base_url('student/visitors.php')); exit;
    }
    if ($action === 'checkout') {
        $pdo->prepare("UPDATE visitors SET check_out=NOW(), status='left' WHERE id=? AND student_id=?")
            ->execute([(int)($_POST['id'] ?? 0), $sid]);
        set_flash('success', 'Visitor checked out.');
        header('Location: ' . base_url('student/visitors.php')); exit;
    }
}*/

$visitors = $pdo->prepare("SELECT * FROM visitors WHERE student_id=? ORDER BY check_in DESC");
$visitors->execute([$sid]);
$visitors = $visitors->fetchAll();

$pageTitle = 'My Visitors';
$activePage = 'visitors';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="toolbar">
    <div class="toolbar-left"><div class="search-box"><input type="text" placeholder="Search visitors..." data-table-search="visTable"></div></div>
    <div class="toolbar-right"><button class="btn btn-primary" onclick="openModal('addModal')">+ Check-In Visitor</button></div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table" id="visTable">
            <thead><tr><th>Visitor Name</th><th>Phone</th><th>Relation</th><th>Purpose</th><th>Check-In</th><th>Check-Out</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (empty($visitors)): ?>
                <tr><td colspan="8"><div class="empty-state"><div class="icon">&#128101;</div><h4>No visitors</h4><p>Check in a visitor to get started.</p></div></td></tr>
            <?php else: foreach ($visitors as $v): ?>
                <tr>
                    <td><?= e($v['visitor_name']) ?></td>
                    <td><?= e($v['visitor_phone'] ?: '-') ?></td>
                    <td><?= e($v['relation'] ?: '-') ?></td>
                    <td><?= e($v['purpose'] ?: '-') ?></td>
                    <td><?= date('M d, Y g:i A', strtotime($v['check_in'])) ?></td>
                    <td><?= $v['check_out'] ? date('M d, Y g:i A', strtotime($v['check_out'])) : '-' ?></td>
                    <td><span class="badge <?= $v['status']==='inside'?'badge-success':'badge-neutral' ?>"><?= e($v['status']) ?></span></td>
                    <td>
                        <?php if ($v['status']==='inside'): ?>
                            <form method="POST" style="display:inline"><input type="hidden" name="action" value="checkout"><input type="hidden" name="id" value="<?= $v['id'] ?>"><button class="btn btn-warning btn-sm">Check-Out</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header"><h3>Check-In Visitor</h3><button class="modal-close" onclick="closeModal('addModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="create">
                <div class="form-row">
                    <div class="form-group"><label>Visitor Name *</label><input type="text" name="visitor_name" class="form-control" required></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="visitor_phone" class="form-control"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Relation</label><input type="text" name="relation" class="form-control" placeholder="e.g. Parent"></div>
                    <div class="form-group"><label>Check-In *</label><input type="datetime-local" name="check_in" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required></div>
                </div>
                <div class="form-group"><label>Purpose</label><textarea name="purpose" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button><button type="submit" class="btn btn-primary">Check-In</button></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
