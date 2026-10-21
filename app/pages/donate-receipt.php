<?php
declare(strict_types=1);

// Printable donation receipt in the trust's own formats: the Receipt (default) and the Detailed Statement (?doc=statement).
// Opened from the thank-you page ("Download receipt") and from Admin -> Donations -> a donation -> Receipt.

$d = db_one(
    'SELECT d.*, p.title AS project_title, dp.title AS purpose_title FROM donations d
       LEFT JOIN projects p ON p.id = d.project_id LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id WHERE d.ref = ?',
    [input('ref')]
);
if (!$d) {
    not_found();
}
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: private, no-store');

$paid = $d['status'] === 'paid';
$paidAt = $d['paid_at'] ?: $d['created_at'];
$year = date('Y', strtotime($paidAt));
$receiptNo = $paid ? donation_receipt_no($d) : '';
$seq = $receiptNo !== '' ? (int) substr((string) strrchr($receiptNo, '/'), 1) : 0;
$rupees = (int) $d['amount_paise'] / 100;
$doc = input('doc') === 'statement' ? 'statement' : 'receipt';
$pay = $paid ? donation_payment_details($d) : [];

require ROOT . '/templates/receipt.php';
