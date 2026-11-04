# SmartFlow AI – Intelligent Traffic Management System (v3.0.0)

A comprehensive, production-ready PHP + MySQL smart-city mobility and adaptive traffic control platform, upgraded with an attractive **Bright Theme**, an **Executive Admin Control Panel**, an **Interactive Leaflet GIS Map with User Geolocation**, **Citizen Incident Reporting**, and a **Python Computer Vision & AI Telemetry Microservice**.

---

## 🌟 What's New in v3.0

1. **🗺️ Interactive Live Traffic Map (`map.php`)**:
   - Leaflet + OpenStreetMap engine rendering real-time metro traffic nodes without third-party API keys.
   - Dynamic SVG congestion marker pins:
     - 🟢 **Green Pin**: Low Congestion (< 60 vehicles)
     - 🟠 **Amber Pin**: Moderate Flow (60–120 vehicles)
     - 🔴 **Red Pin**: Heavy Congestion (> 120 vehicles)
   - Interactive modal popup cards showing 4-way vehicle counts (North, South, East, West), recommended green time splits, less congested alternative detour roads, and last telemetry timestamps.
   - Live search by junction name, address, code, or city zone.
   - 4-tab slide-out operations drawer:
     - **Junctions List**: Sorted by proximity or congestion with one-click map centering.
     - **Road Closures & Detours**: Active construction, maintenance, and diversions.
     - **Report Traffic Issue**: Direct citizen reporting form for real-world incidents.
     - **Saved Favourites**: Instant one-click bookmarks stored safely in browser `localStorage`.

2. **📍 Privacy-First User Geolocation**:
   - Opt-in browser location request (`navigator.geolocation.getCurrentPosition`) triggered only when the user clicks **"Locate Me"**.
   - Calculates real-time distance in kilometers to all monitored traffic intersections using the Haversine formula.
   - **Zero Background Tracking**: Never monitors users without explicit consent.
   - Provides a comprehensive manual text search fallback if location permission is denied.

3. **📢 Public Traffic Information & Citizen Incident Reporting**:
   - Live roadway conditions, speed indices, and delay estimates.
   - Road closure and maintenance notices with recommended detours.
   - Citizen traffic feedback form (`api/reports.php`) allowing citizens to report potholes, signal hardware faults, collisions, or severe bottlenecks.
   - Full administration moderation desk (`admin_reports.php`) for operators to investigate, resolve, or dismiss reports.

4. **🧠 AI Traffic Monitoring & Computer Vision Microservice (`ai_service/`)**:
   - Python Flask microservice (`ai_service/app.py`) providing edge inference and telemetry ingestion.
   - Supports YOLOv8 vehicle detection and OpenCV background modeling.
   - Calculates dynamic queue density across North, South, East, and West approaches.
   - Proportional adaptive green split algorithm:
     $$G_{NS} = \max\left(15, \min\left(75, 15 + 70 \times \frac{N+S}{(N+S)+(E+W)}\right)\right)$$
     $$G_{EW} = \max\left(15, \min\left(75, 15 + 70 \times \frac{E+W}{(N+S)+(E+W)}\right)\right)$$
   - Emergency vehicle priority (ambulance, fire truck preemption override).
   - Real-time automated push to core PHP backend API (`/api/telemetry.php`).
   - Telemetry simulator script (`ai_service/run_simulation.py`) for automated continuous testing.

5. **🎨 Modern Bright Theme & Design System**:
   - Clean White (`#ffffff`) and Soft Light-Blue (`#f0f7ff`, `#f8fafc`) palette with Navy (`#0f172a`), Sky Blue (`#0284c7`), and Cyan (`#06b6d4`) accents.
   - Professional typography: `Plus Jakarta Sans` headings and `Inter` body text.
   - Full responsiveness across desktop monitors, laptops, tablets, and smartphones.

6. **🔒 Safety & Simulation Notice**:
   - SmartFlow AI is an intelligent monitoring, simulation, and decision-support system.
   - Includes mandatory clearance intervals (4s yellow, 2s all-red) and safety green envelopes (15s minimum).
   - Not for direct uncertified control of real-world municipal signals without approved hardware interlocks and local transportation authority approval.

---

## 📂 Project Architecture

```
SmartFlow_AI_Starter/
│
├── about.php                     # About SmartFlow AI, mission & technical architecture
├── admin_alerts.php              # Admin: Dispatch & resolve congestion/emergency alerts
├── admin_images.php              # Admin: Upload & manage website imagery & hero backdrops
├── admin_junctions.php           # Admin: Junction CRUD, GPS coordinates, signal limits
├── admin_logs.php                # Admin: Audit logs of operator actions & logins
├── admin_reports.php             # Admin: Citizen incident moderation & report status desk
├── admin_users.php               # Admin: Manage administrators, dispatchers, and roles
├── admin_website.php             # Admin: Manage website content, hero text, and sections
├── analytics.php                 # Public: Traffic volume charts, delay comparisons, KPIs
├── contact.php                   # Public: Contact details, inquiry form, and FAQ accordion
├── dashboard.php                 # Admin: Central dispatch console with AI recommendations
├── features.php                  # Public: Detailed capability breakdown & benchmark table
├── index.php                     # Public: Hero section, animated counters, feature grid
├── junctions.php                 # Public: Grid of monitored intersections with telemetry
├── live-traffic.php              # Public: Interactive 4-way intersection simulation visualizer
├── login.php                     # Public: Secure administrator authentication portal
├── logout.php                    # Admin: Session termination & activity logging
├── map.php                       # Public: Full-screen Leaflet & OSM interactive traffic map
├── setup_admin.php               # Utility: Initial administrator provisioning tool
│
├── ai_service/                   # Python Computer Vision & Telemetry Microservice
│   ├── app.py                    # Flask microservice (YOLO/OpenCV vehicle detection)
│   ├── requirements.txt          # Python dependencies (flask, requests, numpy)
│   ├── run_simulation.py         # Continuous multi-junction telemetry simulation loop
│   └── README.md                 # Python service installation and API guide
│
├── api/                          # REST API Endpoints (JSON)
│   ├── advisories.php            # GET active road closures, detours & maintenance alerts
│   ├── junctions.php             # GET junction data with 4-way counts & Haversine distance
│   ├── reports.php               # GET/POST citizen incident reports & complaints
│   └── telemetry.php             # POST live AI vehicle counts, adaptive timing & alerts
│
├── assets/
│   ├── css/
│   │   └── style.css             # Unified bright theme stylesheets & responsive layout
│   ├── js/
│   │   ├── live_map.js           # Leaflet controller, geolocation, search, and drawer
│   │   └── main.js               # Counters, mobile navigation, tooltips, interactive tabs
│   └── images/                   # High-definition smart-city imagery & icons
│       └── uploads/              # Administrator uploaded media library
│
├── config/
│   └── db.php                    # PDO database connection with error-resilient fallback
│
├── database/
│   ├── migrate.php               # Web-based database migration utility
│   ├── migration_v3.sql          # v3.0 SQL migration script (reports, advisories, telemetry)
│   ├── smartflow_ai.sql          # Master Schema v3.0 with complete DDL & seed dataset
│   └── smartflow_ai_schema_update.sql # v2.0 schema update script
│
└── includes/
    ├── admin_nav.php             # Admin sidebar and top action bar
    ├── auth.php                  # Session authentication & role permission checks
    ├── footer.php                # Responsive public footer with live status indicators
    ├── functions.php             # Helper utilities, CSRF, sanitization, time formatting
    └── header.php                # Sticky navigation header with mobile drawer
```

---

## 🛠️ Step-by-Step Installation (XAMPP on Windows)

### 1. Place Project in XAMPP
Ensure the project is in your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\SmartFlow_AI_Starter\
```

### 2. Start Apache and MySQL
1. Launch **XAMPP Control Panel**.
2. Click **Start** for **Apache**.
3. Click **Start** for **MySQL**.

### 3. Setup the Database
You can set up the database using any of the following two simple methods:

#### Method A: Web Migration (Recommended & Instant)
1. Open your browser and navigate to:
   ```
   http://localhost/SmartFlow_AI_Starter/database/migrate.php
   ```
2. The migration tool automatically creates the `smartflow_ai` database and imports all tables, columns, indexes, and seed junctions.

#### Method B: phpMyAdmin Import
1. Open `http://localhost/phpmyadmin/`.
2. Create a new database named `smartflow_ai`.
3. Select `smartflow_ai`, click the **Import** tab, and upload:
   ```
   C:\xampp\htdocs\SmartFlow_AI_Starter\database\smartflow_ai.sql
   ```
4. Click **Import**.

### 4. Create Your Administrator Account
1. Open `http://localhost/SmartFlow_AI_Starter/setup_admin.php`.
2. Fill in your details (e.g., Name: `System Administrator`, Email: `admin@smartflow.ai`, Password: `Admin@12345`).
3. Click **Create Administrator**.

---

## 🚦 Using SmartFlow AI

### 1. Public Features
- **Homepage**: `http://localhost/SmartFlow_AI_Starter/index.php`
- **Interactive Live Map**: `http://localhost/SmartFlow_AI_Starter/map.php`
  - Click **"Locate Me"** to find junctions nearest to your location.
  - Click any marker to view real-time 4-way vehicle counts, congestion level, and detour routes.
  - Use the **Search bar** to search by name or street.
  - Filter junctions by congestion level (All, Low, Moderate, High) or Zone.
  - Click the **Star icon** to save your favorite daily commute junctions.
  - Open the **"Report Traffic Issue"** tab to submit real-time roadway feedback.
- **Live Traffic Simulation Visualizer**: `http://localhost/SmartFlow_AI_Starter/live-traffic.php`
- **Junction Locations Directory**: `http://localhost/SmartFlow_AI_Starter/junctions.php`
- **Traffic Analytics**: `http://localhost/SmartFlow_AI_Starter/analytics.php`

### 2. Admin Control Panel
1. Navigate to: `http://localhost/SmartFlow_AI_Starter/login.php`
2. Sign in with your administrator credentials.
3. Access:
   - **Executive Dashboard** (`dashboard.php`): Key metric cards, performance charts, open emergency alerts, recent audit activity logs, and **AI Traffic Recommendations**.
   - **Website Content Management** (`admin_website.php`): Customize hero title, subtitle, background image, feature cards, and contact information.
   - **Junction Management** (`admin_junctions.php`): Add, edit, or configure traffic junctions, GPS coordinates, and signal timing limits.
   - **User Management** (`admin_users.php`): Provision operators, reset passwords, and toggle accounts.
   - **Citizen Reports Moderation Desk** (`admin_reports.php`): Review citizen hazard reports, change status (Pending, Investigating, Resolved, Dismissed), and add notes.
   - **Traffic Alerts** (`admin_alerts.php`): Broadcast congestion and emergency alerts.
   - **Media Library** (`admin_images.php`): Upload and manage hero backgrounds.
   - **Activity Audit Logs** (`admin_logs.php`): Review chronological security logs.

---

## 🐍 Running the Python AI Microservice

The Python microservice connects OpenCV and YOLOv8 vehicle detection with the SmartFlow AI core.

### 1. Install Python Dependencies
Open PowerShell or Command Prompt:
```powershell
cd C:\xampp\htdocs\SmartFlow_AI_Starter\ai_service
pip install -r requirements.txt
```

### 2. Start the AI Microservice
```powershell
python app.py
```
The microservice starts at `http://127.0.0.1:5000/`. You can view its diagnostics dashboard in your browser.

### 3. Run Continuous Multi-Junction Telemetry Streaming (Optional)
To stream dynamic vehicle counts, congestion indices, and adaptive green times into the PHP database every 15 seconds:
```powershell
python run_simulation.py --interval 15 --iterations 0
```
Watch the Live Map (`map.php`) and Admin Dashboard (`dashboard.php`) update dynamically in real time!

---

## 📡 REST API Reference

| Endpoint | Method | Parameters | Description |
|---|---|---|---|
| `/api/junctions.php` | GET | `lat`, `lng`, `congestion`, `zone`, `q` | Returns active junctions, coordinates, 4-way counts, adaptive green splits, and calculated proximity distance. |
| `/api/telemetry.php` | POST | JSON payload with vehicle counts & emergency flag | Ingests camera telemetry, recalculates adaptive green splits, logs history, and updates junction status. |
| `/api/advisories.php` | GET | `type`, `limit` | Returns road closures, scheduled maintenance, and recommended detours. |
| `/api/reports.php` | GET / POST | `junction_id`, `report_type`, `description`, `reporter_name` | Citizens submit traffic hazard reports; retrieves recent non-dismissed reports. |

---

## 🔒 Security Architecture

- **Role-Based Access Control**: Strict PHP session authentication via `require_admin()` on all management pages.
- **SQL Injection Prevention**: All queries use PDO prepared statements with bound parameters.
- **CSRF Tokens**: Form submissions require verified cryptographic CSRF tokens using `hash_equals()`.
- **Password Security**: Passwords hashed with `PASSWORD_BCRYPT` (cost factor 10).
- **XSS Protection**: HTML entities encoded through `e()` (`htmlspecialchars`) across all templates.
- **Brute-Force Protection**: Session-based login throttling (5 failed attempts locks for 5 minutes).

---

## 📄 License & Attribution
- Leaflet map library by Vladimir Agafonkin (BSD-2-Clause).
- Map tiles provided by OpenStreetMap contributors.
- SmartFlow AI core framework developed for modern smart-city mobility and transportation operations.
