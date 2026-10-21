<?php
declare(strict_types=1);

require __DIR__ . '/donation-filters.php';

[$where, $params, $filters] = donation_filters();

$summary = db_one(
    "SELECT COUNT(*) AS cnt, COALESCE(SUM(CASE WHEN d.status = 'paid' THEN d.amount_paise END), 0) AS paid
       FROM donations d WHERE $where",
    $params
);
$pg = paginate((int) $summary['cnt'], 25);

$donations = db_all(
    "SELECT d.*, p.title AS project_title, dp.title AS purpose_title FROM donations d LEFT JOIN projects p ON p.id = d.project_id LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id
      WHERE $where ORDER BY d.created_at DESC LIMIT {$pg['limit']} OFFSET {$pg['offset']}",
    $params
);

// Opening the list counts as reading the new donations: remember which they were (shown as "New"), then clear the badge.
$newIds = array_map('intval', array_column(db_all("SELECT id FROM donations WHERE seen_by_admin = 0 AND status <> 'pending'"), 'id'));
if ($newIds) {
    db_exec("UPDATE donations SET seen_by_admin = 1 WHERE seen_by_admin = 0 AND status <> 'pending'");
}

admin_render('donations', [
    'newIds'    => $newIds,
    'title'     => 'Donations',
    'donations' => $donations,
    'filters'   => $filters,
    'summary'   => $summary,
    'pg'        => $pg,
    'projects'  => db_all('SELECT id, title FROM projects ORDER BY title'),
    'purposes'  => db_all('SELECT id, title FROM donation_purposes ORDER BY sort_order, id'),
]);
