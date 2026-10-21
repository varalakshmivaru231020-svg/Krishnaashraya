<?php
declare(strict_types=1);

// A video slide shows its video once the server has finished shrinking it; until then its cover photo stands in.
$slides = [];
foreach (db_all('SELECT * FROM home_slides WHERE is_active = 1 ORDER BY sort_order, id') as $s) {
    if ($s['media_type'] === 'video' && ($s['video_status'] ?? 'ready') !== 'ready') {
        if (!$s['image']) {
            continue;
        }
        $s['media_type'] = 'image';
    }
    $slides[] = $s;
}

render('home', [
    'slides'       => $slides,
    'impact'       => db_all('SELECT * FROM impact_stats WHERE is_active = 1 ORDER BY sort_order, id'),
    'projects'     => projects_list('ongoing', 3),
    'testimonials' => db_all('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order, id LIMIT 6'),
    'categories'   => db_all('SELECT * FROM activity_categories WHERE is_active = 1 ORDER BY sort_order, id'),
]);
