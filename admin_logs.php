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

// Handle Clear Old Logs
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf()) {
        $error_msg = 'Security token invalid.';
    } else {
        if ($_POST['operation'] === 'purge_logs' && $pdo !== null) {
            $days = (int)($_POST['days'] ?? 30);
            $stmt = $pdo->prepare("DELETE FROM admin_activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->execute([$days]);
            $count = $stmt->rowCount();
            log_admin_activity('PURGE_LOGS', "Purged {$count} activity log entries older than {$days} days.");
            $success_msg = "Successfully purged {$count} log records older than {$days} days.";
        }
    }
}

// Fetch Logs with Filters
$filter_action = trim((string)($_GET['action'] ?? ''));
$search_term = trim((string)($_GET['q'] ?? ''));
$logs_list = [];

if ($pdo !== null) {
    try {
        $sql = "SELECT * FROM admin_activity_logs WHERE 1=1";
        $params = [];

        if ($filter_action !== '') {
            $sql .= " AND action = ?";
            $params[] = $filter_action;
        }

        if ($search_term !== '') {
            $sql .= " AND (admin_name LIKE ? OR details LIKE ? OR ip_address LIKE ?)";
            $term = "%{$search_term}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY id DESC LIMIT 100";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs_list = $stmt->fetchAll();
    } catch (Throwable $e) {}
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Activity Logs | SmartFlow AI</title>
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
        <span class="section-eyebrow" style="margin-bottom: 6px;">AUDIT TRAIL</span>
        <h1>Administrative Activity Logs</h1>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <span style="font-size: 12px; color: var(--text-light);">Last 100 System Actions</span>
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

    <!-- Filter and Search Toolbar -->
    <div class="panel" style="margin-bottom: 24px;">
      <form method="get" action="admin_logs.php" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px;">
          <input type="text" name="q" class="form-control" placeholder="Search logs by admin name, keyword, or IP..." value="<?= e($search_term) ?>">
        </div>
        <div style="width: 200px;">
          <select name="action" class="form-control">
            <option value="">All Actions</option>
            <option value="LOGIN" <?= ($filter_action === 'LOGIN') ? 'selected' : '' ?>>Logins</option>
            <option value="UPDATE_WEBSITE" <?= ($filter_action === 'UPDATE_WEBSITE') ? 'selected' : '' ?>>Website Content Updates</option>
            <option value="ADD_JUNCTION" <?= ($filter_action === 'ADD_JUNCTION') ? 'selected' : '' ?>>Junction Additions</option>
            <option value="EDIT_JUNCTION" <?= ($filter_action === 'EDIT_JUNCTION') ? 'selected' : '' ?>>Junction Edits</option>
            <option value="CREATE_USER" <?= ($filter_action === 'CREATE_USER') ? 'selected' : '' ?>>User Creations</option>
            <option value="USER_STATUS" <?= ($filter_action === 'USER_STATUS') ? 'selected' : '' ?>>User Status Toggles</option>
            <option value="CREATE_ALERT" <?= ($filter_action === 'CREATE_ALERT') ? 'selected' : '' ?>>Alert Broadcasts</option>
            <option value="UPLOAD_IMAGE" <?= ($filter_action === 'UPLOAD_IMAGE') ? 'selected' : '' ?>>Image Uploads</option>
          </select>
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">Filter Logs</button>
        <?php if ($search_term || $filter_action): ?>
          <a href="admin_logs.php" class="btn btn-ghost btn-sm">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Logs Table -->
    <div class="panel">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="font-size: 18px; color: var(--navy-900); margin: 0;">Audit History</h3>

        <!-- Purge Tool Form -->
        <form method="post" action="admin_logs.php" style="display: flex; align-items: center; gap: 8px;" onsubmit="return confirm('Purge log entries older than 30 days?');">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="operation" value="purge_logs">
          <input type="hidden" name="days" value="30">
          <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--text-light); font-size: 11px;">
            Purge Logs > 30 Days
          </button>
        </form>
      </div>

      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
          <thead>
            <tr style="border-bottom: 2px solid var(--border-light); color: var(--text-light);">
              <th style="padding: 10px 12px; width: 60px;">ID</th>
              <th style="padding: 10px 12px; width: 160px;">Operator / Admin</th>
              <th style="padding: 10px 12px; width: 150px;">Action</th>
              <th style="padding: 10px 12px;">Operational Details</th>
              <th style="padding: 10px 12px; width: 110px;">IP Address</th>
              <th style="padding: 10px 12px; width: 150px; text-align: right;">Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs_list)): ?>
              <tr>
                <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-light);">
                  No audit log entries found matching criteria.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($logs_list as $log): ?>
                <tr style="border-bottom: 1px solid var(--border-light);">
                  <td style="padding: 10px 12px; color: var(--text-light); font-size: 11px;">
                    #<?= (int)$log['id'] ?>
                  </td>
                  <td style="padding: 10px 12px; font-weight: 600; color: var(--navy-900);">
                    <?= e($log['admin_name']) ?>
                  </td>
                  <td style="padding: 10px 12px;">
                    <span style="font-size: 10px; font-weight: 800; background: var(--sky-50); color: var(--sky-600); border: 1px solid var(--border-blue); padding: 2px 7px; border-radius: 4px;">
                      <?= e($log['action']) ?>
                    </span>
                  </td>
                  <td style="padding: 10px 12px; color: var(--text-muted); line-height: 1.4;">
                    <?= e($log['details'] ?? 'Executed without extra payload') ?>
                  </td>
                  <td style="padding: 10px 12px; color: var(--text-light); font-family: monospace; font-size: 11px;">
                    <?= e($log['ip_address'] ?? '127.0.0.1') ?>
                  </td>
                  <td style="padding: 10px 12px; text-align: right; color: var(--text-light); font-size: 11px;">
                    <?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</body>
</html>

