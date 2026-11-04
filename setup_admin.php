<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$error = '';
$success = '';
$disabled = false;

if ($pdo === null) {
    $disabled = true;
    $error = 'Database connection failed. Ensure MySQL is running in XAMPP and database smartflow_ai is imported from database/smartflow_ai.sql.';
} else {
    try {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        if ($count > 0) {
            http_response_code(403);
            $disabled = true;
        } else {
            $disabled = false;
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                if (!verify_csrf()) {
                    $error = 'Invalid form token. Refresh and try again.';
                } else {
                    $name = trim((string)($_POST['name'] ?? ''));
                    $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
                    $pass = (string)($_POST['password'] ?? '');
                    if ($name === '' || !$email || strlen($pass) < 10) {
                        $error = 'Enter a name, valid email and password with at least 10 characters.';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO admins (name,email,password,role,status) VALUES (?,?,?,'admin','active')");
                        $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
                        $success = 'Administrator created successfully. Delete setup_admin.php now, then sign in.';
                    }
                }
            }
        }
    } catch (PDOException $e) {
        $disabled = true;
        $error = 'Database query failed: ' . $e->getMessage() . '. Please confirm database/smartflow_ai.sql is imported.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Initial Admin Setup | SmartFlow AI</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <div style="text-align: center; margin-bottom: 24px;">
      <a href="index.php" style="display: inline-flex; align-items: center; gap: 10px;">
        <div class="brand-logo-icon">
          <svg viewBox="0 0 40 40" width="38" height="38" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="url(#setupBrandGrad)"/>
            <path d="M10 26C15 26 16 14 22 14C27 14 28 22 32 22" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
            <circle cx="10" cy="26" r="3" fill="#22d3ee"/>
            <circle cx="22" cy="14" r="3" fill="#38bdf8"/>
            <circle cx="32" cy="22" r="3" fill="#ffffff"/>
            <defs>
              <linearGradient id="setupBrandGrad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
                <stop stop-color="#0f172a"/>
                <stop offset="0.5" stop-color="#1e3a8a"/>
                <stop offset="1" stop-color="#0284c7"/>
              </linearGradient>
            </defs>
          </svg>
        </div>
        <div style="text-align: left;">
          <div style="font-family: var(--font-heading); font-size: 19px; font-weight: 800; color: var(--navy-900); line-height: 1.1;">
            SMARTFLOW <span class="brand-accent">AI</span>
          </div>
          <div style="font-size: 9px; font-weight: 700; letter-spacing: 1.4px; color: var(--text-light);">
            INTELLIGENT TRAFFIC CONTROL
          </div>
        </div>
      </a>
    </div>

    <section class="auth-card">
      <span class="section-eyebrow" style="margin-bottom: 12px;">FIRST-TIME CONFIGURATION</span>
      <h1>Create Administrator</h1>

      <?php if ($disabled && !$error): ?>
        <div class="notice info">
          An administrator already exists. For system security, this setup page has been automatically locked. You can delete <code>setup_admin.php</code>.
        </div>
      <?php elseif ($success): ?>
        <div class="notice success">
          <?= e($success) ?>
        </div>
        <a href="login.php" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 15px;">
          Proceed to Sign In <span>→</span>
        </a>
      <?php else: ?>
        <?php if ($error): ?>
          <div class="notice error">
            <?= e($error) ?>
          </div>
        <?php endif; ?>

        <?php if (!$disabled): ?>
          <p class="subtext">Configure your primary municipal administrator account to manage traffic signals and view telemetry.</p>
          <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="form-group">
              <label class="form-label">Administrator Name</label>
              <input name="name" class="form-control" placeholder="Traffic Admin" required maxlength="100" autocomplete="name" value="<?= e($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control" placeholder="admin@smartflow-ai.org" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Password <small style="color: var(--text-light);">(Minimum 10 characters)</small></label>
              <input type="password" name="password" class="form-control" placeholder="Create a strong password" required minlength="10" autocomplete="new-password">
            </div>

            <button class="btn btn-primary btn-lg" type="submit" style="width: 100%; margin-top: 10px;">
              Create administrator <span>→</span>
            </button>
          </form>
        <?php endif; ?>
      <?php endif; ?>

      <a class="back-link" href="login.php">
        Return to Login
      </a>
    </section>

    <p style="text-align: center; color: var(--text-lighter); font-size: 11px; margin-top: 18px;">
      © <?= date('Y') ?> SmartFlow AI · Local Development Starter
    </p>
  </main>
</body>
</html>
