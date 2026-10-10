<?php
declare(strict_types=1);

render('home', [
    'slides'       => db_all('SELECT * FROM home_slides WHERE is_active = 1 ORDER BY sort_order, id'),
    'impact'       => db_all('SELECT * FROM impact_stats WHERE is_active = 1 ORDER BY sort_order, id'),
    'projects'     => projects_list('ongoing', 3),
    'testimonials' => db_all('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order, id LIMIT 6'),
    'categories'   => db_all('SELECT * FROM activity_categories WHERE is_active = 1 ORDER BY sort_order, id'),
]);
