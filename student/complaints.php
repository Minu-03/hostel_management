<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('student');
$pdo = db();

$student = get_student_by_user($_SESSION['user_id']);
if (!$student) { header('Location: ' . base_url('logout.php')); exit; }
$sid = $student['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $pdo->prepare("INSERT INTO complaints (student_id, category, subject, description, priority, status)
                       VALUES (?,?,?,?,?,'open')")
            ->execute([
                $sid,
                $_POST['category'] ?? 'other',
                trim($_POST['subject'] ?? ''),
                trim($_POST['description'] ?? ''),
                $_POST['priority'] ?? 'medium'
            ]);
        set_flash('success', 'Complaint submitted. Admin will review it shortly.');
        header('Location: ' . base_url('student/complaints.php')); exit;
    }
}

$complaints = $pdo->prepare("SELECT * FROM complaints WHERE student_id=? ORDER BY created_at DESC");
$complaints->execute([$sid]);
$complaints = $complaints->fetchAll();

$pageTitle = 'My Complaints';
$activePage = 'complaints';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="toolbar">
    <div class="toolbar-left"><div class="search-box"><input type="text" placeholder="Search complaints..." data-table-search="compTable"></div></div>
    <div class="toolbar-right"><button class="btn btn-primary" onclick="openModal('addModal')">+ New Complaint</button></div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table" id="compTable">
            <thead><tr><th>Subject</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th><th>Response</th></tr></thead>
            <tbody>
            <?php if (empty($complaints)): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="icon">&#128172;</div><h4>No complaints</h4><p>Submit a complaint if you have an issue.</p></div></td></tr>
            <?php else: foreach ($complaints as $c): ?>
                <tr>
                    <td><?= e($c['subject']) ?></td>
                    <td><?= ucfirst(e($c['category'])) ?></td>
                    <td><span class="badge badge-<?= $c['priority']==='urgent'?'error':($c['priority']==='high'?'warning':($c['priority']==='medium'?'info':'neutral')) ?>"><?= e($c['priority']) ?></span></td>
                    <td><span class="badge badge-<?= $c['status']==='resolved'?'success':($c['status']==='open'?'warning':($c['status']==='rejected'?'error':'info')) ?>"><?= e(str_replace('_',' ',$c['status'])) ?></span></td>
                    <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                    <td><?= e($c['response'] ?: '-') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addModal">
    <div class="modal modal-lg">
        <div class="modal-header"><h3>Submit New Complaint</h3><button class="modal-close" onclick="closeModal('addModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="create">
                <div class="form-row">
                    <div class="form-group"><label>Category</label><select name="category"><option value="maintenance">Maintenance</option><option value="cleanliness">Cleanliness</option><option value="noise">Noise</option><option value="security">Security</option><option value="electrical">Electrical</option><option value="plumbing">Plumbing</option><option value="other">Other</option></select></div>
                    <div class="form-group"><label>Priority</label><select name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
                </div>
                <div class="form-group"><label>Subject *</label><input type="text" name="subject" class="form-control" required></div>
                <div class="form-group"><label>Description *</label><textarea name="description" class="form-control" rows="5" required></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button><button type="submit" class="btn btn-primary">Submit Complaint</button></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
