<?php
/**
 * Admin - Client Inquiries Management
 */

$pageTitle = 'Client Inquiries';
$activeNav = 'inquiries';

require_once __DIR__ . '/includes/header.php';

$filterStatus = trim($_GET['status'] ?? '');

// Handle Actions (Change status, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security verification failed.');
    } else {
        $action = $_POST['action'] ?? '';
        $inquiryId = (int)($_POST['inquiry_id'] ?? 0);

        if ($inquiryId > 0 && $db) {
            try {
                if ($action === 'set_status') {
                    $newStatus = trim($_POST['new_status'] ?? 'contacted');
                    $db->prepare("UPDATE inquiries SET status = ? WHERE id = ?")->execute([$newStatus, $inquiryId]);
                    set_flash('success', 'Inquiry status updated.');
                } elseif ($action === 'delete') {
                    $db->prepare("DELETE FROM inquiries WHERE id = ?")->execute([$inquiryId]);
                    set_flash('success', 'Inquiry deleted successfully.');
                }
            } catch (PDOException $e) {
                set_flash('danger', 'Operation failed: ' . $e->getMessage());
            }
        }
    }
    header('Location: ' . url('admin/inquiries.php?' . http_build_query($_GET)));
    exit;
}

$where = ["1=1"];
$params = [];

if (!empty($filterStatus)) {
    $where[] = "i.status = ?";
    $params[] = $filterStatus;
}

$whereClause = implode(' AND ', $where);
$inquiries = [];

if ($db) {
    try {
        $query = "
            SELECT i.*, p.title as property_title, p.reference_no, p.slug
            FROM inquiries i
            LEFT JOIN properties p ON i.property_id = p.id
            WHERE $whereClause
            ORDER BY i.created_at DESC
        ";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $inquiries = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Inquiries Query Error: " . $e->getMessage());
    }
}
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="font-size: 1.5rem; color: var(--admin-navy);">Client Inquiries & Viewing Leads</h2>
    <span style="font-size: 0.875rem; color: var(--admin-muted);">Total received: <?= count($inquiries) ?></span>
  </div>

  <div style="display: flex; gap: 8px;">
    <a href="<?= url('admin/inquiries.php') ?>" class="btn-admin <?= empty($filterStatus) ? 'btn-admin-primary' : 'btn-admin-outline' ?>">All</a>
    <a href="<?= url('admin/inquiries.php?status=new') ?>" class="btn-admin <?= $filterStatus === 'new' ? 'btn-admin-primary' : 'btn-admin-outline' ?>">New</a>
    <a href="<?= url('admin/inquiries.php?status=contacted') ?>" class="btn-admin <?= $filterStatus === 'contacted' ? 'btn-admin-primary' : 'btn-admin-outline' ?>">Contacted</a>
    <a href="<?= url('admin/inquiries.php?status=closed') ?>" class="btn-admin <?= $filterStatus === 'closed' ? 'btn-admin-primary' : 'btn-admin-outline' ?>">Closed</a>
  </div>
</div>

<div class="admin-card">
  <?php if (!empty($inquiries)): ?>
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Lead Name</th>
            <th>Contact Details</th>
            <th>Related Property</th>
            <th>Message</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($inquiries as $inq): ?>
            <tr>
              <td>
                <span style="display: block; font-weight: 600;"><?= date('d M Y', strtotime($inq['created_at'])) ?></span>
                <span style="font-size: 0.75rem; color: var(--admin-muted);"><?= date('H:i', strtotime($inq['created_at'])) ?></span>
              </td>
              <td>
                <strong style="display: block; font-size: 0.9375rem;"><?= e($inq['name']) ?></strong>
                <span style="font-size: 0.75rem; color: var(--admin-muted); text-transform: capitalize;">Via <?= e($inq['preferred_contact_method']) ?></span>
              </td>
              <td>
                <span style="display: block;"><?= e($inq['phone']) ?></span>
                <?php if (!empty($inq['email'])): ?>
                  <span style="font-size: 0.75rem; color: var(--admin-muted);"><?= e($inq['email']) ?></span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($inq['property_title'])): ?>
                  <a href="<?= url('property.php?id=' . $inq['property_id'] . '&slug=' . urlencode($inq['slug'] ?? '')) ?>" target="_blank" style="color: var(--admin-navy); text-decoration: underline;">
                    <?= e($inq['reference_no']) ?>
                  </a>
                  <span style="display: block; font-size: 0.75rem; color: var(--admin-muted); max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= e($inq['property_title']) ?>
                  </span>
                <?php else: ?>
                  <span style="color: var(--admin-muted);">General</span>
                <?php endif; ?>
              </td>
              <td style="max-width: 280px;">
                <p style="font-size: 0.875rem; line-height: 1.4; color: var(--admin-text); white-space: pre-wrap;"><?= e($inq['message']) ?></p>
              </td>
              <td>
                <span class="admin-badge <?= $inq['status'] === 'new' ? 'badge-new' : ($inq['status'] === 'contacted' ? 'badge-contacted' : 'badge-draft') ?>">
                  <?= ucfirst($inq['status']) ?>
                </span>
              </td>
              <td>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                  <!-- WhatsApp Direct Reply -->
                  <a href="<?= get_whatsapp_url($inq['phone'], 'Hello ' . $inq['name'] . ', thank you for inquiring with Peniel Properties regarding ' . ($inq['property_title'] ?? 'our properties') . '.') ?>" target="_blank" class="btn-admin btn-admin-lime" style="padding: 4px 8px; font-size: 0.75rem; justify-content: center;">
                    Reply on WhatsApp
                  </a>

                  <!-- Status Change Dropdown -->
                  <form action="" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="set_status">
                    <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
                    <select name="new_status" class="admin-select" style="padding: 2px 6px; font-size: 0.75rem;" onchange="this.form.submit()">
                      <option value="new" <?= $inq['status'] === 'new' ? 'selected' : '' ?>>Mark New</option>
                      <option value="contacted" <?= $inq['status'] === 'contacted' ? 'selected' : '' ?>>Mark Contacted</option>
                      <option value="closed" <?= $inq['status'] === 'closed' ? 'selected' : '' ?>>Mark Closed</option>
                    </select>
                  </form>

                  <!-- Delete -->
                  <form action="" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="inquiry_id" value="<?= $inq['id'] ?>">
                    <button type="submit" class="btn-admin btn-admin-danger" style="padding: 2px 6px; font-size: 0.75rem; width: 100%; justify-content: center;" data-confirm="Delete this client inquiry?">
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div style="padding: 48px; text-align: center; color: var(--admin-muted);">
      No client inquiries registered in this view.
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
