<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Live Traffic Map & Smart City Navigator – SmartFlow AI';
$page_desc = 'Interactive Leaflet traffic map tracking metropolitan junctions, 4-way vehicle density, congestion levels, emergency corridors, and alternative detour routes.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Leaflet Map CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<!-- Inner Hero Section with Safety Notice -->
<section class="inner-hero" style="padding: 40px 0 45px;">
  <div class="site-container">
    <div class="inner-hero-content" style="max-width: 900px;">
      <div class="breadcrumb-trail">
        <a href="index.php">Home</a>
        <span>/</span>
        <span>Live Traffic Map</span>
      </div>
      <h1 class="inner-hero-title" style="font-size: 32px; margin-bottom: 8px;">
        Metropolitan Live Traffic Map
      </h1>
      <p class="inner-hero-desc" style="font-size: 15px; margin-bottom: 12px;">
        Explore real-time junction conditions, 4-way queue densities, automated green splits, and alternative routes. Powered by OpenStreetMap and SmartFlow AI edge inference nodes.
      </p>

      <!-- Transparent Demonstration & Safety Notice Pill -->
      <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.15); padding: 5px 12px; border-radius: var(--radius-full); font-size: 11px; color: #cbd5e1;">
        <span style="color: var(--amber-400);">⚠️ Demonstration & Simulation Mode:</span>
        <span>Telemetry streams represent simulated edge data. System does not directly actuate public signal hardware without municipal authorization.</span>
      </div>
    </div>
  </div>
</section>

<!-- Main Map Application Wrapper -->
<section class="section-padding-sm bg-light-blue" style="padding-top: 24px; padding-bottom: 40px;">
  <div class="site-container" style="max-width: 1360px;">

    <!-- Top Map Filter & Action Toolbar -->
    <div class="panel" style="padding: 16px 20px; margin-bottom: 18px; border-radius: var(--radius-md);">
      <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
        
        <!-- Search & Filter Controls -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; flex: 1; min-width: 280px;">
          <div style="position: relative; min-width: 220px; flex: 1;">
            <input type="text" id="mapSearchInput" class="form-control" placeholder="🔍 Search junction, code, or area..." style="padding: 8px 12px; font-size: 13px;">
          </div>

          <div style="width: 140px;">
            <select id="mapCongestionFilter" class="form-control" style="padding: 8px 10px; font-size: 13px;">
              <option value="">All Congestion</option>
              <option value="low">🟢 Smooth (Low)</option>
              <option value="moderate">🟡 Moderate</option>
              <option value="heavy">🔴 Heavy</option>
            </select>
          </div>

          <div style="width: 120px;">
            <select id="mapZoneFilter" class="form-control" style="padding: 8px 10px; font-size: 13px;">
              <option value="">All Zones</option>
              <option value="Central">Central</option>
              <option value="North">North</option>
              <option value="Downtown">Downtown</option>
              <option value="West">West</option>
              <option value="East">East</option>
            </select>
          </div>
        </div>

        <!-- Location & Live Actions -->
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
          <button type="button" class="btn btn-primary btn-sm" id="btnUserLocate" title="Request browser permission to find junctions near you">
            📍 Locate Me
          </button>

          <button type="button" class="btn btn-secondary btn-sm" id="btnRefreshMap" title="Refresh live telemetry from API">
            🔄 Refresh
          </button>

          <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: var(--navy-800); cursor: pointer; background: var(--bg-subtle); padding: 6px 10px; border-radius: var(--radius-sm); border: 1px solid var(--border-light);">
            <input type="checkbox" id="autoRefreshToggle" checked>
            <span>Live Auto-Refresh (10s)</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Map & Side Panel Split Container -->
    <div style="display: grid; grid-template-columns: 1fr 390px; gap: 20px; align-items: stretch;" id="mapSplitLayout">
      
      <!-- Left: Interactive Leaflet Map Canvas -->
      <div style="background: #ffffff; border: 1px solid var(--border-light); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-md); display: flex; flex-direction: column; min-height: 640px; position: relative;">
        
        <!-- Live Map Status Badge Overlaid -->
        <div style="position: absolute; top: 12px; right: 12px; z-index: 500; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); border: 1px solid var(--border-light); padding: 6px 12px; border-radius: var(--radius-full); box-shadow: var(--shadow-sm); font-size: 11px; display: flex; align-items: center; gap: 8px;">
          <span class="pulse-dot"></span>
          <span id="mapLiveStatusText" style="font-weight: 700; color: var(--navy-900);">AI Network Live (6 Active)</span>
          <span style="color: var(--text-light);">·</span>
          <span id="mapLastUpdated" style="color: var(--text-light);">Just now</span>
        </div>

        <!-- Legend Overlay at bottom left -->
        <div style="position: absolute; bottom: 16px; left: 16px; z-index: 500; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(6px); border: 1px solid var(--border-light); padding: 8px 14px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); font-size: 11px; display: flex; gap: 12px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 5px;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
            <span style="color: var(--navy-800); font-weight: 600;">Smooth Flow</span>
          </div>
          <div style="display: flex; align-items: center; gap: 5px;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
            <span style="color: var(--navy-800); font-weight: 600;">Moderate Delay</span>
          </div>
          <div style="display: flex; align-items: center; gap: 5px;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
            <span style="color: var(--navy-800); font-weight: 600;">Heavy Congestion</span>
          </div>
          <div style="display: flex; align-items: center; gap: 5px;">
            <span style="width: 10px; height: 10px; border-radius: 50%; background: #0284c7; display: inline-block;"></span>
            <span style="color: var(--navy-800); font-weight: 600;">Your Location</span>
          </div>
        </div>

        <!-- The actual Leaflet container div -->
        <div id="trafficMap" style="width: 100%; height: 100%; min-height: 640px; flex: 1;"></div>
      </div>

      <!-- Right: Public Traffic Side Drawer & Tabs -->
      <div style="background: #ffffff; border: 1px solid var(--border-light); border-radius: var(--radius-lg); padding: 20px; box-shadow: var(--shadow-md); display: flex; flex-direction: column; max-height: 720px; overflow: hidden;">
        
        <!-- Tab Navigation Buttons -->
        <div style="display: flex; border-bottom: 2px solid var(--border-light); margin-bottom: 16px; gap: 4px;">
          <button type="button" class="tab-btn active" id="tabBtnJunctions" style="flex: 1; padding: 10px 6px; font-size: 12px; font-weight: 700; background: none; border: none; border-bottom: 2px solid var(--sky-600); color: var(--sky-600); cursor: pointer;">
            🚦 Junctions
          </button>
          <button type="button" class="tab-btn" id="tabBtnAdvisories" style="flex: 1; padding: 10px 6px; font-size: 12px; font-weight: 600; background: none; border: none; color: var(--text-muted); cursor: pointer;">
            📢 Closures & Detours
          </button>
          <button type="button" class="tab-btn" id="tabBtnReport" style="flex: 1; padding: 10px 6px; font-size: 12px; font-weight: 600; background: none; border: none; color: var(--text-muted); cursor: pointer;">
            ⚠️ Report Issue
          </button>
          <button type="button" class="tab-btn" id="tabBtnFavourites" style="padding: 10px 10px; font-size: 12px; font-weight: 600; background: none; border: none; color: var(--text-muted); cursor: pointer;" title="Saved Favorite Junctions">
            ⭐
          </button>
        </div>

        <!-- TAB 1: Junctions List & Proximity -->
        <div id="tabContentJunctions" class="map-tab-content" style="display: flex; flex-direction: column; gap: 12px; overflow-y: auto; flex: 1; padding-right: 4px;">
          <div id="userLocationBanner" style="display: none; background: var(--sky-50); border: 1px solid var(--border-blue); padding: 10px 12px; border-radius: var(--radius-sm); font-size: 12px; color: var(--navy-900);">
            <strong>📍 Location Active:</strong> Sorted by shortest driving distance from your position.
          </div>
          <div id="junctionsListContainer">
            <p style="color: var(--text-light); font-size: 13px; text-align: center; padding: 20px;">Loading live traffic junctions...</p>
          </div>
        </div>

        <!-- TAB 2: Road Closures & Traffic Advisories -->
        <div id="tabContentAdvisories" class="map-tab-content" style="display: none; flex-direction: column; gap: 12px; overflow-y: auto; flex: 1; padding-right: 4px;">
          <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">
            Official municipal road maintenance notices and suggested detour routes:
          </div>
          <div id="advisoriesListContainer">
            <p style="color: var(--text-light); font-size: 13px; text-align: center; padding: 20px;">Loading road advisories...</p>
          </div>
        </div>

        <!-- TAB 3: Report Incident / Road Feedback Form -->
        <div id="tabContentReport" class="map-tab-content" style="display: none; flex-direction: column; overflow-y: auto; flex: 1; padding-right: 4px;">
          <h4 style="font-size: 15px; color: var(--navy-900); margin-bottom: 4px;">Report Traffic Incident or Fault</h4>
          <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">
            Submit real-time reports directly to the SmartFlow AI operations dispatch team.
          </p>

          <form id="publicReportForm">
            <div id="reportAlertBox" style="display: none; margin-bottom: 12px;"></div>

            <div class="form-group" style="margin-bottom: 12px;">
              <label class="form-label" style="font-size: 12px;">Your Name (or Anonymous)</label>
              <input type="text" id="repName" class="form-control" placeholder="Commuter / Driver" style="font-size: 13px; padding: 8px 10px;">
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
              <label class="form-label" style="font-size: 12px;">Issue Type *</label>
              <select id="repType" class="form-control" style="font-size: 13px; padding: 8px 10px;" required>
                <option value="heavy_jam">Heavy Traffic Jam / Gridlock</option>
                <option value="accident">Accident / Collision</option>
                <option value="signal_fault">Traffic Signal Malfunction</option>
                <option value="road_closure">Unannounced Road Blockage</option>
                <option value="hazard">Hazard / Debris on Roadway</option>
                <option value="other">General Traffic Feedback</option>
              </select>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
              <label class="form-label" style="font-size: 12px;">Location / Crossroad *</label>
              <input type="text" id="repLocation" class="form-control" placeholder="E.g., North Ring Road near Mile 14" style="font-size: 13px; padding: 8px 10px;" required>
            </div>

            <div class="form-group" style="margin-bottom: 14px;">
              <label class="form-label" style="font-size: 12px;">Incident Description *</label>
              <textarea id="repDesc" class="form-control" placeholder="Provide details on stalled lanes, queue length, or hazards..." style="font-size: 13px; min-height: 80px;" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-sm" id="btnSubmitReport">
              Submit Report to Central Dispatch <span>→</span>
            </button>
          </form>
        </div>

        <!-- TAB 4: Saved Favourites -->
        <div id="tabContentFavourites" class="map-tab-content" style="display: none; flex-direction: column; gap: 12px; overflow-y: auto; flex: 1; padding-right: 4px;">
          <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">
            Your starred junctions for rapid daily commute tracking:
          </div>
          <div id="favouritesListContainer">
            <p style="color: var(--text-light); font-size: 13px; text-align: center; padding: 20px;">
              ⭐ No favourite junctions starred yet. Click the star icon on any junction card or marker popup to save it here!
            </p>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- Leaflet Map JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="assets/js/live_map.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

