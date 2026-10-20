<?php
/**
 * Admin Panel Header
 */
require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? 'Administrator Panel';
$activeNav = $activeNav ?? 'dashboard';

$db = get_db_connection();
$unreadInquiriesCount = 0;
if ($db) {
    try {
        $inqStmt = $db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'");
        $unreadInquiriesCount = (int)$inqStmt->fetchColumn();
    } catch (Exception $e) {}
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> | Peniel Admin</title>
  <link rel="icon" type="image/svg+xml" href="<?= asset(LOGO_FAVICON) ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body>

  <!-- Mobile Sidebar Overlay -->
  <div class="admin-sidebar-overlay" id="adminSidebarOverlay" hidden></div>

  <!-- Admin Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar" data-unread-inquiries="<?= (int) $unreadInquiriesCount ?>">
    <div class="admin-sidebar-header">
      <img src="<?= asset(LOGO_WHITE) ?>" alt="Peniel Properties">
    </div>

    <nav class="admin-nav">
      <a href="<?= url('admin/index.php') ?>" class="admin-nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        Dashboard
      </a>

      <a href="<?= url('admin/properties.php') ?>" class="admin-nav-link <?= $activeNav === 'properties' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        Properties
      </a>

      <a href="<?= url('admin/property-create.php') ?>" class="admin-nav-link <?= $activeNav === 'property-create' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="16"></line>
          <line x1="8" y1="12" x2="16" y2="12"></line>
        </svg>
        Add Property
      </a>

      <a href="<?= url('admin/inquiries.php') ?>" id="adminInquiriesLink" class="admin-nav-link <?= $activeNav === 'inquiries' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        Inquiries
        <?php if ($unreadInquiriesCount > 0): ?>
          <span class="admin-nav-badge" id="adminInquiryBadge"><?= $unreadInquiriesCount ?></span>
        <?php endif; ?>
      </a>

      <a href="<?= url('admin/settings.php') ?>" class="admin-nav-link <?= $activeNav === 'settings' ? 'active' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="3"></circle>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
        Site Settings
      </a>
    </nav>

    <div class="admin-sidebar-footer">
      <a href="<?= url() ?>" target="_blank" class="admin-nav-link" style="color: #94A3B8;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
          <polyline points="15 3 21 3 21 9"></polyline>
          <line x1="10" y1="14" x2="21" y2="3"></line>
        </svg>
        View Live Website
      </a>
      <a href="<?= url('admin/logout.php') ?>" class="admin-nav-link" style="color: #EF4444; margin-top: 4px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        Sign Out
      </a>
    </div>
  </aside>

  <!-- Admin Main Wrapper -->
  <div class="admin-main">
    <header class="admin-topbar">
      <div class="admin-topbar-left">
        <button type="button" class="admin-menu-btn" id="adminMenuBtn" aria-label="Open navigation menu" aria-controls="adminSidebar" aria-expanded="false">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>
        <h1 class="admin-page-title"><?= e($pageTitle) ?></h1>
      </div>
      <div class="admin-user-profile">
        <span class="admin-welcome-text">Welcome, <strong><?= e($currentUser['full_name'] ?: $currentUser['username']) ?></strong></span>
        <span class="admin-welcome-short" title="Welcome, <?= e($currentUser['full_name'] ?: $currentUser['username']) ?>">
          <span class="admin-avatar"><?= e(strtoupper(substr($currentUser['full_name'] ?: $currentUser['username'], 0, 1))) ?></span>
        </span>
      </div>
    </header>

    <main class="admin-body">
      <!-- Flash Alert Display -->
      <?php if ($flash): ?>
        <div style="padding: 14px 18px; border-radius: var(--admin-radius); margin-bottom: 24px; font-size: 0.9375rem; background: <?= $flash['type'] === 'success' ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= $flash['type'] === 'success' ? '#166534' : '#991B1B' ?>; border: 1px solid <?= $flash['type'] === 'success' ? '#BBF7D0' : '#FECACA' ?>;">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>
