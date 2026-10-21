<?php
/** The trust's "Receipt" design (red). @var array $d @var array $pay @var string $paidAt @var string $receiptNo @var float $rupees @var callable $money */
$lines = array_values(array_filter([(string) $d['phone'], (string) $d['email']]));
?>
<style>
    .rc { --red: #ff0000; }
    .rc-logo { width: 190px; height: auto; display: block; }
    .rc-address { margin-top: 26px; padding: 12px 18px; background: var(--red); color: #fff; font-weight: 700; font-size: 13px; line-height: 1.45; text-align: center; }
    .rc-top { display: grid; grid-template-columns: 1fr 1fr; gap: 34px; margin-top: 30px; align-items: start; }
    .rc-label { font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
    .rc-name { margin: 8px 0 6px; font-size: 26px; font-weight: 400; line-height: 1.2; word-break: break-word; }
    .rc-lines { font-size: 13.5px; color: var(--muted); line-height: 1.45; word-break: break-word; }
    .rc-pan { margin-top: 8px; font-size: 14px; font-weight: 700; color: var(--red); }
    .rc-no { padding: 11px 16px; background: var(--red); color: #fff; font-weight: 700; font-size: 15px; word-break: break-all; }
    .rc-meta { padding: 12px 16px; background: #f3f4f6; font-size: 13px; line-height: 1.65; word-break: break-word; }
    .rc-table { width: 100%; margin-top: 34px; border-collapse: collapse; }
    .rc-table th { padding: 12px; background: var(--red); color: #fff; font-size: 12px; font-weight: 700; letter-spacing: .4px; text-align: left; text-transform: uppercase; }
    .rc-table td { padding: 16px 12px 30px; font-size: 13.5px; vertical-align: top; border-bottom: 2px solid var(--ink); }
    .rc-table .c { text-align: center; }
    .rc-table .r { text-align: right; white-space: nowrap; }
    .rc-desc { color: var(--muted); font-size: 12.5px; line-height: 1.5; }
    .rc-title { margin: 30px 0 24px; font-size: 25px; font-weight: 700; text-align: center; }
    .rc-sum { display: flex; justify-content: space-between; gap: 30px; align-items: flex-start; }
    .rc-words { font-size: 21px; font-style: italic; color: var(--red); margin-top: 10px; }
    .rc-totals td { padding: 4px 0 4px 40px; font-size: 14.5px; color: var(--muted); text-align: right; white-space: nowrap; }
    .rc-totals tr:last-child td { padding-top: 10px; font-size: 18px; font-weight: 700; color: var(--ink); }
    .rc-rule { margin: 26px 0; border: 0; border-top: 1.5px solid #e5e7eb; }
    .rc-closing { font-size: 22px; font-weight: 700; font-style: italic; color: var(--red); text-align: center; }
    .rc-legal { margin-top: 30px; padding: 16px 20px; background: #f8fafc; border-left: 5px solid var(--red); font-size: 11.5px; color: var(--muted); line-height: 1.7; }
    .rc-legal b { display: block; color: var(--red); font-size: 12.5px; text-transform: uppercase; letter-spacing: .3px; }
    .rc-legal strong { display: block; margin: 4px 0 6px; color: var(--ink); font-size: 12.5px; }
</style>
<div class="rc">
    <img class="rc-logo" src="<?= e(url('assets/images/sket-logo-web.webp')) ?>" alt="<?= e(receipt_text('receipt_org')) ?>">
    <div class="rc-address"><?= e(receipt_text('receipt_address')) ?></div>

    <div class="rc-top">
        <div>
            <div class="rc-label">Recipient:</div>
            <div class="rc-name"><?= e($d['name']) ?></div>
            <?php if ($lines): ?><div class="rc-lines"><?= implode('<br>', array_map('e', $lines)) ?></div><?php endif; ?>
            <?php if ($d['pan']): ?><div class="rc-pan">PAN: <?= e(strtoupper($d['pan'])) ?></div><?php endif; ?>
        </div>
        <div>
            <div class="rc-no">Receipt No:<?= e($receiptNo) ?></div>
            <div class="rc-meta">
                Transaction Date: <?= e(date('j M Y', strtotime($paidAt))) ?><br>
                Mode: <?= e($pay['mode']) ?><br>
                Transaction ID : <?= e($pay['txn']) ?>
                <?php if ($pay['payment_id'] && $pay['payment_id'] !== $pay['txn']): ?><br>Payment ID : <?= e($pay['payment_id']) ?><?php endif; ?>
            </div>
        </div>
    </div>

    <table class="rc-table">
        <thead><tr><th style="width:24%">Donation</th><th>Description</th><th class="c" style="width:9%">Unit</th><th class="r" style="width:13%">Cost</th><th class="r" style="width:17%">Total</th></tr></thead>
        <tbody>
        <tr>
            <td><strong><?= e(donation_towards($d)) ?></strong></td>
            <td class="rc-desc"><?= $d['message'] ? nl2br(e($d['message'])) : 'Online donation to ' . e(receipt_text('receipt_org')) ?></td>
            <td class="c">1</td>
            <td class="r"><?= e($money($rupees)) ?></td>
            <td class="r"><strong>Rs. <?= e($money($rupees, true)) ?></strong></td>
        </tr>
        </tbody>
    </table>

    <div class="rc-title">Receipt for Payment</div>
    <div class="rc-sum">
        <div>
            <div class="rc-label" style="font-size:11.5px">Receipt for payment (amount in words)</div>
            <div class="rc-words"><?= e(amount_in_words($rupees)) ?></div>
        </div>
        <table class="rc-totals">
            <tr><td>Subtotal</td><td>Rs. <?= e($money($rupees, true)) ?></td></tr>
            <tr><td>Tax (0%)</td><td>Rs. 0.00</td></tr>
            <tr><td>Total</td><td>Rs. <?= e($money($rupees, true)) ?></td></tr>
        </table>
    </div>
    <hr class="rc-rule">
    <div class="rc-closing"><?= e(receipt_text('receipt_closing')) ?></div>

    <div class="rc-legal">
        <b>Statutory Registration Details (Tax Benefits)</b>
        <strong>Trust PAN: <?= e(receipt_text('receipt_pan')) ?> | 12AA Registration: <?= e(receipt_text('receipt_12aa')) ?> | 80G Certification: <?= e(receipt_text('receipt_80g')) ?></strong>
        Registered Address: <?= e(receipt_text('receipt_address')) ?><br>
        This is an electronically generated receipt confirmation. No physical signature is required.<br>
        For verification or support, write to <?= e(receipt_text('receipt_email')) ?>.
    </div>
</div>
