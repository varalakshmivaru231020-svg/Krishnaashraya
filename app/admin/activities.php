<?php
declare(strict_types=1);

admin_render('activities', [
    'title'      => 'Activities & Gallery',
    'categories' => db_all(
        'SELECT c.*, (SELECT COUNT(*) FROM activity_photos p WHERE p.category_id = c.id) AS photo_count
           FROM activity_categories c ORDER BY c.sort_order, c.id'
    ),
]);
