<?php
/**
 * Peniel Properties - Property Catalogue with Dynamic Filters & Sorting
 */

require_once __DIR__ . '/app/helpers/helpers.php';

$db = get_db_connection();

// Filters from GET
$category = trim($_GET['category'] ?? '');
$type = trim($_GET['type'] ?? '');
$location = trim($_GET['location'] ?? '');
$suburb = trim($_GET['suburb'] ?? '');
$city = trim($_GET['city'] ?? '');
$minPrice = !empty($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$bedrooms = !empty($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : null;
$sortBy = trim($_GET['sort'] ?? 'newest');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

$pageTitle = 'Property Catalogue | ' . SITE_NAME;
$activePage = 'properties';

if ($category === 'sale') {
    $pageTitle = 'Properties For Sale in Zimbabwe | ' . SITE_NAME;
    $activePage = 'sale';
} elseif ($category === 'rent') {
    $pageTitle = 'Properties For Rent in Zimbabwe | ' . SITE_NAME;
    $activePage = 'rent';
}

$pageDescription = 'Browse vetted residential and commercial real estate for sale and rent in Zimbabwe. Explore Borrowdale, Highlands, Avondale and prime Harare and Bulawayo locations.';

$properties = [];
$totalResults = 0;
$totalPages = 1;

if ($db) {
    try {
        $where = ["is_published = 1"];
        $params = [];

        if (!empty($category)) {
            $where[] = "category = ?";
            $params[] = $category;
        }

        if (!empty($type)) {
            $where[] = "property_type = ?";
            $params[] = $type;
        }

        if (!empty($suburb)) {
            $where[] = "suburb LIKE ?";
            $params[] = '%' . $suburb . '%';
        }

        if (!empty($city)) {
            $where[] = "city LIKE ?";
            $params[] = '%' . $city . '%';
        }

        if (!empty($location)) {
            $where[] = "(suburb LIKE ? OR city LIKE ? OR title LIKE ? OR description LIKE ?)";
            $locParam = '%' . $location . '%';
            $params[] = $locParam;
            $params[] = $locParam;
            $params[] = $locParam;
            $params[] = $locParam;
        }

        if ($minPrice !== null) {
            $where[] = "price >= ?";
            $params[] = $minPrice;
        }

        if ($maxPrice !== null) {
            $where[] = "price <= ?";
            $params[] = $maxPrice;
        }

        if ($bedrooms !== null) {
            $where[] = "bedrooms >= ?";
            $params[] = $bedrooms;
        }

        $whereClause = implode(' AND ', $where);

        // Count Total
        $countStmt = $db->prepare("SELECT COUNT(*) FROM properties WHERE $whereClause");
        $countStmt->execute($params);
        $totalResults = (int)$countStmt->fetchColumn();
        $totalPages = max(1, ceil($totalResults / $perPage));

        // Sorting
        $orderClause = match ($sortBy) {
            'price_asc'  => 'price ASC',
            'price_desc' => 'price DESC',
            'beds_desc'  => 'bedrooms DESC',
            default      => 'created_at DESC'
        };

        // Query Results
        $query = "SELECT * FROM properties WHERE $whereClause ORDER BY $orderClause LIMIT $perPage OFFSET $offset";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $properties = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Catalogue Query Error: " . $e->getMessage());
    }
}

// Helper to preserve GET parameters
function filter_query(array $overrides = []): string {
    $params = array_merge($_GET, $overrides);
    return http_build_query($params);
}

require_once __DIR__ . '/app/includes/header.php';
?>

<div style="background-color: var(--color-deep-navy); color: var(--color-white); padding: 50px 0 40px;">
  <div class="container">
    <div class="breadcrumbs" style="color: #A0B2C6; margin-bottom: 12px;">
      <a href="<?= url() ?>" style="color: #A0B2C6;">Home</a> &nbsp;/&nbsp; <span>Properties</span>
    </div>
    <h1 style="color: var(--color-white); font-size: 2.75rem; margin-bottom: 8px;">
      <?= $category === 'sale' ? 'Properties For Sale' : ($category === 'rent' ? 'Properties For Rent' : 'All Properties') ?>
    </h1>
    <p style="color: #D3DFE8; max-width: 650px;">
      Explore our portfolio of curated real estate across Harare, Bulawayo, and premier Zimbabwean locations.
    </p>
  </div>
</div>

<section class="section">
  <div class="container">
    <!-- Filter Bar -->
    <div class="catalogue-filter-bar">
      <form action="<?= url('properties.php') ?>" method="GET" class="filter-bar-form">
        <!-- Category -->
        <div class="form-group">
          <label class="form-label" for="filterCategory">Category</label>
          <select id="filterCategory" name="category" class="form-control">
            <option value="">All (Sale & Rent)</option>
            <option value="sale" <?= $category === 'sale' ? 'selected' : '' ?>>For Sale</option>
            <option value="rent" <?= $category === 'rent' ? 'selected' : '' ?>>For Rent</option>
          </select>
        </div>

        <!-- Property Type -->
        <div class="form-group">
          <label class="form-label" for="filterType">Type</label>
          <select id="filterType" name="type" class="form-control">
            <option value="">All Types</option>
            <option value="house" <?= $type === 'house' ? 'selected' : '' ?>>House</option>
            <option value="apartment" <?= $type === 'apartment' ? 'selected' : '' ?>>Apartment</option>
            <option value="townhouse" <?= $type === 'townhouse' ? 'selected' : '' ?>>Townhouse</option>
            <option value="commercial" <?= $type === 'commercial' ? 'selected' : '' ?>>Commercial</option>
            <option value="land" <?= $type === 'land' ? 'selected' : '' ?>>Land</option>
          </select>
        </div>

        <!-- Location Search -->
        <div class="form-group">
          <label class="form-label" for="filterLocation">Location / Suburb</label>
          <input type="text" id="filterLocation" name="location" class="form-control" placeholder="e.g. Borrowdale" value="<?= e($location) ?>">
        </div>

        <!-- Bedrooms -->
        <div class="form-group">
          <label class="form-label" for="filterBeds">Beds</label>
          <select id="filterBeds" name="bedrooms" class="form-control">
            <option value="">Any</option>
            <option value="1" <?= $bedrooms === 1 ? 'selected' : '' ?>>1+</option>
            <option value="2" <?= $bedrooms === 2 ? 'selected' : '' ?>>2+</option>
            <option value="3" <?= $bedrooms === 3 ? 'selected' : '' ?>>3+</option>
            <option value="4" <?= $bedrooms === 4 ? 'selected' : '' ?>>4+</option>
            <option value="5" <?= $bedrooms === 5 ? 'selected' : '' ?>>5+</option>
          </select>
        </div>

        <!-- Max Price -->
        <div class="form-group">
          <label class="form-label" for="filterMaxPrice">Max Price (USD)</label>
          <input type="number" id="filterMaxPrice" name="max_price" class="form-control" placeholder="Any" value="<?= e($maxPrice) ?>">
        </div>

        <!-- Sort By -->
        <div class="form-group">
          <label class="form-label" for="filterSort">Sort By</label>
          <select id="filterSort" name="sort" class="form-control">
            <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Newest First</option>
            <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Price (Low to High)</option>
            <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Price (High to Low)</option>
          </select>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 8px;">
          <button type="submit" class="btn btn-primary" style="height: 48px;">Apply</button>
          <a href="<?= url('properties.php') ?>" class="btn btn-outline-navy" style="height: 48px;" title="Reset Filters">Reset</a>
        </div>
      </form>
    </div>

    <!-- Results Meta -->
    <div class="catalogue-results-meta">
      <div>
        Showing <strong><?= empty($totalResults) ? 0 : ($offset + 1) ?>–<?= min($offset + $perPage, $totalResults) ?></strong> of <strong><?= $totalResults ?></strong> properties
      </div>
    </div>

    <!-- Properties Grid -->
    <?php if (!empty($properties)): ?>
      <div class="properties-grid">
        <?php foreach ($properties as $prop): ?>
          <?php include __DIR__ . '/app/includes/property-card.php'; ?>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Properties Pagination">
          <?php if ($page > 1): ?>
            <a href="?<?= filter_query(['page' => $page - 1]) ?>" class="page-item" aria-label="Previous Page">&laquo;</a>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?<?= filter_query(['page' => $i]) ?>" class="page-item <?= $i === $page ? 'active' : '' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <a href="?<?= filter_query(['page' => $page + 1]) ?>" class="page-item" aria-label="Next Page">&raquo;</a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>

    <?php else: ?>
      <div style="text-align: center; padding: 70px 24px; background-color: var(--color-off-white); border-radius: var(--radius-lg); border: 1px dashed var(--color-border);">
        <h3 style="font-size: 1.5rem; margin-bottom: 8px;">No Properties Match Your Search</h3>
        <p style="color: var(--color-text-muted); margin-bottom: 24px;">Try clearing some filters or searching for another location.</p>
        <a href="<?= url('properties.php') ?>" class="btn btn-primary btn-sm">Clear All Filters</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/app/includes/footer.php'; ?>
