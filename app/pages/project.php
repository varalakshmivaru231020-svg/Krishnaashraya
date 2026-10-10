<?php
declare(strict_types=1);

$project = null;
foreach (projects_list() as $p) {
    if ($p['slug'] === ($_GET['slug'] ?? '')) {
        $project = $p;
        break;
    }
}
if (!$project) {
    not_found();
}

render('project', [
    'title'       => $project['title'],
    'description' => mb_strimwidth((string) $project['summary'], 0, 160, '…'),
    'project'     => $project,
    'others'      => array_values(array_filter(projects_list(), fn($p) => (int) $p['id'] !== (int) $project['id'])),
]);
