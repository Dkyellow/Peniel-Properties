<?php
/**
 * 404 Not Found Page
 */
require_once __DIR__ . '/app/helpers/helpers.php';

$pageTitle = 'Page Not Found | ' . SITE_NAME;
$pageDescription = 'The requested property or page could not be located.';

if (!headers_sent()) {
    http_response_code(404);
}

require_once __DIR__ . '/app/includes/header.php';
?>

<section class="section" style="padding: 100px 0; text-align: center;">
  <div class="container" style="max-width: 600px;">
    <div style="font-family: var(--font-heading); font-size: 6rem; color: var(--color-deep-navy); line-height: 1; margin-bottom: 16px;">
      404
    </div>
    <h1 style="font-size: 2rem; margin-bottom: 16px;">Property or Page Not Found</h1>
    <p style="color: var(--color-text-muted); font-size: 1.0625rem; margin-bottom: 32px; line-height: 1.7;">
      The listing you are looking for may have been sold, leased, or moved. Please explore our active catalogue to find available properties across Zimbabwe.
    </p>
    <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
      <a href="<?= url() ?>" class="btn btn-primary">Return Home</a>
      <a href="<?= url('properties.php') ?>" class="btn btn-outline-navy">View All Properties</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/app/includes/footer.php'; ?>
