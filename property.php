<?php
/**
 * Peniel Properties - Property Details Page
 */

require_once __DIR__ . '/app/helpers/helpers.php';

$db = get_db_connection();

$propertyId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$propertySlug = trim($_GET['slug'] ?? '');

$property = null;
$galleryImages = [];
$relatedProperties = [];
$inquirySuccess = false;
$inquiryError = '';

if ($db) {
    try {
        // Find property by ID or Slug
        if ($propertyId > 0) {
            $stmt = $db->prepare("SELECT * FROM properties WHERE id = ? AND is_published = 1 LIMIT 1");
            $stmt->execute([$propertyId]);
            $property = $stmt->fetch();
        } elseif (!empty($propertySlug)) {
            $stmt = $db->prepare("SELECT * FROM properties WHERE slug = ? AND is_published = 1 LIMIT 1");
            $stmt->execute([$propertySlug]);
            $property = $stmt->fetch();
        }

        if ($property) {
            // Increment view count
            $db->prepare("UPDATE properties SET views_count = views_count + 1 WHERE id = ?")->execute([$property['id']]);

            // Fetch Gallery Images
            $imgStmt = $db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY display_order ASC, id ASC");
            $imgStmt->execute([$property['id']]);
            $galleryImages = $imgStmt->fetchAll();

            // Fetch Related Properties
            $relStmt = $db->prepare("
                SELECT * FROM properties 
                WHERE is_published = 1 AND id != ? AND (category = ? OR suburb = ?) 
                ORDER BY created_at DESC LIMIT 3
            ");
            $relStmt->execute([$property['id'], $property['category'], $property['suburb']]);
            $relatedProperties = $relStmt->fetchAll();

            // Handle Direct Inquiry Form Submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
                if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                    $inquiryError = 'Security validation failed (invalid CSRF token). Please refresh and try again.';
                } else {
                    $name = trim($_POST['name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    $method = trim($_POST['preferred_method'] ?? 'whatsapp');
                    $message = trim($_POST['message'] ?? '');

                    if (empty($name) || empty($phone) || empty($message)) {
                        $inquiryError = 'Please fill in all required fields (Name, Phone number, and Message).';
                    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $inquiryError = 'Please provide a valid email address.';
                    } else {
                        $inqStmt = $db->prepare("
                            INSERT INTO inquiries (property_id, name, email, phone, preferred_contact_method, message, status)
                            VALUES (?, ?, ?, ?, ?, ?, 'new')
                        ");
                        $inqStmt->execute([$property['id'], $name, $email, $phone, $method, $message]);
                        $inquirySuccess = true;
                    }
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Property Details Error: " . $e->getMessage());
    }
}

// If property does not exist, return 404
if (!$property) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$pageTitle = $property['title'] . ' | ' . SITE_NAME;
$pageDescription = substr(strip_tags($property['description']), 0, 160) . '...';
$activePage = 'properties';

// WhatsApp Message Format as specified in requirements:
// "Hello Peniel Properties, I am interested in [Property Title], reference [Property Reference]. Please provide more information."
$waMessage = "Hello Peniel Properties, I am interested in {$property['title']}, reference {$property['reference_no']}. Please provide more information.";
$waUrl = get_whatsapp_url(get_setting('whatsapp_number', COMPANY_WHATSAPP), $waMessage);

// Amenities array
$amenitiesList = [];
if (!empty($property['amenities'])) {
    $amenitiesList = array_map('trim', explode(',', $property['amenities']));
}

require_once __DIR__ . '/app/includes/header.php';
?>

<!-- Property Header Area -->
<div class="property-header-area">
  <div class="container">
    <div class="breadcrumbs">
      <a href="<?= url() ?>">Home</a> &nbsp;/&nbsp;
      <a href="<?= url('properties.php') ?>">Properties</a> &nbsp;/&nbsp;
      <a href="<?= url('properties.php?category=' . $property['category']) ?>">For <?= ucfirst($property['category']) ?></a> &nbsp;/&nbsp;
      <span><?= e($property['suburb']) ?></span>
    </div>

    <div class="property-header-row">
      <div>
        <div style="display: flex; gap: 8px; margin-bottom: 8px; align-items: center;">
          <?= render_status_badge($property['status'], $property['category']) ?>
          <span style="font-size: 0.8125rem; font-weight: 600; text-transform: uppercase; color: var(--color-primary-navy);">
            <?= ucfirst($property['property_type']) ?>
          </span>
          <span class="property-ref-tag" style="margin-left: 8px;">Ref: <?= e($property['reference_no']) ?></span>
        </div>
        <h1 class="property-detail-title"><?= e($property['title']) ?></h1>
        <div class="property-detail-location">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
          <span><?= e($property['address'] ? $property['address'] . ', ' : '') ?><?= e($property['suburb']) ?>, <?= e($property['city']) ?></span>
        </div>
      </div>

      <div class="property-detail-price-box">
        <div class="property-detail-price">
          <?= format_price($property['price'], $property['currency'], $property['rental_frequency']) ?>
        </div>
        <span style="font-size: 0.8125rem; color: var(--color-text-muted);">
          <?= $property['category'] === 'rent' ? 'Rental Lease' : 'Freehold Asking Price' ?>
        </span>
      </div>
    </div>
  </div>
</div>

<!-- Main Property Content -->
<section class="section" style="padding-top: 10px;">
  <div class="container">
    <!-- Gallery Showcase -->
    <div class="property-gallery-grid">
      <div class="gallery-main">
        <img id="galleryMainImage" src="<?= asset($property['cover_image']) ?>" alt="<?= e($property['title']) ?>">
      </div>
      <div class="gallery-thumbs">
        <!-- Main Cover as first thumb -->
        <div class="gallery-thumb-item active-thumb">
          <img src="<?= asset($property['cover_image']) ?>" alt="<?= e($property['title']) ?>" data-full-src="<?= asset($property['cover_image']) ?>">
        </div>
        <?php foreach ($galleryImages as $img): ?>
          <div class="gallery-thumb-item">
            <img src="<?= asset($img['image_path']) ?>" alt="<?= e($img['caption'] ?? $property['title']) ?>" data-full-src="<?= asset($img['image_path']) ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 2 Column Layout: Details and Sticky Sidebar -->
    <div class="property-layout">
      <!-- Left: Specifications, Description & Amenities -->
      <div>
        <!-- Overview Strip -->
        <div class="property-overview-strip">
          <?php if (!empty($property['bedrooms'])): ?>
            <div class="overview-pill">
              <div class="overview-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20M6 8v9"></path>
                </svg>
              </div>
              <div class="overview-data">
                <span>Bedrooms</span>
                <strong><?= (int)$property['bedrooms'] ?> Rooms</strong>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($property['bathrooms'])): ?>
            <div class="overview-pill">
              <div class="overview-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 12h16a1 1 0 0 1 1 1v3a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4v-3a1 1 0 0 1 1-1Z"></path>
                  <path d="M6 12V5a2 2 0 0 1 2-2h3v2"></path>
                </svg>
              </div>
              <div class="overview-data">
                <span>Bathrooms</span>
                <strong><?= (int)$property['bathrooms'] ?> Baths</strong>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($property['garages'])): ?>
            <div class="overview-pill">
              <div class="overview-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                  <path d="M9 17V7h4a3 3 0 0 1 0 6H9"></path>
                </svg>
              </div>
              <div class="overview-data">
                <span>Parking</span>
                <strong><?= (int)$property['garages'] ?> Garages</strong>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($property['floor_area_sqm'])): ?>
            <div class="overview-pill">
              <div class="overview-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 21h18M3 7v14M21 7v14M6 21V11M18 21V11"></path>
                </svg>
              </div>
              <div class="overview-data">
                <span>Floor Area</span>
                <strong><?= number_format($property['floor_area_sqm']) ?> m²</strong>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($property['land_area_sqm'])): ?>
            <div class="overview-pill">
              <div class="overview-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                </svg>
              </div>
              <div class="overview-data">
                <span>Land Area</span>
                <strong><?= number_format($property['land_area_sqm']) ?> m²</strong>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Description -->
        <h2 class="detail-section-title">Property Description</h2>
        <div class="detail-description"><?= e($property['description']) ?></div>

        <!-- Features & Amenities -->
        <?php if (!empty($amenitiesList)): ?>
          <h2 class="detail-section-title">Features & Key Amenities</h2>
          <div class="amenities-list">
            <?php foreach ($amenitiesList as $amenity): ?>
              <div class="amenity-item">
                <svg class="amenity-check" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span><?= e($amenity) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Right: Sticky Inquiry Form & WhatsApp -->
      <aside class="property-sidebar">
        <div class="inquiry-card">
          <h3 class="inquiry-card-title">Inquire About This Property</h3>
          <p class="inquiry-card-desc">
            Directly connect with our property specialists for viewing arrangements or full property packs.
          </p>

          <!-- WhatsApp Primary Action -->
          <a href="<?= $waUrl ?>" target="_blank" class="btn btn-whatsapp" style="width: 100%; margin-bottom: 20px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.05 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            Inquire on WhatsApp
          </a>

          <div style="text-align: center; margin-bottom: 20px; position: relative;">
            <hr style="border: none; border-top: 1px solid var(--color-border); margin: 0;">
            <span style="position: relative; top: -11px; background: #fff; padding: 0 10px; font-size: 0.75rem; color: var(--color-text-muted);">OR SEND AN EMAIL INQUIRY</span>
          </div>

          <?php if ($inquirySuccess): ?>
            <div class="site-toast site-toast-success" data-site-toast role="status" aria-live="polite">
              <span class="site-toast-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
              </span>
              <span class="site-toast-body">
                <strong class="site-toast-title">Inquiry received</strong>
                <span class="site-toast-text">Thank you! Your inquiry has been registered. Our property manager will get in touch shortly.</span>
              </span>
              <button type="button" class="site-toast-close" aria-label="Dismiss notification">&times;</button>
            </div>

            <div class="alert alert-success">
              Thank you! Your inquiry has been registered. Our property manager will get in touch shortly.
            </div>
          <?php else: ?>
            <?php if (!empty($inquiryError)): ?>
              <div class="alert alert-danger"><?= e($inquiryError) ?></div>
            <?php endif; ?>

            <form action="" method="POST">
              <?= csrf_field() ?>

              <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="inqName">Your Name *</label>
                <input type="text" id="inqName" name="name" class="form-control" required placeholder="John Moyo">
              </div>

              <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="inqPhone">Phone Number *</label>
                <input type="tel" id="inqPhone" name="phone" class="form-control" required placeholder="+263 77 000 0000">
              </div>

              <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="inqEmail">Email Address</label>
                <input type="email" id="inqEmail" name="email" class="form-control" placeholder="john@example.com">
              </div>

              <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" for="inqMethod">Preferred Contact</label>
                <select id="inqMethod" name="preferred_method" class="form-control">
                  <option value="whatsapp">WhatsApp</option>
                  <option value="phone">Phone Call</option>
                  <option value="email">Email</option>
                </select>
              </div>

              <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="inqMsg">Message *</label>
                <textarea id="inqMsg" name="message" class="form-control" rows="4" style="height: auto;" required>I am interested in <?= e($property['title']) ?> (Ref: <?= e($property['reference_no']) ?>). Please provide viewing availability.</textarea>
              </div>

              <button type="submit" name="submit_inquiry" value="1" class="btn btn-primary" style="width: 100%;">
                Submit Inquiry
              </button>
            </form>
          <?php endif; ?>
        </div>
      </aside>
    </div>

    <!-- Related Listings -->
    <?php if (!empty($relatedProperties)): ?>
      <div style="margin-top: 80px; padding-top: 48px; border-top: 1px solid var(--color-border);">
        <h2 class="section-title" style="margin-bottom: 32px;">Similar Properties You May Like</h2>
        <div class="properties-grid">
          <?php foreach ($relatedProperties as $prop): ?>
            <?php include __DIR__ . '/app/includes/property-card.php'; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/app/includes/footer.php'; ?>
