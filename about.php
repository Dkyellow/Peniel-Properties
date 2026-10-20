<?php
/**
 * Peniel Properties - About Us Page
 */

require_once __DIR__ . '/app/helpers/helpers.php';

$pageTitle = 'About Us | ' . SITE_NAME;
$pageDescription = 'Peniel Properties is a Zimbabwean real estate firm specializing in luxury residential sales, executive rentals, commercial acquisitions, and property advisory.';
$activePage = 'about';

require_once __DIR__ . '/app/includes/header.php';
?>

<!-- About Hero -->
<div style="background-color: var(--color-deep-navy); color: var(--color-white); padding: 70px 0 50px;">
  <div class="container">
    <div class="breadcrumbs" style="color: #A0B2C6; margin-bottom: 12px;">
      <a href="<?= url() ?>" style="color: #A0B2C6;">Home</a> &nbsp;/&nbsp; <span>About Us</span>
    </div>
    <span class="section-subtitle" style="color: var(--color-lime);">Our Heritage & Philosophy</span>
    <h1 style="color: var(--color-white); font-size: 3rem; margin-bottom: 16px; max-width: 800px;">
      Elevating Real Estate Standards Across Zimbabwe.
    </h1>
    <p style="color: #D3DFE8; font-size: 1.125rem; max-width: 680px; line-height: 1.7;">
      Peniel Properties provides discerning homeowners, buyers, and investors with a transparent, disciplined, and bespoke real estate advisory experience.
    </p>
  </div>
</div>

<!-- Main Story Section -->
<section class="section">
  <div class="container">
    <div class="about-story-grid">
      <div>
        <span class="section-subtitle">Who We Are</span>
        <h2 class="section-title">A Steadfast Commitment to Quality & Discretion</h2>
        <p style="font-size: 1.0625rem; color: #334155; line-height: 1.8; margin-bottom: 20px;">
          Founded with a clear vision to bridge local real estate excellence with global agency standards, <strong>Peniel Properties</strong> operates at the nexus of high-demand Zimbabwean residential and commercial markets.
        </p>
        <p style="font-size: 1.0625rem; color: #334155; line-height: 1.8; margin-bottom: 20px;">
          Whether facilitating the sale of a flagship mansion in Borrowdale Brooke, securing high-yield cluster units in Chisipite, or managing expatriate tenancies in Avondale and Highlands, our team applies rigorous due diligence to ensure every transaction is secure and rewarding.
        </p>
        <div style="display: flex; gap: 24px; margin-top: 32px;">
          <div>
            <h4 style="font-size: 1.5rem; color: var(--color-deep-navy); margin-bottom: 4px;">Harare & Bulawayo</h4>
            <span style="font-size: 0.875rem; color: var(--color-text-muted);">Core Operating Hubs</span>
          </div>
          <div style="border-left: 1px solid var(--color-border); padding-left: 24px;">
            <h4 style="font-size: 1.5rem; color: var(--color-deep-navy); margin-bottom: 4px;">100% Verified</h4>
            <span style="font-size: 0.875rem; color: var(--color-text-muted);">Title Deed & Legal Scrutiny</span>
          </div>
        </div>
      </div>

      <div style="border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow-lg);">
        <img src="<?= asset('assets/images/hero.jpg') ?>" alt="Peniel Properties Portfolio" style="width: 100%; height: 460px; object-fit: cover;">
      </div>
    </div>
  </div>
</section>

<!-- Core Values -->
<section class="section section-bg-soft">
  <div class="container">
    <div class="section-header text-center">
      <span class="section-subtitle">Our Principles</span>
      <h2 class="section-title">The Foundation of Our Practice</h2>
      <p class="section-desc">Every client engagement is governed by four unwavering operational principles.</p>
    </div>

    <div class="features-grid">
      <div class="feature-box">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
          </svg>
        </div>
        <h3 class="feature-title">Uncompromising Integrity</h3>
        <p class="feature-desc">We hold ourselves to the strictest ethical standards, delivering clear property histories, verified documents, and honest valuations without exaggeration.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </div>
        <h3 class="feature-title">Timely Responsiveness</h3>
        <p class="feature-desc">In high-demand markets, agility matters. Our team responds promptly, keeping prospective purchasers and landlords continuously informed at each stage.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
          </svg>
        </div>
        <h3 class="feature-title">Tailored Advisory</h3>
        <p class="feature-desc">No two property transactions are identical. We construct personalized sale strategies and tenant-matching criteria calibrated to your distinct goals.</p>
      </div>
    </div>
  </div>
</section>

<!-- Call to action -->
<section class="section">
  <div class="container">
    <div class="cta-banner">
      <div class="cta-banner-content">
        <h2>Experience Professional Representation</h2>
        <p>Contact our advisors today for a private consultation regarding your property requirements or portfolio management.</p>
      </div>
      <div class="cta-banner-actions">
        <a href="<?= url('contact.php') ?>" class="btn btn-lime">Contact Our Office</a>
        <a href="<?= url('properties.php') ?>" class="btn btn-outline-white">Explore Portfolio</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/app/includes/footer.php'; ?>
