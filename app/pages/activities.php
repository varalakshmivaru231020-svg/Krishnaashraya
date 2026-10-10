<?php
declare(strict_types=1);

render('activities', [
    'title'      => 'Our Activities',
    'categories' => db_all(
        'SELECT c.*, (SELECT COUNT(*) FROM activity_photos p WHERE p.category_id = c.id) AS photo_count
           FROM activity_categories c WHERE c.is_active = 1 ORDER BY c.sort_order, c.id'
    ),
]);
