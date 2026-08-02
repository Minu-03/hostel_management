<?php
// admin/rooms.php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$pdo = db();

// Fetch block & floor from request
$selectedBlock = $_GET['block'] ?? 'M';
$selectedFloor = isset($_GET['floor']) ? (int)$_GET['floor'] : 1;

// Fetch filtered rooms for blueprint
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE block = ? AND floor_number = ? ORDER BY room_number");
$stmt->execute([$selectedBlock, $selectedFloor]);
$rooms = $stmt->fetchAll();

$pageTitle = 'Room Grid Blueprint';
$activePage = 'rooms';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
    .blueprint-controls { display: flex; gap: 20px; background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 25px; align-items: center; }
    .blueprint-controls label { font-weight: bold; margin-right: 8px; color: #444; }
    .blueprint-controls select { padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }

    .blueprint-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 15px; margin-top: 20px; }
    .room-card { background: #fff; border: 2px solid #ccc; border-radius: 8px; padding: 15px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: 0.2s; }
    .room-card.available { border-color: #28a745; background-color: #e8f5e9; }
    .room-card.full { border-color: #dc3545; background-color: #ffebee; }
    .room-card.maintenance { border-color: #6c757d; background-color: #f5f5f5; }

    .room-title { font-size: 1.2rem; font-weight: bold; margin-bottom: 5px; color: #333; }
    .room-meta { font-size: 0.85rem; color: #666; margin-bottom: 8px; }
    .room-badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
    .badge-av { background: #d4edda; color: #155724; }
    .badge-full { background: #f8d7da; color: #721c24; }
    .badge-maint { background: #e2e3e5; color: #383d41; }
</style>

<div class="blueprint-controls">
    <form method="GET" action="" style="display: flex; gap: 15px; width: 100%; align-items: center;">
        <div>
            <label for="block">Select Block:</label>
            <select name="block" id="block" onchange="this.form.submit()">
                <option value="M" <?= $selectedBlock === 'M' ? 'selected' : '' ?>>Male (Block M)</option>
                <option value="F" <?= $selectedBlock === 'F' ? 'selected' : '' ?>>Female (Block F)</option>
            </select>
        </div>
        <div>
            <label for="floor">Select Floor:</label>
            <select name="floor" id="floor" onchange="this.form.submit()">
                <option value="1" <?= $selectedFloor === 1 ? 'selected' : '' ?>>Floor 1 (Dormitories)</option>
                <option value="2" <?= $selectedFloor === 2 ? 'selected' : '' ?>>Floor 2 (Dormitories)</option>
                <option value="3" <?= $selectedFloor === 3 ? 'selected' : '' ?>>Floor 3 (Triple Rooms)</option>
                <option value="4" <?= $selectedFloor === 4 ? 'selected' : '' ?>>Floor 4 (Double Rooms)</option>
                <option value="5" <?= $selectedFloor === 5 ? 'selected' : '' ?>>Floor 5 (Single Rooms)</option>
            </select>
        </div>
        <div style="margin-left: auto; font-size: 14px; font-weight: bold; color: #555;">
            Total Rooms Shown: <?= count($rooms) ?>
        </div>
    </form>
</div>

<h3 style="margin-bottom: 10px;">Floor Layout Overview</h3>
<div class="blueprint-grid">
    <?php if (empty($rooms)): ?>
        <div style="grid-column: 1 / -1; text-align: center; color: #888; padding: 40px;">No rooms found in Block <?= htmlspecialchars($selectedBlock) ?> Floor <?= $selectedFloor ?>.</div>
    <?php else: foreach ($rooms as $r):
        $isFull = ($r['occupied'] >= $r['capacity']) || ($r['status'] === 'full');
        $isMaint = ($r['status'] === 'maintenance');
        $cardClass = $isMaint ? 'maintenance' : ($isFull ? 'full' : 'available');
    ?>
        <div class="room-card <?= $cardClass ?>">
            <div class="room-title"><?= e($r['room_number']) ?></div>
            <div class="room-meta">
                Type: <strong><?= ucfirst(e($r['room_type'])) ?></strong><br>
                Occupied: <strong><?= $r['occupied'] ?>/<?= $r['capacity'] ?></strong><br>
                Rate: <strong>Rs. <?= number_format($r['monthly_fee']) ?></strong>
            </div>
            <div>
                <?php if ($isMaint): ?>
                    <span class="room-badge badge-maint">Maintenance</span>
                <?php elseif ($isFull): ?>
                    <span class="room-badge badge-full">Full</span>
                <?php else: ?>
                    <span class="room-badge badge-av">Available</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>