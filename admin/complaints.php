<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'respond') {
        $id       = (int)($_POST['id'] ?? 0);
        $status   = $_POST['status'] ?? 'open';
        $response = trim($_POST['response'] ?? '');
        $pdo->prepare("UPDATE complaints SET status=?, response=? WHERE id=?")->execute([$status, $response, $id]);
        set_flash('success', 'Complaint updated.');
        header('Location: ' . base_url('admin/complaints.php')); exit;
    }

    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM complaints WHERE id=?")->execute([(int)($_POST['id'] ?? 0)]);
        set_flash('success', 'Complaint deleted.');
        header('Location: ' . base_url('admin/complaints.php')); exit;
    }
}

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT c.*, s.full_name, s.student_number
        FROM complaints c JOIN students s ON c.student_id = s.id";
if ($statusFilter) $sql .= " WHERE c.status = " . $pdo->quote($statusFilter);
$sql .= " ORDER BY c.created_at DESC";
$complaints = $pdo->query($sql)->fetchAll();

$pageTitle = 'Complaint Management';
$activePage = 'complaints';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box"><input type="text" placeholder="Search complaints..." data-table-search="compTable"></div>
        <select class="filter-select" onchange="window.location.href='<?= base_url('admin/complaints.php') ?>?status='+this.value">
            <option value="">All Statuses</option>
            <option value="open" <?= $statusFilter==='open'?'selected':'' ?>>Open</option>
            <option value="in_progress" <?= $statusFilter==='in_progress'?'selected':'' ?>>In Progress</option>
            <option value="resolved" <?= $statusFilter==='resolved'?'selected':'' ?>>Resolved</option>
            <option value="rejected" <?= $statusFilter==='rejected'?'selected':'' ?>>Rejected</option>
        </select>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table" id="compTable">
            <thead><tr><th>Student</th><th>Category</th><th>Subject</th><th>Priority</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($complaints)): ?>
                <tr><td colspan="7"><div class="empty-state"><div class="icon">&#128172;</div><h4>No complaints</h4><p>Complaints from students will appear here.</p></div></td></tr>
            <?php else: foreach ($complaints as $c): ?>
                <tr>
                    <td><?= e($c['full_name']) ?><br><small><?= e($c['student_number']) ?></small></td>
                    <td><?= ucfirst(e($c['category'])) ?></td>
                    <td><?= e($c['subject']) ?></td>
                    <td><span class="badge badge-<?= $c['priority']==='urgent'?'error':($c['priority']==='high'?'warning':($c['priority']==='medium'?'info':'neutral')) ?>"><?= e($c['priority']) ?></span></td>
                    <td><span class="badge badge-<?= $c['status']==='resolved'?'success':($c['status']==='open'?'warning':($c['status']==='rejected'?'error':'info')) ?>"><?= e(str_replace('_',' ',$c['status'])) ?></span></td>
                    <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                    <td class="actions">
                        <button class="btn btn-outline btn-sm" onclick='viewComplaint(<?= json_encode($c, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>Respond</button>
                        <form method="POST" style="display:inline" data-confirm="Delete this complaint?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Respond Modal -->
<div class="modal-overlay" id="respondModal">
    <div class="modal modal-lg">
        <div class="modal-header"><h3>Respond to Complaint</h3><button class="modal-close" onclick="closeModal('respondModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="respond">
                <input type="hidden" name="id" id="resp_id">
                <div class="detail-grid">
                    <div class="detail-item"><label>Student</label><span id="resp_student"></span></div>
                    <div class="detail-item"><label>Category</label><span id="resp_category"></span></div>
                    <div class="detail-item"><label>Subject</label><span id="resp_subject"></span></div>
                    <div class="detail-item"><label>Priority</label><span id="resp_priority"></span></div>
                </div>
                <div class="form-group"><label>Description</label><div class="form-control" id="resp_description" style="background:#f7fafc;min-height:60px;"></div></div>
                <div class="form-group"><label>Response</label><textarea name="response" id="resp_response" class="form-control" required></textarea></div>
                <div class="form-group"><label>Status</label><select name="status" id="resp_status"><option value="open">Open</option><option value="in_progress">In Progress</option><option value="resolved">Resolved</option><option value="rejected">Rejected</option></select></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('respondModal')">Cancel</button><button type="submit" class="btn btn-primary">Submit Response</button></div>
        </form>
    </div>
</div>

<script>
function viewComplaint(c) {
    document.getElementById('resp_id').value = c.id;
    document.getElementById('resp_student').textContent = c.full_name + ' (' + c.student_number + ')';
    document.getElementById('resp_category').textContent = c.category;
    document.getElementById('resp_subject').textContent = c.subject;
    document.getElementById('resp_priority').textContent = c.priority;
    document.getElementById('resp_description').textContent = c.description;
    document.getElementById('resp_response').value = c.response || '';
    document.getElementById('resp_status').value = c.status;
    openModal('respondModal');
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
