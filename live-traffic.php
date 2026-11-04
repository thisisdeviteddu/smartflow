<?php
declare(strict_types=1);
$page_title = 'Live Traffic Monitoring & Simulation – SmartFlow AI';
$page_desc = 'Real-time 4-way intersection simulation and live telemetry visualizer showcasing adaptive signal cycles and emergency priority corridors.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Inner Hero Section -->
<section class="inner-hero">
  <div class="site-container">
    <div class="inner-hero-content">
      <div class="breadcrumb-trail">
        <a href="index.php">Home</a>
        <span>/</span>
        <span>Live Traffic</span>
      </div>
      <h1 class="inner-hero-title">Live Traffic Monitoring & Simulation</h1>
      <p class="inner-hero-desc">
        Observe real-time adaptive signal control on a live simulated 4-way metropolitan intersection. Test rush-hour surges, emergency ambulance overrides, and dynamic queue balancing.
      </p>
    </div>
  </div>
</section>

<!-- Simulation Control Bar & Stage -->
<section class="section-padding bg-light-blue" id="liveTrafficSimWrapper">
  <div class="site-container">
    <!-- Demo Transparency Alert -->
    <div class="notice info" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
      <div style="display: flex; align-items: flex-start; gap: 12px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        <div>
          <strong>Demonstration & Simulation Environment:</strong>
          This interactive monitor executes simulated IoT sensor telemetry and dynamic queue dissipation logic in real time.
        </div>
      </div>
      <a href="map.php" class="btn btn-navy-pill btn-sm" style="white-space: nowrap;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
          <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
        </svg>
        Open Live GIS Map →
      </a>
    </div>

    <!-- Top Simulation Toolbar -->
    <div class="traffic-control-panel">
      <div class="control-bar-inner">
        <div>
          <span style="font-size: 11px; font-weight: 700; color: var(--sky-600); letter-spacing: 1px; display: block; margin-bottom: 4px;">CURRENT INTERSECTION</span>
          <h2 style="font-size: 20px; color: var(--navy-900); margin: 0;">J-101: Central Boulevard & Technology Way</h2>
        </div>

        <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
          <div>
            <span style="font-size: 11px; color: var(--text-light); display: block;">Active Corridor Phase:</span>
            <strong id="activePhaseDisplay" style="color: var(--navy-900); font-size: 15px;">North-South Arterial</strong>
          </div>
          <div>
            <span style="font-size: 11px; color: var(--text-light); display: block;">Phase Countdown:</span>
            <strong id="phaseTimerDisplay" style="color: var(--sky-600); font-size: 20px; font-weight: 800;">12s</strong>
          </div>
        </div>

        <!-- Simulation Interactive Buttons -->
        <div class="sim-buttons-group">
          <button type="button" class="btn btn-secondary btn-sm" id="btnSimRushHour" title="Inject heavy queue surge into the intersection">
            ⚡ Simulate Rush Hour
          </button>
          <button type="button" class="btn btn-navy-pill btn-sm" id="btnSimAmbulance" title="Trigger instant emergency vehicle preemption">
            🚨 Trigger Ambulance Priority
          </button>
          <button type="button" class="btn btn-secondary btn-sm" id="btnSimAutoBalance" title="Run instant queue balancing calculation">
            ⚖️ AI Auto-Balance
          </button>
          <button type="button" class="btn btn-ghost btn-sm" id="btnSimReset" title="Reset intersection parameters">
            ↺ Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Main Visualizer Layout Grid -->
    <div class="simulation-layout-grid">
      <!-- Left: Visual Intersection Diagram & Approaches -->
      <div class="intersection-stage-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
          <h3 style="font-size: 18px; color: var(--navy-900);">Real-Time 4-Road Telemetry</h3>
          <span class="live-pulse-badge">
            <span class="pulse-dot"></span> 30 FPS EDGE INGESTION
          </span>
        </div>

        <!-- SVG Visualizer Stage -->
        <div class="intersection-visual-wrap">
          <img src="assets/images/junction_diagram.svg" alt="4-Way Junction Layout" style="width: 100%; height: 100%; object-fit: contain;">
        </div>

        <!-- 4 Road Approach Status Cards -->
        <div class="approach-grid">
          <!-- North Approach -->
          <div class="approach-card active-phase" id="approach-north">
            <div>
              <div class="approach-direction">
                <span>↑</span> Northbound Approach
              </div>
              <small style="color: var(--text-light); font-size: 11px;">Queue: <strong id="queue-val-north" style="color: var(--navy-900);">14 vehicles</strong></small>
            </div>
            <div id="signal-pill-north" class="approach-signal-pill signal-green">
              ● GREEN (12s)
            </div>
          </div>

          <!-- South Approach -->
          <div class="approach-card active-phase" id="approach-south">
            <div>
              <div class="approach-direction">
                <span>↓</span> Southbound Approach
              </div>
              <small style="color: var(--text-light); font-size: 11px;">Queue: <strong id="queue-val-south" style="color: var(--navy-900);">18 vehicles</strong></small>
            </div>
            <div id="signal-pill-south" class="approach-signal-pill signal-green">
              ● GREEN (12s)
            </div>
          </div>

          <!-- East Approach -->
          <div class="approach-card" id="approach-east">
            <div>
              <div class="approach-direction">
                <span>→</span> Eastbound Approach
              </div>
              <small style="color: var(--text-light); font-size: 11px;">Queue: <strong id="queue-val-east" style="color: var(--navy-900);">29 vehicles</strong></small>
            </div>
            <div id="signal-pill-east" class="approach-signal-pill signal-red">
              ● RED
            </div>
          </div>

          <!-- West Approach -->
          <div class="approach-card" id="approach-west">
            <div>
              <div class="approach-direction">
                <span>←</span> Westbound Approach
              </div>
              <small style="color: var(--text-light); font-size: 11px;">Queue: <strong id="queue-val-west" style="color: var(--navy-900);">24 vehicles</strong></small>
            </div>
            <div id="signal-pill-west" class="approach-signal-pill signal-red">
              ● RED
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Real-Time Event Log Sidebar -->
      <aside class="sim-event-sidebar">
        <div class="event-log-header">
          <div>
            <h3 style="font-size: 16px; color: var(--navy-900); margin: 0 0 2px;">AI Telemetry Feed</h3>
            <small style="color: var(--text-light); font-size: 11px;">Live edge inference stream</small>
          </div>
          <span style="font-size: 10px; font-weight: 700; color: var(--sky-600); background: var(--sky-50); padding: 2px 6px; border-radius: 4px;">
            AUTO-SYNC
          </span>
        </div>

        <div class="event-log-list" id="simEventList">
          <div class="event-log-item">
            <span class="event-log-time"><?= date('H:i:s') ?></span>
            <span class="event-log-msg">Simulation initialized. Connected to Edge Node J-101. Standard adaptive timing active.</span>
          </div>
          <div class="event-log-item">
            <span class="event-log-time"><?= date('H:i:s', time() - 4) ?></span>
            <span class="event-log-msg">Northbound video stream steady at 30 FPS. 14 passenger sedans and 2 buses detected in queue.</span>
          </div>
          <div class="event-log-item">
            <span class="event-log-time"><?= date('H:i:s', time() - 8) ?></span>
            <span class="event-log-msg">Adaptive phase algorithm granted +4s extension to North-South arterial to clear transit bus platoon.</span>
          </div>
        </div>

        <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border-light); font-size: 11px; color: var(--text-light); line-height: 1.4;">
          💡 <strong>Tip:</strong> Click the <em>Trigger Ambulance Priority</em> button above to test emergency vehicle preemption!
        </div>
      </aside>
    </div>
  </div>
</section>

<!-- Additional Information Section -->
<section class="section-padding bg-white">
  <div class="site-container">
    <div class="section-header">
      <span class="section-eyebrow">CONTINUOUS OPTIMISATION</span>
      <h2 class="section-title">How the Live Controller Adapts to Demands</h2>
      <p class="section-subtitle">
        Understanding the micro-decisions made by SmartFlow AI at every signal transition.
      </p>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <h4 style="font-size: 17px; margin-bottom: 8px;">1. Multi-Lane Queue Weighted Score</h4>
        <p class="feature-card-desc">
          Instead of simple vehicle counting, each lane is weighted based on stopping duration, vehicle class weight (buses have higher transit priority than single-occupant cars), and approach speed.
        </p>
        <span class="feature-badge">Queue Weighting</span>
      </div>

      <div class="feature-card">
        <h4 style="font-size: 17px; margin-bottom: 8px;">2. Gap-Out & Max-Out Safety Boundaries</h4>
        <p class="feature-card-desc">
          The adaptive controller enforces minimum pedestrian safe crossing seconds and strict maximum green caps, preventing starvation on cross-streets even during heavy arterial flow.
        </p>
        <span class="feature-badge">Safety Guaranteed</span>
      </div>

      <div class="feature-card">
        <h4 style="font-size: 17px; margin-bottom: 8px;">3. Instant Emergency Recovery</h4>
        <p class="feature-card-desc">
          When an emergency corridor preemption concludes, the controller does not reset abruptly. It enters a smoothing cycle to rapidly dissipate built-up side-street queues.
        </p>
        <span class="feature-badge">Graceful Recovery</span>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

