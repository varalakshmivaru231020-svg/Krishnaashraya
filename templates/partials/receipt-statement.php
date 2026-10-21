<?php
/** The trust's "Detailed Statement" design (orange). @var array $d @var array $pay @var string $paidAt @var string $year @var string $receiptNo @var int $seq @var float $rupees @var callable $money */
$org = receipt_text('receipt_org');
$day = fn(string $dt): string => date('j F Y', strtotime($dt));
$details = [
    ['Donor Name', $d['name'], 'Donor ID', donation_donor_id($d)],
    ['Statement No.', sprintf('%s/%s/W%05d', rtrim(receipt_text('receipt_statement_prefix'), '/'), $year, $seq), 'Generated On', $day('now')],
    ['Donation Period', $day($paidAt) . ' - ' . $day($paidAt), 'Statement Duration', 'One-time'],
    ['Payment Mode', $pay['mode'], 'Transaction ID', $pay['txn']],
    ['Reference No.', strtoupper($d['ref']), 'Donor PAN', $d['pan'] ? strtoupper($d['pan']) : '—'],
];
?>
<style>
    .st { --orange: #f97316; }
    .st-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; }
    .st-head img { width: 190px; height: auto; display: block; }
    .st-org { margin-top: 10px; font-size: 15px; }
    .st-org small { display: block; font-size: 12px; color: var(--muted); margin-top: 2px; }
    .st-address { margin-top: 14px; padding: 12px 16px; background: var(--orange); color: #fff; font-weight: 700; font-size: 13px; line-height: 1.45; }
    .st-bar { margin-top: 16px; padding: 10px 16px; background: var(--orange); color: #fff; font-weight: 700; font-size: 14px; letter-spacing: .4px; text-align: center; text-transform: uppercase; }
    .st table { width: 100%; border-collapse: collapse; }
    .st-details td { padding: 9px 14px; font-size: 13.5px; font-weight: 700; vertical-align: top; }
    .st-details tr:nth-child(even) td { background: var(--row); }
    .st-details td:nth-child(1), .st-details td:nth-child(3) { width: 20%; white-space: nowrap; }
    .st-details td:nth-child(2), .st-details td:nth-child(4) { width: 30%; word-break: break-word; }
    .st-items th { padding: 10px 8px; font-size: 13px; text-align: left; vertical-align: top; }
    .st-items td { padding: 13px 8px; font-size: 13px; background: var(--row); vertical-align: top; }
    .st-items .num { text-align: right; font-weight: 700; white-space: nowrap; }
    .st-summary { display: flex; justify-content: space-between; align-items: baseline; gap: 16px; margin-top: 14px; padding: 0 8px; }
    .st-summary strong { font-size: 21px; }
    .st-summary .total { font-size: 23px; font-weight: 700; color: var(--orange); white-space: nowrap; }
    .st-words { padding: 0 8px; margin-top: 8px; }
    .st-words small { display: block; font-size: 11px; font-weight: 700; color: var(--muted); letter-spacing: .6px; text-transform: uppercase; }
    .st-words div { margin-top: 6px; font-size: 16px; font-weight: 700; color: var(--orange); }
    .st-thanks { display: flex; justify-content: space-between; align-items: flex-end; gap: 30px; margin-top: 18px; padding: 0 8px; }
    .st-thanks h3 { margin: 0 0 8px; font-size: 14px; }
    .st-thanks p { margin: 0; max-width: 112mm; font-size: 13px; font-weight: 700; line-height: 1.5; color: #374151; }
    .st-sign { flex: 0 0 auto; width: 230px; padding-top: 8px; border-top: 1.5px solid var(--ink); text-align: right; font-size: 13px; }
    .st-legal { padding: 12px 20px; background: var(--row); font-size: 11.5px; color: var(--muted); line-height: 1.7; }
    .st-legal strong { display: block; margin-bottom: 4px; font-size: 12.5px; color: var(--ink); }
</style>
<div class="st">
    <div class="st-head">
        <div>
            <img src="<?= e(url('assets/images/sket-logo-web.webp')) ?>" alt="<?= e($org) ?>">
            <div class="st-org"><?= e($org) ?><small><?= e(receipt_text('receipt_tagline')) ?></small></div>
        </div>
    </div>

    <div class="st-address"><?= e(receipt_text('receipt_address')) ?></div>

    <div class="st-bar">Statement Details</div>
    <table class="st-details">
        <?php foreach ($details as [$l1, $v1, $l2, $v2]): ?>
            <tr><td><?= e($l1) ?></td><td><?= e($v1) ?></td><td><?= e($l2) ?></td><td><?= e($v2) ?></td></tr>
        <?php endforeach; ?>
    </table>

    <div class="st-bar">Donation Contribution Details</div>
    <table class="st-items">
        <thead>
        <tr><th style="width:6%">Sl.No</th><th style="width:14%">Date</th><th style="width:22%">Receipt No.</th><th>Description</th><th style="width:11%">Duration</th><th style="width:11%">Period</th><th class="num" style="width:13%">Amount (Rs.)</th></tr>
        </thead>
        <tbody>
        <tr>
            <td>1</td>
            <td style="white-space:nowrap"><?= e(date('d-M-Y', strtotime($paidAt))) ?></td>
            <td><?= e($receiptNo) ?></td>
            <td><?= e(donation_towards($d)) ?><?= $d['message'] ? '<br><small style="color:#6b7280">' . e($d['message']) . '</small>' : '' ?></td>
            <td><?= e(date('F', strtotime($paidAt))) ?></td>
            <td>One-time</td>
            <td class="num"><?= e($money($rupees)) ?></td>
        </tr>
        </tbody>
    </table>

    <div class="st-bar">Contribution Summary</div>
    <div class="st-summary"><strong>Grand Total</strong><span class="total">Rs. <?= e($money($rupees, true)) ?></span></div>
    <div class="st-words"><small>Amount in words</small><div><?= e(amount_in_words($rupees)) ?></div></div>

    <div class="st-thanks">
        <div>
            <h3>Thank You Message</h3>
            <p><?= nl2br(e(receipt_text('receipt_thanks'))) ?></p>
        </div>
        <div class="st-sign">Authorized Signature</div>
    </div>

    <div class="st-bar">Statutory Registration Details (Tax Benefits)</div>
    <div class="st-legal">
        <strong>Trust PAN: <?= e(receipt_text('receipt_pan')) ?> | 12AA Registration: <?= e(receipt_text('receipt_12aa')) ?> | 80G Certification: <?= e(receipt_text('receipt_80g')) ?></strong>
        Registered Address: <?= e(receipt_text('receipt_address')) ?><br>
        This is an electronically generated statement. No physical signature is required.<br>
        For verification or support, write to <?= e(receipt_text('receipt_email')) ?>.
    </div>

    <div class="page-no">Page 1 of 1</div>
</div>
