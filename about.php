<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'About SmartFlow AI – Next-Gen Urban Mobility';
$page_desc = 'Discover the mission, engineering philosophy, and distributed edge AI architecture powering SmartFlow AI intelligent traffic control.';

$mission_text = get_setting('about_mission', 'Pioneering autonomous urban mobility with closed-loop AI computer vision, decentralized edge intelligence, and dynamic green wave synchronization.');
$vision_text = get_setting('about_vision', 'Zero gridlock, zero delay for emergency vehicles, and dramatically lower municipal transport emissions.');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Inner Hero Section -->
<section class="inner-hero">
  <div class="site-container">
    <div class="inner-hero-content">
      <div class="breadcrumb-trail">
        <a href="index.php">Home</a>
        <span>/</span>
        <span>About SmartFlow AI</span>
      </div>
      <h1 class="inner-hero-title">About SmartFlow AI</h1>
      <p class="inner-hero-desc">
        Pioneering autonomous urban mobility with closed-loop AI computer vision, decentralized edge intelligence, and dynamic green wave synchronization.
      </p>
    </div>
  </div>
</section>

<!-- Mission & Vision Section -->
<section class="section-padding bg-white">
  <div class="site-container">
    <div class="benefits-split-wrap">
      <div>
        <span class="section-eyebrow">OUR MISSION & VISION</span>
        <h2 class="section-title" style="margin-top: 10px;">Transforming Urban Congestion into Fluid Mobility</h2>
        <p style="color: var(--text-muted); font-size: 15px; line-height: 1.7; margin-bottom: 16px;">
          <?= nl2br(e($mission_text)) ?>
        </p>
        <p style="color: var(--text-muted); font-size: 15px; line-height: 1.7; margin-bottom: 24px;">
          <?= nl2br(e($vision_text)) ?>
        </p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div style="background-color: var(--bg-subtle); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-blue);">
            <div style="font-size: 24px; font-weight: 800; color: var(--navy-900); margin-bottom: 4px;">Zero Gridlock</div>
            <div style="font-size: 12px; color: var(--text-light);">Continuous dynamic balancing prevents bottleneck cascade failures.</div>
          </div>
          <div style="background-color: var(--bg-subtle); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-blue);">
            <div style="font-size: 24px; font-weight: 800; color: var(--navy-900); margin-bottom: 4px;">Zero Downtime</div>
            <div style="font-size: 12px; color: var(--text-light);">Deterministic local failovers guarantee uninterrupted physical safety.</div>
          </div>
        </div>
      </div>

      <div class="benefit-visual-card">
        <img src="assets/images/junction_diagram.svg" alt="SmartFlow AI 4-Way Junction Layout" style="width: 100%; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); margin-bottom: 18px;">
        <h4 style="font-size: 17px; margin-bottom: 6px;">Edge-Inference 4-Way Junction Architecture</h4>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.55;">
          Every monitored intersection operates an autonomous edge TPU node capable of calculating vehicle queues, velocity, and emergency preemption without cloud reliance.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- The Problem vs The Solution Grid -->
<section class="section-padding bg-light-blue">
  <div class="site-container">
    <div class="section-header">
      <span class="section-eyebrow">THE MOBILITY EVOLUTION</span>
      <h2 class="section-title">Legacy Systems vs. SmartFlow AI</h2>
      <p class="section-subtitle">
        Comparing conventional fixed-schedule traffic management with our next-generation adaptive architecture.
      </p>
    </div>

    <div class="features-grid">
      <!-- Problem Card -->
      <div class="feature-card" style="border-top: 4px solid var(--rose-500);">
        <div class="feature-icon-wrap" style="color: var(--rose-500); background-color: #ffe4e6; border-color: #fecdd3;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
        </div>
        <h3 class="feature-card-title">Legacy Fixed-Time Signals</h3>
        <p class="feature-card-desc">
          • Static timers change green lights regardless of actual car counts.<br>
          • Inground inductive loops frequently break during road resurfacing.<br>
          • Zero coordination between adjacent sequential intersections.<br>
          • Emergency ambulances are trapped behind red light queues.<br>
          • High greenhouse gas emissions from millions of idling engines.
        </p>
        <div class="feature-card-footer">
          <span style="font-size: 11px; font-weight: 700; color: var(--rose-500); background: #ffe4e6; padding: 3px 8px; border-radius: 4px;">Obsolete Infrastructure</span>
        </div>
      </div>

      <!-- Solution Card -->
      <div class="feature-card" style="border-top: 4px solid var(--sky-600);">
        <div class="feature-icon-wrap" style="color: var(--sky-600); background-color: var(--sky-50); border-color: var(--border-blue);">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 14 14"></polyline>
          </svg>
        </div>
        <h3 class="feature-card-title">SmartFlow AI Adaptive Control</h3>
        <p class="feature-card-desc">
          • Computer vision neural networks count vehicles per lane in milliseconds.<br>
          • Non-intrusive camera sensors eliminate costly pavement cutting.<br>
          • Arterial green wave synchronization minimizes stops across avenues.<br>
          • Instant emergency transponder preemption secures green corridors.<br>
          • Carbon footprint tracking quantifies metric tons of CO2 prevented.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">Modern Smart City</span>
        </div>
      </div>

      <!-- Security & Privacy Card -->
      <div class="feature-card" style="border-top: 4px solid var(--emerald-500);">
        <div class="feature-icon-wrap" style="color: var(--emerald-600); background-color: var(--emerald-100); border-color: #a7f3d0;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
          </svg>
        </div>
        <h3 class="feature-card-title">Privacy-By-Design Security</h3>
        <p class="feature-card-desc">
          • Strict anonymous vector processing: no facial recognition is used.<br>
          • License plates and personal identifiers are never stored or transmitted.<br>
          • Local edge processing ensures video stays within roadside cabinets.<br>
          • End-to-end encrypted TLS telemetry between edge and central command.<br>
          • Fail-safe fallback to standard safety timings if cameras are obstructed.
        </p>
        <div class="feature-card-footer">
          <span style="font-size: 11px; font-weight: 700; color: var(--emerald-600); background: var(--emerald-100); padding: 3px 8px; border-radius: 4px;">GDPR & Privacy Compliant</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Call to action -->
<section class="section-padding-sm bg-white">
  <div class="site-container">
    <div class="cta-banner-wrap">
      <div class="cta-text-side">
        <h2>Want to inspect the system features?</h2>
        <p>Explore detailed specifications of our adaptive timing algorithms and emergency priority protocols.</p>
      </div>
      <div class="cta-btn-side">
        <a href="features.php" class="btn btn-primary btn-lg">Explore Features</a>
        <a href="live-traffic.php" class="btn btn-outline-white btn-lg">View Live Simulation</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
