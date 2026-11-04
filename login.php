<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('dashboard.php');
}

$error = '';
$now = time();
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}
$_SESSION['login_attempts'] = array_values(array_filter(
    $_SESSION['login_attempts'],
    fn($t) => ($now - (int)$t) < 300
));
$locked = count($_SESSION['login_attempts']) >= 5;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf()) {
        $error = 'Your session token expired. Refresh the page and try again.';
    } elseif ($locked) {
        $error = 'Too many attempts. Wait five minutes before trying again.';
    } elseif ($pdo === null) {
        $error = 'Database connection failed. Please ensure MySQL is running in XAMPP Control Panel and database smartflow_ai is imported.';
    } else {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $stmt = $pdo->prepare("SELECT id,name,email,password,role,status FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && $admin['status'] === 'active' && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['login_attempts'] = [];
            redirect('dashboard.php');
        } else {
            $_SESSION['login_attempts'][] = $now;
            $error = 'Email or password is incorrect, or the account is disabled.';
            $locked = count($_SESSION['login_attempts']) >= 5;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Portal Login | SmartFlow AI</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
  <main class="auth-shell">
    <!-- Brand Header -->
    <div style="text-align: center; margin-bottom: 24px;">
      <a href="index.php" style="display: inline-flex; align-items: center; gap: 10px;">
        <div class="brand-logo-icon">
          <svg viewBox="0 0 40 40" width="38" height="38" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="40" height="40" rx="10" fill="url(#loginBrandGrad)"/>
            <path d="M10 26C15 26 16 14 22 14C27 14 28 22 32 22" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
            <circle cx="10" cy="26" r="3" fill="#22d3ee"/>
            <circle cx="22" cy="14" r="3" fill="#38bdf8"/>
            <circle cx="32" cy="22" r="3" fill="#ffffff"/>
            <defs>
              <linearGradient id="loginBrandGrad" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
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

    <!-- Login Card in Bright Theme -->
    <section class="auth-card">
      <span class="section-eyebrow" style="margin-bottom: 12px;">MUNICIPAL OPERATIONS</span>
      <h1>Welcome back</h1>
      <p class="subtext">Sign in to access your traffic management dashboard and signal controls.</p>

      <?php if ($error): ?>
        <div class="notice error" role="alert">
          <span style="font-size: 16px;">⚠️</span>
          <div><?= e($error) ?></div>
        </div>
      <?php endif; ?>

      <form method="post" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
          <label class="form-label" for="login_email">Email address</label>
          <input 
            type="email" 
            id="login_email"
            name="email" 
            class="form-control" 
            placeholder="admin@example.com" 
            required 
            autocomplete="username"
            value="<?= e($_POST['email'] ?? '') ?>"
          >
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="password-wrap">
            <input 
              id="password" 
              type="password" 
              name="password" 
              class="form-control" 
              placeholder="Enter your password" 
              required 
              autocomplete="current-password"
            >
            <button class="toggle-pass" type="button" onclick="togglePassword()" aria-label="Show or hide password">
              Show
            </button>
          </div>
        </div>

        <button class="btn btn-primary btn-lg" type="submit" style="width: 100%; margin-top: 10px;" <?= $locked ? 'disabled' : '' ?>>
          Sign in securely <span>→</span>
        </button>
      </form>

      <div class="secure-note">
        <span class="secure-dot"></span> End-to-end encrypted session
      </div>

      <a class="back-link" href="index.php">
        ← Return to SmartFlow AI Home
      </a>
    </section>

    <!-- First time setup hint -->
    <div style="text-align: center; margin-top: 20px; font-size: 12px; color: var(--text-light);">
      First time setting up? <a href="setup_admin.php" style="font-weight: 600; color: var(--sky-600);">Configure Administrator</a>
    </div>

    <p style="text-align: center; color: var(--text-lighter); font-size: 11px; margin-top: 14px;">
      © <?= date('Y') ?> SmartFlow AI · Intelligent Traffic Management
    </p>
  </main>

  <script src="assets/js/main.js"></script>
</body>
</html>
