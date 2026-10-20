<?php
/**
 * Admin Properties Management
 */

$pageTitle = 'Property Listings';
$activeNav = 'properties';

require_once __DIR__ . '/includes/header.php';

$search = trim($_GET['search'] ?? '');
$filterCategory = trim($_GET['category'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Handle Actions: Toggle Publish, Toggle Featured, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash('danger', 'Security token mismatch. Please try again.');
    } else {
        $action = $_POST['action'] ?? '';
        $propId = (int)($_POST['property_id'] ?? 0);

        if ($propId > 0 && $db) {
            try {
                if ($action === 'toggle_publish') {
                    $db->prepare("UPDATE properties SET is_published = 1 - is_published WHERE id = ?")->execute([$propId]);
                    set_flash('success', 'Publication status updated successfully.');
                } elseif ($action === 'toggle_featured') {
                    $db->prepare("UPDATE properties SET is_featured = 1 - is_featured WHERE id = ?")->execute([$propId]);
                    set_flash('success', 'Featured status updated successfully.');
                } elseif ($action === 'delete') {
                    // Fetch cover image and gallery images to remove from disk if inside uploads
                    $prop = $db->query("SELECT cover_image FROM properties WHERE id = $propId")->fetch();
                    $gallery = $db->query("SELECT image_path FROM property_images WHERE property_id = $propId")->fetchAll();

                    $db->prepare("DELETE FROM properties WHERE id = ?")->execute([$propId]);

                    // Cleanup physical files if in uploads
                    if ($prop && str_starts_with($prop['cover_image'], 'uploads/')) {
                        @unlink(ROOT_PATH . DIRECTORY_SEPARATOR . $prop['cover_image']);
                    }
                    foreach ($gallery as $g) {
                        if (str_starts_with($g['image_path'], 'uploads/')) {
                            @unlink(ROOT_PATH . DIRECTORY_SEPARATOR . $g['image_path']);
                        }
                    }

                    set_flash('success', 'Property listing removed permanently.');
                }
            } catch (PDOException $e) {
                set_flash('danger', 'Operation failed: ' . $e->getMessage());
            }
        }
    }
    header('Location: ' . url('admin/properties.php?' . http_build_query($_GET)));
    exit;
}

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(title LIKE ? OR reference_no LIKE ? OR suburb LIKE ?)";
    $sParam = '%' . $search . '%';
    $params = array_merge($params, [$sParam, $sParam, $sParam]);
}

if (!empty($filterCategory)) {
    $where[] = "category = ?";
    $params[] = $filterCategory;
}

if (!empty($filterStatus)) {
    $where[] = "status = ?";
    $params[] = $filterStatus;
}

$whereClause = implode(' AND ', $where);

$totalCount = 0;
$properties = [];

if ($db) {
    try {
        $countStmt = $db->prepare("SELECT COUNT(*) FROM properties WHERE $whereClause");
        $countStmt->execute($params);
        $totalCount = (int)$countStmt->fetchColumn();

        $query = "SELECT * FROM properties WHERE $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $properties = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Properties Admin Query Error: " . $e->getMessage());
    }
}
$totalPages = max(1, ceil($totalCount / $perPage));
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <h2 style="font-size: 1.5rem; color: var(--admin-navy);">Manage Property Portfolio</h2>
    <span style="font-size: 0.875rem; color: var(--admin-muted);">Total properties: <?= $totalCount ?></span>
  </div>
  <a href="<?= url('admin/property-create.php') ?>" class="btn-admin btn-admin-lime">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="12" y1="8" x2="12" y2="16"></line>
      <line x1="8" y1="12" x2="16" y2="12"></line>
    </svg>
    Add New Property
  </a>
</div>

<!-- Filters Bar -->
<div class="admin-card" style="padding: 16px 20px; margin-bottom: 24px;">
  <form action="" method="GET" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 200px;">
      <input type="text" name="search" class="admin-input" placeholder="Search by title, ref, or suburb..." value="<?= e($search) ?>">
    </div>

    <div>
      <select name="category" class="admin-select" style="min-width: 140px;">
        <option value="">All Categories</option>
        <option value="sale" <?= $filterCategory === 'sale' ? 'selected' : '' ?>>For Sale</option>
        <option value="rent" <?= $filterCategory === 'rent' ? 'selected' : '' ?>>For Rent</option>
      </select>
    </div>

    <div>
      <select name="status" class="admin-select" style="min-width: 140px;">
        <option value="">All Statuses</option>
        <option value="available" <?= $filterStatus === 'available' ? 'selected' : '' ?>>Available</option>
        <option value="under_offer" <?= $filterStatus === 'under_offer' ? 'selected' : '' ?>>Under Offer</option>
        <option value="sold" <?= $filterStatus === 'sold' ? 'selected' : '' ?>>Sold</option>
        <option value="rented" <?= $filterStatus === 'rented' ? 'selected' : '' ?>>Rented</option>
      </select>
    </div>

    <button type="submit" class="btn-admin btn-admin-primary">Filter</button>
    <a href="<?= url('admin/properties.php') ?>" class="btn-admin btn-admin-outline">Reset</a>
  </form>
</div>

<!-- Properties Table Card -->
<div class="admin-card">
  <?php if (!empty($properties)): ?>
    <div style="overflow-x: auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Property Details</th>
            <th>Type</th>
            <th>Price</th>
            <th>Location</th>
            <th>Status</th>
            <th>Featured</th>
            <th>Published</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($properties as $prop): ?>
            <tr>
              <td>
                <div style="display: flex; align-items: center; gap: 14px;">
                  <img src="<?= asset($prop['cover_image']) ?>" style="width: 54px; height: 40px; object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                  <div>
                    <strong style="display: block; font-size: 0.9375rem;"><?= e($prop['title']) ?></strong>
                    <span style="font-size: 0.75rem; color: var(--admin-muted);">
                      Ref: <?= e($prop['reference_no']) ?> &bull; <?= (int)$prop['bedrooms'] ?> Beds &bull; <?= (int)$prop['bathrooms'] ?> Baths
                    </span>
                  </div>
                </div>
              </td>
              <td><span style="text-transform: capitalize;"><?= e($prop['property_type']) ?></span> (<?= ucfirst($prop['category']) ?>)</td>
              <td><strong><?= format_price($prop['price'], $prop['currency'], $prop['rental_frequency']) ?></strong></td>
              <td><?= e($prop['suburb']) ?>, <?= e($prop['city']) ?></td>
              <td>
                <span class="admin-badge badge-published" style="text-transform: capitalize;">
                  <?= str_replace('_', ' ', $prop['status']) ?>
                </span>
              </td>
              <td>
                <form action="" method="POST" style="display: inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_featured">
                  <input type="hidden" name="property_id" value="<?= $prop['id'] ?>">
                  <button type="submit" style="background: none; border: none; cursor: pointer;" title="Click to toggle featured">
                    <span class="admin-badge <?= $prop['is_featured'] ? 'badge-new' : 'badge-draft' ?>">
                      <?= $prop['is_featured'] ? 'Featured' : 'Standard' ?>
                    </span>
                  </button>
                </form>
              </td>
              <td>
                <form action="" method="POST" style="display: inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="toggle_publish">
                  <input type="hidden" name="property_id" value="<?= $prop['id'] ?>">
                  <button type="submit" style="background: none; border: none; cursor: pointer;" title="Click to toggle publish status">
                    <span class="admin-badge <?= $prop['is_published'] ? 'badge-published' : 'badge-draft' ?>">
                      <?= $prop['is_published'] ? 'Active' : 'Draft' ?>
                    </span>
                  </button>
                </form>
              </td>
              <td>
                <div style="display: flex; gap: 6px; align-items: center;">
                  <a href="<?= url('admin/property-edit.php?id=' . $prop['id']) ?>" class="btn-admin btn-admin-outline" style="padding: 4px 8px; font-size: 0.75rem;">
                    Edit
                  </a>
                  <a href="<?= url('property.php?id=' . $prop['id'] . '&slug=' . urlencode($prop['slug'])) ?>" target="_blank" class="btn-admin btn-admin-outline" style="padding: 4px 8px; font-size: 0.75rem;">
                    View
                  </a>
                  <form action="" method="POST" style="display: inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="property_id" value="<?= $prop['id'] ?>">
                    <button type="submit" class="btn-admin btn-admin-danger" style="padding: 4px 8px; font-size: 0.75rem;" data-confirm="Are you sure you want to permanently delete this property? This action cannot be undone.">
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
      No properties found matching your criteria.
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
