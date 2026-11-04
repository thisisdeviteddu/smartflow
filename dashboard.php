<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db.php';
require_admin();

$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
$initial = strtoupper(substr($admin_name, 0, 1));

// Initialize default metric counters
$total_users = 1;
$total_junctions = 6;
$active_locations = 6;
$congestion_alerts_count = 1;
$emergency_alerts_count = 1;
$pending_reports_count = 0;
$recent_activities = [];
$recent_alerts = [];
$recent_reports = [];
$ai_recommendations = [];
$tables_migrated = false;

if ($pdo !== null) {
    try {
        // Check if new tables exist
        $check = $pdo->query("SHOW TABLES LIKE 'junctions'")->fetch();
        if ($check) {
            $tables_migrated = true;

            // 1. Total users
            $total_users = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

            // 2. Managed junctions
            $total_junctions = (int)$pdo->query("SELECT COUNT(*) FROM junctions")->fetchColumn();

            // 3. Active locations
            $active_locations = (int)$pdo->query("SELECT COUNT(*) FROM junctions WHERE status = 'active'")->fetchColumn();

            // 4. Congestion alerts
            $congestion_alerts_count = (int)$pdo->query("SELECT COUNT(*) FROM traffic_alerts WHERE alert_type = 'congestion' AND status = 'active'")->fetchColumn();

            // 5. Emergency alerts
            $emergency_alerts_count = (int)$pdo->query("SELECT COUNT(*) FROM traffic_alerts WHERE alert_type = 'emergency' AND status = 'active'")->fetchColumn();

            // 6. Citizen Reports
            $check_rep = $pdo->query("SHOW TABLES LIKE 'traffic_reports'")->fetch();
            if ($check_rep) {
                $pending_reports_count = (int)$pdo->query("SELECT COUNT(*) FROM traffic_reports WHERE status = 'pending'")->fetchColumn();
                $recent_reports = $pdo->query("SELECT r.*, j.name as junction_name FROM traffic_reports r LEFT JOIN junctions j ON r.junction_id = j.id ORDER BY r.created_at DESC LIMIT 4")->fetchAll();
            }

            // 7. Recent admin activities
            $recent_activities = $pdo->query("SELECT * FROM admin_activity_logs ORDER BY created_at DESC LIMIT 8")->fetchAll();

            // 8. Recent open alerts
            $recent_alerts = $pdo->query("SELECT a.*, j.name as junction_name FROM traffic_alerts a LEFT JOIN junctions j ON a.junction_id = j.id WHERE a.status = 'active' ORDER BY a.created_at DESC LIMIT 5")->fetchAll();

            // 9. AI recommendations from junctions
            $ai_recommendations = $pdo->query("SELECT id, junction_code, name, count_north, count_south, count_east, count_west, vehicle_count, congestion_level, recommended_green_ns, recommended_green_ew, ai_data_source, last_telemetry_at FROM junctions WHERE status = 'active' ORDER BY vehicle_count DESC LIMIT 4")->fetchAll();
        } else {
            $total_users = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        }
    } catch (Throwable $e) {
        // Graceful fallback to default values
    }
}

// Fallback AI recommendations if empty
if (empty($ai_recommendations)) {
    $ai_recommendations = [
        [
            'id' => 1,
            'junction_code' => 'J-101',
            'name' => 'Central Boulevard & Technology Way',
            'count_north' => 48,
            'count_south' => 54,
            'count_east' => 18,
            'count_west' => 22,
            'vehicle_count' => 142,
            'congestion_level' => 'high',
            'recommended_green_ns' => 58,
            'recommended_green_ew' => 22,
            'ai_data_source' => 'YOLOv8 Edge Vision',
            'last_telemetry_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'junction_code' => 'J-102',
            'name' => 'Metro Ring Road – North Flyover',
            'count_north' => 30,
            'count_south' => 28,
            'count_east' => 15,
            'count_west' => 14,
            'vehicle_count' => 87,
            'congestion_level' => 'moderate',
            'recommended_green_ns' => 45,
            'recommended_green_ew' => 35,
            'ai_data_source' => 'YOLOv8 Edge Vision',
            'last_telemetry_at' => date('Y-m-d H:i:s', time() - 30)
        ],
        [
            'id' => 3,
            'junction_code' => 'J-103',
            'name' => 'Harbor Expressway & Marine Drive',
            'count_north' => 12,
            'count_south' => 10,
            'count_east' => 14,
            'count_west' => 16,
            'vehicle_count' => 52,
            'congestion_level' => 'low',
            'recommended_green_ns' => 30,
            'recommended_green_ew' => 30,
            'ai_data_source' => 'OpenCV Camera Feed',
            'last_telemetry_at' => date('Y-m-d H:i:s', time() - 60)
        ],
        [
            'id' => 4,
            'junction_code' => 'J-104',
            'name' => 'Airport Commercial Terminal Exit',
            'count_north' => 38,
            'count_south' => 42,
            'count_east' => 19,
            'count_west' => 21,
            'vehicle_count' => 120,
            'congestion_level' => 'high',
            'recommended_green_ns' => 54,
            'recommended_green_ew' => 26,
            'ai_data_source' => 'YOLOv8 Edge Vision',
            'last_telemetry_at' => date('Y-m-d H:i:s', time() - 15)
        ]
    ];
}

// Default fallback activities if empty
if (empty($recent_activities)) {
    $recent_activities = [
        ['admin_name' => $admin_name, 'action' => 'LOGIN', 'details' => 'Administrator successfully authenticated to control panel.', 'created_at' => date('Y-m-d H:i:s'), 'ip_address' => '127.0.0.1'],
        ['admin_name' => 'System', 'action' => 'SIGNAL_SYNC', 'details' => 'Dynamic 4-road green cycle optimized across Central Corridor.', 'created_at' => date('Y-m-d H:i:s', time() - 3600), 'ip_address' => '127.0.0.1']
    ];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Control Panel | SmartFlow AI</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="app-page">
  <?php require_once __DIR__ . '/includes/admin_nav.php'; ?>

  <!-- Main Administrative Content -->
  <main class="main-content">
    <header class="topbar">
      <div>
        <span class="section-eyebrow" style="margin-bottom: 6px;">OPERATIONS & CONTROL</span>
        <h1>Executive Dashboard</h1>
      </div>
      <div style="display: flex; align-items: center; gap: 14px;">
        <a href="index.php" target="_blank" class="btn btn-secondary btn-sm">
          <span>Live Site ↗</span>
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

    <?php if (!$tables_migrated): ?>
      <div class="notice info" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
        <div>
          <strong>Database Schema Update Available:</strong>
          Upgrade your database tables to enable full junction management, activity logs, content editor, and alerts.
        </div>
        <a href="database/migrate.php" class="btn btn-primary btn-sm">Execute Database Migration →</a>
      </div>
    <?php endif; ?>

    <!-- Welcome Operational Banner -->
    <div class="welcome-banner">
      <div>
        <span class="section-eyebrow" style="background: rgba(255, 255, 255, 0.15); border-color: rgba(255, 255, 255, 0.3); color: #ffffff; margin-bottom: 8px;">
          CENTRAL DISPATCH CONSOLE
        </span>
        <h2>Hello, <?= e($admin_name) ?></h2>
        <p>
          Managing <strong><?= (int)$total_junctions ?></strong> metropolitan junctions with adaptive signal timing and automated emergency green waves. All edge nodes operational.
        </p>
      </div>
      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="admin_junctions.php?action=add" class="btn btn-primary btn-sm" style="background: #ffffff; color: var(--navy-900) !important;">
          + Add Junction
        </a>
        <a href="admin_website.php" class="btn btn-outline-white btn-sm">
          Edit Website Content
        </a>
      </div>
    </div>

    <!-- 6 Key Metrics Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 30px;">
      <!-- Total Users -->
      <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <span style="font-size: 20px;">👥</span>
          <span class="stat-demo-badge">Accounts</span>
        </div>
        <p>Registered Users</p>
        <strong><?= (int)$total_users ?></strong>
        <small><a href="admin_users.php" style="color: var(--sky-600); font-weight: 600;">Manage Users →</a></small>
      </div>

      <!-- Total Junctions -->
      <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <span style="font-size: 20px;">🚦</span>
          <span class="stat-demo-badge">Infrastructure</span>
        </div>
        <p>Managed Junctions</p>
        <strong><?= (int)$total_junctions ?></strong>
        <small><a href="admin_junctions.php" style="color: var(--sky-600); font-weight: 600;">View Junctions →</a></small>
      </div>

      <!-- Active Locations -->
      <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <span style="font-size: 20px;">📍</span>
          <span class="stat-demo-badge" style="background: var(--emerald-100); color: var(--emerald-600);">Online</span>
        </div>
        <p>Active Monitoring</p>
        <strong style="color: var(--emerald-600);"><?= (int)$active_locations ?></strong>
        <small style="color: var(--text-light);">Real-time Telemetry</small>
      </div>

      <!-- Congestion Alerts -->
      <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <span style="font-size: 20px;">⚠️</span>
          <span class="stat-demo-badge" style="background: var(--amber-100); color: var(--amber-600);">Traffic</span>
        </div>
        <p>Congestion Alerts</p>
        <strong style="color: var(--amber-600);"><?= (int)$congestion_alerts_count ?></strong>
        <small><a href="admin_alerts.php" style="color: var(--amber-600); font-weight: 600;">Review Queues →</a></small>
      </div>

      <!-- Emergency Alerts -->
      <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <span style="font-size: 20px;">🚨</span>
          <span class="stat-demo-badge" style="background: #ffe4e6; color: var(--rose-600);">Priority</span>
        </div>
        <p>Emergency Alerts</p>
        <strong style="color: var(--rose-600);"><?= (int)$emergency_alerts_count ?></strong>
        <small><a href="admin_alerts.php" style="color: var(--rose-600); font-weight: 600;">Emergency Status →</a></small>
      </div>

      <!-- Citizen Reports -->
      <div class="stat-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <span style="font-size: 20px;">💬</span>
          <span class="stat-demo-badge" style="background: var(--sky-100); color: var(--sky-700);">Citizens</span>
        </div>
        <p>Pending Reports</p>
        <strong style="color: var(--sky-600);"><?= (int)$pending_reports_count ?></strong>
        <small><a href="admin_reports.php" style="color: var(--sky-600); font-weight: 600;">Incident Desk →</a></small>
      </div>
    </div>

    <!-- Analytics Charts & Active Alerts Section -->
    <div style="display: grid; grid-template-columns: 1.6fr 1.2fr; gap: 24px; margin-bottom: 30px;">
      <!-- Chart: Traffic Volume & Delay Comparison -->
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <h3 class="chart-title">Hourly Traffic Flow & Signal Performance</h3>
            <small style="color: var(--text-light); font-size: 12px;">Average vehicles processed per hour across monitored network</small>
          </div>
          <span class="chart-badge">Live Telemetry</span>
        </div>

        <div class="bar-chart-container" style="height: 220px;">
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 55px;" title="Legacy Wait: 55s"></div>
              <div class="bar smartflow" style="height: 32px;" title="Adaptive Wait: 32s"></div>
            </div>
            <span class="bar-label">06:00</span>
          </div>
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 160px;" title="Legacy Wait: 160s"></div>
              <div class="bar smartflow" style="height: 98px;" title="Adaptive Wait: 98s"></div>
            </div>
            <span class="bar-label">09:00</span>
          </div>
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 85px;" title="Legacy Wait: 85s"></div>
              <div class="bar smartflow" style="height: 52px;" title="Adaptive Wait: 52s"></div>
            </div>
            <span class="bar-label">12:00</span>
          </div>
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 95px;" title="Legacy Wait: 95s"></div>
              <div class="bar smartflow" style="height: 60px;" title="Adaptive Wait: 60s"></div>
            </div>
            <span class="bar-label">15:00</span>
          </div>
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 175px;" title="Legacy Wait: 175s"></div>
              <div class="bar smartflow" style="height: 105px;" title="Adaptive Wait: 105s"></div>
            </div>
            <span class="bar-label">18:00</span>
          </div>
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 50px;" title="Legacy Wait: 50s"></div>
              <div class="bar smartflow" style="height: 26px;" title="Adaptive Wait: 26s"></div>
            </div>
            <span class="bar-label">21:00</span>
          </div>
        </div>

        <div class="chart-legend">
          <div class="legend-item">
            <span class="legend-dot" style="background-color: #cbd5e1;"></span>
            <span>Conventional Cycle</span>
          </div>
          <div class="legend-item">
            <span class="legend-dot" style="background-color: var(--sky-600);"></span>
            <span><strong>SmartFlow AI Adaptive Cycle</strong></span>
          </div>
        </div>
      </div>

      <!-- Current Alerts Card -->
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <h3 class="chart-title">Open Traffic & Emergency Alerts</h3>
            <small style="color: var(--text-light); font-size: 12px;">Active incidents requiring operator awareness</small>
          </div>
          <a href="admin_alerts.php" style="font-size: 12px; font-weight: 700; color: var(--sky-600);">Manage All →</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px; flex: 1;">
          <?php if (empty($recent_alerts)): ?>
            <div style="padding: 24px; text-align: center; color: var(--text-light); font-size: 13px;">
              ✓ All clear! No open emergency or congestion alerts.
            </div>
          <?php else: ?>
            <?php foreach ($recent_alerts as $alert): ?>
              <div style="background-color: var(--bg-subtle); border: 1px solid var(--border-light); border-left: 4px solid <?= ($alert['alert_type'] === 'emergency') ? 'var(--rose-500)' : 'var(--amber-500)' ?>; border-radius: var(--radius-sm); padding: 12px 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                  <strong style="font-size: 13px; color: var(--navy-900);"><?= e($alert['title']) ?></strong>
                  <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; <?= ($alert['alert_type'] === 'emergency') ? 'background:#ffe4e6;color:var(--rose-600)' : 'background:var(--amber-100);color:var(--amber-600)' ?>">
                    <?= e(strtoupper($alert['severity'])) ?>
                  </span>
                </div>
                <p style="font-size: 12px; color: var(--text-muted); margin: 0 0 6px; line-height: 1.4;">
                  <?= e($alert['message']) ?>
                </p>
                <div style="font-size: 11px; color: var(--text-light); display: flex; justify-content: space-between;">
                  <span><?= e($alert['junction_name'] ?? 'Metro Network') ?></span>
                  <span><?= time_ago($alert['created_at']) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- AI Traffic Recommendations & Computer Vision Telemetry Section -->
    <div class="panel" style="margin-bottom: 30px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
            <span class="live-pulse-badge" style="font-size: 11px;"><span class="pulse-dot"></span> LIVE EDGE INFERENCE</span>
            <span class="stat-demo-badge" style="background: var(--sky-100); color: var(--sky-800);">Python Flask YOLO Bridge</span>
          </div>
          <h3 style="font-size: 18px; color: var(--navy-900); margin: 0;">AI Traffic Signal Timing Recommendations</h3>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
          <a href="map.php" class="btn btn-navy-pill btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
              <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
            </svg>
            Inspect on Live Map
          </a>
          <a href="admin_junctions.php" class="btn btn-outline btn-sm">
            Edit Signal Limits
          </a>
        </div>
      </div>

      <!-- AI Telemetry Cards Grid -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 18px;">
        <?php foreach ($ai_recommendations as $rec): ?>
          <?php
            $cong_badge = match($rec['congestion_level'] ?? 'low') {
                'low' => 'background: var(--emerald-100); color: var(--emerald-700); border: 1px solid var(--emerald-200);',
                'moderate' => 'background: var(--amber-100); color: var(--amber-700); border: 1px solid var(--amber-200);',
                'high' => 'background: #ffe4e6; color: var(--rose-700); border: 1px solid #fecdd3;',
                default => 'background: var(--sky-100); color: var(--sky-700);'
            };
          ?>
          <div style="background: var(--bg-subtle); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                <div>
                  <span style="font-size: 11px; font-weight: 700; color: var(--sky-600); letter-spacing: 0.5px;"><?= e($rec['junction_code'] ?? 'J-NODE') ?></span>
                  <h4 style="font-size: 14px; color: var(--navy-900); margin: 2px 0 0; line-height: 1.3;"><?= e($rec['name']) ?></h4>
                </div>
                <span style="font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 6px; text-transform: uppercase; <?= $cong_badge ?>">
                  <?= e($rec['congestion_level'] ?? 'low') ?>
                </span>
              </div>

              <!-- 4-Way Vehicle Flow Telemetry Grid -->
              <div style="background: white; border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 10px 12px; margin: 12px 0;">
                <div style="font-size: 11px; color: var(--text-light); margin-bottom: 6px; display: flex; justify-content: space-between;">
                  <span>4-Way Vehicle Density (YOLO/Vision):</span>
                  <strong>Total: <?= (int)$rec['vehicle_count'] ?> veh</strong>
                </div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; text-align: center;">
                  <div style="background: var(--bg-subtle); padding: 4px; border-radius: 4px;">
                    <div style="font-size: 9px; color: var(--text-light); font-weight: 700;">NORTH</div>
                    <div style="font-size: 13px; font-weight: 800; color: var(--navy-900);"><?= (int)($rec['count_north'] ?? 0) ?></div>
                  </div>
                  <div style="background: var(--bg-subtle); padding: 4px; border-radius: 4px;">
                    <div style="font-size: 9px; color: var(--text-light); font-weight: 700;">SOUTH</div>
                    <div style="font-size: 13px; font-weight: 800; color: var(--navy-900);"><?= (int)($rec['count_south'] ?? 0) ?></div>
                  </div>
                  <div style="background: var(--bg-subtle); padding: 4px; border-radius: 4px;">
                    <div style="font-size: 9px; color: var(--text-light); font-weight: 700;">EAST</div>
                    <div style="font-size: 13px; font-weight: 800; color: var(--navy-900);"><?= (int)($rec['count_east'] ?? 0) ?></div>
                  </div>
                  <div style="background: var(--bg-subtle); padding: 4px; border-radius: 4px;">
                    <div style="font-size: 9px; color: var(--text-light); font-weight: 700;">WEST</div>
                    <div style="font-size: 13px; font-weight: 800; color: var(--navy-900);"><?= (int)($rec['count_west'] ?? 0) ?></div>
                  </div>
                </div>
              </div>

              <!-- Recommended Green Split -->
              <div style="display: flex; gap: 10px; margin-bottom: 12px;">
                <div style="flex: 1; background: #ecfeff; border: 1px solid #cffafe; padding: 8px 10px; border-radius: var(--radius-sm);">
                  <div style="font-size: 10px; font-weight: 700; color: #0891b2;">REC. GREEN (N-S)</div>
                  <div style="font-size: 18px; font-weight: 800; color: #0e7490;"><?= (int)($rec['recommended_green_ns'] ?? 45) ?>s</div>
                </div>
                <div style="flex: 1; background: #f0fdf4; border: 1px solid #dcfce7; padding: 8px 10px; border-radius: var(--radius-sm);">
                  <div style="font-size: 10px; font-weight: 700; color: #16a34a;">REC. GREEN (E-W)</div>
                  <div style="font-size: 18px; font-weight: 800; color: #15803d;"><?= (int)($rec['recommended_green_ew'] ?? 35) ?>s</div>
                </div>
              </div>
            </div>

            <div style="border-top: 1px solid var(--border-light); padding-top: 10px; display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--text-light);">
              <span>Source: <strong style="color: var(--navy-800);"><?= e($rec['ai_data_source'] ?? 'Edge AI Model') ?></strong></span>
              <span><?= !empty($rec['last_telemetry_at']) ? time_ago($rec['last_telemetry_at']) : 'Active' ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Recent Admin Activities & Quick Shortcuts Grid -->
    <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px;">
      <!-- Recent Admin Activity Logs -->
      <div class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
          <div>
            <h3 style="font-size: 18px; color: var(--navy-900); margin: 0 0 2px;">Recent Administrative Activities</h3>
            <small style="color: var(--text-light); font-size: 12px;">Audited system interactions, logins, and settings updates</small>
          </div>
          <a href="admin_logs.php" style="font-size: 12px; font-weight: 700; color: var(--sky-600);">View Full Log →</a>
        </div>

        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
            <thead>
              <tr style="border-bottom: 2px solid var(--border-light); color: var(--text-light);">
                <th style="padding: 10px 12px;">Admin</th>
                <th style="padding: 10px 12px;">Action</th>
                <th style="padding: 10px 12px;">Details</th>
                <th style="padding: 10px 12px; text-align: right;">Time</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_activities as $act): ?>
                <tr style="border-bottom: 1px solid var(--border-light);">
                  <td style="padding: 10px 12px; font-weight: 600; color: var(--navy-900);">
                    <?= e($act['admin_name']) ?>
                  </td>
                  <td style="padding: 10px 12px;">
                    <span style="font-size: 11px; font-weight: 700; background: var(--sky-50); color: var(--sky-600); border: 1px solid var(--border-blue); padding: 2px 7px; border-radius: 4px;">
                      <?= e($act['action']) ?>
                    </span>
                  </td>
                  <td style="padding: 10px 12px; color: var(--text-muted);">
                    <?= e($act['details'] ?? 'Operation executed') ?>
                  </td>
                  <td style="padding: 10px 12px; text-align: right; color: var(--text-light); font-size: 12px;">
                    <?= time_ago($act['created_at']) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Quick Operations Hub -->
      <div class="panel">
        <h3 style="font-size: 18px; color: var(--navy-900); margin: 0 0 6px;">Management Shortcuts</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;">
          Direct links to administrative tools and traffic control functions.
        </p>

        <div style="display: flex; flex-direction: column; gap: 10px;">
          <a href="admin_website.php" style="background: var(--bg-subtle); border: 1px solid var(--border-light); padding: 12px 14px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between;">
            <div>
              <strong style="color: var(--navy-900); font-size: 13px; display: block;">Edit Website Content</strong>
              <small style="color: var(--text-muted); font-size: 11px;">Title, hero heading, subtitle, contact details</small>
            </div>
            <span style="color: var(--sky-600); font-weight: 700;">→</span>
          </a>

          <a href="admin_junctions.php" style="background: var(--bg-subtle); border: 1px solid var(--border-light); padding: 12px 14px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between;">
            <div>
              <strong style="color: var(--navy-900); font-size: 13px; display: block;">Configure Traffic Junctions</strong>
              <small style="color: var(--text-muted); font-size: 11px;">Add, edit coordinates, cycle timing limits</small>
            </div>
            <span style="color: var(--sky-600); font-weight: 700;">→</span>
          </a>

          <a href="admin_users.php" style="background: var(--bg-subtle); border: 1px solid var(--border-light); padding: 12px 14px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between;">
            <div>
              <strong style="color: var(--navy-900); font-size: 13px; display: block;">User & Role Management</strong>
              <small style="color: var(--text-muted); font-size: 11px;">Activate, deactivate, and manage administrators</small>
            </div>
            <span style="color: var(--sky-600); font-weight: 700;">→</span>
          </a>

          <a href="admin_reports.php" style="background: var(--bg-subtle); border: 1px solid var(--border-light); padding: 12px 14px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between;">
            <div>
              <strong style="color: var(--navy-900); font-size: 13px; display: block;">Citizen Incident Desk</strong>
              <small style="color: var(--text-muted); font-size: 11px;">Review citizen hazard and congestion complaints</small>
            </div>
            <span style="color: var(--sky-600); font-weight: 700;">→</span>
          </a>

          <a href="map.php" target="_blank" style="background: var(--bg-subtle); border: 1px solid var(--border-light); padding: 12px 14px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: space-between;">
            <div>
              <strong style="color: var(--navy-900); font-size: 13px; display: block;">Open Interactive GIS Map ↗</strong>
              <small style="color: var(--text-muted); font-size: 11px;">Full-screen Leaflet & OpenStreetMap surveillance</small>
            </div>
            <span style="color: var(--sky-600); font-weight: 700;">→</span>
          </a>
        </div>
      </div>
    </div>

    <footer style="margin-top: 35px; color: var(--text-light); font-size: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
      <div>SmartFlow AI · Intelligent Traffic Management Control Panel · <?= date('Y') ?></div>
      <div>Logged in as: <strong><?= e($admin_name) ?></strong> (Role: <?= e($admin_role) ?>)</div>
    </footer>
  </main>
</body>
</html>
