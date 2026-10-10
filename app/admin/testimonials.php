<?php
declare(strict_types=1);

admin_render('testimonials', [
    'title'        => 'Testimonials',
    'testimonials' => db_all('SELECT * FROM testimonials ORDER BY sort_order, id'),
]);
