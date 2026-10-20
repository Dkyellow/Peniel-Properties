<?php
/**
 * Dynamic XML Sitemap Generator
 */
header('Content-Type: application/xml; charset=utf-8');
require_once __DIR__ . '/app/helpers/helpers.php';

$db = get_db_connection();

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- Static Pages -->
  <url>
    <loc><?= url() ?></loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc><?= url('properties.php') ?></loc>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= url('properties.php?category=sale') ?></loc>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc><?= url('properties.php?category=rent') ?></loc>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc><?= url('about.php') ?></loc>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
  <url>
    <loc><?= url('contact.php') ?></loc>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>

  <!-- Published Properties -->
  <?php if ($db): ?>
    <?php
    try {
        $stmt = $db->query("SELECT id, slug, updated_at FROM properties WHERE is_published = 1 ORDER BY updated_at DESC");
        while ($row = $stmt->fetch()):
            $propUrl = url('property.php?id=' . $row['id'] . '&slug=' . urlencode($row['slug']));
            $lastmod = date('Y-m-d', strtotime($row['updated_at'] ?? 'now'));
    ?>
    <url>
      <loc><?= e($propUrl) ?></loc>
      <lastmod><?= $lastmod ?></lastmod>
      <changefreq>weekly</changefreq>
      <priority>0.8</priority>
    </url>
    <?php
        endwhile;
    } catch (PDOException $e) {
        // Suppress on sitemap
    }
    ?>
  <?php endif; ?>
</urlset>
