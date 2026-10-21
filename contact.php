<?php
/**
 * Peniel Properties - Contact Us Page
 */

require_once __DIR__ . '/app/helpers/helpers.php';

$pageTitle = 'Contact Us | ' . SITE_NAME;
$pageDescription = 'Get in touch with Peniel Properties. Inquire about properties for sale and rent, schedule a viewing, or speak with a property consultant in Zimbabwe.';
$activePage = 'contact';

$db = get_db_connection();
$contactSuccess = false;
$contactError = '';

$companyPhone = get_setting('contact_phone', COMPANY_PHONE);
$companyWhatsapp = get_setting('whatsapp_number', COMPANY_WHATSAPP);
$companyEmail = get_setting('contact_email', COMPANY_EMAIL);
$companyAddress = get_setting('office_address', COMPANY_ADDRESS);
$companyHours = get_setting('working_hours', 'Monday - Friday: 8:00 AM - 5:00 PM | Saturday: 9:00 AM - 1:00 PM');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $contactError = 'Security validation failed (CSRF token expired). Please refresh and submit again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $method = trim($_POST['preferred_method'] ?? 'whatsapp');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($phone) || empty($message)) {
            $contactError = 'Please fill in all required fields (Name, Phone number, and Message).';
        } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $contactError = 'Please enter a valid email address.';
        } elseif (!$db) {
            $contactError = 'Database connection is temporarily unavailable. Please contact us directly on WhatsApp or telephone.';
        } else {
            try {
                $stmt = $db->prepare("
                    INSERT INTO inquiries (name, email, phone, preferred_contact_method, message, status)
                    VALUES (?, ?, ?, ?, ?, 'new')
                ");
                $stmt->execute([$name, $email, $phone, $method, $message]);
                $contactSuccess = true;
            } catch (PDOException $e) {
                error_log("Contact Form Error: " . $e->getMessage());
                $contactError = 'An unexpected error occurred while saving your inquiry. Please try again or reach out on WhatsApp.';
            }
        }
    }
}

require_once __DIR__ . '/app/includes/header.php';
?>

<!-- Header Banner -->
<div style="background-color: var(--color-deep-navy); color: var(--color-white); padding: 70px 0 50px;">
  <div class="container">
    <div class="breadcrumbs" style="color: #A0B2C6; margin-bottom: 12px;">
      <a href="<?= url() ?>" style="color: #A0B2C6;">Home</a> &nbsp;/&nbsp; <span>Contact Us</span>
    </div>
    <span class="section-subtitle" style="color: var(--color-lime);">Get In Touch</span>
    <h1 style="color: var(--color-white); font-size: 3rem; margin-bottom: 16px;">Speak With Our Team</h1>
    <p style="color: #D3DFE8; font-size: 1.125rem; max-width: 650px;">
      Whether you are seeking your next residence, listing an exclusive estate, or exploring commercial real estate opportunities in Zimbabwe, our team is ready to assist.
    </p>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="contact-layout">
      <!-- Contact Details & Direct Actions -->
      <div>
        <h2 class="detail-section-title">Head Office & Inquiries</h2>
        <p style="color: var(--color-text-muted); margin-bottom: 32px; font-size: 1rem; line-height: 1.7;">
          Visit our offices or connect directly with an agent via phone or WhatsApp for prompt consultation.
        </p>

        <!-- Address Card -->
        <div style="display: flex; gap: 16px; margin-bottom: 24px; padding: 20px; border-radius: var(--radius-md); background: var(--color-off-white); border: 1px solid var(--color-border);">
          <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background: var(--color-deep-navy); color: var(--color-lime); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
          </div>
          <div>
            <strong style="display: block; font-size: 1.0625rem; color: var(--color-deep-navy); margin-bottom: 4px;">Office Address</strong>
            <span style="color: var(--color-text-muted); font-size: 0.9375rem;"><?= e($companyAddress) ?></span>
          </div>
        </div>

        <!-- Phone & WhatsApp Card -->
        <div style="display: flex; gap: 16px; margin-bottom: 24px; padding: 20px; border-radius: var(--radius-md); background: var(--color-off-white); border: 1px solid var(--color-border);">
          <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background: var(--color-deep-navy); color: var(--color-lime); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
          </div>
          <div>
            <strong style="display: block; font-size: 1.0625rem; color: var(--color-deep-navy); margin-bottom: 4px;">Telephone & Inquiries</strong>
            <span style="color: var(--color-text-muted); font-size: 0.9375rem; display: block;"><?= e($companyPhone) ?></span>
            <span style="color: var(--color-text-muted); font-size: 0.9375rem;"><?= e($companyEmail) ?></span>
          </div>
        </div>

        <!-- Working Hours Card -->
        <div style="display: flex; gap: 16px; margin-bottom: 32px; padding: 20px; border-radius: var(--radius-md); background: var(--color-off-white); border: 1px solid var(--color-border);">
          <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background: var(--color-deep-navy); color: var(--color-lime); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <div>
            <strong style="display: block; font-size: 1.0625rem; color: var(--color-deep-navy); margin-bottom: 4px;">Hours of Operation</strong>
            <span style="color: var(--color-text-muted); font-size: 0.875rem;"><?= e($companyHours) ?></span>
          </div>
        </div>
      </div>

      <!-- Inquiry Form Card -->
      <div class="contact-form-card">
        <h2 style="font-size: 1.875rem; margin-bottom: 8px;">Send an Inquiry</h2>
        <p style="color: var(--color-text-muted); font-size: 0.9375rem; margin-bottom: 28px;">
          Complete the form below and an advisor will contact you within one business day.
        </p>

        <?php if ($contactSuccess): ?>
          <div class="site-toast site-toast-success" data-site-toast role="status" aria-live="polite">
            <span class="site-toast-icon site-toast-logo">
              <img src="<?= asset(LOGO_FAVICON) ?>" alt="<?= e(SITE_NAME) ?>">
            </span>
            <span class="site-toast-body">
              <strong class="site-toast-title">Inquiry received</strong>
              <span class="site-toast-text">Thank you! Your message has been received. A Peniel Properties representative will contact you shortly.</span>
            </span>
            <button type="button" class="site-toast-close" aria-label="Dismiss notification">&times;</button>
          </div>

          <div class="alert alert-success">
            Thank you! Your message has been received. A Peniel Properties representative will contact you shortly.
          </div>
        <?php else: ?>
          <?php if (!empty($contactError)): ?>
            <div class="alert alert-danger"><?= e($contactError) ?></div>
          <?php endif; ?>

          <form action="" method="POST">
            <?= csrf_field() ?>

            <div class="form-group" style="margin-bottom: 18px;">
              <label class="form-label" for="contactName">Full Name *</label>
              <input type="text" id="contactName" name="name" class="form-control" required placeholder="e.g. Tendai Choto">
            </div>

            <div class="contact-inputs-row">
              <div class="form-group">
                <label class="form-label" for="contactPhone">Phone / WhatsApp *</label>
                <input type="tel" id="contactPhone" name="phone" class="form-control" required placeholder="+263 77 123 4567">
              </div>

              <div class="form-group">
                <label class="form-label" for="contactEmail">Email Address</label>
                <input type="email" id="contactEmail" name="email" class="form-control" placeholder="tendai@example.com">
              </div>
            </div>

            <div class="form-group" style="margin-bottom: 18px;">
              <label class="form-label" for="contactMethod">Preferred Response Channel</label>
              <select id="contactMethod" name="preferred_method" class="form-control">
                <option value="whatsapp">WhatsApp</option>
                <option value="phone">Direct Phone Call</option>
                <option value="email">Email</option>
              </select>
            </div>

            <div class="form-group" style="margin-bottom: 24px;">
              <label class="form-label" for="contactMsg">Message / Property Requirement *</label>
              <textarea id="contactMsg" name="message" class="form-control" rows="5" style="height: auto;" required placeholder="Tell us about the property you are looking for or the property you wish to sell/let..."></textarea>
            </div>

            <button type="submit" name="submit_contact" value="1" class="btn btn-primary" style="width: 100%; height: 50px;">
              Send Message
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/app/includes/footer.php'; ?>
