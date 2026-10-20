<?php
/**
 * Admin - Edit Existing Property Listing
 */

$pageTitle = 'Edit Property';
$activeNav = 'properties';

require_once __DIR__ . '/includes/header.php';

$propertyId = (int)($_GET['id'] ?? 0);
$property = null;
$galleryImages = [];

if ($db && $propertyId > 0) {
    $stmt = $db->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->execute([$propertyId]);
    $property = $stmt->fetch();

    if ($property) {
        $imgStmt = $db->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY display_order ASC, id ASC");
        $imgStmt->execute([$propertyId]);
        $galleryImages = $imgStmt->fetchAll();
    }
}

if (!$property) {
    set_flash('danger', 'Property not found.');
    header('Location: ' . url('admin/properties.php'));
    exit;
}

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

// Current property amenities array
$currentAmenities = array_map('trim', explode(',', $property['amenities'] ?? ''));

// Handle Delete Single Gallery Image via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_gallery_image_id'])) {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $imgId = (int)$_POST['delete_gallery_image_id'];
        $imgRow = $db->query("SELECT image_path FROM property_images WHERE id = $imgId AND property_id = $propertyId")->fetch();
        if ($imgRow) {
            $db->prepare("DELETE FROM property_images WHERE id = ?")->execute([$imgId]);
            if (str_starts_with($imgRow['image_path'], 'uploads/')) {
                @unlink(ROOT_PATH . DIRECTORY_SEPARATOR . $imgRow['image_path']);
            }
            set_flash('success', 'Gallery photograph deleted.');
            header('Location: ' . url('admin/property-edit.php?id=' . $propertyId));
            exit;
        }
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_property'])) {
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

        if (empty($title) || empty($suburb) || $price <= 0 || empty($description)) {
            $error = 'Please fill in all mandatory fields (Title, Suburb, Price, and Description).';
        } else {
            try {
                $coverImagePath = $property['cover_image'];

                // Handle replacement cover image if uploaded
                if (!empty($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    $newCover = handle_image_upload($_FILES['cover_image']);
                    if (str_starts_with($coverImagePath, 'uploads/')) {
                        @unlink(ROOT_PATH . DIRECTORY_SEPARATOR . $coverImagePath);
                    }
                    $coverImagePath = $newCover;
                }

                // Update Property Record
                $stmt = $db->prepare("
                    UPDATE properties SET 
                        title = ?, description = ?, category = ?, property_type = ?,
                        price = ?, currency = ?, rental_frequency = ?, province = ?, city = ?, suburb = ?, address = ?,
                        bedrooms = ?, bathrooms = ?, garages = ?, floor_area_sqm = ?, land_area_sqm = ?,
                        amenities = ?, cover_image = ?, status = ?, is_featured = ?, is_published = ?
                    WHERE id = ?
                ");

                $stmt->execute([
                    $title, $description, $category, $propertyType,
                    $price, $currency, $rentalFrequency, $province, $city, $suburb, $address,
                    $bedrooms, $bathrooms, $garages, $floorArea, $landArea,
                    $amenitiesStr, $coverImagePath, $status, $isFeatured, $isPublished,
                    $propertyId
                ]);

                // Upload additional gallery photos if provided
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
                                $imgStmt->execute([$propertyId, $galleryPath, count($galleryImages) + $i + 1]);
                            } catch (Exception $e) {}
                        }
                    }
                }

                set_flash('success', "Property {$property['reference_no']} updated successfully!");
                header('Location: ' . url('admin/properties.php'));
                exit;

            } catch (Exception $e) {
                $error = 'Error updating property: ' . $e->getMessage();
            }
        }
    }
}
?>

<div style="max-width: 960px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
      <h2 style="font-size: 1.5rem; color: var(--admin-navy);">Edit Property: <?= e($property['reference_no']) ?></h2>
      <span style="font-size: 0.875rem; color: var(--admin-muted);"><?= e($property['title']) ?></span>
    </div>
    <div style="display: flex; gap: 8px;">
      <a href="<?= url('property.php?id=' . $property['id'] . '&slug=' . urlencode($property['slug'])) ?>" target="_blank" class="btn-admin btn-admin-outline">View Live &rarr;</a>
      <a href="<?= url('admin/properties.php') ?>" class="btn-admin btn-admin-outline">&larr; Back</a>
    </div>
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
          <input type="text" id="propTitle" name="title" class="admin-input" required value="<?= e($property['title']) ?>">
        </div>

        <div>
          <label class="admin-label" for="propCategory">Transaction Category *</label>
          <select id="propCategory" name="category" class="admin-select">
            <option value="sale" <?= $property['category'] === 'sale' ? 'selected' : '' ?>>For Sale</option>
            <option value="rent" <?= $property['category'] === 'rent' ? 'selected' : '' ?>>For Rent</option>
          </select>
        </div>

        <div>
          <label class="admin-label" for="propType">Property Type *</label>
          <select id="propType" name="property_type" class="admin-select">
            <option value="house" <?= $property['property_type'] === 'house' ? 'selected' : '' ?>>House / Villa</option>
            <option value="apartment" <?= $property['property_type'] === 'apartment' ? 'selected' : '' ?>>Apartment / Penthouse</option>
            <option value="townhouse" <?= $property['property_type'] === 'townhouse' ? 'selected' : '' ?>>Townhouse / Cluster</option>
            <option value="commercial" <?= $property['property_type'] === 'commercial' ? 'selected' : '' ?>>Commercial Building / Office</option>
            <option value="land" <?= $property['property_type'] === 'land' ? 'selected' : '' ?>>Residential / Commercial Stand</option>
          </select>
        </div>

        <div>
          <label class="admin-label" for="propPrice">Price *</label>
          <input type="number" step="0.01" id="propPrice" name="price" class="admin-input" required value="<?= (float)$property['price'] ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <div>
            <label class="admin-label" for="propCurrency">Currency</label>
            <select id="propCurrency" name="currency" class="admin-select">
              <option value="USD" <?= $property['currency'] === 'USD' ? 'selected' : '' ?>>USD ($)</option>
              <option value="ZiG" <?= $property['currency'] === 'ZiG' ? 'selected' : '' ?>>ZiG</option>
            </select>
          </div>
          <div>
            <label class="admin-label" for="propFreq">Rental Frequency</label>
            <select id="propFreq" name="rental_frequency" class="admin-select">
              <option value="month" <?= $property['rental_frequency'] === 'month' ? 'selected' : '' ?>>Per Month</option>
              <option value="year" <?= $property['rental_frequency'] === 'year' ? 'selected' : '' ?>>Per Year</option>
              <option value="day" <?= $property['rental_frequency'] === 'day' ? 'selected' : '' ?>>Per Day</option>
            </select>
          </div>
        </div>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">2. Location & Address</h3>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div>
          <label class="admin-label" for="propCity">City / Town *</label>
          <input type="text" id="propCity" name="city" class="admin-input" required value="<?= e($property['city']) ?>">
        </div>

        <div>
          <label class="admin-label" for="propSuburb">Suburb *</label>
          <input type="text" id="propSuburb" name="suburb" class="admin-input" required value="<?= e($property['suburb']) ?>">
        </div>

        <div class="form-full">
          <label class="admin-label" for="propAddress">Street Address / Landmark</label>
          <input type="text" id="propAddress" name="address" class="admin-input" value="<?= e($property['address']) ?>">
        </div>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">3. Specifications & Measurements</h3>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div>
          <label class="admin-label" for="propBeds">Bedrooms</label>
          <input type="number" id="propBeds" name="bedrooms" class="admin-input" min="0" value="<?= (int)$property['bedrooms'] ?>">
        </div>

        <div>
          <label class="admin-label" for="propBaths">Bathrooms</label>
          <input type="number" id="propBaths" name="bathrooms" class="admin-input" min="0" value="<?= (int)$property['bathrooms'] ?>">
        </div>

        <div>
          <label class="admin-label" for="propGarages">Garages / Parking</label>
          <input type="number" id="propGarages" name="garages" class="admin-input" min="0" value="<?= (int)$property['garages'] ?>">
        </div>

        <div>
          <label class="admin-label" for="propFloor">Floor Area (m²)</label>
          <input type="number" id="propFloor" name="floor_area_sqm" class="admin-input" value="<?= e($property['floor_area_sqm']) ?>">
        </div>

        <div class="form-full">
          <label class="admin-label" for="propLand">Land / Stand Size (m²)</label>
          <input type="number" id="propLand" name="land_area_sqm" class="admin-input" value="<?= e($property['land_area_sqm']) ?>">
        </div>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">4. Amenities & Property Features</h3>

      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px;">
        <?php foreach ($predefinedAmenities as $amenity): ?>
          <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; cursor: pointer;">
            <input type="checkbox" name="amenities[]" value="<?= e($amenity) ?>" <?= in_array($amenity, $currentAmenities) ? 'checked' : '' ?>>
            <?= e($amenity) ?>
          </label>
        <?php endforeach; ?>
      </div>

      <div style="margin-bottom: 24px;">
        <label class="admin-label" for="customAmenities">Additional Features (comma-separated)</label>
        <input type="text" id="customAmenities" name="custom_amenities" class="admin-input" placeholder="e.g. Scullery, Modern Fireplace">
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">5. Full Description</h3>

      <div style="margin-bottom: 24px;">
        <label class="admin-label" for="propDesc">Detailed Description *</label>
        <textarea id="propDesc" name="description" class="admin-textarea" rows="6" required><?= e($property['description']) ?></textarea>
      </div>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">6. Photographs & Media</h3>

      <div class="form-grid" style="margin-bottom: 24px;">
        <div>
          <label class="admin-label">Current Cover Image</label>
          <img src="<?= asset($property['cover_image']) ?>" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: var(--admin-radius); border: 1px solid var(--admin-border); margin-bottom: 10px;">
          <label class="admin-label" for="propCoverInput">Replace Cover (Optional)</label>
          <input type="file" id="propCoverInput" name="cover_image" class="admin-input" accept="image/jpeg,image/png,image/webp">
        </div>

        <div>
          <label class="admin-label" for="propGallery">Upload More Gallery Images</label>
          <input type="file" id="propGallery" name="gallery_images[]" class="admin-input" accept="image/jpeg,image/png,image/webp" multiple>
          <span style="font-size: 0.75rem; color: var(--admin-muted); display: block; margin-top: 6px;">New images will be appended to the current gallery.</span>
        </div>
      </div>

      <!-- Current Gallery Thumbnails -->
      <?php if (!empty($galleryImages)): ?>
        <div style="margin-bottom: 24px;">
          <label class="admin-label">Existing Gallery Photos (<?= count($galleryImages) ?>)</label>
          <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 8px;">
            <?php foreach ($galleryImages as $img): ?>
              <div style="position: relative; border-radius: var(--admin-radius); overflow: hidden; border: 1px solid var(--admin-border);">
                <img src="<?= asset($img['image_path']) ?>" style="width: 100%; height: 110px; object-fit: cover;">
                <button type="submit" form="delImgForm_<?= $img['id'] ?>" class="btn-admin btn-admin-danger" style="position: absolute; top: 4px; right: 4px; padding: 2px 6px; font-size: 0.75rem;" data-confirm="Delete this photo?">
                  &times;
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <h3 style="font-size: 1.125rem; margin-bottom: 16px; border-bottom: 1px solid var(--admin-border); padding-bottom: 8px;">7. Status & Visibility</h3>

      <div class="form-grid" style="margin-bottom: 32px;">
        <div>
          <label class="admin-label" for="propStatus">Availability Status</label>
          <select id="propStatus" name="status" class="admin-select">
            <option value="available" <?= $property['status'] === 'available' ? 'selected' : '' ?>>Available</option>
            <option value="under_offer" <?= $property['status'] === 'under_offer' ? 'selected' : '' ?>>Under Offer</option>
            <option value="sold" <?= $property['status'] === 'sold' ? 'selected' : '' ?>>Sold</option>
            <option value="rented" <?= $property['status'] === 'rented' ? 'selected' : '' ?>>Rented</option>
          </select>
        </div>

        <div style="display: flex; gap: 24px; align-items: center; padding-top: 24px;">
          <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.9375rem; cursor: pointer;">
            <input type="checkbox" name="is_featured" value="1" <?= $property['is_featured'] ? 'checked' : '' ?>>
            Feature on Homepage
          </label>

          <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 0.9375rem; cursor: pointer;">
            <input type="checkbox" name="is_published" value="1" <?= $property['is_published'] ? 'checked' : '' ?>>
            Publish Live
          </label>
        </div>
      </div>

      <div style="display: flex; gap: 16px;">
        <button type="submit" name="update_property" value="1" class="btn-admin btn-admin-lime" style="padding: 12px 28px; font-size: 1rem;">
          Save Changes
        </button>
        <a href="<?= url('admin/properties.php') ?>" class="btn-admin btn-admin-outline" style="padding: 12px 20px;">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>

<!-- Hidden individual forms for gallery photo deletion -->
<?php foreach ($galleryImages as $img): ?>
  <form id="delImgForm_<?= $img['id'] ?>" action="" method="POST" style="display: none;">
    <?= csrf_field() ?>
    <input type="hidden" name="delete_gallery_image_id" value="<?= $img['id'] ?>">
  </form>
<?php endforeach; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
