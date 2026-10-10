<?php
declare(strict_types=1);

$donation = db_one(
    'SELECT d.*, p.title AS project_title, dp.title AS purpose_title FROM donations d
       LEFT JOIN projects p ON p.id = d.project_id LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id WHERE d.ref = ?',
    [input('ref')]
);
if (!$donation) {
    not_found();
}

// Cashfree donors may land here straight from Cashfree's page; confirm the result with Cashfree first.
if ($donation['status'] !== 'paid' && $donation['gateway'] === 'cashfree') {
    try {
        if (cashfree_sync_donation($donation) !== $donation['status']) {
            $donation = db_one(
                'SELECT d.*, p.title AS project_title, dp.title AS purpose_title FROM donations d
                   LEFT JOIN projects p ON p.id = d.project_id LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id WHERE d.id = ?',
                [$donation['id']]
            );
        }
    } catch (RuntimeException $ex) {
        error_log('Cashfree status check failed: ' . $ex->getMessage());
    }
}

render('donate-thanks', [
    'title'    => $donation['status'] === 'paid' ? 'Thank you for your donation' : 'Donation status',
    'donation' => $donation,
]);
