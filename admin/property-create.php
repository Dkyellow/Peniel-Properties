<?php
/**
 * Admin - Create New Property Listing
 */

$pageTitle = 'Add New Property';
$activeNav = 'property-create';

require_once __DIR__ . '/includes/header.php';

$error = '';
$predefinedAmenities = [
    'Prolific Borehole',
    'Solar Power Backup',
    'Swimming Pool',
    '24/7 Security Guarding',
    'Electric Fence & Gate',
    'Walled & Gated',
    'Staff Quarters',
    'Fiber Internet Ready',
    'Air Conditioning',
    'Backup Generator',
    'Tennis Court',
    'Self-Contained Cottage'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Security verification failed (invalid CSRF token).';
    } else {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'sale');
        $propertyType = trim($_POST['property_type'] ?? 'house');
        $price = (float)($_POST['price'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'USD');
        $rentalFrequency = ($category === 'rent') ? trim($_POST['rental_frequency'] ?? 'month') : null;
        $province = trim($_POST['province'] ?? 'Harare');
        $city = trim($_POST['city'] ?? 'Harare');
        $suburb = trim($_POST['suburb'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $bedrooms = (int)($_POST['bedrooms'] ?? 0);
        $bathrooms = (int)($_POST['bathrooms'] ?? 0);
        $garages = (int)($_POST['garages'] ?? 0);
        $floorArea = !empty($_POST['floor_area_sqm']) ? (int)$_POST['floor_area_sqm'] : null;
        $landArea = !empty($_POST['land_area_sqm']) ? (int)$_POST['land_area_sqm'] : null;
        $description = trim($_POST['description'] ?? '');
        $status = trim($_POST['status'] ?? 'available');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        // Amenities processing
        $selectedAmenities = $_POST['amenities'] ?? [];
        $customAmenities = trim($_POST['custom_amenities'] ?? '');
        if (!empty($customAmenities)) {
            $customArr = array_map('trim', explode(',', $customAmenities));
            $selectedAmenities = array_merge($selectedAmenities, $customArr);
        }
        $amenitiesStr = implode(', ', array_unique(array_filter($selectedAmenities)));

        // Validation
        if (empty($title) || empty($suburb) || $price <= 0 || empty($description)) {
            $error = 'Please fill in all mandatory fields (Title, Suburb, Price, and Description).';
        } elseif (empty($_FILES['cover_image']) || $_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please upload a primary cover image for the listing.';
        } else {
            try {
                // Upload Cover Image
                $coverImagePath = handle_image_upload($_FILES['cover_image']);

                // Generate Unique Slug
                $slugBase = slugify($title);
                $slug = $slugBase;
                $slugCount = 1;
                while ($db->query("SELECT COUNT(*) FROM properties WHERE slug = " . $db->quote($slug))->fetchColumn() > 0) {
                    $slug = $slugBase . '-' . (++$slugCount);
                }

                // Reference Number
                $reference = generate_property_reference($db);

                // Insert Property
                $stmt = $db->prepare("
                    INSERT INTO properties (
                        reference_no, slug, title, description, category, property_type,
                        price, currency, rental_frequency, province, city, suburb, address,
                        bedrooms, bathrooms, garages, floor_area_sqm, land_area_sqm,
                        amenities, cover_image, status, is_featured, is_published
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?
                    )
                ");

                $stmt->execute([
                    $reference, $slug, $title, $description, $category, $propertyType,
                    $price, $currency, $rentalFrequency, $province, $city, $suburb, $address,
                    $bedrooms, $bathrooms, $garages, $floorArea, $landArea,
                    $amenitiesStr, $coverImagePath, $status, $isFeatured, $isPublished
                ]);

                $newPropertyId = (int)$db->lastInsertId();

                // Process Additional Gallery Images
                if (!empty($_FILES['gallery_images']['name'][0])) {
                    $totalFiles = count($_FILES['gallery_images']['name']);
                    for ($i = 0; $i < $totalFiles; $i++) {
                        if ($_FILES['gallery_images']['error'][$i] === UPLOAD_ERR_OK) {
                            $singleFile = [
                                'name'     => $_FILES['gallery_images']['name'][$i],
                                'type'     => $_FILES['gallery_images']['type'][$i],
                                'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                                'error'    => $_FILES['gallery_images']['error'][$i],
                                'size'     => $_FILES['gallery_images']['size'][$i]
                            ];
                            try {
                                $galleryPath = handle_image_upload($singleFile);
                                $imgStmt = $db->prepare("INSERT INTO property_images (property_id, image_path, display_order) VALUES (?, ?, ?)");
                                $imgStmt->execute([$newPropertyId, $galleryPath, $i + 1]);
                            } catch (Exception $e) {
                                // Ignore non-fatal gallery upload error
                            }
                        }
                    }
                }

                set_flash('success', "Property listing {$reference} created successfully!");
                header('Location: ' . url('admin/properties.php'));
                exit;

            } catch (Exception $e) {
                $error = 'Error creating property: ' . $e->getMessage();
            }
        }
    }
}
?>

<div style="max-width: 960px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
    <h2 style="font-size: 1.5rem; color: var(--admin-navy);">Create New Property Listing</h2>
    <a href="<?= url('admin/properties.php') ?>" class="btn-admin btn-admin-outline">&larr; Back to Properties</a>
  </div>

  <?php if (!empty($error)): ?>
    <div style="background-color: #FEE2E2; color: #991B1B; padding: 14px 18px; border-radius: var(--admin-radius); margin-bottom: 24px; border: 1px solid #FECACA;">
      <?= e($error) ?>
    </div>
  <?php endif; ?>

  <div class="admin-card" style="padding: 32px;">
    <form action="" method="POST" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">1. Basic Property Details</h3>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div class="form-full">
          <label class="admin-label" for="propTitle">Property Title *</label>
          <input type="text" id="propTitle" name="title" class="admin-input" required placeholder="e.g. Modern Double-Storey Villa in Highlands" value="<?= e($_POST['title'] ?? '') ?>">
        </div>

        <div>
          <label class="admin-label" for="propCategory">Transaction Category *</label>
          <select id="propCategory" name="category" class="admin-select">
            <option value="sale" <?= ($_POST['category'] ?? '') === 'sale' ? 'selected' : '' ?>>For Sale</option>
            <option value="rent" <?= ($_POST['category'] ?? '') === 'rent' ? 'selected' : '' ?>>For Rent</option>
          </select>
        </div>

        <div>
          <label class="admin-label" for="propType">Property Type *</label>
          <select id="propType" name="property_type" class="admin-select">
            <option value="house">House / Villa</option>
            <option value="apartment">Apartment / Penthouse</option>
            <option value="townhouse">Townhouse / Cluster</option>
            <option value="commercial">Commercial Building / Office</option>
            <option value="land">Residential / Commercial Stand</option>
          </select>
        </div>

        <div>
          <label class="admin-label" for="propPrice">Price *</label>
          <input type="number" step="0.01" id="propPrice" name="price" class="admin-input" required placeholder="e.g. 450000" value="<?= e($_POST['price'] ?? '') ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <div>
            <label class="admin-label" for="propCurrency">Currency</label>
            <select id="propCurrency" name="currency" class="admin-select">
              <option value="USD">USD ($)</option>
              <option value="ZiG">ZiG</option>
            </select>
          </div>
          <div>
            <label class="admin-label" for="propFreq">Rental Frequency</label>
            <select id="propFreq" name="rental_frequency" class="admin-select">
              <option value="month">Per Month</option>
              <option value="year">Per Year</option>
              <option value="day">Per Day</option>
            </select>
          </div>
        </div>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">2. Location & Address</h3>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div>
          <label class="admin-label" for="propCity">City / Town *</label>
          <input type="text" id="propCity" name="city" class="admin-input" required value="Harare">
        </div>

        <div>
          <label class="admin-label" for="propSuburb">Suburb *</label>
          <input type="text" id="propSuburb" name="suburb" class="admin-input" required placeholder="e.g. Borrowdale Brooke, Highlands, Avondale" value="<?= e($_POST['suburb'] ?? '') ?>">
        </div>

        <div class="form-full">
          <label class="admin-label" for="propAddress">Street Address / Landmark (Optional)</label>
          <input type="text" id="propAddress" name="address" class="admin-input" placeholder="e.g. 12 Fairway Drive" value="<?= e($_POST['address'] ?? '') ?>">
        </div>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">3. Specifications & Measurements</h3>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div>
          <label class="admin-label" for="propBeds">Bedrooms</label>
          <input type="number" id="propBeds" name="bedrooms" class="admin-input" min="0" value="<?= e($_POST['bedrooms'] ?? '3') ?>">
        </div>

        <div>
          <label class="admin-label" for="propBaths">Bathrooms</label>
          <input type="number" id="propBaths" name="bathrooms" class="admin-input" min="0" value="<?= e($_POST['bathrooms'] ?? '2') ?>">
        </div>

        <div>
          <label class="admin-label" for="propGarages">Garages / Parking</label>
          <input type="number" id="propGarages" name="garages" class="admin-input" min="0" value="<?= e($_POST['garages'] ?? '2') ?>">
        </div>

        <div>
          <label class="admin-label" for="propFloor">Floor Area (m²)</label>
          <input type="number" id="propFloor" name="floor_area_sqm" class="admin-input" placeholder="e.g. 350" value="<?= e($_POST['floor_area_sqm'] ?? '') ?>">
        </div>

        <div class="form-full">
          <label class="admin-label" for="propLand">Land / Stand Size (m²)</label>
          <input type="number" id="propLand" name="land_area_sqm" class="admin-input" placeholder="e.g. 2000" value="<?= e($_POST['land_area_sqm'] ?? '') ?>">
        </div>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">4. Amenities & Property Features</h3>

      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px;">
        <?php foreach ($predefinedAmenities as $amenity): ?>
          <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; cursor: pointer;">
            <input type="checkbox" name="amenities[]" value="<?= e($amenity) ?>">
            <?= e($amenity) ?>
          </label>
        <?php endforeach; ?>
      </div>

      <div style="margin-bottom: 24px;">
        <label class="admin-label" for="customAmenities">Additional Features (comma-separated)</label>
        <input type="text" id="customAmenities" name="custom_amenities" class="admin-input" placeholder="e.g. Scullery, Modern Fireplace, Solar Geysers">
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">5. Full Description</h3>

      <div style="margin-bottom: 24px;">
        <label class="admin-label" for="propDesc">Detailed Description *</label>
        <textarea id="propDesc" name="description" class="admin-textarea" rows="6" required placeholder="Write a thorough, compelling editorial overview of the property, finishing standards, garden, security, and proximity to schools/shops..."><?= e($_POST['description'] ?? '') ?></textarea>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">6. Photographs & Media</h3>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div>
          <label class="admin-label" for="propCoverInput">Primary Cover Photograph * (Max 6MB)</label>
          <input type="file" id="propCoverInput" name="cover_image" class="admin-input" accept="image/jpeg,image/png,image/webp" required>
          <img id="coverImagePreview" src="" alt="Cover Preview" style="display: none; width: 100%; max-height: 200px; object-fit: cover; margin-top: 10px; border-radius: var(--admin-radius); border: 1px solid var(--admin-border);">
        </div>

        <div>
          <label class="admin-label" for="propGallery">Gallery Photographs (Multiple selection allowed)</label>
          <input type="file" id="propGallery" name="gallery_images[]" class="admin-input" accept="image/jpeg,image/png,image/webp" multiple>
          <span style="font-size: 0.75rem; color: var(--admin-muted);">You can select multiple photos holding Shift/Ctrl.</span>
        </div>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">7. Status & Visibility</h3>

      <div class="form-grid" style="margin-bottom: 32px;">
        <div>
          <label class="admin-label" for="propStatus">Availability Status</label>
          <select id="propStatus" name="status" class="admin-select">
            <option value="available">Available</option>
            <option value="under_offer">Under Offer</option>
            <option value="sold">Sold</option>
            <option value="rented">Rented</option>
          </select>
        </div>

        <div style="display: flex; gap: 24px; align-items: center; padding-top: 24px; flex-wrap: wrap;">
          <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.9375rem; cursor: pointer;">
            <input type="checkbox" name="is_featured" value="1">
            Feature on Homepage
          </label>

          <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.9375rem; cursor: pointer;">
            <input type="checkbox" name="is_published" value="1" checked>
            Publish Live Immediately
          </label>
        </div>
      </div>

      <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        <button type="submit" class="btn-admin btn-admin-lime" style="padding: 12px 28px; font-size: 1rem;">
          Save & Publish Listing
        </button>
        <a href="<?= url('admin/properties.php') ?>" class="btn-admin btn-admin-outline" style="padding: 12px 20px;">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
