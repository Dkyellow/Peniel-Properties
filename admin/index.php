<?php
/**
 * Admin Dashboard Overview
 */

$pageTitle = 'Dashboard Overview';
$activeNav = 'dashboard';

require_once __DIR__ . '/includes/header.php';

$totalProps = 0;
$publishedProps = 0;
$saleProps = 0;
$rentProps = 0;
$totalInquiries = 0;
$newInquiries = 0;
$recentInquiries = [];
$recentProperties = [];

if ($db) {
    try {
        $totalProps = (int)$db->query("SELECT COUNT(*) FROM properties")->fetchColumn();
        $publishedProps = (int)$db->query("SELECT COUNT(*) FROM properties WHERE is_published = 1")->fetchColumn();
        $saleProps = (int)$db->query("SELECT COUNT(*) FROM properties WHERE category = 'sale'")->fetchColumn();
        $rentProps = (int)$db->query("SELECT COUNT(*) FROM properties WHERE category = 'rent'")->fetchColumn();
        
        $totalInquiries = (int)$db->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
        $newInquiries = (int)$db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();

        // Recent Inquiries
        $recentInquiries = $db->query("
            SELECT i.*, p.title as property_title, p.reference_no 
            FROM inquiries i 
            LEFT JOIN properties p ON i.property_id = p.id 
            ORDER BY i.created_at DESC LIMIT 5
        ")->fetchAll();

        // Recent Properties
        $recentProperties = $db->query("SELECT * FROM properties ORDER BY created_at DESC LIMIT 5")->fetchAll();
    } catch (PDOException $e) {
        error_log("Dashboard query error: " . $e->getMessage());
    }
}
?>

<!-- Metrics Overview -->
<div class="metrics-grid">
  <div class="metric-card">
    <div class="metric-label">Total Properties</div>
    <div class="metric-value"><?= $totalProps ?></div>
  </div>

  <div class="metric-card">
    <div class="metric-label">Active / Published</div>
    <div class="metric-value" style="color: #166534;"><?= $publishedProps ?></div>
  </div>

  <div class="metric-card">
    <div class="metric-label">Properties For Sale</div>
    <div class="metric-value"><?= $saleProps ?></div>
  </div>

  <div class="metric-card">
    <div class="metric-label">Properties For Rent</div>
    <div class="metric-value"><?= $rentProps ?></div>
  </div>

  <div class="metric-card">
    <div class="metric-label">Total Inquiries</div>
    <div class="metric-value"><?= $totalInquiries ?></div>
  </div>

  <div class="metric-card">
    <div class="metric-label">New / Pending</div>
    <div class="metric-value" style="color: #D97706;"><?= $newInquiries ?></div>
  </div>
</div>

<!-- Quick Action Strip -->
<div style="display: flex; gap: 14px; margin-bottom: 32px; flex-wrap: wrap;">
  <a href="<?= url('admin/property-create.php') ?>" class="btn-admin btn-admin-lime">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="12" y1="8" x2="12" y2="16"></line>
      <line x1="8" y1="12" x2="16" y2="12"></line>
    </svg>
    Add New Property
  </a>
  <a href="<?= url('admin/properties.php') ?>" class="btn-admin btn-admin-outline">
    View All Listings
  </a>
  <a href="<?= url('admin/inquiries.php') ?>" class="btn-admin btn-admin-outline">
    Manage Inquiries
  </a>
  <a href="<?= url('admin/settings.php') ?>" class="btn-admin btn-admin-outline">
    Update Contact Settings
  </a>
</div>

<!-- Recent Inquiries Section -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2 class="admin-card-title">Recent Client Inquiries</h2>
    <a href="<?= url('admin/inquiries.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.8125rem;">View All Inquiries &rarr;</a>
  </div>

  <?php if (!empty($recentInquiries)): ?>
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Client Name</th>
            <th>Phone / WhatsApp</th>
            <th>Property Ref</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentInquiries as $inq): ?>
            <tr>
              <td><?= date('d M Y, H:i', strtotime($inq['created_at'])) ?></td>
              <td><strong><?= e($inq['name']) ?></strong></td>
              <td><?= e($inq['phone']) ?></td>
              <td>
                <?php if (!empty($inq['property_title'])): ?>
                  <span title="<?= e($inq['property_title']) ?>">
                    <?= e($inq['reference_no']) ?>
                  </span>
                <?php else: ?>
                  <span style="color: var(--admin-muted);">General Inquiry</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="admin-badge <?= $inq['status'] === 'new' ? 'badge-new' : 'badge-contacted' ?>">
                  <?= ucfirst($inq['status']) ?>
                </span>
              </td>
              <td>
                <a href="<?= get_whatsapp_url($inq['phone'], 'Hello ' . $inq['name'] . ', thank you for contacting Peniel Properties.') ?>" target="_blank" class="btn-admin btn-admin-lime" style="padding: 4px 10px; font-size: 0.75rem;">
                  WhatsApp
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div style="padding: 32px; text-align: center; color: var(--admin-muted);">
      No client inquiries registered yet.
    </div>
  <?php endif; ?>
</div>

<!-- Recent Properties Section -->
<div class="admin-card">
  <div class="admin-card-header">
    <h2 class="admin-card-title">Latest Property Listings</h2>
    <a href="<?= url('admin/properties.php') ?>" class="btn-admin btn-admin-outline" style="font-size: 0.8125rem;">View All &rarr;</a>
  </div>

  <?php if (!empty($recentProperties)): ?>
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Property</th>
            <th>Category</th>
            <th>Price</th>
            <th>Location</th>
            <th>Status</th>
            <th>Published</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentProperties as $prop): ?>
            <tr>
              <td>
                <div style="display: flex; align-items: center; gap: 12px;">
                  <img src="<?= asset($prop['cover_image']) ?>" style="width: 48px; height: 36px; object-fit: cover; border-radius: 4px;">
                  <div>
                    <strong style="display: block;"><?= e($prop['title']) ?></strong>
                    <span style="font-size: 0.75rem; color: var(--admin-muted);">Ref: <?= e($prop['reference_no']) ?></span>
                  </div>
                </div>
              </td>
              <td><span style="text-transform: uppercase; font-size: 0.75rem; font-weight: 600;"><?= e($prop['category']) ?></span></td>
              <td><strong><?= format_price($prop['price'], $prop['currency'], $prop['rental_frequency']) ?></strong></td>
              <td><?= e($prop['suburb']) ?>, <?= e($prop['city']) ?></td>
              <td><?= ucfirst(str_replace('_', ' ', $prop['status'])) ?></td>
              <td>
                <span class="admin-badge <?= $prop['is_published'] ? 'badge-published' : 'badge-draft' ?>">
                  <?= $prop['is_published'] ? 'Active' : 'Hidden' ?>
                </span>
              </td>
              <td>
                <div style="display: flex; gap: 6px;">
                  <a href="<?= url('admin/property-edit.php?id=' . $prop['id']) ?>" class="btn-admin btn-admin-outline" style="padding: 4px 8px; font-size: 0.75rem;">Edit</a>
                  <a href="<?= url('property.php?id=' . $prop['id'] . '&slug=' . urlencode($prop['slug'])) ?>" target="_blank" class="btn-admin btn-admin-outline" style="padding: 4px 8px; font-size: 0.75rem;">View</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div style="padding: 32px; text-align: center; color: var(--admin-muted);">
      No properties added yet.
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
