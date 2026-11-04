<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_admin();

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$initial = strtoupper(substr($admin_name, 0, 1));

$action = $_GET['action'] ?? 'list';
$success_msg = '';
$error_msg = '';

// Handle POST submissions (Save / Delete / Status Toggle)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf()) {
        $error_msg = 'Security token expired. Please refresh and try again.';
    } else {
        $op = $_POST['operation'] ?? '';

        // 1. Save (Create or Update) Junction
        if ($op === 'save_junction') {
            if ($pdo === null) {
                $error_msg = 'Database connection offline.';
            } else {
                $j_id = (int)($_POST['junction_id'] ?? 0);
                $code = strtoupper(trim((string)($_POST['junction_code'] ?? '')));
                $name = trim((string)($_POST['name'] ?? ''));
                $address = trim((string)($_POST['address'] ?? ''));
                $zone = trim((string)($_POST['zone'] ?? 'Central'));
                $lat = (float)($_POST['latitude'] ?? 37.774929);
                $lng = (float)($_POST['longitude'] ?? -122.419416);
                $status = ($_POST['status'] === 'inactive') ? 'inactive' : 'active';
                $daily_volume = (int)($_POST['daily_volume'] ?? 0);
                $cameras_count = (int)($_POST['cameras_count'] ?? 4);
                $min_green = (int)($_POST['min_green_seconds'] ?? 10);
                $max_green = (int)($_POST['max_green_seconds'] ?? 60);
                $yellow = (int)($_POST['yellow_duration_seconds'] ?? 4);
                $cycle_mode = in_array($_POST['cycle_mode'], ['adaptive', 'fixed', 'emergency_standby'], true) ? $_POST['cycle_mode'] : 'adaptive';
                $image_url = trim((string)($_POST['image_url'] ?? ''));

                if ($code === '' || $name === '' || $address === '') {
                    $error_msg = 'Junction code, name, and address are mandatory fields.';
                } else {
                    try {
                        if ($j_id > 0) {
                            $stmt = $pdo->prepare("UPDATE junctions SET junction_code=?, name=?, address=?, zone=?, latitude=?, longitude=?, status=?, daily_volume=?, cameras_count=?, min_green_seconds=?, max_green_seconds=?, yellow_duration_seconds=?, cycle_mode=?, image_url=? WHERE id=?");
                            $stmt->execute([$code, $name, $address, $zone, $lat, $lng, $status, $daily_volume, $cameras_count, $min_green, $max_green, $yellow, $cycle_mode, $image_url, $j_id]);
                            log_admin_activity('EDIT_JUNCTION', "Updated junction {$code} ({$name})");
                            $success_msg = "Junction {$code} updated successfully!";
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO junctions (junction_code, name, address, zone, latitude, longitude, status, daily_volume, cameras_count, min_green_seconds, max_green_seconds, yellow_duration_seconds, cycle_mode, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$code, $name, $address, $zone, $lat, $lng, $status, $daily_volume, $cameras_count, $min_green, $max_green, $yellow, $cycle_mode, $image_url]);
                            $new_id = $pdo->lastInsertId();
                            log_admin_activity('ADD_JUNCTION', "Added new junction {$code} ({$name})");
                            $success_msg = "Junction {$code} configured and added to network!";
                        }
                        $action = 'list';
                    } catch (PDOException $e) {
                        $error_msg = 'Database error: ' . $e->getMessage();
                    }
                }
            }
        }

        // 2. Toggle Status (Active / Inactive)
        elseif ($op === 'toggle_status') {
            if ($pdo !== null) {
                $j_id = (int)($_POST['junction_id'] ?? 0);
                $new_status = ($_POST['new_status'] === 'active') ? 'active' : 'inactive';
                $stmt = $pdo->prepare("UPDATE junctions SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $j_id]);
                log_admin_activity('JUNCTION_STATUS', "Changed junction #{$j_id} status to {$new_status}");
                $success_msg = "Junction #{$j_id} marked as {$new_status}.";
            }
        }

        // 3. Delete Junction
        elseif ($op === 'delete_junction') {
            if ($pdo !== null) {
                $j_id = (int)($_POST['junction_id'] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM junctions WHERE id = ?");
                $stmt->execute([$j_id]);
                log_admin_activity('DELETE_JUNCTION', "Deleted junction #{$j_id} from network");
                $success_msg = "Junction #{$j_id} removed from network configuration.";
            }
        }
    }
}

// Fetch junctions with optional search and status filter
$search_query = trim((string)($_GET['q'] ?? ''));
$filter_status = trim((string)($_GET['status'] ?? ''));
$junctions_list = [];

if ($pdo !== null) {
    try {
        $sql = "SELECT * FROM junctions WHERE 1=1";
        $params = [];

        if ($search_query !== '') {
            $sql .= " AND (junction_code LIKE ? OR name LIKE ? OR address LIKE ? OR zone LIKE ?)";
            $term = "%{$search_query}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($filter_status === 'active' || $filter_status === 'inactive') {
            $sql .= " AND status = ?";
            $params[] = $filter_status;
        }

        $sql .= " ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $junctions_list = $stmt->fetchAll();
    } catch (Throwable $e) {}
}

// If editing or viewing history for a junction
$edit_j = null;
$history_logs = [];
$view_history_j = null;

if ($action === 'edit' && isset($_GET['id']) && $pdo !== null) {
    $stmt = $pdo->prepare("SELECT * FROM junctions WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $edit_j = $stmt->fetch();
} elseif ($action === 'history' && isset($_GET['id']) && $pdo !== null) {
    $stmt = $pdo->prepare("SELECT * FROM junctions WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $view_history_j = $stmt->fetch();

    if ($view_history_j) {
        $log_stmt = $pdo->prepare("SELECT * FROM traffic_logs WHERE junction_id = ? ORDER BY recorded_date DESC, hour_label ASC LIMIT 24");
        $log_stmt->execute([$view_history_j['id']]);
        $history_logs = $log_stmt->fetchAll();

        // If no stored logs yet, generate sample hourly benchmarks for the demo
        if (empty($history_logs)) {
            $sample_hours = ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
            foreach ($sample_hours as $h) {
                $history_logs[] = [
                    'hour_label' => $h,
                    'vehicles_count' => rand(650, 1850),
                    'average_wait_seconds' => rand(22, 68),
                    'congestion_level' => (intval(substr($h, 0, 2)) >= 8 && intval(substr($h, 0, 2)) <= 18) ? 'moderate' : 'low',
                    'recorded_date' => date('Y-m-d')
                ];
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Junction Infrastructure Management | SmartFlow AI</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-page">
  <?php require_once __DIR__ . '/includes/admin_nav.php'; ?>

  <main class="main-content">
    <header class="topbar">
      <div>
        <span class="section-eyebrow" style="margin-bottom: 6px;">JUNCTION CONTROL</span>
        <h1>Traffic Junction Infrastructure</h1>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <?php if ($action !== 'add'): ?>
          <a href="admin_junctions.php?action=add" class="btn btn-primary btn-sm">+ Configure New Junction</a>
        <?php endif; ?>
        <a href="junctions.php" target="_blank" class="btn btn-secondary btn-sm">Public Grid ↗</a>
      </div>
    </header>

    <?php if ($success_msg): ?>
      <div class="notice success" role="alert">
        <span style="font-size: 16px;">✓</span>
        <div><?= e($success_msg) ?></div>
      </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
      <div class="notice error" role="alert">
        <span style="font-size: 16px;">⚠️</span>
        <div><?= e($error_msg) ?></div>
      </div>
    <?php endif; ?>

    <!-- VIEW 1: Add or Edit Junction Form -->
    <?php if ($action === 'add' || $action === 'edit'): ?>
      <div class="panel" style="max-width: 960px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <div>
            <h3 style="font-size: 20px; color: var(--navy-900); margin: 0;">
              <?= ($action === 'edit') ? 'Edit Junction ' . e($edit_j['junction_code'] ?? '') : 'Configure New Traffic Junction' ?>
            </h3>
            <small style="color: var(--text-light); font-size: 12px;">Define GPS coordinates, camera count, and adaptive cycle timing limits.</small>
          </div>
          <a href="admin_junctions.php" class="btn btn-secondary btn-sm">← Back to List</a>
        </div>

        <form method="post" action="admin_junctions.php">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="operation" value="save_junction">
          <input type="hidden" name="junction_id" value="<?= (int)($edit_j['id'] ?? 0) ?>">

          <!-- Row 1: Code, Name, Zone -->
          <div style="display: grid; grid-template-columns: 140px 1.4fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Junction Code *</label>
              <input type="text" name="junction_code" class="form-control" placeholder="J-107" value="<?= e($edit_j['junction_code'] ?? 'J-' . (count($junctions_list) + 101)) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Intersection Name *</label>
              <input type="text" name="name" class="form-control" placeholder="E.g., Central Blvd & 8th St" value="<?= e($edit_j['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Metropolitan Zone</label>
              <select name="zone" class="form-control">
                <?php $zones = ['Central', 'North', 'Downtown', 'West', 'East', 'South']; ?>
                <?php foreach ($zones as $z): ?>
                  <option value="<?= $z ?>" <?= (($edit_j['zone'] ?? '') === $z) ? 'selected' : '' ?>><?= $z ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Row 2: Address -->
          <div class="form-group">
            <label class="form-label">Physical Street Address *</label>
            <input type="text" name="address" class="form-control" placeholder="Physical location description or crossroads..." value="<?= e($edit_j['address'] ?? '') ?>" required>
          </div>

          <!-- Row 3: GPS Coordinates & Volume -->
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Latitude (GPS)</label>
              <input type="number" step="any" name="latitude" class="form-control" value="<?= e((string)($edit_j['latitude'] ?? '37.7749290')) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Longitude (GPS)</label>
              <input type="number" step="any" name="longitude" class="form-control" value="<?= e((string)($edit_j['longitude'] ?? '-122.4194160')) ?>" required>
            </div>
          </div>

          <!-- Row 4: Status, Daily Volume, Cameras -->
          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
            <div class="form-group">
              <label class="form-label">Operational Status</label>
              <select name="status" class="form-control">
                <option value="active" <?= (($edit_j['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active (Online Telemetry)</option>
                <option value="inactive" <?= (($edit_j['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive (Maintenance)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Daily Vehicle Volume (est.)</label>
              <input type="number" name="daily_volume" class="form-control" value="<?= (int)($edit_j['daily_volume'] ?? 25000) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Vision Camera Count</label>
              <input type="number" name="cameras_count" class="form-control" value="<?= (int)($edit_j['cameras_count'] ?? 4) ?>">
            </div>
          </div>

          <!-- Row 5: Signal Timing Boundaries -->
          <div style="background-color: var(--bg-subtle); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-light); margin-bottom: 20px;">
            <h4 style="font-size: 15px; color: var(--navy-900); margin-bottom: 12px;">🚦 Adaptive Signal Timing Limits</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1.2fr; gap: 14px;">
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 11px;">Min Green (sec)</label>
                <input type="number" name="min_green_seconds" class="form-control" value="<?= (int)($edit_j['min_green_seconds'] ?? 10) ?>" min="5" max="30">
              </div>
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 11px;">Max Green (sec)</label>
                <input type="number" name="max_green_seconds" class="form-control" value="<?= (int)($edit_j['max_green_seconds'] ?? 60) ?>" min="30" max="120">
              </div>
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 11px;">Amber/Yellow (sec)</label>
                <input type="number" name="yellow_duration_seconds" class="form-control" value="<?= (int)($edit_j['yellow_duration_seconds'] ?? 4) ?>" min="3" max="7">
              </div>
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 11px;">Cycle Mode</label>
                <select name="cycle_mode" class="form-control">
                  <option value="adaptive" <?= (($edit_j['cycle_mode'] ?? 'adaptive') === 'adaptive') ? 'selected' : '' ?>>Adaptive Dynamic Split</option>
                  <option value="fixed" <?= (($edit_j['cycle_mode'] ?? '') === 'fixed') ? 'selected' : '' ?>>Fixed Cycle (Legacy)</option>
                  <option value="emergency_standby" <?= (($edit_j['cycle_mode'] ?? '') === 'emergency_standby') ? 'selected' : '' ?>>Emergency Standby</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Image URL -->
          <div class="form-group">
            <label class="form-label">Thumbnail Image URL</label>
            <input type="text" name="image_url" class="form-control" placeholder="https://images.unsplash.com/... or assets/images/..." value="<?= e($edit_j['image_url'] ?? '') ?>">
          </div>

          <div style="display: flex; gap: 12px; margin-top: 10px;">
            <button type="submit" class="btn btn-primary btn-lg">
              <?= ($action === 'edit') ? 'Update Junction Details' : 'Save & Deploy Junction' ?>
            </button>
            <a href="admin_junctions.php" class="btn btn-secondary btn-lg">Cancel</a>
          </div>
        </form>
      </div>

    <!-- VIEW 2: Junction-wise Traffic History -->
    <?php elseif ($action === 'history' && $view_history_j): ?>
      <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <div>
            <div style="display: flex; align-items: center; gap: 10px;">
              <span class="feature-badge"><?= e($view_history_j['junction_code']) ?></span>
              <h3 style="font-size: 20px; color: var(--navy-900); margin: 0;"><?= e($view_history_j['name']) ?></h3>
            </div>
            <small style="color: var(--text-light); font-size: 12px;"><?= e($view_history_j['address']) ?> · Zone: <?= e($view_history_j['zone']) ?></small>
          </div>
          <a href="admin_junctions.php" class="btn btn-secondary btn-sm">← Back to Junctions</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
          <div class="stat-card">
            <p>Daily Flow Volume</p>
            <strong><?= number_format((int)$view_history_j['daily_volume']) ?></strong>
            <small style="color: var(--text-light);">Vehicles / day</small>
          </div>
          <div class="stat-card">
            <p>Signal Mode</p>
            <strong style="font-size: 20px; text-transform: capitalize; color: var(--sky-600);"><?= e($view_history_j['cycle_mode']) ?></strong>
            <small style="color: var(--text-light);">NTCIP 1202</small>
          </div>
          <div class="stat-card">
            <p>Green Limits</p>
            <strong><?= (int)$view_history_j['min_green_seconds'] ?>s – <?= (int)$view_history_j['max_green_seconds'] ?>s</strong>
            <small style="color: var(--text-light);">Min / Max dynamic split</small>
          </div>
          <div class="stat-card">
            <p>Vision Hardware</p>
            <strong><?= (int)$view_history_j['cameras_count'] ?> 4K Nodes</strong>
            <small style="color: var(--emerald-600);">Edge TPU Connected</small>
          </div>
        </div>

        <h4 style="font-size: 16px; color: var(--navy-900); margin-bottom: 12px;">Hourly Traffic Log History</h4>
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border-light); color: var(--text-light);">
                <th style="padding: 10px 14px;">Hour Timestamp</th>
                <th style="padding: 10px 14px;">Vehicles Recorded</th>
                <th style="padding: 10px 14px;">Average Delay</th>
                <th style="padding: 10px 14px;">Congestion Level</th>
                <th style="padding: 10px 14px; text-align: right;">Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($history_logs as $hl): ?>
                <tr style="border-bottom: 1px solid var(--border-light);">
                  <td style="padding: 10px 14px; font-weight: 700; color: var(--navy-900);">
                    🕒 <?= e($hl['hour_label']) ?>
                  </td>
                  <td style="padding: 10px 14px; font-weight: 600;">
                    <?= number_format((int)$hl['vehicles_count']) ?> veh
                  </td>
                  <td style="padding: 10px 14px; color: var(--text-muted);">
                    <?= (int)$hl['average_wait_seconds'] ?>s wait
                  </td>
                  <td style="padding: 10px 14px;">
                    <span style="font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 4px; <?= ($hl['congestion_level'] === 'moderate') ? 'background:var(--amber-100);color:var(--amber-600);' : (($hl['congestion_level'] === 'heavy' || $hl['congestion_level'] === 'critical') ? 'background:#fee2e2;color:var(--rose-600);' : 'background:var(--emerald-100);color:var(--emerald-600);') ?>">
                      <?= e(strtoupper($hl['congestion_level'])) ?>
                    </span>
                  </td>
                  <td style="padding: 10px 14px; text-align: right; color: var(--text-light);">
                    <?= e($hl['recorded_date']) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    <!-- VIEW 3: Main Junctions Table List -->
    <?php else: ?>
      <div class="panel">
        <!-- Search and Filter Bar -->
        <form method="get" action="admin_junctions.php" style="display: flex; gap: 12px; align-items: center; margin-bottom: 24px; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 240px;">
            <input type="text" name="q" class="form-control" placeholder="Search junction code, name, zone or address..." value="<?= e($search_query) ?>">
          </div>
          <div style="width: 160px;">
            <select name="status" class="form-control">
              <option value="">All Statuses</option>
              <option value="active" <?= ($filter_status === 'active') ? 'selected' : '' ?>>Active Only</option>
              <option value="inactive" <?= ($filter_status === 'inactive') ? 'selected' : '' ?>>Inactive Only</option>
            </select>
          </div>
          <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
          <?php if ($search_query || $filter_status): ?>
            <a href="admin_junctions.php" class="btn btn-ghost btn-sm">Reset</a>
          <?php endif; ?>
        </form>

        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border-light); color: var(--text-light);">
                <th style="padding: 12px 14px;">ID / Code</th>
                <th style="padding: 12px 14px;">Junction Name</th>
                <th style="padding: 12px 14px;">Zone</th>
                <th style="padding: 12px 14px;">GPS Coordinates</th>
                <th style="padding: 12px 14px;">Daily Volume</th>
                <th style="padding: 12px 14px;">Cycle Limits</th>
                <th style="padding: 12px 14px;">Status</th>
                <th style="padding: 12px 14px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($junctions_list)): ?>
                <tr>
                  <td colspan="8" style="padding: 30px; text-align: center; color: var(--text-light);">
                    No traffic junctions found matching your criteria.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($junctions_list as $j): ?>
                  <tr style="border-bottom: 1px solid var(--border-light);">
                    <td style="padding: 12px 14px; font-weight: 800; color: var(--navy-900);">
                      <span class="junction-id-tag" style="position: static;"><?= e($j['junction_code']) ?></span>
                    </td>
                    <td style="padding: 12px 14px;">
                      <strong style="color: var(--navy-900); font-size: 14px; display: block;"><?= e($j['name']) ?></strong>
                      <small style="color: var(--text-light); font-size: 11px;"><?= e($j['address']) ?></small>
                    </td>
                    <td style="padding: 12px 14px; color: var(--sky-600); font-weight: 600;">
                      <?= e($j['zone']) ?>
                    </td>
                    <td style="padding: 12px 14px; font-family: monospace; font-size: 11px; color: var(--text-muted);">
                      <?= number_format((float)$j['latitude'], 4) ?>, <?= number_format((float)$j['longitude'], 4) ?>
                    </td>
                    <td style="padding: 12px 14px; font-weight: 700; color: var(--navy-900);">
                      <?= number_format((int)$j['daily_volume']) ?> veh
                    </td>
                    <td style="padding: 12px 14px; font-size: 12px; color: var(--text-muted);">
                      <?= (int)$j['min_green_seconds'] ?>s - <?= (int)$j['max_green_seconds'] ?>s (<?= e($j['cycle_mode']) ?>)
                    </td>
                    <td style="padding: 12px 14px;">
                      <?php if ($j['status'] === 'active'): ?>
                        <span class="live-pulse-badge" style="font-size: 9px; padding: 2px 7px;">
                          <span class="pulse-dot"></span> ACTIVE
                        </span>
                      <?php else: ?>
                        <span style="font-size: 10px; font-weight: 700; background: #f1f5f9; color: var(--text-light); padding: 3px 8px; border-radius: 4px;">
                          INACTIVE
                        </span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px 14px; text-align: right;">
                      <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <a href="admin_junctions.php?action=history&id=<?= (int)$j['id'] ?>" class="btn btn-secondary btn-sm" title="View traffic log history">
                          📊 History
                        </a>
                        <a href="admin_junctions.php?action=edit&id=<?= (int)$j['id'] ?>" class="btn btn-secondary btn-sm" title="Edit junction">
                          ✏️ Edit
                        </a>
                        <form method="post" action="admin_junctions.php" style="display: inline;" onsubmit="return confirm('Toggle status for junction <?= e($j['junction_code']) ?>?');">
                          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                          <input type="hidden" name="operation" value="toggle_status">
                          <input type="hidden" name="junction_id" value="<?= (int)$j['id'] ?>">
                          <input type="hidden" name="new_status" value="<?= ($j['status'] === 'active') ? 'inactive' : 'active' ?>">
                          <button type="submit" class="btn btn-ghost btn-sm" title="<?= ($j['status'] === 'active') ? 'Deactivate' : 'Activate' ?>">
                            <?= ($j['status'] === 'active') ? '⏸️' : '▶️' ?>
                          </button>
                        </form>
                        <form method="post" action="admin_junctions.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to remove junction <?= e($j['junction_code']) ?>?');">
                          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                          <input type="hidden" name="operation" value="delete_junction">
                          <input type="hidden" name="junction_id" value="<?= (int)$j['id'] ?>">
                          <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--rose-600);" title="Delete junction">
                            🗑️
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>

