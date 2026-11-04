<?php
declare(strict_types=1);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$success_msg = '';
$error_msg = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf()) {
        $error_msg = 'Session validation token expired. Please refresh and submit again.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $organization = trim((string)($_POST['organization'] ?? ''));
        $inquiry_type = trim((string)($_POST['inquiry_type'] ?? 'General Inquiry'));
        $message = trim((string)($_POST['message'] ?? ''));

        if ($name === '' || !$email || strlen($message) < 5) {
            $error_msg = 'Please provide your name, a valid email address, and an inquiry message.';
        } else {
            $success_msg = "Thank you, {$name}! Your inquiry regarding '{$inquiry_type}' has been recorded. Our Smart City Operations team will reach out to {$email} within 24 hours.";
        }
    }
}

$page_title = 'Contact Operations & Engineering – SmartFlow AI';
$page_desc = 'Get in touch with the SmartFlow AI smart city deployment team, request a municipal trial, or inquire about academic research partnerships.';

$contact_email = get_setting('contact_email', 'operations@smartflow-ai.org');
$contact_phone = get_setting('contact_phone', '+1 (800) 555-FLOW');
$contact_addr  = get_setting('contact_address', '100 Innovation Boulevard, Suite 400, Technology Corridor, District 4');
$contact_hours = get_setting('contact_hours', 'Central Operations: 24/7/365');

require_once __DIR__ . '/includes/header.php';
?>

<!-- Inner Hero Section -->
<section class="inner-hero">
  <div class="site-container">
    <div class="inner-hero-content">
      <div class="breadcrumb-trail">
        <a href="index.php">Home</a>
        <span>/</span>
        <span>Contact Us</span>
      </div>
      <h1 class="inner-hero-title">Contact Operations & Deployment</h1>
      <p class="inner-hero-desc">
        Connect with our smart mobility engineers for municipal pilot inquiries, academic research partnerships, and technical support.
      </p>
    </div>
  </div>
</section>

<!-- Contact Form & Info Grid -->
<section class="section-padding bg-light-blue">
  <div class="site-container">
    <div class="contact-layout-grid">
      <!-- Left: Contact Details Cards -->
      <div class="contact-info-panel">
        <div class="contact-info-card">
          <span class="section-eyebrow">HEADQUARTERS</span>
          <h3 style="font-size: 20px; color: var(--navy-900); margin: 8px 0 14px;">Smart Mobility Operations Lab</h3>
          <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin-bottom: 20px;">
            <?= nl2br(e($contact_addr)) ?>
          </p>

          <div style="display: flex; flex-direction: column; gap: 14px; font-size: 14px;">
            <div style="display: flex; align-items: center; gap: 10px;">
              <div style="width: 34px; height: 34px; border-radius: var(--radius-sm); background: var(--sky-50); color: var(--sky-600); display: flex; align-items: center; justify-content: center;">
                📞
              </div>
              <div>
                <small style="color: var(--text-light); display: block; font-size: 11px;">Operations Hotline (24/7)</small>
                <strong><?= e($contact_phone) ?></strong>
              </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
              <div style="width: 34px; height: 34px; border-radius: var(--radius-sm); background: var(--cyan-50); color: var(--cyan-600); display: flex; align-items: center; justify-content: center;">
                ✉️
              </div>
              <div>
                <small style="color: var(--text-light); display: block; font-size: 11px;">Municipal Inquiries & Support</small>
                <strong><?= e($contact_email) ?></strong>
              </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
              <div style="width: 34px; height: 34px; border-radius: var(--radius-sm); background: var(--emerald-100); color: var(--emerald-600); display: flex; align-items: center; justify-content: center;">
                ⏱️
              </div>
              <div>
                <small style="color: var(--text-light); display: block; font-size: 11px;">Central Operations Center</small>
                <strong><?= e($contact_hours) ?></strong>
              </div>
            </div>
          </div>
        </div>

        <div class="contact-info-card" style="background: linear-gradient(145deg, #ffffff 0%, var(--bg-subtle) 100%); border-color: var(--border-blue);">
          <h4 style="font-size: 16px; color: var(--navy-900); margin-bottom: 8px;">Academic Evaluation & Thesis Pilots</h4>
          <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
            Are you reviewing this system for an academic capstone, final-year project, or research paper? Reach out directly for simulation log sets, algorithm source diagrams, and methodology documentation.
          </p>
        </div>
      </div>

      <!-- Right: Contact Form -->
      <div class="contact-form-card">
        <h3 style="font-size: 22px; color: var(--navy-900); margin-bottom: 8px;">Send an Inquiry to Operations</h3>
        <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 24px;">
          Complete the dispatch form below and our smart city mobility engineers will review your request.
        </p>

        <?php if ($success_msg): ?>
          <div class="notice success" role="alert">
            <span style="font-size: 18px;">✓</span>
            <div><?= e($success_msg) ?></div>
          </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
          <div class="notice error" role="alert">
            <span style="font-size: 18px;">⚠️</span>
            <div><?= e($error_msg) ?></div>
          </div>
        <?php endif; ?>

        <form method="post" action="contact.php">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="c_name">Full Name *</label>
              <input type="text" id="c_name" name="name" class="form-control" placeholder="Jane Doe" required value="<?= e($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="c_email">Work / Academic Email *</label>
              <input type="email" id="c_email" name="email" class="form-control" placeholder="jane@cityagency.gov" required value="<?= e($_POST['email'] ?? '') ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="c_org">Organization / City Agency</label>
              <input type="text" id="c_org" name="organization" class="form-control" placeholder="Department of Transportation" value="<?= e($_POST['organization'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="c_type">Inquiry Classification</label>
              <select id="c_type" name="inquiry_type" class="form-control">
                <option value="Smart City Pilot">Smart City Pilot Deployment</option>
                <option value="Academic Research & Thesis">Academic Research / Thesis Review</option>
                <option value="Enterprise NTCIP Integration">Enterprise NTCIP Integration</option>
                <option value="Technical Operations Support">Technical Operations Support</option>
                <option value="General Inquiry">General Inquiry</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="c_msg">Your Message *</label>
            <textarea id="c_msg" name="message" class="form-control" placeholder="Provide details regarding your intersection network or project requirements..." required><?= e($_POST['message'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
            Send Transmission to Operations <span>→</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- Frequently Asked Questions -->
<section class="section-padding bg-white">
  <div class="site-container">
    <div class="section-header">
      <span class="section-eyebrow">COMMON QUESTIONS</span>
      <h2 class="section-title">Frequently Asked Questions</h2>
      <p class="section-subtitle">
        Key details regarding deployment, privacy safeguards, and hardware compatibility.
      </p>
    </div>

    <div style="max-width: 820px; margin: 0 auto; display: flex; flex-direction: column; gap: 16px;">
      <div style="background-color: var(--bg-subtle); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 20px;">
        <h4 style="font-size: 16px; color: var(--navy-900); margin-bottom: 6px;">How is commuter privacy maintained?</h4>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin: 0;">
          SmartFlow AI operates strict anonymous vector classification. Camera feeds are processed inside the local roadside edge TPU in volatile memory. No video footage, facial images, or license plates are ever stored, logged, or uploaded to external servers.
        </p>
      </div>

      <div style="background-color: var(--bg-subtle); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 20px;">
        <h4 style="font-size: 16px; color: var(--navy-900); margin-bottom: 6px;">What happens if a camera or sensor malfunctions?</h4>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin: 0;">
          The system implements a deterministic fail-safe architecture. If any camera stream drops below 15 FPS or fails health checks, the controller instantly reverts that approach to standard NTCIP safety timing, while alerting central dispatch without interrupting intersection flow.
        </p>
      </div>

      <div style="background-color: var(--bg-subtle); border: 1px solid var(--border-light); border-radius: var(--radius-md); padding: 20px;">
        <h4 style="font-size: 16px; color: var(--navy-900); margin-bottom: 6px;">Can SmartFlow AI connect with existing municipal signals?</h4>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6; margin: 0;">
          Yes. SmartFlow AI communicates via the industry-standard NTCIP 1202 protocol. There is no need to replace existing physical traffic light fixtures, wiring, or signal heads; only the optical sensor and edge processor are added.
        </p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
