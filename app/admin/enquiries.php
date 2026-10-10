<?php
declare(strict_types=1);

require __DIR__ . '/enquiry-filters.php';

[$where, $params, $filters] = enquiry_filters();
$pg = paginate((int) db_value("SELECT COUNT(*) FROM enquiries WHERE $where", $params), 25);

admin_render('enquiries', [
    'title'     => 'Enquiries',
    'enquiries' => db_all("SELECT * FROM enquiries WHERE $where ORDER BY created_at DESC LIMIT {$pg['limit']} OFFSET {$pg['offset']}", $params),
    'filters'   => $filters,
    'pg'        => $pg,
]);
