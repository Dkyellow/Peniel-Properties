<?php
/**
 * Global Header Component
 */
require_once __DIR__ . '/../helpers/helpers.php';

$pageTitle = $pageTitle ?? SITE_NAME . ' | ' . SITE_TAGLINE;
$pageDescription = $pageDescription ?? SITE_DESCRIPTION;
$canonicalUrl = $canonicalUrl ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$activePage = $activePage ?? '';
$ogImage = $ogImage ?? asset(LOGO_SHARE);
$ogImageAlt = $ogImageAlt ?? SITE_NAME . ' - ' . SITE_TAGLINE;
$ogImageWidth = $ogImageWidth ?? 1200;
$ogImageHeight = $ogImageHeight ?? 630;
$companyPhone = get_setting('contact_phone', COMPANY_PHONE);
$companyWhatsapp = get_setting('whatsapp_number', COMPANY_WHATSAPP);
$companyEmail = get_setting('contact_email', COMPANY_EMAIL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <meta name="description" content="<?= e($pageDescription) ?>">
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">
  
  <!-- Open Graph (link previews: WhatsApp, Facebook, Telegram, LinkedIn) -->
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($pageDescription) ?>">
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
  <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
  <meta property="og:type" content="<?= e($ogType ?? 'website') ?>">
  <meta property="og:locale" content="en_US">
  <meta property="og:image" content="<?= e($ogImage) ?>">
<?php if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'): ?>
  <meta property="og:image:secure_url" content="<?= e(preg_replace('#^http://#i', 'https://', $ogImage)) ?>">
<?php endif; ?>
  <meta property="og:image:type" content="<?= e($ogImageType ?? 'image/png') ?>">
  <meta property="og:image:width" content="<?= (int) $ogImageWidth ?>">
  <meta property="og:image:height" content="<?= (int) $ogImageHeight ?>">
  <meta property="og:image:alt" content="<?= e($ogImageAlt) ?>">

  <!-- Twitter / X Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($pageTitle) ?>">
  <meta name="twitter:description" content="<?= e($pageDescription) ?>">
  <meta name="twitter:image" content="<?= e($ogImage) ?>">
  <meta name="twitter:image:alt" content="<?= e($ogImageAlt) ?>">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= asset(LOGO_FAVICON) ?>">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
  <script>
    window.PenielSite = {
      logo: "<?= e(asset(LOGO_FAVICON)) ?>",
      name: "<?= e(SITE_NAME) ?>"
    };
  </script>
</head>
<body>

  <!-- Top Bar -->
  <div class="site-topbar">
    <div class="container topbar-inner">
      <div class="topbar-contacts">
        <span class="topbar-contact-item"><strong style="color: var(--color-white);">Office:</strong> <?= e($companyPhone) ?></span>
        <span class="topbar-contact-item"><strong style="color: var(--color-lime);">WhatsApp:</strong> <?= e($companyWhatsapp) ?></span>
      </div>
      <div class="topbar-portal">
        <a href="<?= url('admin/login.php') ?>">Staff Portal</a>
      </div>
    </div>
  </div>

  <!-- Main Navigation Bar -->
  <header class="site-header">
    <div class="container header-inner">
      <a href="<?= url() ?>" class="header-brand" aria-label="Peniel Properties Homepage">
        <img src="<?= asset(LOGO_WHITE) ?>" alt="Peniel Properties Logo">
      </a>

      <!-- Desktop Navigation Links -->
      <nav class="nav-desktop" aria-label="Main Navigation">
        <a href="<?= url() ?>" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>">Home</a>
        <a href="<?= url('properties.php') ?>" class="nav-link <?= $activePage === 'properties' ? 'active' : '' ?>">All Properties</a>
        <a href="<?= url('properties.php?category=sale') ?>" class="nav-link <?= $activePage === 'sale' ? 'active' : '' ?>">For Sale</a>
        <a href="<?= url('properties.php?category=rent') ?>" class="nav-link <?= $activePage === 'rent' ? 'active' : '' ?>">For Rent</a>
        <a href="<?= url('about.php') ?>" class="nav-link <?= $activePage === 'about' ? 'active' : '' ?>">About</a>
        <a href="<?= url('contact.php') ?>" class="nav-link <?= $activePage === 'contact' ? 'active' : '' ?>">Contact</a>
      </nav>

      <!-- Header Action Group -->
      <div class="header-cta-group">
        <a href="<?= url('properties.php') ?>" class="btn btn-lime btn-sm header-desktop-cta">Find a Property</a>
        
        <!-- Mobile Hamburger Button -->
        <button id="mobileMenuToggle" class="mobile-menu-btn" aria-label="Open Navigation Menu" aria-expanded="false">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div id="mobileNavDrawer" class="mobile-nav-drawer">
      <div class="mobile-nav-header">
        <img src="<?= asset(LOGO_WHITE) ?>" alt="Peniel Properties Logo" style="height: 38px; width: auto;">
        <button id="mobileMenuClose" class="mobile-drawer-close" aria-label="Close menu">&times;</button>
      </div>
      <div class="mobile-nav-list">
        <a href="<?= url() ?>" class="mobile-nav-link <?= $activePage === 'home' ? 'active' : '' ?>">Home</a>
        <a href="<?= url('properties.php') ?>" class="mobile-nav-link <?= $activePage === 'properties' ? 'active' : '' ?>">All Properties</a>
        <a href="<?= url('properties.php?category=sale') ?>" class="mobile-nav-link <?= $activePage === 'sale' ? 'active' : '' ?>">For Sale</a>
        <a href="<?= url('properties.php?category=rent') ?>" class="mobile-nav-link <?= $activePage === 'rent' ? 'active' : '' ?>">For Rent</a>
        <a href="<?= url('about.php') ?>" class="mobile-nav-link <?= $activePage === 'about' ? 'active' : '' ?>">About Us</a>
        <a href="<?= url('contact.php') ?>" class="mobile-nav-link <?= $activePage === 'contact' ? 'active' : '' ?>">Contact Us</a>
      </div>
      <div class="mobile-nav-actions" style="margin-top: auto; padding-top: 24px;">
        <a href="<?= url('properties.php') ?>" class="btn btn-lime" style="width: 100%; text-align: center; margin-bottom: 12px;">Browse Properties</a>
        <a href="<?= get_whatsapp_url($companyWhatsapp, 'Hello Peniel Properties, I would like to speak to an agent.') ?>" target="_blank" class="btn btn-whatsapp" style="width: 100%; text-align: center;">Chat on WhatsApp</a>
      </div>
    </div>
  </header>
