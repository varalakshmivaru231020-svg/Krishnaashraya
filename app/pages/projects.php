<?php
declare(strict_types=1);

$all = projects_list();

render('projects', [
    'title'     => 'Our Projects',
    'ongoing'   => array_values(array_filter($all, fn($p) => $p['status'] === 'ongoing')),
    'completed' => array_values(array_filter($all, fn($p) => $p['status'] === 'completed')),
]);
