<?php
declare(strict_types=1);

admin_render('projects', [
    'title'    => 'Projects',
    'projects' => db_all(
        "SELECT p.*, COALESCE((SELECT SUM(amount_paise) FROM donations d WHERE d.project_id = p.id AND d.status = 'paid'), 0) AS raised_paise
           FROM projects p ORDER BY p.status = 'completed', p.sort_order, p.id"
    ),
]);
