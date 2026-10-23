<?php
/**
 * Global Footer Component
 */
$companyPhone = get_setting('contact_phone', COMPANY_PHONE);
$companyWhatsapp = get_setting('whatsapp_number', COMPANY_WHATSAPP);
$companyEmail = get_setting('contact_email', COMPANY_EMAIL);
$companyAddress = get_setting('office_address', COMPANY_ADDRESS);
$newsletterFlash = get_flash();
$newsletterRedirect = $_SERVER['REQUEST_URI'] ?? '/';
if ($newsletterRedirect === '' || $newsletterRedirect[0] !== '/' || str_starts_with($newsletterRedirect, '//')) {
    $newsletterRedirect = '/';
}
?>
  <!-- Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-top">
        <!-- Brand Info -->
        <div class="footer-brand">
          <img src="<?= asset(LOGO_MAIN) ?>" alt="Peniel Properties" style="max-height: 60px;">
          <p class="footer-desc">
            Peniel Properties is an established Zimbabwean real estate brand dedicated to presenting premier residential, commercial, and investment opportunities with integrity, discretion, and market intelligence.
          </p>
        </div>

        <!-- Quick Links -->
        <div>
          <h3 class="footer-col-title">Navigation</h3>
          <ul class="footer-links">
            <li><a href="<?= url() ?>" class="footer-link">Home</a></li>
            <li><a href="<?= url('properties.php') ?>" class="footer-link">All Properties</a></li>
            <li><a href="<?= url('properties.php?category=sale') ?>" class="footer-link">Properties For Sale</a></li>
            <li><a href="<?= url('properties.php?category=rent') ?>" class="footer-link">Properties For Rent</a></li>
            <li><a href="<?= url('about.php') ?>" class="footer-link">About Peniel</a></li>
            <li><a href="<?= url('contact.php') ?>" class="footer-link">Contact Us</a></li>
          </ul>
        </div>

        <!-- Popular Locations in Zimbabwe -->
        <div>
          <h3 class="footer-col-title">Prime Locations</h3>
          <ul class="footer-links">
            <li><a href="<?= url('properties.php?suburb=Borrowdale+Brooke') ?>" class="footer-link">Borrowdale Brooke</a></li>
            <li><a href="<?= url('properties.php?suburb=Highlands') ?>" class="footer-link">Highlands</a></li>
            <li><a href="<?= url('properties.php?suburb=Avondale') ?>" class="footer-link">Avondale</a></li>
            <li><a href="<?= url('properties.php?suburb=Chisipite') ?>" class="footer-link">Chisipite</a></li>
            <li><a href="<?= url('properties.php?city=Harare') ?>" class="footer-link">Harare Prime</a></li>
            <li><a href="<?= url('properties.php?city=Bulawayo') ?>" class="footer-link">Bulawayo</a></li>
          </ul>
        </div>

        <!-- Contact Information -->
        <div>
          <h3 class="footer-col-title">Head Office</h3>
          <div class="footer-contact-item">
            <svg class="footer-contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
              <circle cx="12" cy="10" r="3"></circle>
            </svg>
            <span><?= e($companyAddress) ?></span>
          </div>
          <div class="footer-contact-item">
            <svg class="footer-contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            </svg>
            <span><?= e($companyPhone) ?></span>
          </div>
          <div class="footer-contact-item">
            <svg class="footer-contact-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="4" width="20" height="16" rx="2"></rect>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
            </svg>
            <span><?= e($companyEmail) ?></span>
          </div>
        </div>

        <div class="footer-newsletter" id="newsletter-signup">
          <h3 class="footer-col-title">Property Updates</h3>
          <p class="footer-newsletter-text">Get new listings and Zimbabwe property insights delivered to your inbox.</p>
          <?php if ($newsletterFlash): ?>
            <div class="footer-newsletter-message <?= $newsletterFlash['type'] === 'success' ? 'is-success' : 'is-error' ?>" role="status">
              <?= e($newsletterFlash['message']) ?>
            </div>
          <?php endif; ?>
          <form action="<?= url('newsletter.php') ?>" method="POST" class="footer-newsletter-form">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= e($newsletterRedirect) ?>">
            <label class="sr-only" for="newsletterEmail">Email address</label>
            <input type="email" id="newsletterEmail" name="email" class="footer-newsletter-input" placeholder="Your email address" maxlength="190" required>
            <button type="submit" name="submit_newsletter" value="1" class="btn btn-lime">Subscribe</button>
          </form>
          <small class="footer-newsletter-note">Property updates only. No spam.</small>
        </div>
      </div>

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <div>
          &copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved. Registered Real Estate Agent in Zimbabwe.
        </div>
        <div style="display: flex; gap: 24px;">
          <a href="<?= url('about.php') ?>" style="color: #8E9FAA;">Privacy Notice</a>
          <a href="<?= url('contact.php') ?>" style="color: #8E9FAA;">Contact</a>
          <a href="<?= url('admin/login.php') ?>" style="color: #8E9FAA;">Admin Area</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- WhatsApp Floating Action Button (FAB) -->
  <a href="<?= get_whatsapp_url($companyWhatsapp, 'Hello Peniel Properties, I am inquiring about available properties.') ?>" 
     target="_blank" 
     class="whatsapp-fab" 
     aria-label="Chat with Peniel Properties on WhatsApp" 
     title="Chat with us on WhatsApp">
    <svg viewBox="0 0 24 24">
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.05 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    <span class="fab-text">Chat with Us</span>
  </a>

  <!-- Scripts -->
  <script src="<?= asset('assets/js/main.js') ?>"></script>
</body>
</html>
