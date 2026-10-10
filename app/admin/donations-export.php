<?php
declare(strict_types=1);

require __DIR__ . '/donation-filters.php';

[$where, $params] = donation_filters();
$rows = db_all(
    "SELECT d.*, p.title AS project_title, dp.title AS purpose_title FROM donations d LEFT JOIN projects p ON p.id = d.project_id LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id WHERE $where ORDER BY d.created_at DESC",
    $params
);

csv_download(
    'donations-' . date('Y-m-d') . '.csv',
    ['Date', 'Name', 'Email', 'Phone', 'Gender', 'PAN', 'Amount (INR)', 'Towards', 'Status', 'Paid at', 'Gateway', 'Payment ID', 'Order ID', 'Reference', 'Message', 'Followed up', 'Admin notes', 'Failure reason'],
    array_map(fn($d) => [
        $d['created_at'], $d['name'], $d['email'], $d['phone'], $d['gender'] ?? '', $d['pan'] ?? '', number_format($d['amount_paise'] / 100, 2, '.', ''),
        donation_towards($d), $d['status'], $d['paid_at'] ?? '', $d['gateway'] ? gateway_label($d['gateway']) : '', $d['gateway_payment_id'] ?? '', $d['gateway_order_id'] ?? '',
        strtoupper($d['ref']), $d['message'] ?? '', $d['followed_up'] ? 'Yes' : 'No', $d['admin_notes'] ?? '', $d['failure_reason'] ?? '',
    ], $rows)
);
