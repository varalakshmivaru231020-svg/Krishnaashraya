<?php
declare(strict_types=1);

/** Shared by the enquiries list and its CSV export. Returns [whereSql, params, filters]. */
function enquiry_filters(): array
{
    $f = ['status' => input('status'), 'type' => input('type'), 'q' => input('q')];
    $where = ['1 = 1'];
    $params = [];
    if (in_array($f['status'], ['new', 'read', 'replied'], true)) {
        $where[] = 'status = ?';
        $params[] = $f['status'];
    }
    if (array_key_exists($f['type'], enquiry_types())) {
        $where[] = 'type = ?';
        $params[] = $f['type'];
    }
    if ($f['q'] !== '') {
        $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ? OR message LIKE ?)';
        $like = '%' . $f['q'] . '%';
        array_push($params, $like, $like, $like, $like);
    }
    return [implode(' AND ', $where), $params, $f];
}
