<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = get_setting('site_title', 'SmartFlow AI – Intelligent Traffic Management');
$page_desc = get_setting('site_description', 'Empowering next-generation smart cities with AI-driven adaptive traffic signal control.');

$hero_heading = get_setting('hero_heading', 'SmartFlow AI – Intelligent Traffic Management');
$hero_subtitle = get_setting('hero_subtitle', 'Eliminate urban bottlenecks with AI-based traffic optimisation. Our intelligent system dynamically coordinates signal timings using real-time edge computer vision, queue density sensing, and automated emergency corridors.');
$hero_bg_image = get_setting('hero_bg_image', 'assets/images/traffic_hero_bg.svg');

// Section visibility toggles
$show_stats = (get_setting('section_stats_enabled', '1') === '1');
$show_features = (get_setting('section_features_enabled', '1') === '1');
$show_how = (get_setting('section_how_it_works_enabled', '1') === '1');
$show_benefits = (get_setting('section_benefits_enabled', '1') === '1');
$show_cta = (get_setting('section_cta_enabled', '1') === '1');

// Fetch feature cards from database if present
$db_feature_cards = [];
global $pdo;
if ($pdo !== null) {
    try {
        $db_feature_cards = $pdo->query("SELECT * FROM feature_cards WHERE is_active = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (Throwable $e) {}
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section with Configurable Background and Readable Overlays -->
<section class="hero-section" id="hero" style="background-image: linear-gradient(135deg, rgba(10, 17, 32, 0.92) 0%, rgba(15, 30, 60, 0.88) 50%, rgba(2, 132, 199, 0.82) 100%), url('<?= e($hero_bg_image) ?>');">
  <div class="site-container">
    <div class="hero-content">
      <div class="hero-pill-badge">
        <span class="pulse-dot"></span> NEXT-GEN ADAPTIVE MOBILITY PLATFORM
      </div>
      <h1 class="hero-title">
        <?= e($hero_heading) ?>
      </h1>
      <p class="hero-subtitle">
        <?= e($hero_subtitle) ?>
      </p>
      <div class="hero-buttons">
        <a href="live-traffic.php" class="btn btn-primary btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="5 3 19 12 5 21 5 3"></polygon>
          </svg>
          Explore Live Traffic
        </a>
        <a href="map.php" class="btn btn-outline-white btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
            <line x1="8" y1="2" x2="8" y2="18"></line>
            <line x1="16" y1="6" x2="16" y2="22"></line>
          </svg>
          Interactive Map
        </a>
        <a href="features.php" class="btn btn-ghost btn-lg" style="color: white; border: 1px solid rgba(255, 255, 255, 0.35);">
          Explore Features
        </a>
      </div>

      <!-- Trust & Spec Points -->
      <div class="hero-meta-bar">
        <div class="hero-meta-item">
          <span class="hero-check-icon">✓</span>
          <span><strong>Sub-50ms</strong> Edge Inference</span>
        </div>
        <div class="hero-meta-item">
          <span class="hero-check-icon">✓</span>
          <span><strong>Dynamic</strong> Green Wave Sync</span>
        </div>
        <div class="hero-meta-item">
          <span class="hero-check-icon">✓</span>
          <span><strong>Zero</strong> Legacy Hardware Replacement</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Animated Key Statistics Section -->
<?php if ($show_stats): ?>
<section class="stats-banner-wrap">
  <div class="site-container">
    <div class="stats-grid">
      <!-- Stat 1 -->
      <div class="stat-counter-card">
        <div class="stat-card-header">
          <div class="stat-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="2" y1="12" x2="22" y2="12"></line>
              <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
          </div>
          <span class="stat-demo-badge">Demo Grid</span>
        </div>
        <div class="stat-number" data-counter-target="48" data-suffix="">0</div>
        <div class="stat-title">Connected Junctions</div>
        <div class="stat-desc">Active simulated smart traffic intersections across metro corridors.</div>
      </div>

      <!-- Stat 2 -->
      <div class="stat-counter-card">
        <div class="stat-card-header">
          <div class="stat-card-icon cyan">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <span class="stat-demo-badge">Optimised</span>
        </div>
        <div class="stat-number" data-counter-target="34.2" data-decimals="1" data-suffix="%">0%</div>
        <div class="stat-title">Wait Time Reduction</div>
        <div class="stat-desc">Average reduction in intersection queue delay versus fixed cycles.</div>
      </div>

      <!-- Stat 3 -->
      <div class="stat-counter-card">
        <div class="stat-card-header">
          <div class="stat-card-icon emerald">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1 .4-1 1v7c0 .6.4 1 1 1h1"></path>
              <circle cx="7" cy="17" r="2"></circle>
              <circle cx="15" cy="17" r="2"></circle>
            </svg>
          </div>
          <span class="stat-demo-badge">Daily Rate</span>
        </div>
        <div class="stat-number" data-counter-target="1.42" data-decimals="2" data-prefix="" data-suffix="M">0M</div>
        <div class="stat-title">Vehicles Processed</div>
        <div class="stat-desc">Daily simulated vehicle flows analyzed by edge neural networks.</div>
      </div>

      <!-- Stat 4 -->
      <div class="stat-counter-card">
        <div class="stat-card-header">
          <div class="stat-card-icon navy">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2v20"></path>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </div>
          <span class="stat-demo-badge">Target Metric</span>
        </div>
        <div class="stat-number" data-counter-target="32" data-suffix="s">0s</div>
        <div class="stat-title">Emergency Clearance</div>
        <div class="stat-desc">Average response time for preemptive green corridor activation.</div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Core Features Grid (Equal Heights & Admin Manageable) -->
<?php if ($show_features): ?>
<section class="section-padding bg-white" id="features">
  <div class="site-container">
    <div class="section-header">
      <span class="section-eyebrow">INTELLIGENT CAPABILITIES</span>
      <h2 class="section-title">Engineered for Autonomous Urban Mobility</h2>
      <p class="section-subtitle">
        SmartFlow AI replaces rigid legacy cycle clocks with dynamic, demand-responsive traffic regulation driven by state-of-the-art computer vision and reinforcement algorithms.
      </p>
    </div>

    <div class="features-grid">
      <?php if (!empty($db_feature_cards)): ?>
        <?php foreach ($db_feature_cards as $card): ?>
          <article class="feature-card">
            <div class="feature-icon-wrap">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 14 14"></polyline>
              </svg>
            </div>
            <h3 class="feature-card-title"><?= e($card['title']) ?></h3>
            <p class="feature-card-desc">
              <?= e($card['description']) ?>
            </p>
            <div class="feature-card-footer">
              <span class="feature-badge"><?= e($card['badge']) ?></span>
              <a href="<?= e($card['link_url']) ?>" class="feature-link">Learn more →</a>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Default fallback cards if database table is empty -->
        <article class="feature-card">
          <div class="feature-icon-wrap">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
              <circle cx="12" cy="13" r="4"></circle>
            </svg>
          </div>
          <h3 class="feature-card-title">Real-Time Vehicle Vision & Counting</h3>
          <p class="feature-card-desc">
            Detects, counts, and classifies vehicles with over 98% accuracy using edge-accelerated computer vision models.
          </p>
          <div class="feature-card-footer">
            <span class="feature-badge">YOLOv8 Edge</span>
            <a href="features.php#vision" class="feature-link">Learn more →</a>
          </div>
        </article>

        <article class="feature-card">
          <div class="feature-icon-wrap">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 14 14"></polyline>
            </svg>
          </div>
          <h3 class="feature-card-title">Adaptive Dynamic Signal Timers</h3>
          <p class="feature-card-desc">
            Replaces fixed green timers with live demand-responsive cycles. Empty lanes receive minimal holds while congested approaches stretch.
          </p>
          <div class="feature-card-footer">
            <span class="feature-badge">Dynamic Timing</span>
            <a href="features.php#adaptive" class="feature-link">Learn more →</a>
          </div>
        </article>

        <article class="feature-card">
          <div class="feature-icon-wrap">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
          </div>
          <h3 class="feature-card-title">Automated Emergency Priority Waves</h3>
          <p class="feature-card-desc">
            Detects emergency vehicles and dynamically secures a synchronized green corridor across multiple intersections.
          </p>
          <div class="feature-card-footer">
            <span class="feature-badge">Zero-Delay Transit</span>
            <a href="features.php#emergency" class="feature-link">Learn more →</a>
          </div>
        </article>

        <article class="feature-card">
          <div class="feature-icon-wrap">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
          </div>
          <h3 class="feature-card-title">Multi-Junction Green Wave Sync</h3>
          <p class="feature-card-desc">
            Coordinates timing offsets between sequential arterial traffic lights so drivers encounter continuous green progression.
          </p>
          <div class="feature-card-footer">
            <span class="feature-badge">Arterial Sync</span>
            <a href="features.php#green-wave" class="feature-link">Learn more →</a>
          </div>
        </article>

        <article class="feature-card">
          <div class="feature-icon-wrap">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
              <line x1="12" y1="9" x2="12" y2="13"></line>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
          </div>
          <h3 class="feature-card-title">Bottleneck & Incident Telemetry</h3>
          <p class="feature-card-desc">
            Continuously scans for abnormal stoppages and stalled cars, alerting operations and rerouting upstream flows.
          </p>
          <div class="feature-card-footer">
            <span class="feature-badge">Incident Alert</span>
            <a href="features.php#incident" class="feature-link">Learn more →</a>
          </div>
        </article>

        <article class="feature-card">
          <div class="feature-icon-wrap">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
          </div>
          <h3 class="feature-card-title">CO2 & Fuel Emission Tracking</h3>
          <p class="feature-card-desc">
            Quantifies reductions in idle fuel consumption and tailpipe carbon emissions achieved by eliminating unnecessary red stops.
          </p>
          <div class="feature-card-footer">
            <span class="feature-badge">Green Impact</span>
            <a href="features.php#eco" class="feature-link">Learn more →</a>
          </div>
        </article>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- How It Works Section -->
<?php if ($show_how): ?>
<section class="section-padding bg-light-blue" id="how-it-works">
  <div class="site-container">
    <div class="section-header">
      <span class="section-eyebrow">SYSTEM ARCHITECTURE</span>
      <h2 class="section-title">How SmartFlow AI Operates in Real Time</h2>
      <p class="section-subtitle">
        A seamless 4-stage closed loop connects physical road junctions with edge computer vision and municipal traffic management infrastructure.
      </p>
    </div>

    <div class="how-it-works-grid">
      <div class="process-step-card">
        <div class="step-num-badge">01</div>
        <h3 class="step-title">Video Stream Ingestion</h3>
        <p class="step-desc">
          High-definition optical and thermal cameras positioned at each junction approach feed real-time video streams into the local roadside edge computing unit.
        </p>
        <span class="step-tech-tag">RTSP / 30 FPS Stream</span>
      </div>

      <div class="process-step-card">
        <div class="step-num-badge">02</div>
        <h3 class="step-title">Edge AI Density Estimation</h3>
        <p class="step-desc">
          Neural networks identify vehicles per lane, calculate instantaneous queue lengths, and estimate arrival rate velocity without sending private video offsite.
        </p>
        <span class="step-tech-tag">Sub-50ms Inference</span>
      </div>

      <div class="process-step-card">
        <div class="step-num-badge">03</div>
        <h3 class="step-title">Adaptive Phase Computation</h3>
        <p class="step-desc">
          The SmartFlow reinforcement engine computes the mathematically optimal green split for all 4 intersection approaches, minimizing overall vehicle wait time.
        </p>
        <span class="step-tech-tag">Queue-Based Optimization</span>
      </div>

      <div class="process-step-card">
        <div class="step-num-badge">04</div>
        <h3 class="step-title">Signal Actuation & Telemetry</h3>
        <p class="step-desc">
          Actuators trigger signal phase transitions safely through standard NTCIP interfaces, while live performance telemetry is streamed to the cloud dashboard.
        </p>
        <span class="step-tech-tag">NTCIP / SCADA Protocol</span>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Smart-City Benefits Section -->
<?php if ($show_benefits): ?>
<section class="section-padding bg-white" id="benefits">
  <div class="site-container">
    <div class="section-header">
      <span class="section-eyebrow">URBAN MOBILITY IMPACT</span>
      <h2 class="section-title">Measurable Benefits for the Entire Smart City</h2>
      <p class="section-subtitle">
        From everyday drivers to city engineers, emergency personnel, and environmental planners.
      </p>
    </div>

    <div class="benefits-split-wrap">
      <div class="benefit-visual-card">
        <img src="assets/images/traffic_banner.svg" alt="Smart City Infrastructure Schematic" class="benefit-visual-img">
        <div class="benefit-visual-meta">
          <h3 style="font-size: 20px; margin-bottom: 8px;">Autonomous Network Coordination</h3>
          <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
            SmartFlow AI operates autonomously 24/7/365, adapting instantly to sudden downpours, stadium event surges, road maintenance closures, and unexpected transit detours.
          </p>
        </div>
      </div>

      <div class="benefits-list">
        <div class="benefit-item-card">
          <div class="benefit-item-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="1" y="3" width="15" height="13"></rect>
              <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
              <circle cx="5.5" cy="18.5" r="2.5"></circle>
              <circle cx="18.5" cy="18.5" r="2.5"></circle>
            </svg>
          </div>
          <div class="benefit-item-content">
            <h4>For Everyday Commuters</h4>
            <p>Saves 15 to 25 minutes on average daily peak commutes by eliminating senseless stops at empty cross-streets.</p>
          </div>
        </div>

        <div class="benefit-item-card">
          <div class="benefit-item-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
            </svg>
          </div>
          <div class="benefit-item-content">
            <h4>For Municipalities & Traffic Police</h4>
            <p>Unified operations command with automated incident alerts, reducing the need for manual officer deployment at congested intersections.</p>
          </div>
        </div>

        <div class="benefit-item-card">
          <div class="benefit-item-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
          <div class="benefit-item-content">
            <h4>For Emergency First Responders</h4>
            <p>Emergency medical and fire crews reach life-saving scenes up to 45% faster with automated green corridor preemption.</p>
          </div>
        </div>

        <div class="benefit-item-card">
          <div class="benefit-item-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
              <line x1="4" y1="22" x2="4" y2="15"></line>
            </svg>
          </div>
          <div class="benefit-item-content">
            <h4>For Environmental Sustainability</h4>
            <p>Prevents thousands of metric tons of toxic exhaust emissions and wasted fuel caused by stop-and-go congestion.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Call to Action Banner -->
<?php if ($show_cta): ?>
<section class="section-padding-sm bg-light-blue">
  <div class="site-container">
    <div class="cta-banner-wrap">
      <div class="cta-text-side">
        <h2>Experience SmartFlow AI in Action</h2>
        <p>
          View live four-road traffic simulations, test emergency ambulance priority triggers, or explore the junction infrastructure network.
        </p>
      </div>
      <div class="cta-btn-side">
        <a href="live-traffic.php" class="btn btn-primary btn-lg">Launch Live Traffic Simulation</a>
        <a href="contact.php" class="btn btn-outline-white btn-lg">Schedule Municipal Demo</a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
