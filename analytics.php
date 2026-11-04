<?php
declare(strict_types=1);
$page_title = 'Traffic Analytics & Mobility Insights – SmartFlow AI';
$page_desc = 'Explore urban mobility analytics, queue dissipation charts, emission reductions, and AI recommendations.';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Inner Hero Section -->
<section class="inner-hero">
  <div class="site-container">
    <div class="inner-hero-content">
      <div class="breadcrumb-trail">
        <a href="index.php">Home</a>
        <span>/</span>
        <span>Traffic Analytics</span>
      </div>
      <h1 class="inner-hero-title">Urban Mobility Analytics</h1>
      <p class="inner-hero-desc">
        Comprehensive performance telemetry, queue reduction analytics, and sustainability metrics aggregated across all monitored smart intersections.
      </p>
    </div>
  </div>
</section>

<!-- Analytics Dashboard Section -->
<section class="section-padding bg-light-blue">
  <div class="site-container">
    <!-- Demonstration Notice -->
    <div class="notice info" style="margin-bottom: 24px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="16" x2="12" y2="12"></line>
        <line x1="12" y1="8" x2="12.01" y2="8"></line>
      </svg>
      <div>
        <strong>Demonstration Analytics Data:</strong>
        All charts, flow distributions, and efficiency gains represent simulated benchmark datasets generated to evaluate SmartFlow AI against conventional fixed-time controllers.
      </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="stats-grid" style="margin-bottom: 30px;">
      <div class="stat-counter-card">
        <div class="stat-card-header">
          <div class="stat-card-icon emerald">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
              <polyline points="17 6 23 6 23 12"></polyline>
            </svg>
          </div>
          <span class="stat-demo-badge">Delay Metric</span>
        </div>
        <div class="stat-number" data-counter-target="34.2" data-decimals="1" data-suffix="%">0%</div>
        <div class="stat-title">Avg. Wait Time Reduction</div>
        <div class="stat-desc">Seconds of driver idling eliminated per intersection.</div>
      </div>

      <div class="stat-counter-card">
        <div class="stat-card-header">
          <div class="stat-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1 .4-1 1v7c0 .6.4 1 1 1h1"></path>
              <circle cx="7" cy="17" r="2"></circle>
              <circle cx="15" cy="17" r="2"></circle>
            </svg>
          </div>
          <span class="stat-demo-badge">Throughput</span>
        </div>
        <div class="stat-number" data-counter-target="1420" data-suffix=" vph">0</div>
        <div class="stat-title">Peak Flow Throughput</div>
        <div class="stat-desc">Vehicles processed per hour per lane during rush hour.</div>
      </div>

      <div class="stat-counter-card">
        <div class="stat-card-header">
          <div class="stat-card-icon cyan">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
            </svg>
          </div>
          <span class="stat-demo-badge">Fuel Metric</span>
        </div>
        <div class="stat-number" data-counter-target="18400" data-suffix=" L">0</div>
        <div class="stat-title">Fuel Conserved</div>
        <div class="stat-desc">Estimated monthly fuel saved from reduced stop-and-go idle.</div>
      </div>

      <div class="stat-counter-card">
        <div class="stat-card-header">
          <div class="stat-card-icon navy">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"></path>
            </svg>
          </div>
          <span class="stat-demo-badge">Emissions</span>
        </div>
        <div class="stat-number" data-counter-target="42.6" data-decimals="1" data-suffix=" t">0</div>
        <div class="stat-title">CO2 Averted</div>
        <div class="stat-desc">Monthly greenhouse gases prevented across metro grid.</div>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="analytics-grid">
      <!-- Chart 1: Hourly Wait Time Comparison -->
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <h3 class="chart-title">Hourly Intersection Wait Time (Seconds)</h3>
            <small style="color: var(--text-light); font-size: 12px;">Fixed Timers vs. SmartFlow AI Adaptive</small>
          </div>
          <span class="chart-badge">Comparative Test</span>
        </div>

        <!-- CSS Bar Chart -->
        <div class="bar-chart-container">
          <!-- 06:00 -->
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 48px;" title="Legacy: 48s"></div>
              <div class="bar smartflow" style="height: 28px;" title="SmartFlow: 28s"></div>
            </div>
            <span class="bar-label">06:00</span>
          </div>

          <!-- 09:00 Peak -->
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 140px;" title="Legacy: 140s"></div>
              <div class="bar smartflow" style="height: 82px;" title="SmartFlow: 82s"></div>
            </div>
            <span class="bar-label">09:00</span>
          </div>

          <!-- 12:00 -->
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 65px;" title="Legacy: 65s"></div>
              <div class="bar smartflow" style="height: 42px;" title="SmartFlow: 42s"></div>
            </div>
            <span class="bar-label">12:00</span>
          </div>

          <!-- 15:00 -->
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 78px;" title="Legacy: 78s"></div>
              <div class="bar smartflow" style="height: 50px;" title="SmartFlow: 50s"></div>
            </div>
            <span class="bar-label">15:00</span>
          </div>

          <!-- 18:00 Peak -->
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 155px;" title="Legacy: 155s"></div>
              <div class="bar smartflow" style="height: 94px;" title="SmartFlow: 94s"></div>
            </div>
            <span class="bar-label">18:00</span>
          </div>

          <!-- 21:00 -->
          <div class="chart-bar-group">
            <div class="bars-pair">
              <div class="bar legacy" style="height: 44px;" title="Legacy: 44s"></div>
              <div class="bar smartflow" style="height: 24px;" title="SmartFlow: 24s"></div>
            </div>
            <span class="bar-label">21:00</span>
          </div>
        </div>

        <div class="chart-legend">
          <div class="legend-item">
            <span class="legend-dot" style="background-color: #cbd5e1;"></span>
            <span>Conventional Fixed Timing</span>
          </div>
          <div class="legend-item">
            <span class="legend-dot" style="background-color: var(--sky-600);"></span>
            <span><strong>SmartFlow AI Adaptive</strong></span>
          </div>
        </div>
      </div>

      <!-- Chart 2: Vehicle Modal Distribution -->
      <div class="chart-card">
        <div class="chart-header">
          <div>
            <h3 class="chart-title">Vehicle Modal Classification Split</h3>
            <small style="color: var(--text-light); font-size: 12px;">Computer vision categorization over 1.4M daily passes</small>
          </div>
          <span class="chart-badge">YOLOv8 Edge</span>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px; margin: auto 0; padding: 10px 0;">
          <!-- Sedans -->
          <div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
              <span>Passenger Sedans & SUVs</span>
              <span>54.2% (770,000)</span>
            </div>
            <div style="height: 10px; background-color: var(--bg-subtle); border-radius: 6px; overflow: hidden;">
              <div style="width: 54.2%; height: 100%; background: linear-gradient(90deg, var(--navy-blue), var(--sky-600)); border-radius: 6px;"></div>
            </div>
          </div>

          <!-- Commercial Freight -->
          <div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
              <span>Commercial Freight & Heavy Trucks</span>
              <span>18.6% (264,000)</span>
            </div>
            <div style="height: 10px; background-color: var(--bg-subtle); border-radius: 6px; overflow: hidden;">
              <div style="width: 18.6%; height: 100%; background: linear-gradient(90deg, var(--sky-500), var(--cyan-400)); border-radius: 6px;"></div>
            </div>
          </div>

          <!-- Motorcycles & Two-wheelers -->
          <div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
              <span>Motorcycles & Bicycles</span>
              <span>15.4% (218,000)</span>
            </div>
            <div style="height: 10px; background-color: var(--bg-subtle); border-radius: 6px; overflow: hidden;">
              <div style="width: 15.4%; height: 100%; background: linear-gradient(90deg, #34d399, #10b981)); border-radius: 6px;"></div>
            </div>
          </div>

          <!-- Transit Buses -->
          <div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; margin-bottom: 6px;">
              <span>Public Transit Buses</span>
              <span>11.8% (168,000)</span>
            </div>
            <div style="height: 10px; background-color: var(--bg-subtle); border-radius: 6px; overflow: hidden;">
              <div style="width: 11.8%; height: 100%; background: linear-gradient(90deg, var(--amber-500), #fbbf24); border-radius: 6px;"></div>
            </div>
          </div>
        </div>

        <div style="font-size: 12px; color: var(--text-light); border-top: 1px solid var(--border-light); padding-top: 14px; margin-top: 16px;">
          Note: Transit buses receive 1.8x priority multiplier in adaptive phase weighting to prioritize mass transit.
        </div>
      </div>
    </div>

    <!-- AI Automated Recommendations Section -->
    <div class="panel" style="margin-top: 30px;">
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
        <div>
          <span class="section-eyebrow" style="margin-bottom: 6px;">MACHINE LEARNING OPTIMIZER</span>
          <h3 style="font-size: 20px; color: var(--navy-900); margin: 0;">Automated Corridor Recommendations</h3>
        </div>
        <span style="font-size: 11px; font-weight: 700; color: var(--emerald-600); background: var(--emerald-100); padding: 4px 10px; border-radius: 4px;">
          4 AI ACTIONS PENDING APPROVAL
        </span>
      </div>

      <div style="display: flex; flex-direction: column; gap: 12px;">
        <div style="background-color: var(--bg-subtle); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
          <div>
            <strong style="font-size: 14px; color: var(--navy-900);">J-102: North Ring Road Offset Shift</strong>
            <p style="font-size: 12px; color: var(--text-muted); margin: 3px 0 0;">
              Detected 22% recurring queue spillover at 17:45. Recommendation: Advance green offset by +6 seconds on southbound collector.
            </p>
          </div>
          <span style="font-size: 12px; font-weight: 700; color: var(--sky-600);">Auto-Applied</span>
        </div>

        <div style="background-color: var(--bg-subtle); border: 1px solid var(--border-light); border-radius: var(--radius-sm); padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
          <div>
            <strong style="font-size: 14px; color: var(--navy-900);">J-104: Hospital Express Preemption Clearance Calibration</strong>
            <p style="font-size: 12px; color: var(--text-muted); margin: 3px 0 0;">
              Ambulance egress delay averaged 21s last week. Recommendation: Extend post-emergency cross-street flush cycle by +8 seconds.
            </p>
          </div>
          <span style="font-size: 12px; font-weight: 700; color: var(--emerald-600);">Verified Safe</span>
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
        <h2>Want custom telemetry integration?</h2>
        <p>Our RESTful JSON APIs and MQTT brokers export vehicle density streams directly into your municipal data warehouse.</p>
      </div>
      <div class="cta-btn-side">
        <a href="contact.php" class="btn btn-primary btn-lg">Request API Access</a>
        <a href="live-traffic.php" class="btn btn-outline-white btn-lg">Live Simulator</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

