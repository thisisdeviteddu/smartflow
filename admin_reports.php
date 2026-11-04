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

        // 1. Update Report Status & Admin Notes
        if ($op === 'update_report') {
            if ($pdo !== null) {
                $report_id = (int)($_POST['report_id'] ?? 0);
                $status = in_array($_POST['status'] ?? '', ['pending', 'investigating', 'resolved', 'dismissed'], true) ? $_POST['status'] : 'pending';
                $admin_notes = trim((string)($_POST['admin_notes'] ?? ''));

                if ($report_id > 0) {
                    $stmt = $pdo->prepare("UPDATE traffic_reports SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$status, $admin_notes, $report_id]);
                    log_admin_activity('UPDATE_REPORT', "Updated citizen report #{$report_id} status to '{$status}'");
                    $success_msg = "Citizen report #{$report_id} updated successfully.";
                }
            }
        }

        // 2. Delete Report
        elseif ($op === 'delete_report') {
            if ($pdo !== null) {
                $report_id = (int)($_POST['report_id'] ?? 0);
                if ($report_id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM traffic_reports WHERE id = ?");
                    $stmt->execute([$report_id]);
                    log_admin_activity('DELETE_REPORT', "Deleted citizen incident report #{$report_id}");
                    $success_msg = "Report #{$report_id} deleted permanently.";
                }
            }
        }
    }
}

// Fetch filters
$filter_status = trim((string)($_GET['status'] ?? ''));
$filter_type = trim((string)($_GET['type'] ?? ''));
$search_query = trim((string)($_GET['q'] ?? ''));

$reports_list = [];
$counts = ['total' => 0, 'pending' => 0, 'investigating' => 0, 'resolved' => 0, 'dismissed' => 0];

if ($pdo !== null) {
    try {
        // Fetch counts
        $c_rows = $pdo->query("SELECT status, COUNT(*) as c FROM traffic_reports GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
        $counts['pending'] = (int)($c_rows['pending'] ?? 0);
        $counts['investigating'] = (int)($c_rows['investigating'] ?? 0);
        $counts['resolved'] = (int)($c_rows['resolved'] ?? 0);
        $counts['dismissed'] = (int)($c_rows['dismissed'] ?? 0);
        $counts['total'] = array_sum($counts);

        $sql = "SELECT r.*, j.junction_code, j.name as junction_name FROM traffic_reports r LEFT JOIN junctions j ON r.junction_id = j.id WHERE 1=1";
        $params = [];

        if ($filter_status !== '' && in_array($filter_status, ['pending', 'investigating', 'resolved', 'dismissed'], true)) {
            $sql .= " AND r.status = ?";
            $params[] = $filter_status;
        }

        if ($filter_type !== '') {
            $sql .= " AND r.report_type = ?";
            $params[] = $filter_type;
        }

        if ($search_query !== '') {
            $sql .= " AND (r.description LIKE ? OR r.reporter_name LIKE ? OR j.name LIKE ?)";
            $term = "%{$search_query}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $reports_list = $stmt->fetchAll();
    } catch (Throwable $e) {}
}

// Fallback demo reports if empty
if (empty($reports_list) && $filter_status === '' && $filter_type === '' && $search_query === '') {
    $reports_list = [
        [
            'id' => 1,
            'junction_code' => 'J-101',
            'junction_name' => 'Central Boulevard & Technology Way',
            'report_type' => 'congestion',
            'description' => 'Severe tailback stretching over 400m eastbound on Technology Way due to lane merging.',
            'reporter_name' => 'Metro Commuter',
            'reporter_contact' => 'commuter@city.org',
            'status' => 'pending',
            'admin_notes' => 'Awaiting field camera review.',
            'created_at' => date('Y-m-d H:i:s', time() - 1800),
            'updated_at' => date('Y-m-d H:i:s', time() - 1800)
        ],
        [
            'id' => 2,
            'junction_code' => 'J-103',
            'junction_name' => 'Harbor Expressway & Marine Drive',
            'report_type' => 'signal_fault',
            'description' => 'Pedestrian countdown signal stuck on 0s flashing amber.',
            'reporter_name' => 'Civilian Patrol',
            'reporter_contact' => 'patrol@harbor.gov',
            'status' => 'investigating',
            'admin_notes' => 'Dispatched technician team unit 4.',
            'created_at' => date('Y-m-d H:i:s', time() - 7200),
            'updated_at' => date('Y-m-d H:i:s', time() - 3600)
        ]
    ];
    $counts['total'] = 2;
    $counts['pending'] = 1;
    $counts['investigating'] = 1;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Citizen Traffic Reports | SmartFlow AI Admin</title>
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
        <span class="section-eyebrow" style="margin-bottom: 6px;">COMMUNITY INCIDENT DESK</span>
        <h1>Citizen Traffic Reports</h1>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <a href="map.php" target="_blank" class="btn btn-secondary btn-sm">
          <span>Live Map ↗</span>
        </a>
        <div class="user-chip">
          <div class="avatar"><?= e($initial) ?></div>
          <div>
            <strong style="font-size: 13px; color: var(--navy-900); display: block;"><?= e($admin_name) ?></strong>
            <small style="color: var(--text-light); font-size: 10px;"><?= e(ucfirst($admin_role)) ?></small>
          </div>
        </div>
      </div>
    </header>

    <?php if ($success_msg): ?>
      <div class="notice success" style="margin-bottom: 20px;">
        <span>✓</span> <?= e($success_msg) ?>
      </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
      <div class="notice error" style="margin-bottom: 20px;">
        <span>⚠</span> <?= e($error_msg) ?>
      </div>
    <?php endif; ?>

    <!-- Status Tally Bar -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
      <a href="admin_reports.php" class="stat-card" style="text-decoration: none; border-left: 4px solid var(--sky-500);">
        <p>Total Submitted</p>
        <strong style="color: var(--navy-900);"><?= (int)$counts['total'] ?></strong>
        <small style="color: var(--text-light);">All feedback records</small>
      </a>

      <a href="admin_reports.php?status=pending" class="stat-card" style="text-decoration: none; border-left: 4px solid var(--amber-500);">
        <p>Pending Review</p>
        <strong style="color: var(--amber-600);"><?= (int)$counts['pending'] ?></strong>
        <small style="color: var(--amber-600); font-weight: 600;">Requires attention →</small>
      </a>

      <a href="admin_reports.php?status=investigating" class="stat-card" style="text-decoration: none; border-left: 4px solid var(--cyan-500);">
        <p>Under Investigation</p>
        <strong style="color: var(--cyan-600);"><?= (int)$counts['investigating'] ?></strong>
        <small style="color: var(--text-light);">Field team dispatched</small>
      </a>

      <a href="admin_reports.php?status=resolved" class="stat-card" style="text-decoration: none; border-left: 4px solid var(--emerald-500);">
        <p>Resolved</p>
        <strong style="color: var(--emerald-600);"><?= (int)$counts['resolved'] ?></strong>
        <small style="color: var(--text-light);">Remediated issues</small>
      </a>
    </div>

    <!-- Filters and Search Toolbar -->
    <div class="panel" style="margin-bottom: 24px; padding: 18px 24px;">
      <form method="GET" action="admin_reports.php" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px;">
          <input type="text" name="q" value="<?= e($search_query) ?>" placeholder="Search description, citizen name, or junction..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-sm); font-size: 13px;">
        </div>

        <div>
          <select name="status" style="padding: 10px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-sm); font-size: 13px; background: white;">
            <option value="">All Statuses</option>
            <option value="pending" <?= ($filter_status === 'pending') ? 'selected' : '' ?>>Pending</option>
            <option value="investigating" <?= ($filter_status === 'investigating') ? 'selected' : '' ?>>Investigating</option>
            <option value="resolved" <?= ($filter_status === 'resolved') ? 'selected' : '' ?>>Resolved</option>
            <option value="dismissed" <?= ($filter_status === 'dismissed') ? 'selected' : '' ?>>Dismissed</option>
          </select>
        </div>

        <div>
          <select name="type" style="padding: 10px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-sm); font-size: 13px; background: white;">
            <option value="">All Incident Types</option>
            <option value="congestion" <?= ($filter_type === 'congestion') ? 'selected' : '' ?>>Heavy Congestion</option>
            <option value="accident" <?= ($filter_type === 'accident') ? 'selected' : '' ?>>Accident / Collision</option>
            <option value="signal_fault" <?= ($filter_type === 'signal_fault') ? 'selected' : '' ?>>Signal Hardware Fault</option>
            <option value="hazard" <?= ($filter_type === 'hazard') ? 'selected' : '' ?>>Road Hazard / Debris</option>
            <option value="other" <?= ($filter_type === 'other') ? 'selected' : '' ?>>Other Feedback</option>
          </select>
        </div>

        <button type="submit" class="btn btn-navy btn-sm">Filter</button>
        <?php if ($filter_status !== '' || $filter_type !== '' || $search_query !== ''): ?>
          <a href="admin_reports.php" class="btn btn-ghost btn-sm">Clear</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Reports Table -->
    <div class="panel">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
          <h3 style="font-size: 18px; color: var(--navy-900); margin: 0 0 4px;">Submitted Citizen Reports</h3>
          <small style="color: var(--text-light); font-size: 12px;">Displaying <?= count($reports_list) ?> reports based on current criteria</small>
        </div>
      </div>

      <?php if (empty($reports_list)): ?>
        <div style="padding: 40px; text-align: center; color: var(--text-light);">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 12px; display: block; opacity: 0.5;">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M8 12h8"></path>
          </svg>
          No citizen traffic incident reports match your criteria.
        </div>
      <?php else: ?>
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border-light); background: var(--bg-subtle);">
                <th style="padding: 12px 14px; font-weight: 700; color: var(--navy-800);">ID</th>
                <th style="padding: 12px 14px; font-weight: 700; color: var(--navy-800);">Type</th>
                <th style="padding: 12px 14px; font-weight: 700; color: var(--navy-800);">Location / Junction</th>
                <th style="padding: 12px 14px; font-weight: 700; color: var(--navy-800);">Description & Reporter</th>
                <th style="padding: 12px 14px; font-weight: 700; color: var(--navy-800);">Status</th>
                <th style="padding: 12px 14px; font-weight: 700; color: var(--navy-800);">Submitted</th>
                <th style="padding: 12px 14px; font-weight: 700; color: var(--navy-800); text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reports_list as $rep): ?>
                <?php
                  $badge_style = match($rep['status']) {
                      'pending' => 'background: var(--amber-100); color: var(--amber-700); border: 1px solid var(--amber-200);',
                      'investigating' => 'background: var(--sky-100); color: var(--sky-700); border: 1px solid var(--sky-200);',
                      'resolved' => 'background: var(--emerald-100); color: var(--emerald-700); border: 1px solid var(--emerald-200);',
                      'dismissed' => 'background: #f1f5f9; color: var(--text-light); border: 1px solid var(--border-light);',
                      default => 'background: #f1f5f9; color: var(--text-light);'
                  };
                  $type_label = match($rep['report_type']) {
                      'congestion' => '🚗 Heavy Congestion',
                      'accident' => '💥 Collision/Accident',
                      'signal_fault' => '🚦 Signal Fault',
                      'hazard' => '⚠️ Hazard/Obstacle',
                      default => '📝 Other Report'
                  };
                ?>
                <tr style="border-bottom: 1px solid var(--border-light);">
                  <td style="padding: 14px; font-weight: 700; color: var(--navy-900);">#<?= (int)$rep['id'] ?></td>
                  <td style="padding: 14px; white-space: nowrap; font-weight: 600; color: var(--navy-800);"><?= e($type_label) ?></td>
                  <td style="padding: 14px;">
                    <strong style="color: var(--navy-900); display: block;"><?= e($rep['junction_name'] ?? 'General Metro Corridor') ?></strong>
                    <?php if (!empty($rep['junction_code'])): ?>
                      <span style="font-size: 11px; font-weight: 700; color: var(--sky-600);"><?= e($rep['junction_code']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 14px; max-width: 320px;">
                    <p style="margin: 0 0 6px; color: var(--navy-800); line-height: 1.4; font-size: 13px;">
                      <?= nl2br(e($rep['description'])) ?>
                    </p>
                    <div style="font-size: 11px; color: var(--text-light);">
                      By: <strong><?= e($rep['reporter_name'] ?: 'Anonymous Citizen') ?></strong>
                      <?php if (!empty($rep['reporter_contact'])): ?>
                        · <span style="font-family: monospace;"><?= e($rep['reporter_contact']) ?></span>
                      <?php endif; ?>
                    </div>
                    <?php if (!empty($rep['admin_notes'])): ?>
                      <div style="margin-top: 6px; padding: 4px 8px; background: var(--bg-subtle); border-left: 2px solid var(--sky-500); font-size: 11px; color: var(--text-muted); border-radius: 2px;">
                        <strong>Note:</strong> <?= e($rep['admin_notes']) ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 14px;">
                    <span style="display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; <?= $badge_style ?>">
                      <?= e($rep['status']) ?>
                    </span>
                  </td>
                  <td style="padding: 14px; font-size: 12px; color: var(--text-light); white-space: nowrap;">
                    <?= time_ago($rep['created_at']) ?>
                  </td>
                  <td style="padding: 14px; text-align: right; white-space: nowrap;">
                    <!-- Quick Status Update Form -->
                    <form method="POST" action="admin_reports.php" style="display: inline-block;">
                      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                      <input type="hidden" name="operation" value="update_report">
                      <input type="hidden" name="report_id" value="<?= (int)$rep['id'] ?>">
                      <select name="status" onchange="this.form.submit()" style="font-size: 11px; padding: 4px 8px; border: 1px solid var(--border-light); border-radius: 4px; background: white; cursor: pointer;">
                        <option value="pending" <?= ($rep['status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="investigating" <?= ($rep['status'] === 'investigating') ? 'selected' : '' ?>>Investigating</option>
                        <option value="resolved" <?= ($rep['status'] === 'resolved') ? 'selected' : '' ?>>Resolved</option>
                        <option value="dismissed" <?= ($rep['status'] === 'dismissed') ? 'selected' : '' ?>>Dismissed</option>
                      </select>
                    </form>

                    <!-- Delete Form -->
                    <form method="POST" action="admin_reports.php" style="display: inline-block; margin-left: 6px;" onsubmit="return confirm('Delete this citizen report permanently?');">
                      <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                      <input type="hidden" name="operation" value="delete_report">
                      <input type="hidden" name="report_id" value="<?= (int)$rep['id'] ?>">
                      <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--rose-500); padding: 4px 8px;" title="Delete report">✕</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>

