<?php
declare(strict_types=1);

// /sitemap.xml — every public page, built from the database so new projects and activities appear automatically.
// Submit https://YOUR-DOMAIN/sitemap.xml in Google Search Console -> Sitemaps.

$origin = site_origin();
$pages = [
    ['', null, 'weekly', '1.0'],
    ['about', null, 'monthly', '0.8'],
    ['projects', null, 'weekly', '0.9'],
    ['our-activities', null, 'weekly', '0.8'],
    ['donate', null, 'monthly', '0.9'],
    ['contact', null, 'yearly', '0.6'],
];
foreach (db_all('SELECT slug, updated_at FROM projects WHERE is_active = 1 ORDER BY sort_order, id') as $p) {
    $pages[] = ['projects/' . $p['slug'], $p['updated_at'], 'monthly', '0.7'];
}
foreach (db_all('SELECT slug, updated_at FROM activity_categories WHERE is_active = 1 ORDER BY sort_order, id') as $c) {
    $pages[] = [$c['slug'], $c['updated_at'], 'monthly', '0.6'];
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($pages as [$path, $updated, $freq, $priority]) {
    echo "  <url>\n";
    echo '    <loc>', htmlspecialchars($origin . url($path), ENT_XML1), "</loc>\n";
    if ($updated) {
        echo '    <lastmod>', date('Y-m-d', strtotime($updated)), "</lastmod>\n";
    }
    echo "    <changefreq>$freq</changefreq>\n";
    echo "    <priority>$priority</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>', "\n";
