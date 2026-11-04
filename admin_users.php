<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_admin();

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$admin_id = (int)($_SESSION['admin_id'] ?? 0);
$initial = strtoupper(substr($admin_name, 0, 1));

$success_msg = '';
$error_msg = '';

// Handle POST operations
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf()) {
        $error_msg = 'Security token validation failed. Refresh and retry.';
    } else {
        $op = $_POST['operation'] ?? '';

        // 1. Create New User
        if ($op === 'create_user') {
            if ($pdo !== null) {
                $name = trim((string)($_POST['name'] ?? ''));
                $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
                $password = (string)($_POST['password'] ?? '');
                $role = in_array($_POST['role'], ['admin', 'operator'], true) ? $_POST['role'] : 'operator';
                $status = in_array($_POST['status'], ['active', 'disabled'], true) ? $_POST['status'] : 'active';

                if ($name === '' || !$email || strlen($password) < 10) {
                    $error_msg = 'Please provide a name, valid email, and password with at least 10 characters.';
                } else {
                    try {
                        // Check if email already exists
                        $check_stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? LIMIT 1");
                        $check_stmt->execute([$email]);
                        if ($check_stmt->fetch()) {
                            $error_msg = "An account with email '{$email}' already exists.";
                        } else {
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                            $insert = $pdo->prepare("INSERT INTO admins (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
                            $insert->execute([$name, $email, $hash, $role, $status]);
                            log_admin_activity('CREATE_USER', "Created user account for {$name} ({$email}) with role {$role}");
                            $success_msg = "User '{$name}' created successfully as {$role}.";
                        }
                    } catch (PDOException $e) {
                        $error_msg = 'Database error: ' . $e->getMessage();
                    }
                }
            }
        }

        // 2. Toggle Status (Active / Disabled)
        elseif ($op === 'toggle_status') {
            if ($pdo !== null) {
                $target_id = (int)($_POST['target_user_id'] ?? 0);
                $new_status = ($_POST['new_status'] === 'active') ? 'active' : 'disabled';

                if ($target_id === $admin_id) {
                    $error_msg = 'Security violation: You cannot disable your own active administrator account.';
                } else {
                    $stmt = $pdo->prepare("UPDATE admins SET status = ? WHERE id = ?");
                    $stmt->execute([$new_status, $target_id]);
                    log_admin_activity('USER_STATUS', "Changed account #{$target_id} status to {$new_status}");
                    $success_msg = "Account #{$target_id} has been marked as {$new_status}.";
                }
            }
        }

        // 3. Change User Role
        elseif ($op === 'change_role') {
            if ($pdo !== null) {
                $target_id = (int)($_POST['target_user_id'] ?? 0);
                $new_role = ($_POST['new_role'] === 'admin') ? 'admin' : 'operator';

                if ($target_id === $admin_id && $new_role !== 'admin') {
                    $error_msg = 'Security violation: You cannot remove admin role from your own current session.';
                } else {
                    $stmt = $pdo->prepare("UPDATE admins SET role = ? WHERE id = ?");
                    $stmt->execute([$new_role, $target_id]);
                    log_admin_activity('USER_ROLE', "Changed account #{$target_id} role to {$new_role}");
                    $success_msg = "User #{$target_id} role changed to {$new_role}.";
                }
            }
        }

        // 4. Reset User Password
        elseif ($op === 'reset_password') {
            if ($pdo !== null) {
                $target_id = (int)($_POST['target_user_id'] ?? 0);
                $new_pass = (string)($_POST['new_password'] ?? '');

                if (strlen($new_pass) < 10) {
                    $error_msg = 'New password must be at least 10 characters long.';
                } else {
                    $hash = password_hash($new_pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                    $stmt->execute([$hash, $target_id]);
                    log_admin_activity('RESET_PASSWORD', "Updated credentials for user #{$target_id}");
                    $success_msg = "Password updated successfully for user #{$target_id}.";
                }
            }
        }
    }
}

// Search and Filter parameters
$search = trim((string)($_GET['q'] ?? ''));
$filter_role = trim((string)($_GET['role'] ?? ''));
$filter_status = trim((string)($_GET['status'] ?? ''));
$users_list = [];

if ($pdo !== null) {
    try {
        $sql = "SELECT id, name, email, role, status, last_login, created_at FROM admins WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (name LIKE ? OR email LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
        }

        if ($filter_role === 'admin' || $filter_role === 'operator') {
            $sql .= " AND role = ?";
            $params[] = $filter_role;
        }

        if ($filter_status === 'active' || $filter_status === 'disabled') {
            $sql .= " AND status = ?";
            $params[] = $filter_status;
        }

        $sql .= " ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users_list = $stmt->fetchAll();
    } catch (Throwable $e) {}
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>User & Role Management | SmartFlow AI</title>
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
        <span class="section-eyebrow" style="margin-bottom: 6px;">ACCESS GOVERNANCE</span>
        <h1>User & Role Management</h1>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <a href="#createUserPanel" class="btn btn-primary btn-sm">+ Add New User</a>
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

    <!-- Search & Filter Bar -->
    <div class="panel" style="margin-bottom: 24px;">
      <form method="get" action="admin_users.php" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 220px;">
          <input type="text" name="q" class="form-control" placeholder="Search by name or email address..." value="<?= e($search) ?>">
        </div>
        <div style="width: 150px;">
          <select name="role" class="form-control">
            <option value="">All Roles</option>
            <option value="admin" <?= ($filter_role === 'admin') ? 'selected' : '' ?>>Administrators</option>
            <option value="operator" <?= ($filter_role === 'operator') ? 'selected' : '' ?>>Operators</option>
          </select>
        </div>
        <div style="width: 150px;">
          <select name="status" class="form-control">
            <option value="">All Statuses</option>
            <option value="active" <?= ($filter_status === 'active') ? 'selected' : '' ?>>Active</option>
            <option value="disabled" <?= ($filter_status === 'disabled') ? 'selected' : '' ?>>Disabled</option>
          </select>
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">Filter Users</button>
        <?php if ($search || $filter_role || $filter_status): ?>
          <a href="admin_users.php" class="btn btn-ghost btn-sm">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Users Table Grid -->
    <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px;">
      <!-- Left: Users List -->
      <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
          <h3 style="font-size: 18px; color: var(--navy-900); margin: 0;">Registered Accounts (<?= count($users_list) ?>)</h3>
        </div>

        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border-light); color: var(--text-light);">
                <th style="padding: 10px 12px;">User</th>
                <th style="padding: 10px 12px;">Role</th>
                <th style="padding: 10px 12px;">Status</th>
                <th style="padding: 10px 12px;">Created</th>
                <th style="padding: 10px 12px; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($users_list)): ?>
                <tr>
                  <td colspan="5" style="padding: 24px; text-align: center; color: var(--text-light);">
                    No accounts found matching your query.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($users_list as $u): ?>
                  <tr style="border-bottom: 1px solid var(--border-light);">
                    <td style="padding: 12px;">
                      <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="avatar" style="width: 30px; height: 30px; font-size: 12px;">
                          <?= e(strtoupper(substr($u['name'], 0, 1))) ?>
                        </div>
                        <div>
                          <strong style="color: var(--navy-900); font-size: 13px; display: block;">
                            <?= e($u['name']) ?>
                            <?php if ((int)$u['id'] === $admin_id): ?>
                              <span style="font-size: 10px; color: var(--sky-600);">(You)</span>
                            <?php endif; ?>
                          </strong>
                          <small style="color: var(--text-light); font-size: 11px;"><?= e($u['email']) ?></small>
                        </div>
                      </div>
                    </td>
                    <td style="padding: 12px;">
                      <span style="font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px; <?= ($u['role'] === 'admin') ? 'background:var(--sky-50);color:var(--sky-600);border:1px solid var(--border-blue);' : 'background:#f1f5f9;color:var(--navy-800);' ?>">
                        <?= e(strtoupper($u['role'])) ?>
                      </span>
                    </td>
                    <td style="padding: 12px;">
                      <?php if ($u['status'] === 'active'): ?>
                        <span style="font-size: 11px; font-weight: 700; color: var(--emerald-600);">● Active</span>
                      <?php else: ?>
                        <span style="font-size: 11px; font-weight: 700; color: var(--rose-600);">● Disabled</span>
                      <?php endif; ?>
                    </td>
                    <td style="padding: 12px; color: var(--text-light); font-size: 11px;">
                      <?= date('M j, Y', strtotime($u['created_at'])) ?>
                    </td>
                    <td style="padding: 12px; text-align: right;">
                      <div style="display: inline-flex; align-items: center; gap: 6px;">
                        <!-- Role Toggle Button -->
                        <form method="post" action="admin_users.php" style="display: inline;">
                          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                          <input type="hidden" name="operation" value="change_role">
                          <input type="hidden" name="target_user_id" value="<?= (int)$u['id'] ?>">
                          <input type="hidden" name="new_role" value="<?= ($u['role'] === 'admin') ? 'operator' : 'admin' ?>">
                          <button type="submit" class="btn btn-secondary btn-sm" title="Toggle role between admin and operator">
                            <?= ($u['role'] === 'admin') ? 'Set Operator' : 'Make Admin' ?>
                          </button>
                        </form>

                        <!-- Status Toggle Button -->
                        <?php if ((int)$u['id'] !== $admin_id): ?>
                          <form method="post" action="admin_users.php" style="display: inline;" onsubmit="return confirm('Toggle status for <?= e($u['name']) ?>?');">
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="operation" value="toggle_status">
                            <input type="hidden" name="target_user_id" value="<?= (int)$u['id'] ?>">
                            <input type="hidden" name="new_status" value="<?= ($u['status'] === 'active') ? 'disabled' : 'active' ?>">
                            <button type="submit" class="btn btn-ghost btn-sm" style="color: <?= ($u['status'] === 'active') ? 'var(--rose-600)' : 'var(--emerald-600)' ?>;">
                              <?= ($u['status'] === 'active') ? 'Deactivate' : 'Activate' ?>
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Right: Create New User Form -->
      <div class="panel" id="createUserPanel">
        <h3 style="font-size: 18px; color: var(--navy-900); margin-bottom: 6px;">Create New User / Operator</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
          Provision authenticated accounts for dispatch operators and engineers.
        </p>

        <form method="post" action="admin_users.php">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="operation" value="create_user">

          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control" placeholder="Jane Smith" required>
          </div>

          <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" placeholder="jane@smartflow-ai.org" required>
          </div>

          <div class="form-group">
            <label class="form-label">Temporary Password * <small style="color: var(--text-light);">(Min 10 chars)</small></label>
            <input type="password" name="password" class="form-control" placeholder="Enter secure password" minlength="10" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Role</label>
              <select name="role" class="form-control">
                <option value="operator">Operator</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Status</label>
              <select name="status" class="form-control">
                <option value="active">Active</option>
                <option value="disabled">Disabled</option>
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
            Create User Account <span>→</span>
          </button>
        </form>

        <!-- Quick Reset Password Drawer -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-light);">
          <h4 style="font-size: 15px; color: var(--navy-900); margin-bottom: 10px;">Password Reset Tool</h4>
          <form method="post" action="admin_users.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="operation" value="reset_password">

            <div class="form-group">
              <label class="form-label">Target Account</label>
              <select name="target_user_id" class="form-control">
                <?php foreach ($users_list as $u): ?>
                  <option value="<?= (int)$u['id'] ?>"><?= e($u['name']) ?> (<?= e($u['email']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">New Password (Min 10 chars)</label>
              <input type="password" name="new_password" class="form-control" placeholder="New password..." minlength="10" required>
            </div>

            <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">
              Reset User Password
            </button>
          </form>
        </div>
      </div>
    </div>
  </main>
</body>
</html>

