<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Junction Locations & Infrastructure Network – SmartFlow AI';
$page_desc = 'Explore the interconnected smart city intersection network managed by SmartFlow AI edge inference units.';

// Fetch dynamic junctions from database
$db_junctions = [];
global $pdo;
if ($pdo !== null) {
    try {
        $db_junctions = $pdo->query("SELECT * FROM junctions WHERE status = 'active' ORDER BY id ASC")->fetchAll();
    } catch (Throwable $e) {}
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Inner Hero Section -->
<section class="inner-hero">
  <div class="site-container">
    <div class="inner-hero-content">
      <div class="breadcrumb-trail">
        <a href="index.php">Home</a>
        <span>/</span>
        <span>Junction Locations</span>
      </div>
      <h1 class="inner-hero-title">Metropolitan Junction Network</h1>
      <p class="inner-hero-desc">
        Explore interconnected smart intersections equipped with high-definition computer vision, roadside edge TPUs, and NTCIP automated signal controllers.
      </p>
    </div>
  </div>
</section>

<!-- Junction Network Grid Section -->
<section class="section-padding bg-light-blue">
  <div class="site-container">
    <!-- Filter Toolbar -->
    <div class="junction-filter-bar">
      <div>
        <span class="section-eyebrow">METROPOLITAN GRID</span>
        <h2 style="font-size: 24px; color: var(--navy-900); margin: 6px 0 0;">Active Monitored Intersections</h2>
      </div>

      <div class="filter-btn-group">
        <button type="button" class="filter-btn active" data-filter="all">All Zones</button>
        <button type="button" class="filter-btn" data-filter="central">Central</button>
        <button type="button" class="filter-btn" data-filter="north">North</button>
        <button type="button" class="filter-btn" data-filter="downtown">Downtown</button>
        <button type="button" class="filter-btn" data-filter="west">West</button>
        <button type="button" class="filter-btn" data-filter="east">East</button>
      </div>
    </div>

    <!-- Demonstration Notice -->
    <div class="notice info" style="margin-bottom: 28px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="16" x2="12" y2="12"></line>
        <line x1="12" y1="8" x2="12.01" y2="8"></line>
      </svg>
      <div>
        <strong>Demonstration Network:</strong>
        The listed junctions represent simulated metropolitan smart city deployment nodes. Hardware configurations, GPS coordinates, and vehicle counts are generated for evaluation.
      </div>
    </div>

    <!-- Junction Cards Grid -->
    <div class="junction-cards-grid">
      <?php if (!empty($db_junctions)): ?>
        <?php foreach ($db_junctions as $j): ?>
          <article class="junction-card" data-zone="<?= strtolower(e($j['zone'])) ?>">
            <div class="junction-thumb-wrap">
              <?php $thumb = !empty($j['image_url']) ? $j['image_url'] : 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=800&q=80'; ?>
              <img src="<?= e($thumb) ?>" alt="<?= e($j['name']) ?>" class="junction-thumb-img">
              <span class="junction-status-badge">
                <span class="status-indicator-dot"></span> ONLINE · <?= strtoupper(e($j['cycle_mode'])) ?>
              </span>
              <span class="junction-id-tag"><?= e($j['junction_code']) ?></span>
            </div>
            <div class="junction-body">
              <h3 class="junction-title"><?= e($j['name']) ?></h3>
              <span class="junction-zone"><?= e($j['zone']) ?> Zone Corridor</span>
              <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 14px;">
                <?= e($j['address']) ?>. Features <?= (int)$j['cameras_count'] ?> high-speed computer vision detection nodes.
              </p>

              <div class="junction-stats-row">
                <div>
                  <div class="jstat-val"><?= number_format((int)$j['daily_volume']) ?></div>
                  <div class="jstat-lbl">Daily Vehicles</div>
                </div>
                <div>
                  <div class="jstat-val" style="color: var(--emerald-600);">-34%</div>
                  <div class="jstat-lbl">Wait Time</div>
                </div>
                <div>
                  <div class="jstat-val"><?= (int)$j['cameras_count'] ?> Cams</div>
                  <div class="jstat-lbl">Edge Hardware</div>
                </div>
              </div>

              <div class="junction-footer-action">
                <span class="feature-badge"><?= (int)$j['min_green_seconds'] ?>s - <?= (int)$j['max_green_seconds'] ?>s Cycle</span>
                <a href="live-traffic.php" class="btn btn-navy-pill btn-sm">Inspect Feed →</a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Default fallback cards if database table is empty -->
        <article class="junction-card" data-zone="central">
          <div class="junction-thumb-wrap">
            <img src="https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=800&q=80" alt="Central Blvd & Tech Way" class="junction-thumb-img">
            <span class="junction-status-badge">
              <span class="status-indicator-dot"></span> ONLINE · SYNCED
            </span>
            <span class="junction-id-tag">#J-101</span>
          </div>
          <div class="junction-body">
            <h3 class="junction-title">Central Blvd & Tech Way</h3>
            <span class="junction-zone">Zone 1 · Central Arterial Corridor</span>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 14px;">
              Primary east-west 6-lane boulevard connected to university tech hub. Features 4K edge neural cameras with pedestrian extension modules.
            </p>
            <div class="junction-stats-row">
              <div>
                <div class="jstat-val">34,200</div>
                <div class="jstat-lbl">Daily Vehicles</div>
              </div>
              <div>
                <div class="jstat-val" style="color: var(--emerald-600);">-38%</div>
                <div class="jstat-lbl">Wait Time</div>
              </div>
              <div>
                <div class="jstat-val">4 Cameras</div>
                <div class="jstat-lbl">Edge Hardware</div>
              </div>
            </div>
            <div class="junction-footer-action">
              <span class="feature-badge">Arterial Green Wave</span>
              <a href="live-traffic.php" class="btn btn-navy-pill btn-sm">Inspect Feed →</a>
            </div>
          </div>
        </article>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Call to action -->
<section class="section-padding-sm bg-white">
  <div class="site-container">
    <div class="cta-banner-wrap">
      <div class="cta-text-side">
        <h2>Need to integrate custom junction hardware?</h2>
        <p>SmartFlow AI connects via standard NTCIP 1202 interfaces, supporting Econolite, Naztec, and Siemens controllers.</p>
      </div>
      <div class="cta-btn-side">
        <a href="contact.php" class="btn btn-primary btn-lg">Request Technical Specs</a>
        <a href="analytics.php" class="btn btn-outline-white btn-lg">View Traffic Analytics</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
