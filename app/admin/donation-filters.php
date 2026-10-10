<?php
declare(strict_types=1);

/** Shared by the donations list and its CSV export. Returns [whereSql, params, filters]. */
function donation_filters(): array
{
    $f = [
        'status'   => input('status'),
        'followed' => input('followed'),
        'q'        => input('q'),
        'from'     => input('from'),
        'to'       => input('to'),
        'project'  => input('project'),
        'purpose'  => input('purpose'),
    ];
    $where = ['1 = 1'];
    $params = [];

    if (in_array($f['status'], ['paid', 'pending', 'failed', 'cancelled'], true)) {
        $where[] = 'd.status = ?';
        $params[] = $f['status'];
    } elseif ($f['status'] === 'unpaid') {
        $where[] = "d.status <> 'paid'";
    }
    if ($f['followed'] === '0' || $f['followed'] === '1') {
        $where[] = 'd.followed_up = ?';
        $params[] = (int) $f['followed'];
    }
    if ($f['q'] !== '') {
        $where[] = '(d.name LIKE ? OR d.email LIKE ? OR d.phone LIKE ? OR d.gateway_payment_id LIKE ? OR d.gateway_order_id LIKE ? OR d.ref LIKE ?)';
        $like = '%' . $f['q'] . '%';
        array_push($params, $like, $like, $like, $like, $like, $like);
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['from'])) {
        $where[] = 'd.created_at >= ?';
        $params[] = $f['from'] . ' 00:00:00';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['to'])) {
        $where[] = 'd.created_at <= ?';
        $params[] = $f['to'] . ' 23:59:59';
    }
    if (ctype_digit($f['purpose'])) {
        $where[] = 'd.purpose_id = ?';
        $params[] = (int) $f['purpose'];
    }
    if ($f['project'] === 'general') {
        $where[] = 'd.project_id IS NULL';
    } elseif (ctype_digit($f['project'])) {
        $where[] = 'd.project_id = ?';
        $params[] = (int) $f['project'];
    }
    return [implode(' AND ', $where), $params, $f];
}
