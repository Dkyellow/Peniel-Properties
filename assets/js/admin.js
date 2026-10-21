/**
 * Peniel Properties - Admin Portal Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
  // 0. Mobile Sidebar Toggle
  const sidebar = document.getElementById('adminSidebar');
  const menuBtn = document.getElementById('adminMenuBtn');
  const overlay = document.getElementById('adminSidebarOverlay');

  if (sidebar && menuBtn) {
    const setNavState = function (open) {
      sidebar.classList.toggle('open', open);
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.classList.toggle('admin-nav-open', open);

      if (overlay) {
        overlay.classList.toggle('active', open);
        overlay.hidden = !open;
      }
    };

    menuBtn.addEventListener('click', function () {
      setNavState(!sidebar.classList.contains('open'));
    });

    if (overlay) {
      overlay.addEventListener('click', function () {
        setNavState(false);
      });
    }

    sidebar.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', function () {
        if (window.matchMedia('(max-width: 992px)').matches) {
          setNavState(false);
        }
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('open')) {
        setNavState(false);
      }
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 992 && sidebar.classList.contains('open')) {
        setNavState(false);
      }
    });
  }

  // 0.1 New Inquiry Notifications (polled from the admin notification feed)
  if (sidebar && sidebar.dataset.unreadInquiries !== undefined) {
    let lastCount = parseInt(sidebar.dataset.unreadInquiries, 10) || 0;
    const endpoint = window.location.pathname.replace(/[^/]*$/, '') + 'notifications.php';
    const inquiriesLink = document.getElementById('adminInquiriesLink');
    let badge = document.getElementById('adminInquiryBadge');

    const renderBadge = function (count) {
      if (!inquiriesLink) return;
      if (count > 0) {
        if (!badge || !badge.isConnected) {
          badge = document.createElement('span');
          badge.className = 'admin-nav-badge';
          badge.id = 'adminInquiryBadge';
          inquiriesLink.appendChild(badge);
        }
        badge.textContent = count;
      } else if (badge) {
        badge.remove();
        badge = null;
      }
    };

    const showAdminToast = function (title, message) {
      const cfg = window.PenielAdmin || {};
      const toastIcon = cfg.logo
        ? '<span class="admin-toast-logo"><img src="' + cfg.logo + '" alt=""></span>'
        : '<span class="admin-toast-icon">' +
          '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>' +
          '</span>';

      const toast = document.createElement('div');
      toast.className = 'admin-toast';
      toast.setAttribute('role', 'status');
      toast.innerHTML =
        toastIcon +
        '<span class="admin-toast-body">' +
        '<strong class="admin-toast-title"></strong>' +
        '<span class="admin-toast-text"></span>' +
        '</span>' +
        '<button type="button" class="admin-toast-close" aria-label="Dismiss notification">&times;</button>';

      toast.querySelector('.admin-toast-title').textContent = title;
      toast.querySelector('.admin-toast-text').textContent = message;

      const dismiss = function () {
        toast.classList.remove('is-visible');
        setTimeout(function () {
          if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, 320);
      };

      toast.querySelector('.admin-toast-close').addEventListener('click', dismiss);

      document.body.appendChild(toast);
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          toast.classList.add('is-visible');
        });
      });
      setTimeout(dismiss, 7000);
    };

    const sendBrowserNotification = function (title, message) {
      if (!('Notification' in window) || Notification.permission !== 'granted') return;
      const cfg = window.PenielAdmin || {};
      const options = { body: message, tag: 'peniel-new-inquiry' };
      if (cfg.logo) {
        options.icon = cfg.logo;
        options.badge = cfg.logo;
      }

      const legacyNotify = function () {
        try {
          new Notification(title, options);
        } catch (e) { /* blocked in this context - toast already shown */ }
      };

      if ('serviceWorker' in navigator && navigator.serviceWorker.getRegistration) {
        navigator.serviceWorker.getRegistration().then(function (reg) {
          if (reg && reg.showNotification) {
            reg.showNotification(title, options).catch(legacyNotify);
          } else {
            legacyNotify();
          }
        }).catch(legacyNotify);
      } else {
        legacyNotify();
      }
    };

    if ('serviceWorker' in navigator && (window.PenielAdmin || {}).sw) {
      navigator.serviceWorker.register(window.PenielAdmin.sw).catch(function () {});
    }

    if ('Notification' in window && Notification.permission === 'default') {
      const requestPermission = function () {
        Notification.requestPermission().then(function (result) {
          document.removeEventListener('click', requestPermission);
          if (result === 'granted') {
            sendBrowserNotification(
              'Notifications Enabled',
              'You will be alerted here whenever a new client inquiry arrives.'
            );
          }
        }).catch(function () {});
      };
      document.addEventListener('click', requestPermission);
    }

    const pollNotifications = function () {
      fetch(endpoint, { cache: 'no-store', credentials: 'same-origin' })
        .then(function (res) {
          const type = res.headers.get('content-type') || '';
          if (!res.ok || type.indexOf('application/json') === -1) throw new Error('feed unavailable');
          return res.json();
        })
        .then(function (data) {
          if (!data || !data.ok) return;

          const count = parseInt(data.count, 10) || 0;

          if (count > lastCount && data.latest) {
            const title = 'New Client Inquiry';
            const property = data.latest.property_title || data.latest.reference_no;
            const message = property
              ? data.latest.name + ' inquired about ' + property
              : data.latest.name + ' sent a general inquiry';

            showAdminToast(title, message);
            sendBrowserNotification(title, message);
          }

          if (count !== lastCount) {
            renderBadge(count);
            sidebar.dataset.unreadInquiries = count;
            lastCount = count;
          }
        })
        .catch(function () { /* silent - retry on next tick */ });
    };

    setInterval(pollNotifications, 10000);
  }

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
