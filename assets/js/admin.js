/**
 * Peniel Properties - Admin Portal Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Automatic Slug Generator
  const titleInput = document.getElementById('propTitle');
  const slugInput = document.getElementById('propSlug');

  if (titleInput && slugInput) {
    titleInput.addEventListener('input', function () {
      // Only auto-generate if slug field hasn't been manually heavily edited
      if (!slugInput.dataset.touched) {
        slugInput.value = titleInput.value
          .toLowerCase()
          .trim()
          .replace(/[^a-z0-9\s-]/g, '')
          .replace(/[\s-]+/g, '-');
      }
    });

    slugInput.addEventListener('focus', function () {
      this.dataset.touched = 'true';
    });
  }

  // 2. Cover Image Preview
  const coverFileInput = document.getElementById('propCoverInput');
  const coverPreview = document.getElementById('coverImagePreview');

  if (coverFileInput && coverPreview) {
    coverFileInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          coverPreview.src = e.target.result;
          coverPreview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // 3. Confirm Delete Prompts
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function (e) {
      const msg = this.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
      if (!confirm(msg)) {
        e.preventDefault();
      }
    });
  });

  // 4. Show/Hide Password Toggle
  document.querySelectorAll('.toggle-password-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const targetId = this.getAttribute('data-target');
      const input = targetId ? document.getElementById(targetId) : this.parentElement.querySelector('input');

      if (input) {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        this.setAttribute('title', isPassword ? 'Hide password' : 'Show password');
        this.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');

        if (isPassword) {
          this.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
              <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"></path>
              <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"></path>
              <line x1="2" y1="2" x2="22" y2="22"></line>
            </svg>
          `;
        } else {
          this.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          `;
        }
      }
    });
  });
});
