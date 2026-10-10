<?php
declare(strict_types=1);

/** @var array $category  (loaded by the router) */

$perPage = 24;
$total = (int) db_value('SELECT COUNT(*) FROM activity_photos WHERE category_id = ?', [$category['id']]);
$pages = max(1, (int) ceil($total / $perPage));
$page = min($pages, max(1, (int) ($_GET['page'] ?? 1)));

$photos = db_all(
    'SELECT * FROM activity_photos WHERE category_id = ? ORDER BY sort_order, id LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage),
    [$category['id']]
);

render('activity', [
    'title'       => $category['title'],
    'description' => $category['description'] ?: $category['title'] . ' at Sri Krishnashraya Educational Trust',
    'category'    => $category,
    'photos'      => $photos,
    'page'        => $page,
    'pages'       => $pages,
    'others'      => db_all('SELECT slug, title FROM activity_categories WHERE is_active = 1 AND id <> ? ORDER BY sort_order, id', [$category['id']]),
]);
