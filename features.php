<?php
declare(strict_types=1);
$page_title = 'Features & Capabilities – SmartFlow AI';
$page_desc = 'Explore the core technical features of SmartFlow AI including computer vision vehicle detection, adaptive signal control, emergency preemption, and green waves.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Inner Hero Section -->
<section class="inner-hero">
  <div class="site-container">
    <div class="inner-hero-content">
      <div class="breadcrumb-trail">
        <a href="index.php">Home</a>
        <span>/</span>
        <span>Features</span>
      </div>
      <h1 class="inner-hero-title">Platform Features & Capabilities</h1>
      <p class="inner-hero-desc">
        Engineered with cutting-edge artificial intelligence to revolutionize urban traffic flow, eliminate congestion delays, and protect first responder transit.
      </p>
    </div>
  </div>
</section>

<!-- Features Grid Section -->
<section class="section-padding bg-white">
  <div class="site-container">
    <div class="section-header">
      <span class="section-eyebrow">INTELLIGENT SUITE</span>
      <h2 class="section-title">Comprehensive Smart City Traffic Modules</h2>
      <p class="section-subtitle">
        Every module operates with microsecond precision to deliver a cohesive, self-optimizing urban transportation ecosystem.
      </p>
    </div>

    <div class="features-grid">
      <!-- Feature 1 -->
      <article class="feature-card" id="vision">
        <div class="feature-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </div>
        <h3 class="feature-card-title">Computer Vision Vehicle Detection</h3>
        <p class="feature-card-desc">
          Utilizes optimized YOLOv8 neural network architectures to detect, track, and classify multi-category traffic: sedans, buses, heavy trucks, emergency vehicles, and bicycles at up to 60 FPS under varying weather and lighting conditions.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">YOLOv8 & TensorRT</span>
          <span style="font-size: 11px; color: var(--text-light);">>98.4% Accuracy</span>
        </div>
      </article>

      <!-- Feature 2 -->
      <article class="feature-card" id="adaptive">
        <div class="feature-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 14 14"></polyline>
          </svg>
        </div>
        <h3 class="feature-card-title">Dynamic Adaptive Signal Control</h3>
        <p class="feature-card-desc">
          Replaces obsolete time-of-day clock cycles with a real-time queue dissipation model. Green light durations automatically stretch when queues are dense and immediately terminate when incoming lanes become clear.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">Dynamic Phase Split</span>
          <span style="font-size: 11px; color: var(--text-light);">Demand Responsive</span>
        </div>
      </article>

      <!-- Feature 3 -->
      <article class="feature-card" id="emergency">
        <div class="feature-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
          </svg>
        </div>
        <h3 class="feature-card-title">Emergency Vehicle Green Corridors</h3>
        <p class="feature-card-desc">
          Employs visual flashing-beacon recognition, siren acoustic sensors, and DSRC/GPS transponders to preempt intersection phases, clearing waiting queues ahead of ambulances, fire engines, and police responders.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">Zero-Delay Override</span>
          <span style="font-size: 11px; color: var(--text-light);">< 35s Clearance</span>
        </div>
      </article>

      <!-- Feature 4 -->
      <article class="feature-card" id="green-wave">
        <div class="feature-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
          </svg>
        </div>
        <h3 class="feature-card-title">Arterial Green Wave Coordination</h3>
        <p class="feature-card-desc">
          Synchronizes timing offsets across consecutive traffic signals along primary boulevards. Platoon progression models ensure that vehicular groups travelling at the posted speed limit encounter continuous green signals.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">Corridor Synchronization</span>
          <span style="font-size: 11px; color: var(--text-light);">Continuous Flow</span>
        </div>
      </article>

      <!-- Feature 5 -->
      <article class="feature-card" id="incident">
        <div class="feature-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
        </div>
        <h3 class="feature-card-title">Automated Incident Detection</h3>
        <p class="feature-card-desc">
          Continuous video telemetry algorithms flag stalled cars, debris obstructions, and sudden vehicle collisions in seconds, alerting municipal traffic controllers and automatically modifying upstream signal timings.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">Instant Telemetry</span>
          <span style="font-size: 11px; color: var(--text-light);">< 10s Alert Time</span>
        </div>
      </article>

      <!-- Feature 6 -->
      <article class="feature-card" id="pedestrian">
        <div class="feature-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="5" r="3"></circle>
            <path d="M6.5 9h11L15 22h-2l-1-7-1 7H9L6.5 9z"></path>
          </svg>
        </div>
        <h3 class="feature-card-title">Smart Pedestrian Safety Crossings</h3>
        <p class="feature-card-desc">
          Optical and thermal sensors detect pedestrians in crosswalk zones. If elderly, disabled, or young pedestrians require additional crossing time, the system extends the pedestrian clearance phase automatically.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">Safety First</span>
          <span style="font-size: 11px; color: var(--text-light);">Dynamic Crosswalk</span>
        </div>
      </article>

      <!-- Feature 7 -->
      <article class="feature-card" id="telemetry">
        <div class="feature-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
          </svg>
        </div>
        <h3 class="feature-card-title">Hardware Health & Fail-Safe Modes</h3>
        <p class="feature-card-desc">
          Roadside controllers maintain real-time diagnostics on optical cameras, signal bulbs, and network interfaces. If an anomaly occurs, the controller gracefully transitions to standard NTCIP safety flash modes.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">NTCIP Standard</span>
          <span style="font-size: 11px; color: var(--text-light);">99.99% Reliability</span>
        </div>
      </article>

      <!-- Feature 8 -->
      <article class="feature-card" id="eco">
        <div class="feature-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2v20"></path>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </div>
        <h3 class="feature-card-title">Emissions & Fuel Waste Analytics</h3>
        <p class="feature-card-desc">
          Calculates estimated fuel consumption saved and carbon monoxide/dioxide emissions averted per junction. Delivers verifiable sustainability reports for municipal climate action scorecards.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">ESG Sustainability</span>
          <span style="font-size: 11px; color: var(--text-light);">-34% Idle Emissions</span>
        </div>
      </article>

      <!-- Feature 9 -->
      <article class="feature-card">
        <div class="feature-icon-wrap">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
          </svg>
        </div>
        <h3 class="feature-card-title">Role-Based Operations Portal</h3>
        <p class="feature-card-desc">
          Authenticated operations console for municipal administrators, engineers, and dispatch operators. Features secure sessions, attempt throttling, audit trails, and manual phase override controls.
        </p>
        <div class="feature-card-footer">
          <span class="feature-badge">Admin & Auth</span>
          <a href="login.php" class="feature-link">Access portal →</a>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- Comparison Table Section -->
<section class="section-padding bg-light-blue">
  <div class="site-container">
    <div class="section-header">
      <span class="section-eyebrow">TECHNOLOGY BENCHMARK</span>
      <h2 class="section-title">Comparative Analysis of Traffic Control Systems</h2>
      <p class="section-subtitle">
        How SmartFlow AI delivers superior responsiveness compared to conventional alternatives.
      </p>
    </div>

    <div style="background-color: #ffffff; border: 1px solid var(--border-light); border-radius: var(--radius-xl); overflow-x: auto; box-shadow: var(--shadow-sm);">
      <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
        <thead>
          <tr style="background-color: var(--navy-950); color: #ffffff;">
            <th style="padding: 18px 24px;">Capability / Dimension</th>
            <th style="padding: 18px 24px;">Legacy Fixed-Timers</th>
            <th style="padding: 18px 24px;">Inductive In-Road Loops</th>
            <th style="padding: 18px 24px; color: var(--cyan-400);">SmartFlow AI Platform</th>
          </tr>
        </thead>
        <tbody>
          <tr style="border-bottom: 1px solid var(--border-light);">
            <td style="padding: 16px 24px; font-weight: 700;">Demand Adaptation</td>
            <td style="padding: 16px 24px; color: var(--rose-600);">None (Rigid schedule)</td>
            <td style="padding: 16px 24px; color: var(--amber-600);">Binary (Car present/absent)</td>
            <td style="padding: 16px 24px; color: var(--emerald-600); font-weight: 700;">Continuous Queue-Weighted AI</td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-light); background-color: var(--bg-subtle);">
            <td style="padding: 16px 24px; font-weight: 700;">Vehicle Classification</td>
            <td style="padding: 16px 24px; color: var(--rose-600);">None</td>
            <td style="padding: 16px 24px; color: var(--rose-600);">Crude metal estimation</td>
            <td style="padding: 16px 24px; color: var(--emerald-600); font-weight: 700;">Full Multi-Class Computer Vision</td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-light);">
            <td style="padding: 16px 24px; font-weight: 700;">Emergency Priority</td>
            <td style="padding: 16px 24px; color: var(--rose-600);">Manual police dispatch</td>
            <td style="padding: 16px 24px; color: var(--amber-600);">Line-of-sight optical strobe only</td>
            <td style="padding: 16px 24px; color: var(--emerald-600); font-weight: 700;">Multi-Sensor Automated Corridors</td>
          </tr>
          <tr style="border-bottom: 1px solid var(--border-light); background-color: var(--bg-subtle);">
            <td style="padding: 16px 24px; font-weight: 700;">Installation Impact</td>
            <td style="padding: 16px 24px; color: var(--text-light);">Simple cabinet clock</td>
            <td style="padding: 16px 24px; color: var(--rose-600);">Road digging & lane closures</td>
            <td style="padding: 16px 24px; color: var(--emerald-600); font-weight: 700;">Non-intrusive Over-Road Cameras</td>
          </tr>
          <tr>
            <td style="padding: 16px 24px; font-weight: 700;">Emissions Telemetry</td>
            <td style="padding: 16px 24px; color: var(--rose-600);">No metrics available</td>
            <td style="padding: 16px 24px; color: var(--rose-600);">No carbon data</td>
            <td style="padding: 16px 24px; color: var(--emerald-600); font-weight: 700;">Real-time CO2 & Fuel Tracking</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Call to action -->
<section class="section-padding-sm bg-white">
  <div class="site-container">
    <div class="cta-banner-wrap">
      <div class="cta-text-side">
        <h2>Ready to see live traffic control?</h2>
        <p>Experience our 4-way interactive intersection simulator with real-time signal switches and emergency preemption triggers.</p>
      </div>
      <div class="cta-btn-side">
        <a href="live-traffic.php" class="btn btn-primary btn-lg">Launch Live Simulation</a>
        <a href="junctions.php" class="btn btn-outline-white btn-lg">View Junction Grid</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

