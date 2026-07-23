<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'allocate') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        $room_id    = (int)($_POST['room_id'] ?? 0);
        $date       = $_POST['allocated_date'] ?? date('Y-m-d');

        // Check room availability
        $room = $pdo->prepare("SELECT * FROM rooms WHERE id=? AND status='available'");
        $room->execute([$room_id]);
        $roomData = $room->fetch();
        if (!$roomData || $roomData['occupied'] >= $roomData['capacity']) {
            set_flash('error', 'Room is not available or full.');
            header('Location: ' . base_url('admin/allocations.php')); exit;
        }

        // Check student doesn't already have active allocation
        $existing = $pdo->prepare("SELECT id FROM allocations WHERE student_id=? AND status='active'");
        $existing->execute([$student_id]);
        if ($existing->fetch()) {
            set_flash('error', 'Student already has an active allocation.');
            header('Location: ' . base_url('admin/allocations.php')); exit;
        }

        $pdo->beginTransaction();
        $pdo->prepare("INSERT INTO allocations (student_id, room_id, allocated_date, status) VALUES (?,?,?,'active')")
            ->execute([$student_id, $room_id, $date]);
        $pdo->prepare("UPDATE rooms SET occupied = occupied + 1, status = CASE WHEN occupied + 1 >= capacity THEN 'full' ELSE status END WHERE id=?")
            ->execute([$room_id]);
        $pdo->prepare("UPDATE students SET status='checked_in', check_in_date=? WHERE id=?")->execute([$date, $student_id]);
        $pdo->commit();
        set_flash('success', 'Room allocated successfully.');
        header('Location: ' . base_url('admin/allocations.php')); exit;
    }

    if ($action === 'vacate') {
        $alloc_id = (int)($_POST['id'] ?? 0);
        $alloc = $pdo->prepare("SELECT * FROM allocations WHERE id=?");
        $alloc->execute([$alloc_id]);
        $a = $alloc->fetch();
        if ($a) {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE allocations SET status='vacated', vacate_date=CURDATE() WHERE id=?")->execute([$alloc_id]);
            $pdo->prepare("UPDATE rooms SET occupied = GREATEST(occupied - 1, 0), status='available' WHERE id=?")->execute([$a['room_id']]);
            $pdo->prepare("UPDATE students SET status='checked_out', check_out_date=CURDATE() WHERE id=?")->execute([$a['student_id']]);
            $pdo->commit();
            set_flash('success', 'Allocation vacated.');
        }
        header('Location: ' . base_url('admin/allocations.php')); exit;
    }
}

$allocations = $pdo->query("SELECT a.*, s.full_name, s.student_number, r.room_number, r.block
                            FROM allocations a
                            JOIN students s ON a.student_id = s.id
                            JOIN rooms r ON a.room_id = r.id
                            ORDER BY a.allocated_date DESC")->fetchAll();

$students = $pdo->query("SELECT id, student_number, full_name FROM students ORDER BY full_name")->fetchAll();
$rooms   = $pdo->query("SELECT id, room_number, block, capacity, occupied FROM rooms WHERE status='available' ORDER BY room_number")->fetchAll();

$pageTitle = 'Room Allocations';
$activePage = 'allocations';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="toolbar">
    <div class="toolbar-left"><div class="search-box"><input type="text" placeholder="Search allocations..." data-table-search="allocTable"></div></div>
    <div class="toolbar-right"><button class="btn btn-primary" onclick="openModal('allocModal')">+ Allocate Room</button></div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table" id="allocTable">
            <thead><tr><th>Student</th><th>Student No.</th><th>Room</th><th>Block</th><th>Allocated Date</th><th>Vacate Date</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (empty($allocations)): ?>
                <tr><td colspan="8"><div class="empty-state"><div class="icon">&#128203;</div><h4>No allocations yet</h4><p>Allocate a room to a student.</p></div></td></tr>
            <?php else: foreach ($allocations as $a): ?>
                <tr>
                    <td><?= e($a['full_name']) ?></td>
                    <td><?= e($a['student_number']) ?></td>
                    <td><?= e($a['room_number']) ?></td>
                    <td><?= e($a['block'] ?: '-') ?></td>
                    <td><?= date('M d, Y', strtotime($a['allocated_date'])) ?></td>
                    <td><?= $a['vacate_date'] ? date('M d, Y', strtotime($a['vacate_date'])) : '-' ?></td>
                    <td><span class="badge <?= $a['status']==='active'?'badge-success':'badge-neutral' ?>"><?= e($a['status']) ?></span></td>
                    <td>
                        <?php if ($a['status']==='active'): ?>
                            <form method="POST" style="display:inline" data-confirm="Vacate this allocation?"><input type="hidden" name="action" value="vacate"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn btn-warning btn-sm">Vacate</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Allocate Modal -->
<div class="modal-overlay" id="allocModal">
    <div class="modal">
        <div class="modal-header"><h3>Allocate Room</h3><button class="modal-close" onclick="closeModal('allocModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="allocate">
                <div class="form-group">
                    <label>Student *</label>
                    <select name="student_id" required>
                        <option value="">Select student...</option>
                        <?php foreach ($students as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['student_number'] . ' - ' . $s['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room *</label>
                    <select name="room_id" required>
                        <option value="">Select room...</option>
                        <?php foreach ($rooms as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['room_number'] . ' - Block ' . ($r['block'] ?: 'N/A') . ' (' . $r['occupied'] . '/' . $r['capacity'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Allocated Date *</label><input type="date" name="allocated_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('allocModal')">Cancel</button><button type="submit" class="btn btn-primary">Allocate</button></div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
