<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO rooms (room_number, block, floor_number, capacity, room_type, monthly_fee, status, description)
                               VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['room_number']),
            trim($_POST['block'] ?? ''),
            (int)($_POST['floor_number'] ?? 0),
            (int)($_POST['capacity'] ?? 1),
            $_POST['room_type'] ?? 'single',
            (float)($_POST['monthly_fee'] ?? 0),
            $_POST['status'] ?? 'available',
            trim($_POST['description'] ?? '')
        ]);
        set_flash('success', 'Room added successfully.');
        header('Location: ' . base_url('admin/rooms.php')); exit;
    }

    if ($action === 'update') {
        $stmt = $pdo->prepare("UPDATE rooms SET room_number=?, block=?, floor_number=?, capacity=?, room_type=?, monthly_fee=?, status=?, description=? WHERE id=?");
        $stmt->execute([
            trim($_POST['room_number']),
            trim($_POST['block'] ?? ''),
            (int)($_POST['floor_number'] ?? 0),
            (int)($_POST['capacity'] ?? 1),
            $_POST['room_type'] ?? 'single',
            (float)($_POST['monthly_fee'] ?? 0),
            $_POST['status'] ?? 'available',
            trim($_POST['description'] ?? ''),
            (int)($_POST['id'] ?? 0)
        ]);
        set_flash('success', 'Room updated successfully.');
        header('Location: ' . base_url('admin/rooms.php')); exit;
    }

    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM rooms WHERE id=?")->execute([(int)($_POST['id'] ?? 0)]);
        set_flash('success', 'Room deleted.');
        header('Location: ' . base_url('admin/rooms.php')); exit;
    }
}

$rooms = $pdo->query("SELECT * FROM rooms ORDER BY room_number")->fetchAll();

$pageTitle = 'Room Management';
$activePage = 'rooms';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="toolbar">
    <div class="toolbar-left">
        <div class="search-box"><input type="text" placeholder="Search rooms..." data-table-search="roomsTable"></div>
    </div>
    <div class="toolbar-right">
        <button class="btn btn-primary" onclick="openModal('addModal')">+ Add Room</button>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="data-table" id="roomsTable">
            <thead><tr><th>Room No.</th><th>Block</th><th>Floor</th><th>Type</th><th>Capacity</th><th>Occupied</th><th>Monthly Fee</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($rooms)): ?>
                <tr><td colspan="9"><div class="empty-state"><div class="icon">&#127968;</div><h4>No rooms yet</h4><p>Add a room to get started.</p></div></td></tr>
            <?php else: foreach ($rooms as $r): ?>
                <tr>
                    <td><?= e($r['room_number']) ?></td>
                    <td><?= e($r['block'] ?: '-') ?></td>
                    <td><?= e($r['floor_number'] ?: '-') ?></td>
                    <td><?= ucfirst(e($r['room_type'])) ?></td>
                    <td><?= $r['capacity'] ?></td>
                    <td><?= $r['occupied'] ?></td>
                    <td>$<?= number_format($r['monthly_fee'], 2) ?></td>
                    <td><span class="badge <?= $r['status']==='available'?'badge-success':($r['status']==='full'?'badge-error':'badge-warning') ?>"><?= e($r['status']) ?></span></td>
                    <td class="actions">
                        <button class="btn btn-outline btn-sm" onclick='editRoom(<?= json_encode($r, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'>Edit</button>
                        <form method="POST" style="display:inline" data-confirm="Delete this room?"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-danger btn-sm">Delete</button></form>
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
        <div class="modal-header"><h3>Add New Room</h3><button class="modal-close" onclick="closeModal('addModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="create">
                <div class="form-row">
                    <div class="form-group"><label>Room Number *</label><input type="text" name="room_number" class="form-control" required></div>
                    <div class="form-group"><label>Block</label><input type="text" name="block" class="form-control"></div>
                </div>
                <div class="form-row-3">
                    <div class="form-group"><label>Floor</label><input type="number" name="floor_number" class="form-control" min="0"></div>
                    <div class="form-group"><label>Capacity *</label><input type="number" name="capacity" class="form-control" value="1" min="1" required></div>
                    <div class="form-group"><label>Monthly Fee *</label><input type="number" name="monthly_fee" class="form-control" value="0.00" step="0.01" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Room Type</label><select name="room_type"><option value="single">Single</option><option value="double">Double</option><option value="triple">Triple</option><option value="dormitory">Dormitory</option></select></div>
                    <div class="form-group"><label>Status</label><select name="status"><option value="available">Available</option><option value="maintenance">Maintenance</option></select></div>
                </div>
                <div class="form-group"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button><button type="submit" class="btn btn-primary">Add Room</button></div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal modal-lg">
        <div class="modal-header"><h3>Edit Room</h3><button class="modal-close" onclick="closeModal('editModal')">&times;</button></div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-row">
                    <div class="form-group"><label>Room Number *</label><input type="text" name="room_number" id="edit_room_number" class="form-control" required></div>
                    <div class="form-group"><label>Block</label><input type="text" name="block" id="edit_block" class="form-control"></div>
                </div>
                <div class="form-row-3">
                    <div class="form-group"><label>Floor</label><input type="number" name="floor_number" id="edit_floor_number" class="form-control" min="0"></div>
                    <div class="form-group"><label>Capacity *</label><input type="number" name="capacity" id="edit_capacity" class="form-control" min="1" required></div>
                    <div class="form-group"><label>Monthly Fee *</label><input type="number" name="monthly_fee" id="edit_monthly_fee" class="form-control" step="0.01" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label>Room Type</label><select name="room_type" id="edit_room_type"><option value="single">Single</option><option value="double">Double</option><option value="triple">Triple</option><option value="dormitory">Dormitory</option></select></div>
                    <div class="form-group"><label>Status</label><select name="status" id="edit_status"><option value="available">Available</option><option value="full">Full</option><option value="maintenance">Maintenance</option></select></div>
                </div>
                <div class="form-group"><label>Description</label><textarea name="description" id="edit_description" class="form-control"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button><button type="submit" class="btn btn-primary">Save Changes</button></div>
        </form>
    </div>
</div>

<script>
function editRoom(r) {
    document.getElementById('edit_id').value = r.id;
    document.getElementById('edit_room_number').value = r.room_number;
    document.getElementById('edit_block').value = r.block || '';
    document.getElementById('edit_floor_number').value = r.floor_number || '';
    document.getElementById('edit_capacity').value = r.capacity;
    document.getElementById('edit_monthly_fee').value = r.monthly_fee;
    document.getElementById('edit_room_type').value = r.room_type;
    document.getElementById('edit_status').value = r.status;
    document.getElementById('edit_description').value = r.description || '';
    openModal('editModal');
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
