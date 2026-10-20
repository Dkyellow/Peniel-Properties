/**
 * Peniel Properties - Public Website Interactive Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Mobile Menu Toggle & Drawer Controls
  const mobileBtn = document.getElementById('mobileMenuToggle');
  const mobileDrawer = document.getElementById('mobileNavDrawer');
  const mobileCloseBtn = document.getElementById('mobileMenuClose');

  function openMobileMenu() {
    if (mobileDrawer) {
      mobileDrawer.classList.add('is-open');
      document.body.classList.add('drawer-open');
      if (mobileBtn) mobileBtn.setAttribute('aria-expanded', 'true');
    }
  }

  function closeMobileMenu() {
    if (mobileDrawer) {
      mobileDrawer.classList.remove('is-open');
      document.body.classList.remove('drawer-open');
      if (mobileBtn) mobileBtn.setAttribute('aria-expanded', 'false');
    }
  }

  if (mobileBtn) {
    mobileBtn.addEventListener('click', function () {
      if (mobileDrawer && mobileDrawer.classList.contains('is-open')) {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    });
  }

  if (mobileCloseBtn) {
    mobileCloseBtn.addEventListener('click', closeMobileMenu);
  }

  // Close drawer when clicking any link inside it
  if (mobileDrawer) {
    mobileDrawer.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', closeMobileMenu);
    });
  }

  // Close drawer on Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && mobileDrawer && mobileDrawer.classList.contains('is-open')) {
      closeMobileMenu();
    }
  });

  // 2. Search Tabs (Sale / Rent)
  const searchTabs = document.querySelectorAll('.search-tab');
  const categoryInput = document.getElementById('searchCategoryInput');

  if (searchTabs.length && categoryInput) {
    searchTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        searchTabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const cat = this.getAttribute('data-category');
        categoryInput.value = cat || '';
      });
    });
  }

  // 3. Property Detail Gallery Thumbnail Switcher
  const mainImage = document.getElementById('galleryMainImage');
  const thumbs = document.querySelectorAll('.gallery-thumb-item img');

  if (mainImage && thumbs.length) {
    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        const fullSrc = this.getAttribute('data-full-src') || this.src;
        mainImage.src = fullSrc;
        thumbs.forEach(t => t.parentElement.classList.remove('active-thumb'));
        this.parentElement.classList.add('active-thumb');
      });
    });
  }

  // 4. Smooth Anchor Scrolling
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId.length > 1) {
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          e.preventDefault();
          targetElement.scrollIntoView({ behavior: 'smooth' });
        }
      }
    });
  });
});
