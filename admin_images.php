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
$upload_dir = __DIR__ . '/assets/images/uploads';

if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}

// Handle Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf()) {
        $error_msg = 'Security token invalid. Please refresh and retry.';
    } else {
        $op = $_POST['operation'] ?? '';

        // 1. Upload File
        if ($op === 'upload_image') {
            if (!isset($_FILES['image_file']) || $_FILES['image_file']['error'] !== UPLOAD_ERR_OK) {
                $error_msg = 'File upload failed or no file was selected.';
            } else {
                $file = $_FILES['image_file'];
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed_extensions, true)) {
                    $error_msg = 'Invalid file type. Allowed formats: JPG, PNG, SVG, WEBP.';
                } elseif ($file['size'] > 5 * 1024 * 1024) {
                    $error_msg = 'File size exceeds maximum 5MB limit.';
                } else {
                    $safe_basename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
                    $new_filename = $safe_basename . '_' . time() . '.' . $ext;
                    $target_path = $upload_dir . '/' . $new_filename;
                    $relative_path = 'assets/images/uploads/' . $new_filename;

                    if (move_uploaded_file($file['tmp_name'], $target_path)) {
                        $caption = trim((string)($_POST['caption'] ?? ''));
                        if ($pdo !== null) {
                            try {
                                $stmt = $pdo->prepare("INSERT INTO uploaded_images (file_name, file_path, file_size, mime_type, caption, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
                                $stmt->execute([$new_filename, $relative_path, $file['size'], $file['type'], $caption, $admin_name]);
                            } catch (Throwable $e) {}
                        }
                        log_admin_activity('UPLOAD_IMAGE', "Uploaded media file: {$new_filename}");
                        $success_msg = "Image uploaded successfully to {$relative_path}!";
                    } else {
                        $error_msg = 'Could not save file to disk. Check permissions on assets/images/uploads.';
                    }
                }
            }
        }

        // 2. Set as Hero Background Image
        elseif ($op === 'set_hero') {
            $selected_path = trim((string)($_POST['image_path'] ?? ''));
            if ($selected_path !== '') {
                set_setting('hero_bg_image', $selected_path);
                log_admin_activity('SET_HERO_IMAGE', "Updated home page hero background image to: {$selected_path}");
                $success_msg = "Hero background updated to {$selected_path}!";
            }
        }

        // 3. Delete Uploaded Image
        elseif ($op === 'delete_image') {
            $img_id = (int)($_POST['image_id'] ?? 0);
            $file_path = trim((string)($_POST['file_path'] ?? ''));

            if ($pdo !== null && $img_id > 0) {
                $stmt = $pdo->prepare("DELETE FROM uploaded_images WHERE id = ?");
                $stmt->execute([$img_id]);
            }

            if ($file_path !== '' && file_exists(__DIR__ . '/' . $file_path)) {
                @unlink(__DIR__ . '/' . $file_path);
            }

            log_admin_activity('DELETE_IMAGE', "Deleted media file: {$file_path}");
            $success_msg = "Image removed successfully.";
        }
    }
}

// Fetch images from database
$uploaded_db_images = [];
if ($pdo !== null) {
    try {
        $uploaded_db_images = $pdo->query("SELECT * FROM uploaded_images ORDER BY id DESC")->fetchAll();
    } catch (Throwable $e) {}
}

// System Built-in Images
$system_images = [
    ['file_name' => 'traffic_hero_bg.svg', 'file_path' => 'assets/images/traffic_hero_bg.svg', 'caption' => 'Default Vector City Grid Hero', 'file_size' => 5265],
    ['file_name' => 'junction_diagram.svg', 'file_path' => 'assets/images/junction_diagram.svg', 'caption' => '4-Way Road Layout Schematic', 'file_size' => 6867],
    ['file_name' => 'traffic_banner.svg', 'file_path' => 'assets/images/traffic_banner.svg', 'caption' => 'Infrastructure Highway Flow Vector', 'file_size' => 3541],
];

$current_hero = get_setting('hero_bg_image', 'assets/images/traffic_hero_bg.svg');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Website Media Library | SmartFlow AI</title>
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
        <span class="section-eyebrow" style="margin-bottom: 6px;">MEDIA STORAGE</span>
        <h1>Website Images & Asset Library</h1>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <span style="font-size: 12px; color: var(--text-light);">
          Current Hero: <strong><?= e(basename($current_hero)) ?></strong>
        </span>
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

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: flex-start;">
      <!-- Left: Image Gallery Grid -->
      <div class="panel">
        <h3 style="font-size: 19px; color: var(--navy-900); margin-bottom: 6px;">Media Gallery</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
          Available assets for website hero sections, junction cards, and promotional content.
        </p>

        <!-- Uploaded Images Section -->
        <h4 style="font-size: 15px; color: var(--navy-900); margin-bottom: 12px;">Uploaded Assets</h4>
        <?php if (empty($uploaded_db_images)): ?>
          <div style="background-color: var(--bg-subtle); padding: 20px; border-radius: var(--radius-md); text-align: center; color: var(--text-light); font-size: 13px; margin-bottom: 24px;">
            No user uploads yet. Use the upload tool on the right to upload custom city images or photography.
          </div>
        <?php else: ?>
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px;">
            <?php foreach ($uploaded_db_images as $img): ?>
              <div style="background: #ffffff; border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column;">
                <div style="height: 140px; background-color: var(--navy-900); overflow: hidden; position: relative;">
                  <img src="<?= e($img['file_path']) ?>" alt="<?= e($img['caption'] ?? '') ?>" style="width: 100%; height: 100%; object-fit: cover;">
                  <?php if ($current_hero === $img['file_path']): ?>
                    <span style="position: absolute; top: 8px; right: 8px; font-size: 10px; font-weight: 800; background: var(--emerald-600); color: white; padding: 2px 7px; border-radius: 4px;">
                      Active Hero
                    </span>
                  <?php endif; ?>
                </div>
                <div style="padding: 12px; flex: 1; display: flex; flex-direction: column;">
                  <strong style="font-size: 12px; color: var(--navy-900); word-break: break-all; margin-bottom: 4px;">
                    <?= e($img['file_name']) ?>
                  </strong>
                  <small style="color: var(--text-light); font-size: 10px; margin-bottom: 10px;">
                    <?= round($img['file_size'] / 1024, 1) ?> KB · <?= e($img['uploaded_by']) ?>
                  </small>

                  <div style="margin-top: auto; display: flex; gap: 6px; border-top: 1px solid var(--border-light); padding-top: 8px;">
                    <form method="post" action="admin_images.php" style="flex: 1;">
                      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="operation" value="set_hero">
                      <input type="hidden" name="image_path" value="<?= e($img['file_path']) ?>">
                      <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; font-size: 11px;">
                        Set as Hero
                      </button>
                    </form>
                    <form method="post" action="admin_images.php" onsubmit="return confirm('Delete this image?');">
                      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="operation" value="delete_image">
                      <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                      <input type="hidden" name="file_path" value="<?= e($img['file_path']) ?>">
                      <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--rose-600);">
                        🗑️
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- System Built-in Assets Section -->
        <h4 style="font-size: 15px; color: var(--navy-900); margin-bottom: 12px;">System Vector Backdrops</h4>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
          <?php foreach ($system_images as $s_img): ?>
            <div style="background: #ffffff; border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm); display: flex; flex-direction: column;">
              <div style="height: 140px; background-color: var(--navy-950); overflow: hidden; position: relative;">
                <img src="<?= e($s_img['file_path']) ?>" alt="<?= e($s_img['caption']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php if ($current_hero === $s_img['file_path']): ?>
                  <span style="position: absolute; top: 8px; right: 8px; font-size: 10px; font-weight: 800; background: var(--emerald-600); color: white; padding: 2px 7px; border-radius: 4px;">
                    Active Hero
                  </span>
                <?php endif; ?>
              </div>
              <div style="padding: 12px; flex: 1; display: flex; flex-direction: column;">
                <strong style="font-size: 12px; color: var(--navy-900); margin-bottom: 2px;">
                  <?= e($s_img['file_name']) ?>
                </strong>
                <small style="color: var(--text-light); font-size: 11px; margin-bottom: 10px;">
                  <?= e($s_img['caption']) ?>
                </small>

                <div style="margin-top: auto; border-top: 1px solid var(--border-light); padding-top: 8px;">
                  <form method="post" action="admin_images.php">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="operation" value="set_hero">
                    <input type="hidden" name="image_path" value="<?= e($s_img['file_path']) ?>">
                    <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; font-size: 11px;" <?= ($current_hero === $s_img['file_path']) ? 'disabled' : '' ?>>
                      <?= ($current_hero === $s_img['file_path']) ? 'Current Active Hero' : 'Set as Hero' ?>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right: Upload Form -->
      <div class="panel">
        <h3 style="font-size: 18px; color: var(--navy-900); margin-bottom: 6px;">Upload New Image</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
          Upload high-resolution smart-city, traffic signal, or roadway images.
        </p>

        <form method="post" action="admin_images.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="operation" value="upload_image">

          <div class="form-group">
            <label class="form-label">Select File *</label>
            <input type="file" name="image_file" class="form-control" accept=".jpg,.jpeg,.png,.svg,.webp" required>
            <small style="color: var(--text-light); font-size: 11px;">Formats: JPG, PNG, SVG, WEBP (Max 5MB)</small>
          </div>

          <div class="form-group">
            <label class="form-label">Caption / Description</label>
            <input type="text" name="caption" class="form-control" placeholder="E.g., North Arterial Highway Night">
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 10px;">
            Upload Image to Library <span>↑</span>
          </button>
        </form>
      </div>
    </div>
  </main>
</body>
</html>

