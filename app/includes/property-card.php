<?php
/**
 * Standard Reusable Property Card
 * Expects $prop array
 */
$propCover = !empty($prop['cover_image']) ? asset($prop['cover_image']) : asset('assets/images/hero.png');
$propUrl = url('property.php?id=' . $prop['id'] . '&slug=' . urlencode($prop['slug']));
$propPrice = format_price($prop['price'], $prop['currency'] ?? 'USD', $prop['rental_frequency'] ?? null);
?>
<div class="property-card">
  <div class="property-card-media">
    <a href="<?= $propUrl ?>" aria-label="<?= e($prop['title']) ?>">
      <img src="<?= $propCover ?>" alt="<?= e($prop['title']) ?>" class="property-card-img" loading="lazy">
    </a>
    <div class="property-card-badges">
      <?= render_status_badge($prop['status'], $prop['category']) ?>
      <?php if (!empty($prop['is_featured'])): ?>
        <span class="property-badge badge-featured">Featured</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="property-card-body">
    <div class="property-card-price"><?= e($propPrice) ?></div>
    <h3 class="property-card-title">
      <a href="<?= $propUrl ?>"><?= e($prop['title']) ?></a>
    </h3>
    <div class="property-card-location">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
        <circle cx="12" cy="10" r="3"></circle>
      </svg>
      <span><?= e($prop['suburb']) ?>, <?= e($prop['city']) ?></span>
    </div>

    <div class="property-specs">
      <?php if (!empty($prop['bedrooms'])): ?>
        <div class="spec-item" title="Bedrooms">
          <svg class="spec-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2 4v16M2 8h18a2 2 0 0 1 2 2v10M2 17h20M6 8v9"></path>
          </svg>
          <span><?= (int)$prop['bedrooms'] ?> Beds</span>
        </div>
      <?php endif; ?>

      <?php if (!empty($prop['bathrooms'])): ?>
        <div class="spec-item" title="Bathrooms">
          <svg class="spec-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 12h16a1 1 0 0 1 1 1v3a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4v-3a1 1 0 0 1 1-1Z"></path>
            <path d="M6 12V5a2 2 0 0 1 2-2h3v2"></path>
          </svg>
          <span><?= (int)$prop['bathrooms'] ?> Baths</span>
        </div>
      <?php endif; ?>

      <?php if (!empty($prop['land_area_sqm'])): ?>
        <div class="spec-item" title="Land Area">
          <svg class="spec-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
          </svg>
          <span><?= number_format($prop['land_area_sqm']) ?> m²</span>
        </div>
      <?php elseif (!empty($prop['floor_area_sqm'])): ?>
        <div class="spec-item" title="Floor Area">
          <svg class="spec-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 21h18M3 7v14M21 7v14M6 21V11M18 21V11"></path>
          </svg>
          <span><?= number_format($prop['floor_area_sqm']) ?> m²</span>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="property-card-footer">
    <span style="color: var(--color-text-muted); font-size: 0.75rem;">Ref: <?= e($prop['reference_no']) ?></span>
    <a href="<?= $propUrl ?>" style="display: flex; align-items: center; gap: 4px;">
      View Details &rarr;
    </a>
  </div>
</div>
