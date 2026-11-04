<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_admin();

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$initial = strtoupper(substr($admin_name, 0, 1));

$success_msg = '';
$error_msg = '';

// Handle POST operations
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf()) {
        $error_msg = 'Security token expired. Please retry.';
    } else {
        $op = $_POST['operation'] ?? '';

        // 1. Create Alert
        if ($op === 'create_alert') {
            if ($pdo !== null) {
                $j_id = !empty($_POST['junction_id']) ? (int)$_POST['junction_id'] : null;
                $type = in_array($_POST['alert_type'], ['congestion', 'emergency', 'incident', 'hardware'], true) ? $_POST['alert_type'] : 'congestion';
                $severity = in_array($_POST['severity'], ['low', 'medium', 'high', 'critical'], true) ? $_POST['severity'] : 'medium';
                $title = trim((string)($_POST['title'] ?? ''));
                $message = trim((string)($_POST['message'] ?? ''));

                if ($title === '' || $message === '') {
                    $error_msg = 'Alert title and message description are required.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO traffic_alerts (junction_id, alert_type, severity, title, message, status) VALUES (?, ?, ?, ?, ?, 'active')");
                    $stmt->execute([$j_id, $type, $severity, $title, $message]);
                    log_admin_activity('CREATE_ALERT', "Dispatched {$severity} {$type} alert: {$title}");
                    $success_msg = "Traffic alert dispatched to operations network!";
                }
            }
        }

        // 2. Resolve Alert
        elseif ($op === 'resolve_alert') {
            if ($pdo !== null) {
                $a_id = (int)($_POST['alert_id'] ?? 0);
                $stmt = $pdo->prepare("UPDATE traffic_alerts SET status = 'resolved', resolved_at = NOW() WHERE id = ?");
                $stmt->execute([$a_id]);
                log_admin_activity('RESOLVE_ALERT', "Marked alert #{$a_id} as resolved.");
                $success_msg = "Alert #{$a_id} resolved and archived.";
            }
        }

        // 3. Delete Alert
        elseif ($op === 'delete_alert') {
            if ($pdo !== null) {
                $a_id = (int)($_POST['alert_id'] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM traffic_alerts WHERE id = ?");
                $stmt->execute([$a_id]);
                log_admin_activity('DELETE_ALERT', "Deleted alert record #{$a_id}");
                $success_msg = "Alert record #{$a_id} deleted permanently.";
            }
        }
    }
}

// Fetch alerts with optional filters
$filter_type = trim((string)($_GET['type'] ?? ''));
$filter_status = trim((string)($_GET['status'] ?? 'active'));
$alerts_list = [];
$junctions_options = [];

if ($pdo !== null) {
    try {
        $junctions_options = $pdo->query("SELECT id, junction_code, name FROM junctions ORDER BY junction_code ASC")->fetchAll();

        $sql = "SELECT a.*, j.junction_code, j.name as junction_name FROM traffic_alerts a LEFT JOIN junctions j ON a.junction_id = j.id WHERE 1=1";
        $params = [];

        if ($filter_type !== '') {
            $sql .= " AND a.alert_type = ?";
            $params[] = $filter_type;
        }

        if ($filter_status === 'active' || $filter_status === 'resolved') {
            $sql .= " AND a.status = ?";
            $params[] = $filter_status;
        }

        $sql .= " ORDER BY a.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $alerts_list = $stmt->fetchAll();
    } catch (Throwable $e) {}
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Traffic Alerts & Emergency Management | SmartFlow AI</title>
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
        <span class="section-eyebrow" style="margin-bottom: 6px;">INCIDENT DISPATCH</span>
        <h1>Traffic & Emergency Alerts</h1>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <a href="#newAlertPanel" class="btn btn-primary btn-sm">+ Dispatch New Alert</a>
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

    <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px;">
      <!-- Left: Alerts List -->
      <div class="panel">
        <!-- Filter Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
          <div class="filter-btn-group">
            <a href="admin_alerts.php?status=active" class="filter-btn <?= ($filter_status === 'active' && $filter_type === '') ? 'active' : '' ?>">
              Active Alerts
            </a>
            <a href="admin_alerts.php?type=emergency&status=active" class="filter-btn <?= ($filter_type === 'emergency') ? 'active' : '' ?>" style="border-color: #fecdd3;">
              🚨 Emergency
            </a>
            <a href="admin_alerts.php?type=congestion&status=active" class="filter-btn <?= ($filter_type === 'congestion') ? 'active' : '' ?>" style="border-color: var(--amber-100);">
              ⚠️ Congestion
            </a>
            <a href="admin_alerts.php?status=resolved" class="filter-btn <?= ($filter_status === 'resolved') ? 'active' : '' ?>">
              ✓ Resolved History
            </a>
          </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 14px;">
          <?php if (empty($alerts_list)): ?>
            <div style="padding: 30px; text-align: center; color: var(--text-light); font-size: 14px;">
              No alerts found under selected filter.
            </div>
          <?php else: ?>
            <?php foreach ($alerts_list as $a): ?>
              <div style="background-color: var(--bg-subtle); border: 1px solid var(--border-light); border-left: 4px solid <?= ($a['alert_type'] === 'emergency') ? 'var(--rose-500)' : (($a['alert_type'] === 'congestion') ? 'var(--amber-500)' : 'var(--sky-500)') ?>; border-radius: var(--radius-md); padding: 16px; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                <div style="flex: 1;">
                  <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 4px; <?= ($a['alert_type'] === 'emergency') ? 'background:#ffe4e6;color:var(--rose-600);' : 'background:var(--amber-100);color:var(--amber-600);' ?>">
                      <?= e(strtoupper($a['severity'])) ?>
                    </span>
                    <strong style="font-size: 15px; color: var(--navy-900);"><?= e($a['title']) ?></strong>
                    <?php if ($a['status'] === 'resolved'): ?>
                      <span style="font-size: 10px; color: var(--emerald-600); font-weight: 700;">[Resolved]</span>
                    <?php endif; ?>
                  </div>
                  <p style="font-size: 13px; color: var(--text-muted); margin: 0 0 8px; line-height: 1.5;">
                    <?= e($a['message']) ?>
                  </p>
                  <div style="font-size: 11px; color: var(--text-light); display: flex; gap: 14px;">
                    <span>📍 <?= e($a['junction_name'] ?? 'Citywide Network') ?> (<?= e($a['junction_code'] ?? 'ALL') ?>)</span>
                    <span>⏱️ <?= time_ago($a['created_at']) ?></span>
                  </div>
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                  <?php if ($a['status'] === 'active'): ?>
                    <form method="post" action="admin_alerts.php" onsubmit="return confirm('Resolve alert #<?= (int)$a['id'] ?>?');">
                      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="operation" value="resolve_alert">
                      <input type="hidden" name="alert_id" value="<?= (int)$a['id'] ?>">
                      <button type="submit" class="btn btn-secondary btn-sm" title="Mark as resolved">
                        ✓ Resolve
                      </button>
                    </form>
                  <?php endif; ?>
                  <form method="post" action="admin_alerts.php" onsubmit="return confirm('Delete alert #<?= (int)$a['id'] ?> permanently?');">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="operation" value="delete_alert">
                    <input type="hidden" name="alert_id" value="<?= (int)$a['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--rose-600);">
                      🗑️
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: Dispatch New Alert -->
      <div class="panel" id="newAlertPanel">
        <h3 style="font-size: 18px; color: var(--navy-900); margin-bottom: 6px;">Dispatch Operations Alert</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
          Publish a real-time advisory or emergency signal preemption flag to the network.
        </p>

        <form method="post" action="admin_alerts.php">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="operation" value="create_alert">

          <div class="form-group">
            <label class="form-label">Associated Junction</label>
            <select name="junction_id" class="form-control">
              <option value="">Metropolitan Network (General)</option>
              <?php foreach ($junctions_options as $jo): ?>
                <option value="<?= (int)$jo['id'] ?>"><?= e($jo['junction_code']) ?> – <?= e($jo['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Alert Category</label>
              <select name="alert_type" class="form-control">
                <option value="congestion">Congestion Spillover</option>
                <option value="emergency">Emergency Priority Wave</option>
                <option value="incident">Traffic Incident / Stalled Vehicle</option>
                <option value="hardware">Hardware / Sensor Diagnostic</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Severity Level</label>
              <select name="severity" class="form-control">
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical (Preemption)</option>
                <option value="low">Low (Advisory)</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Alert Headline *</label>
            <input type="text" name="title" class="form-control" placeholder="E.g., Northbound Ambulance Preemption Corridor" required>
          </div>

          <div class="form-group">
            <label class="form-label">Details & Operator Instructions *</label>
            <textarea name="message" class="form-control" style="min-height: 100px;" placeholder="Describe the conditions, lane closures, or emergency transponder status..." required></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
            Broadcast Alert to Dispatch <span>→</span>
          </button>
        </form>
      </div>
    </div>
  </main>
</body>
</html>

