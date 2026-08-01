<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pdo = db();

// ----- Handle form actions -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'allocate') {
        $student_id = (int)$_POST['student_id'];
        $room_id = (int)$_POST['room_id'];
        $date = $_POST['allocated_date'];
        if ($date === '') {
            $date = date('Y-m-d');
        }

        // Check the room is available and not already full
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id=? AND status='available'");
        $stmt->execute([$room_id]);
        $roomData = $stmt->fetch();

        $roomIsFull = false;
        if ($roomData) {
            if ($roomData['occupied'] >= $roomData['capacity']) {
                $roomIsFull = true;
            }
        }

        if (!$roomData || $roomIsFull) {
            set_flash('error', 'Room is not available or full.');
            header('Location: ' . base_url('admin/allocations.php'));
            exit;
        }

        // Check the student doesn't already have an active allocation
        $stmt = $pdo->prepare("SELECT id FROM allocations WHERE student_id=? AND status='active'");
        $stmt->execute([$student_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            set_flash('error', 'Student already has an active allocation.');
            header('Location: ' . base_url('admin/allocations.php'));
            exit;
        }

        // Create the allocation
        $stmt = $pdo->prepare("INSERT INTO allocations (student_id, room_id, allocated_date, status) VALUES (?, ?, ?, 'active')");
        $stmt->execute([$student_id, $room_id, $date]);

        // Work out the room's new occupied count and status in PHP, then save it
        $newOccupied = $roomData['occupied'] + 1;
        if ($newOccupied >= $roomData['capacity']) {
            $newStatus = 'full';
        } else {
            $newStatus = 'available';
        }
        $stmt = $pdo->prepare("UPDATE rooms SET occupied=?, status=? WHERE id=?");
        $stmt->execute([$newOccupied, $newStatus, $room_id]);

        // Mark the student as checked in
        $stmt = $pdo->prepare("UPDATE students SET status='checked_in', check_in_date=? WHERE id=?");
        $stmt->execute([$date, $student_id]);

        set_flash('success', 'Room allocated successfully.');
        header('Location: ' . base_url('admin/allocations.php'));
        exit;
    }

    if ($action === 'vacate') {
        $alloc_id = (int)$_POST['id'];

        $stmt = $pdo->prepare("SELECT * FROM allocations WHERE id=?");
        $stmt->execute([$alloc_id]);
        $a = $stmt->fetch();

        if ($a) {
            // Mark the allocation as vacated
            $stmt = $pdo->prepare("UPDATE allocations SET status='vacated', vacate_date=CURDATE() WHERE id=?");
            $stmt->execute([$alloc_id]);

            // Work out the room's new occupied count in PHP
            $stmt = $pdo->prepare("SELECT occupied FROM rooms WHERE id=?");
            $stmt->execute([$a['room_id']]);
            $room = $stmt->fetch();

            $newOccupied = $room['occupied'] - 1;
            if ($newOccupied < 0) {
                $newOccupied = 0;
            }
            $stmt = $pdo->prepare("UPDATE rooms SET occupied=?, status='available' WHERE id=?");
            $stmt->execute([$newOccupied, $a['room_id']]);

            // Mark the student as checked out
            $stmt = $pdo->prepare("UPDATE students SET status='checked_out', check_out_date=CURDATE() WHERE id=?");
            $stmt->execute([$a['student_id']]);

            set_flash('success', 'Allocation vacated.');
        }

        header('Location: ' . base_url('admin/allocations.php'));
        exit;
    }
}

// ----- Get all allocations, with student and room info -----
$allocations = array();
$result = $pdo->query("SELECT a.*, s.full_name, s.student_number, r.room_number, r.block
                        FROM allocations a
                        JOIN students s ON a.student_id = s.id
                        JOIN rooms r ON a.room_id = r.id
                        ORDER BY a.allocated_date DESC");
while ($row = $result->fetch()) {
    $allocations[] = $row;
}

// ----- Get all students (for the dropdown) -----
$students = array();
$result = $pdo->query("SELECT id, student_number, full_name FROM students ORDER BY full_name");
while ($row = $result->fetch()) {
    $students[] = $row;
}

// ----- Get only available rooms (for the dropdown) -----
$rooms = array();
$result = $pdo->query("SELECT id, room_number, block, capacity, occupied FROM rooms WHERE status='available' ORDER BY room_number");
while ($row = $result->fetch()) {
    $rooms[] = $row;
}

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
            <?php if (count($allocations) === 0): ?>
                <tr><td colspan="8"><div class="empty-state"><div class="icon">&#128203;</div><h4>No allocations yet</h4><p>Allocate a room to a student.</p></div></td></tr>
            <?php else: ?>
                <?php foreach ($allocations as $a): ?>
                <tr>
                    <td><?= e($a['full_name']) ?></td>
                    <td><?= e($a['student_number']) ?></td>
                    <td><?= e($a['room_number']) ?></td>
                    <td>
                        <?php if ($a['block']) { echo e($a['block']); } else { echo '-'; } ?>
                    </td>
                    <td><?= date('M d, Y', strtotime($a['allocated_date'])) ?></td>
                    <td>
                        <?php if ($a['vacate_date']) { echo date('M d, Y', strtotime($a['vacate_date'])); } else { echo '-'; } ?>
                    </td>
                    <td>
                        <?php if ($a['status'] === 'active'): ?>
                            <span class="badge badge-success"><?= e($a['status']) ?></span>
                        <?php else: ?>
                            <span class="badge badge-neutral"><?= e($a['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($a['status'] === 'active'): ?>
                            <form method="POST" style="display:inline" data-confirm="Vacate this allocation?">
                                <input type="hidden" name="action" value="vacate">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button class="btn btn-warning btn-sm">Vacate</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
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
                            <?php
                            $blockLabel = $r['block'];
                            if (!$blockLabel) {
                                $blockLabel = 'N/A';
                            }
                            ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['room_number'] . ' - Block ' . $blockLabel . ' (' . $r['occupied'] . '/' . $r['capacity'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>Allocated Date *</label><input type="date" name="allocated_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('allocModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Allocate</button>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>