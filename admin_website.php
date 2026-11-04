<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_admin();

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$initial = strtoupper(substr($admin_name, 0, 1));

$active_tab = $_GET['tab'] ?? 'general';
$success_msg = '';
$error_msg = '';

// Handle Settings Form Submissions
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf()) {
        $error_msg = 'Session security token expired. Please refresh and try again.';
    } else {
        $form_type = $_POST['form_type'] ?? '';

        // 1. General & Hero Settings
        if ($form_type === 'general') {
            $site_title = trim((string)($_POST['site_title'] ?? ''));
            $site_desc = trim((string)($_POST['site_description'] ?? ''));
            $hero_heading = trim((string)($_POST['hero_heading'] ?? ''));
            $hero_subtitle = trim((string)($_POST['hero_subtitle'] ?? ''));
            $hero_bg_image = trim((string)($_POST['hero_bg_image'] ?? ''));

            set_setting('site_title', $site_title);
            set_setting('site_description', $site_desc);
            set_setting('hero_heading', $hero_heading);
            set_setting('hero_subtitle', $hero_subtitle);
            set_setting('hero_bg_image', $hero_bg_image);

            log_admin_activity('UPDATE_WEBSITE', 'Updated general site metadata and hero banner configuration.');
            $success_msg = 'General settings and hero section updated successfully!';
            $active_tab = 'general';
        }

        // 2. Announcements & Sections
        elseif ($form_type === 'announcements') {
            $ann_text = trim((string)($_POST['announcement_text'] ?? ''));
            $ann_status = trim((string)($_POST['announcement_status'] ?? ''));
            $ann_active = isset($_POST['announcement_active']) ? '1' : '0';

            $sec_stats = isset($_POST['section_stats_enabled']) ? '1' : '0';
            $sec_features = isset($_POST['section_features_enabled']) ? '1' : '0';
            $sec_how = isset($_POST['section_how_it_works_enabled']) ? '1' : '0';
            $sec_benefits = isset($_POST['section_benefits_enabled']) ? '1' : '0';
            $sec_cta = isset($_POST['section_cta_enabled']) ? '1' : '0';

            set_setting('announcement_text', $ann_text);
            set_setting('announcement_status', $ann_status);
            set_setting('announcement_active', $ann_active);
            set_setting('section_stats_enabled', $sec_stats);
            set_setting('section_features_enabled', $sec_features);
            set_setting('section_how_it_works_enabled', $sec_how);
            set_setting('section_benefits_enabled', $sec_benefits);
            set_setting('section_cta_enabled', $sec_cta);

            log_admin_activity('UPDATE_ANNOUNCEMENT', 'Modified top announcement bar and section visibility flags.');
            $success_msg = 'Announcement and homepage section display rules updated successfully!';
            $active_tab = 'announcements';
        }

        // 3. Contact & Social Details
        elseif ($form_type === 'contact') {
            $c_email = trim((string)($_POST['contact_email'] ?? ''));
            $c_phone = trim((string)($_POST['contact_phone'] ?? ''));
            $c_addr = trim((string)($_POST['contact_address'] ?? ''));
            $c_hours = trim((string)($_POST['contact_hours'] ?? ''));
            $s_tw = trim((string)($_POST['social_twitter'] ?? ''));
            $s_li = trim((string)($_POST['social_linkedin'] ?? ''));
            $s_gh = trim((string)($_POST['social_github'] ?? ''));

            set_setting('contact_email', $c_email);
            set_setting('contact_phone', $c_phone);
            set_setting('contact_address', $c_addr);
            set_setting('contact_hours', $c_hours);
            set_setting('social_twitter', $s_tw);
            set_setting('social_linkedin', $s_li);
            set_setting('social_github', $s_gh);

            log_admin_activity('UPDATE_CONTACT', 'Updated public contact coordinates and social profile links.');
            $success_msg = 'Contact information and social links updated successfully!';
            $active_tab = 'contact';
        }

        // 4. About Us Content
        elseif ($form_type === 'about') {
            $about_mission = trim((string)($_POST['about_mission'] ?? ''));
            $about_vision = trim((string)($_POST['about_vision'] ?? ''));

            set_setting('about_mission', $about_mission);
            set_setting('about_vision', $about_vision);

            log_admin_activity('UPDATE_ABOUT', 'Modified About Us mission and vision statements.');
            $success_msg = 'About Us mission and vision updated successfully!';
            $active_tab = 'about';
        }

        // 5. Add or Edit Feature Card
        elseif ($form_type === 'save_feature') {
            if ($pdo !== null) {
                $f_id = (int)($_POST['feature_id'] ?? 0);
                $f_title = trim((string)($_POST['feature_title'] ?? ''));
                $f_desc = trim((string)($_POST['feature_description'] ?? ''));
                $f_badge = trim((string)($_POST['feature_badge'] ?? 'AI Module'));
                $f_icon = trim((string)($_POST['feature_icon'] ?? 'camera'));
                $f_link = trim((string)($_POST['feature_link'] ?? 'features.php'));
                $f_order = (int)($_POST['feature_order'] ?? 0);
                $f_active = isset($_POST['feature_active']) ? 1 : 0;

                if ($f_title === '' || $f_desc === '') {
                    $error_msg = 'Feature title and description are required.';
                } else {
                    if ($f_id > 0) {
                        $stmt = $pdo->prepare("UPDATE feature_cards SET title=?, description=?, badge=?, icon=?, link_url=?, sort_order=?, is_active=? WHERE id=?");
                        $stmt->execute([$f_title, $f_desc, $f_badge, $f_icon, $f_link, $f_order, $f_active, $f_id]);
                        log_admin_activity('EDIT_FEATURE', "Updated feature card #{$f_id}: {$f_title}");
                        $success_msg = "Feature card '{$f_title}' updated successfully!";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO feature_cards (title, description, badge, icon, link_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$f_title, $f_desc, $f_badge, $f_icon, $f_link, $f_order, $f_active]);
                        $new_id = $pdo->lastInsertId();
                        log_admin_activity('ADD_FEATURE', "Added new feature card #{$new_id}: {$f_title}");
                        $success_msg = "New feature card '{$f_title}' created successfully!";
                    }
                }
            }
            $active_tab = 'features';
        }

        // 6. Delete Feature Card
        elseif ($form_type === 'delete_feature') {
            if ($pdo !== null) {
                $del_id = (int)($_POST['del_feature_id'] ?? 0);
                if ($del_id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM feature_cards WHERE id = ?");
                    $stmt->execute([$del_id]);
                    log_admin_activity('DELETE_FEATURE', "Deleted feature card #{$del_id}");
                    $success_msg = "Feature card #{$del_id} removed successfully.";
                }
            }
            $active_tab = 'features';
        }
    }
}

// Fetch all feature cards
$feature_cards = [];
if ($pdo !== null) {
    try {
        $feature_cards = $pdo->query("SELECT * FROM feature_cards ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Throwable $e) {}
}

// If editing a specific feature card
$edit_feature = null;
if (isset($_GET['edit_feature']) && $pdo !== null) {
    $ef_id = (int)$_GET['edit_feature'];
    $stmt = $pdo->prepare("SELECT * FROM feature_cards WHERE id = ?");
    $stmt->execute([$ef_id]);
    $edit_feature = $stmt->fetch();
    if ($edit_feature) {
        $active_tab = 'features';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Website Content Management | SmartFlow AI</title>
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
        <span class="section-eyebrow" style="margin-bottom: 6px;">WEBSITE MANAGEMENT</span>
        <h1>Content & Homepage Customization</h1>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <a href="index.php" target="_blank" class="btn btn-secondary btn-sm">Preview Public Home ↗</a>
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

    <!-- Navigation Tab Toolbar -->
    <div class="junction-filter-bar" style="margin-bottom: 24px;">
      <div class="filter-btn-group">
        <a href="admin_website.php?tab=general" class="filter-btn <?= ($active_tab === 'general') ? 'active' : '' ?>">
          🏠 General & Hero
        </a>
        <a href="admin_website.php?tab=features" class="filter-btn <?= ($active_tab === 'features') ? 'active' : '' ?>">
          ✨ Feature Cards (<?= count($feature_cards) ?>)
        </a>
        <a href="admin_website.php?tab=about" class="filter-btn <?= ($active_tab === 'about') ? 'active' : '' ?>">
          📖 About Us
        </a>
        <a href="admin_website.php?tab=contact" class="filter-btn <?= ($active_tab === 'contact') ? 'active' : '' ?>">
          📞 Contact & Social
        </a>
        <a href="admin_website.php?tab=announcements" class="filter-btn <?= ($active_tab === 'announcements') ? 'active' : '' ?>">
          📢 Announcements & Sections
        </a>
      </div>
    </div>

    <!-- TAB 1: General & Hero Settings -->
    <?php if ($active_tab === 'general'): ?>
      <div class="panel">
        <h3 style="font-size: 20px; color: var(--navy-900); margin-bottom: 6px;">Website Identity & Hero Section</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
          Configure global metadata, the primary brand title, and the full-width smart city hero presentation.
        </p>

        <form method="post" action="admin_website.php?tab=general">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="form_type" value="general">

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Website Title</label>
              <input type="text" name="site_title" class="form-control" value="<?= e(get_setting('site_title', 'SmartFlow AI – Intelligent Traffic Management')) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Hero Background Image Path / URL</label>
              <input type="text" name="hero_bg_image" class="form-control" value="<?= e(get_setting('hero_bg_image', 'assets/images/traffic_hero_bg.svg')) ?>" required>
              <small style="color: var(--text-light); font-size: 11px;">E.g. <code>assets/images/traffic_hero_bg.svg</code> or an uploaded file path.</small>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Website Meta Description</label>
            <textarea name="site_description" class="form-control" style="min-height: 80px;" required><?= e(get_setting('site_description', 'Next-generation adaptive traffic management platform powered by AI computer vision.')) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Hero Main Heading</label>
            <input type="text" name="hero_heading" class="form-control" value="<?= e(get_setting('hero_heading', 'SmartFlow AI – Intelligent Traffic Management')) ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label">Hero Subtitle</label>
            <textarea name="hero_subtitle" class="form-control" style="min-height: 90px;" required><?= e(get_setting('hero_subtitle', 'Eliminate urban bottlenecks with AI-based traffic optimisation.')) ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg">
            Save General & Hero Settings
          </button>
        </form>
      </div>

    <!-- TAB 2: Feature Cards Management -->
    <?php elseif ($active_tab === 'features'): ?>
      <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px;">
        <!-- Left: List of Existing Feature Cards -->
        <div class="panel">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
              <h3 style="font-size: 19px; color: var(--navy-900); margin: 0;">Homepage Feature Cards</h3>
              <small style="color: var(--text-light); font-size: 12px;">Equal-height cards displayed on the public home page</small>
            </div>
            <a href="admin_website.php?tab=features#featureForm" class="btn btn-primary btn-sm">+ New Card</a>
          </div>

          <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php if (empty($feature_cards)): ?>
              <p style="color: var(--text-light); font-size: 13px;">No feature cards found in the database. Run migration or add one.</p>
            <?php else: ?>
              <?php foreach ($feature_cards as $fc): ?>
                <div style="background-color: var(--bg-subtle); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 16px; display: flex; justify-content: space-between; align-items: flex-start; gap: 14px;">
                  <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                      <span class="feature-badge" style="font-size: 10px;"><?= e($fc['badge']) ?></span>
                      <strong style="font-size: 15px; color: var(--navy-900);"><?= e($fc['title']) ?></strong>
                      <?php if (!$fc['is_active']): ?>
                        <span style="font-size: 10px; background: #fee2e2; color: var(--rose-600); padding: 1px 6px; border-radius: 4px; font-weight: 700;">Disabled</span>
                      <?php endif; ?>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 0 0 8px; line-height: 1.5;">
                      <?= e($fc['description']) ?>
                    </p>
                    <small style="color: var(--text-light); font-size: 11px;">
                      Order: <?= (int)$fc['sort_order'] ?> · Link: <?= e($fc['link_url']) ?>
                    </small>
                  </div>

                  <div style="display: flex; align-items: center; gap: 6px;">
                    <a href="admin_website.php?tab=features&edit_feature=<?= (int)$fc['id'] ?>#featureForm" class="btn btn-secondary btn-sm" title="Edit feature card">
                      ✏️ Edit
                    </a>
                    <form method="post" action="admin_website.php?tab=features" onsubmit="return confirm('Delete feature card #<?= (int)$fc['id'] ?>?');">
                      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                      <input type="hidden" name="form_type" value="delete_feature">
                      <input type="hidden" name="del_feature_id" value="<?= (int)$fc['id'] ?>">
                      <button type="submit" class="btn btn-ghost btn-sm" style="color: var(--rose-600);" title="Delete feature card">
                        🗑️
                      </button>
                    </form>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Right: Add / Edit Feature Card Form -->
        <div class="panel" id="featureForm">
          <h3 style="font-size: 19px; color: var(--navy-900); margin-bottom: 6px;">
            <?= $edit_feature ? 'Edit Feature Card #' . (int)$edit_feature['id'] : 'Add New Feature Card' ?>
          </h3>
          <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 20px;">
            <?= $edit_feature ? 'Modify existing feature card content below.' : 'Create a new feature card for the public website.' ?>
          </p>

          <form method="post" action="admin_website.php?tab=features">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="form_type" value="save_feature">
            <input type="hidden" name="feature_id" value="<?= (int)($edit_feature['id'] ?? 0) ?>">

            <div class="form-group">
              <label class="form-label">Card Title *</label>
              <input type="text" name="feature_title" class="form-control" placeholder="E.g., Automated Emergency Waves" value="<?= e($edit_feature['title'] ?? '') ?>" required>
            </div>

            <div class="form-group">
              <label class="form-label">Pill Badge Tag</label>
              <input type="text" name="feature_badge" class="form-control" placeholder="E.g., YOLOv8 Edge" value="<?= e($edit_feature['badge'] ?? 'AI Module') ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Description *</label>
              <textarea name="feature_description" class="form-control" style="min-height: 90px;" placeholder="Describe what this intelligent feature does..." required><?= e($edit_feature['description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Learn More Link</label>
                <input type="text" name="feature_link" class="form-control" value="<?= e($edit_feature['link_url'] ?? 'features.php') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="feature_order" class="form-control" value="<?= (int)($edit_feature['sort_order'] ?? 1) ?>">
              </div>
            </div>

            <div class="form-group">
              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="feature_active" value="1" <?= (!isset($edit_feature) || $edit_feature['is_active']) ? 'checked' : '' ?>>
                <strong>Enable card on public website</strong>
              </label>
            </div>

            <div style="display: flex; gap: 10px;">
              <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                <?= $edit_feature ? 'Update Feature Card' : 'Create Feature Card' ?>
              </button>
              <?php if ($edit_feature): ?>
                <a href="admin_website.php?tab=features" class="btn btn-secondary btn-lg">Cancel</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>

    <!-- TAB 3: About Us Content -->
    <?php elseif ($active_tab === 'about'): ?>
      <div class="panel">
        <h3 style="font-size: 20px; color: var(--navy-900); margin-bottom: 6px;">Manage About Us Content</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
          Update the corporate mission, technical vision, and strategic goals presented on the About page.
        </p>

        <form method="post" action="admin_website.php?tab=about">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="form_type" value="about">

          <div class="form-group">
            <label class="form-label">Mission Statement</label>
            <textarea name="about_mission" class="form-control" style="min-height: 120px;" required><?= e(get_setting('about_mission', 'Pioneering autonomous urban mobility with closed-loop AI computer vision, decentralized edge intelligence, and dynamic green wave synchronization.')) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label">Vision Statement & Goals</label>
            <textarea name="about_vision" class="form-control" style="min-height: 120px;" required><?= e(get_setting('about_vision', 'Zero gridlock, zero delay for emergency vehicles, and dramatically lower municipal transport emissions.')) ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg">
            Save About Us Content
          </button>
        </form>
      </div>

    <!-- TAB 4: Contact & Social Details -->
    <?php elseif ($active_tab === 'contact'): ?>
      <div class="panel">
        <h3 style="font-size: 20px; color: var(--navy-900); margin-bottom: 6px;">Manage Contact Coordinates & Social Profiles</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
          These details are displayed in the site footer, contact page, and header announcement bar.
        </p>

        <form method="post" action="admin_website.php?tab=contact">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="form_type" value="contact">

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Operations Email Address</label>
              <input type="email" name="contact_email" class="form-control" value="<?= e(get_setting('contact_email', 'operations@smartflow-ai.org')) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">24/7 Operations Dispatch Phone</label>
              <input type="text" name="contact_phone" class="form-control" value="<?= e(get_setting('contact_phone', '+1 (800) 555-FLOW')) ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Smart Mobility Lab HQ Address</label>
              <input type="text" name="contact_address" class="form-control" value="<?= e(get_setting('contact_address', '100 Innovation Boulevard, Smart Mobility Lab, District 4')) ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Operations Hours Label</label>
              <input type="text" name="contact_hours" class="form-control" value="<?= e(get_setting('contact_hours', 'Central Operations: 24/7/365')) ?>" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Twitter / X Profile URL</label>
              <input type="url" name="social_twitter" class="form-control" value="<?= e(get_setting('social_twitter', 'https://twitter.com')) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">LinkedIn Organization URL</label>
              <input type="url" name="social_linkedin" class="form-control" value="<?= e(get_setting('social_linkedin', 'https://linkedin.com')) ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">GitHub Repository URL</label>
            <input type="url" name="social_github" class="form-control" value="<?= e(get_setting('social_github', 'https://github.com')) ?>">
          </div>

          <button type="submit" class="btn btn-primary btn-lg">
            Save Contact Details & Social Links
          </button>
        </form>
      </div>

    <!-- TAB 5: Announcements & Sections -->
    <?php elseif ($active_tab === 'announcements'): ?>
      <div class="panel">
        <h3 style="font-size: 20px; color: var(--navy-900); margin-bottom: 6px;">Website Announcement & Homepage Section Toggles</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
          Manage the top notification alert bar and toggle visibility of homepage sections.
        </p>

        <form method="post" action="admin_website.php?tab=announcements">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="form_type" value="announcements">

          <div style="background-color: var(--bg-subtle); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-light); margin-bottom: 24px;">
            <h4 style="font-size: 16px; color: var(--navy-900); margin-bottom: 14px;">Top Announcement Bar</h4>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Badge Status Text</label>
                <input type="text" name="announcement_status" class="form-control" value="<?= e(get_setting('announcement_status', 'SIMULATION NETWORK ONLINE')) ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Announcement Message</label>
                <input type="text" name="announcement_text" class="form-control" value="<?= e(get_setting('announcement_text', 'SmartFlow AI v2.4 Active · Dynamic Signal Optimisation Enabled')) ?>" required>
              </div>
            </div>

            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
              <input type="checkbox" name="announcement_active" value="1" <?= (get_setting('announcement_active', '1') === '1') ? 'checked' : '' ?>>
              <strong>Show top announcement bar across all public pages</strong>
            </label>
          </div>

          <div style="background-color: var(--bg-subtle); padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-light); margin-bottom: 24px;">
            <h4 style="font-size: 16px; color: var(--navy-900); margin-bottom: 14px;">Homepage Section Visibility</h4>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">
              Select which sections should be active and rendered on <code>index.php</code>:
            </p>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;">
              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="section_stats_enabled" value="1" <?= (get_setting('section_stats_enabled', '1') === '1') ? 'checked' : '' ?>>
                <span>Key Statistics & Animated Counters</span>
              </label>

              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="section_features_enabled" value="1" <?= (get_setting('section_features_enabled', '1') === '1') ? 'checked' : '' ?>>
                <span>Feature Cards Grid</span>
              </label>

              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="section_how_it_works_enabled" value="1" <?= (get_setting('section_how_it_works_enabled', '1') === '1') ? 'checked' : '' ?>>
                <span>How It Works (4-Stage Process Flow)</span>
              </label>

              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="section_benefits_enabled" value="1" <?= (get_setting('section_benefits_enabled', '1') === '1') ? 'checked' : '' ?>>
                <span>Smart-City Benefits Grid</span>
              </label>

              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                <input type="checkbox" name="section_cta_enabled" value="1" <?= (get_setting('section_cta_enabled', '1') === '1') ? 'checked' : '' ?>>
                <span>Bottom Call to Action Banner</span>
              </label>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-lg">
            Save Announcement & Section Toggles
          </button>
        </form>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>

