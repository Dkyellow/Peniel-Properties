<?php
/**
 * Peniel Properties - Homepage
 */

require_once __DIR__ . '/app/helpers/helpers.php';

$pageTitle = 'Peniel Properties | Find a Place to Call Yours';
$pageDescription = 'Discover luxury and residential properties for sale and rent across Zimbabwe. Exclusive homes in Borrowdale, Highlands, Avondale and beyond.';
$activePage = 'home';

$db = get_db_connection();

$featuredProperties = [];
$saleProperties = [];
$rentProperties = [];
$propertyTypeCounts = [];
$dbConnected = ($db !== null);

if ($dbConnected) {
    try {
        // Featured Listings
        $stmt = $db->query("SELECT * FROM properties WHERE is_published = 1 AND is_featured = 1 ORDER BY created_at DESC LIMIT 3");
        $featuredProperties = $stmt->fetchAll();

        // For Sale Listings
        $stmt = $db->query("SELECT * FROM properties WHERE is_published = 1 AND category = 'sale' ORDER BY created_at DESC LIMIT 3");
        $saleProperties = $stmt->fetchAll();

        // For Rent Listings
        $stmt = $db->query("SELECT * FROM properties WHERE is_published = 1 AND category = 'rent' ORDER BY created_at DESC LIMIT 3");
        $rentProperties = $stmt->fetchAll();

        // Property Type counts
        $stmt = $db->query("SELECT property_type, COUNT(*) as cnt FROM properties WHERE is_published = 1 GROUP BY property_type");
        while ($row = $stmt->fetch()) {
            $propertyTypeCounts[$row['property_type']] = (int)$row['cnt'];
        }
    } catch (PDOException $e) {
        error_log("DB Query Error: " . $e->getMessage());
    }
}

require_once __DIR__ . '/app/includes/header.php';
?>

<!-- DB Setup Alert if user hasn't imported DB yet in phpMyAdmin -->
<?php if (!$dbConnected): ?>
<div style="background: #FFF3CD; border-bottom: 2px solid #FFEEBA; padding: 18px 0; color: #856404;">
  <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
      <strong>Database Setup Note:</strong> MySQL connection not yet established. Import <code>database/database.sql</code> into phpMyAdmin and check <code>app/config/config.php</code>.
    </div>
    <a href="<?= url('admin/login.php') ?>" class="btn btn-primary btn-sm">Staff Setup Guide</a>
  </div>
</div>
<?php endif; ?>

<!-- Hero Section -->
<?php
$heroSlides = [
    asset('assets/images/hero.png'),
    asset('assets/images/hero (1).png'),
    asset('assets/images/hero (2).png'),
    asset('assets/images/hero (3).png'),
];
?>
<section class="hero-section">
  <div class="hero-slides" aria-hidden="true">
    <?php foreach ($heroSlides as $heroIndex => $heroSrc): ?>
      <div class="hero-slide" style="background-image: url('<?= e($heroSrc) ?>'); animation-delay: <?= $heroIndex * 8 ?>s;"></div>
    <?php endforeach; ?>
  </div>
  <div class="hero-overlay"></div>
  <div class="container">
    <div class="hero-content">
      
      <h1 class="hero-title">Find a Place to Call Yours.</h1>
      <p class="hero-subtitle">
        Discover exceptional properties for sale and rent across Zimbabwe. Curated architectural homes, modern townhouses, and prime commercial investments.
      </p>
      <div class="hero-actions">
        <a href="<?= url('properties.php') ?>" class="btn btn-lime">Explore Properties</a>
        <a href="<?= url('contact.php') ?>" class="btn btn-outline-white">Speak to Our Team</a>
      </div>
    </div>
  </div>
</section>

<!-- Search Container Overlay -->
<div class="container search-container">
  <div class="search-box-card">
    <div class="search-tabs">
      <button type="button" class="search-tab active" data-category="">All Categories</button>
      <button type="button" class="search-tab" data-category="sale">For Sale</button>
      <button type="button" class="search-tab" data-category="rent">For Rent</button>
    </div>

    <form action="<?= url('properties.php') ?>" method="GET" class="search-form-grid">
      <input type="hidden" name="category" id="searchCategoryInput" value="">

      <!-- Location / Suburb -->
      <div class="form-group">
        <label for="searchLocation" class="form-label">Location / Suburb</label>
        <input type="text" id="searchLocation" name="location" class="form-control" placeholder="e.g. Borrowdale, Highlands">
      </div>

      <!-- Property Type -->
      <div class="form-group">
        <label for="searchType" class="form-label">Property Type</label>
        <select id="searchType" name="type" class="form-control">
          <option value="">All Types</option>
          <option value="house">House / Villa</option>
          <option value="apartment">Apartment / Flat</option>
          <option value="townhouse">Townhouse / Cluster</option>
          <option value="commercial">Commercial</option>
          <option value="land">Land / Stand</option>
        </select>
      </div>

      <!-- Bedrooms -->
      <div class="form-group">
        <label for="searchBeds" class="form-label">Bedrooms</label>
        <select id="searchBeds" name="bedrooms" class="form-control">
          <option value="">Any Beds</option>
          <option value="1">1+ Bedrooms</option>
          <option value="2">2+ Bedrooms</option>
          <option value="3">3+ Bedrooms</option>
          <option value="4">4+ Bedrooms</option>
          <option value="5">5+ Bedrooms</option>
        </select>
      </div>

      <!-- Max Price -->
      <div class="form-group">
        <label for="searchMaxPrice" class="form-label">Max Price (USD)</label>
        <input type="number" id="searchMaxPrice" name="max_price" class="form-control" placeholder="Any Price" step="5000">
      </div>

      <!-- Submit Button -->
      <div>
        <button type="submit" class="btn btn-primary" style="width: 100%; height: 48px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          Search
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Featured Properties Section -->
<section class="section">
  <div class="container">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
      <div>
        <span class="section-subtitle">Curated Collection</span>
        <h2 class="section-title">Featured Properties</h2>
        <p class="section-desc">Hand-picked residences representing the peak of design, location, and lifestyle.</p>
      </div>
      <div>
        <a href="<?= url('properties.php') ?>" class="btn btn-outline-navy btn-sm">View All Listings &rarr;</a>
      </div>
    </div>

    <?php if (!empty($featuredProperties)): ?>
      <div class="properties-grid">
        <?php foreach ($featuredProperties as $prop): ?>
          <?php include __DIR__ . '/app/includes/property-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div style="text-align: center; padding: 60px 0; background: var(--color-off-white); border-radius: var(--radius-lg);">
        <p style="color: var(--color-text-muted);">No featured properties currently listed. Please check back soon.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Browse by Property Type -->
<section class="section section-bg-soft">
  <div class="container">
    <div class="section-header text-center">
      <span class="section-subtitle">Property Categories</span>
      <h2 class="section-title">Browse by Property Type</h2>
      <p class="section-desc">Explore tailored opportunities across residential homes, apartments, and commercial ventures.</p>
    </div>

    <div class="type-grid">
      <!-- House -->
      <a href="<?= url('properties.php?type=house') ?>" class="type-card">
        <div class="type-icon-wrapper">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
          </svg>
        </div>
        <div>
          <h3 class="type-name">Houses & Villas</h3>
          <span class="type-count"><?= $propertyTypeCounts['house'] ?? 0 ?> Listings</span>
        </div>
      </a>

      <!-- Apartment -->
      <a href="<?= url('properties.php?type=apartment') ?>" class="type-card">
        <div class="type-icon-wrapper">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
            <line x1="9" y1="22" x2="9" y2="2"></line>
            <line x1="15" y1="22" x2="15" y2="2"></line>
            <line x1="4" y1="12" x2="20" y2="12"></line>
            <line x1="4" y1="7" x2="20" y2="7"></line>
            <line x1="4" y1="17" x2="20" y2="17"></line>
          </svg>
        </div>
        <div>
          <h3 class="type-name">Apartments</h3>
          <span class="type-count"><?= $propertyTypeCounts['apartment'] ?? 0 ?> Listings</span>
        </div>
      </a>

      <!-- Townhouse -->
      <a href="<?= url('properties.php?type=townhouse') ?>" class="type-card">
        <div class="type-icon-wrapper">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2 10v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V10L12 2 2 10Z"></path>
            <path d="M9 22v-6h6v6"></path>
          </svg>
        </div>
        <div>
          <h3 class="type-name">Townhouses</h3>
          <span class="type-count"><?= $propertyTypeCounts['townhouse'] ?? 0 ?> Listings</span>
        </div>
      </a>

      <!-- Commercial / Land -->
      <a href="<?= url('properties.php?type=commercial') ?>" class="type-card">
        <div class="type-icon-wrapper">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
          </svg>
        </div>
        <div>
          <h3 class="type-name">Commercial</h3>
          <span class="type-count"><?= $propertyTypeCounts['commercial'] ?? 0 ?> Listings</span>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- Properties for Sale Highlights -->
<?php if (!empty($saleProperties)): ?>
<section class="section">
  <div class="container">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
      <div>
        <span class="section-subtitle">Ownership Opportunities</span>
        <h2 class="section-title">Properties For Sale</h2>
        <p class="section-desc">Exclusive freehold residential properties and prime investment parcels.</p>
      </div>
      <div>
        <a href="<?= url('properties.php?category=sale') ?>" class="btn btn-outline-navy btn-sm">Browse All For Sale &rarr;</a>
      </div>
    </div>

    <div class="properties-grid">
      <?php foreach ($saleProperties as $prop): ?>
        <?php include __DIR__ . '/app/includes/property-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Properties for Rent Highlights -->
<?php if (!empty($rentProperties)): ?>
<section class="section section-bg-soft">
  <div class="container">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
      <div>
        <span class="section-subtitle">Executive Leases</span>
        <h2 class="section-title">Properties For Rent</h2>
        <p class="section-desc">Turnkey executive residences and diplomatic rentals across premier suburbs.</p>
      </div>
      <div>
        <a href="<?= url('properties.php?category=rent') ?>" class="btn btn-outline-navy btn-sm">Browse All Rentals &rarr;</a>
      </div>
    </div>

    <div class="properties-grid">
      <?php foreach ($rentProperties as $prop): ?>
        <?php include __DIR__ . '/app/includes/property-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Why Choose Peniel Properties -->
<section class="section">
  <div class="container">
    <div class="section-header text-center">
      <span class="section-subtitle">The Peniel Standard</span>
      <h2 class="section-title">Why Peniel Properties</h2>
      <p class="section-desc">We deliver a disciplined, discreet, and refined real estate advisory experience tailored to the Zimbabwean property market.</p>
    </div>

    <div class="features-grid">
      <div class="feature-box">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
          </svg>
        </div>
        <h3 class="feature-title">Verified Title & Integrity</h3>
        <p class="feature-desc">Every listing undergoes comprehensive property verification, ensuring unambiguous ownership, clear documentation, and complete transaction peace of mind.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="m4.93 4.93 4.24 4.24"></path>
            <path d="m14.83 9.17 4.24-4.24"></path>
            <path d="m14.83 14.83 4.24 4.24"></path>
            <path d="m9.17 14.83-4.24 4.24"></path>
            <circle cx="12" cy="12" r="4"></circle>
          </svg>
        </div>
        <h3 class="feature-title">Local Market Intelligence</h3>
        <p class="feature-desc">Deep familiarity with Zimbabwe's high-demand suburbs—from Borrowdale and Highlands to Avondale and Kumalo—enabling precise valuations and smart acquisitions.</p>
      </div>

      <div class="feature-box">
        <div class="feature-icon-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </div>
        <h3 class="feature-title">Discreet Private Advisory</h3>
        <p class="feature-desc">Whether representing institutional investors, diaspora buyers, or private individuals, we uphold the highest confidentiality and personalized client service.</p>
      </div>
    </div>
  </div>
</section>

<!-- Call to Action Banner -->
<section class="section" style="padding-top: 0;">
  <div class="container">
    <div class="cta-banner">
      <div class="cta-banner-content">
        <h2>Are You Planning to Sell or Let a Property?</h2>
        <p>Partner with Peniel Properties for strategic marketing, qualified buyer introductions, and dedicated management across Zimbabwe.</p>
      </div>
      <div class="cta-banner-actions">
        <a href="<?= url('contact.php') ?>" class="btn btn-lime">Schedule a Consultation</a>
        <a href="<?= url('properties.php') ?>" class="btn btn-outline-white">Browse Portfolio</a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/app/includes/footer.php'; ?>
