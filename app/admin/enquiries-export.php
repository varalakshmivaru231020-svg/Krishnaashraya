<?php
declare(strict_types=1);

require __DIR__ . '/enquiry-filters.php';

[$where, $params] = enquiry_filters();
$rows = db_all("SELECT * FROM enquiries WHERE $where ORDER BY created_at DESC", $params);

csv_download(
    'enquiries-' . date('Y-m-d') . '.csv',
    ['Date', 'Name', 'Email', 'Phone', 'Type', 'Message', 'Status', 'Admin notes'],
    array_map(fn($q) => [
        $q['created_at'], $q['name'], $q['email'], $q['phone'] ?? '', enquiry_types()[$q['type']] ?? $q['type'],
        $q['message'], $q['status'], $q['admin_notes'] ?? '',
    ], $rows)
);
