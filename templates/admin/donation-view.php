<?php
/** @var array $donation @var array $history */
$d = $donation;
$wa = 'https://wa.me/91' . $d['phone'] . '?text=' . rawurlencode(
    'Namaste ' . $d['name'] . ', this is ' . setting('site_name') . '. ' .
    ($d['status'] === 'paid'
        ? 'Thank you so much for your donation of ' . paise_to_inr((int) $d['amount_paise']) . '.'
        : 'Thank you for wanting to support our children. We noticed your donation of ' . paise_to_inr((int) $d['amount_paise']) . ' was not completed - can we help?')
);
$org = setting('site_name', 'Sri Krishnashraya Educational Trust');
// Ready-to-send thank-you email, with the receipt link once the donation is paid.
$emailBody = "Dear {$d['name']},\n\nThank you for your generous donation of " . paise_to_inr((int) $d['amount_paise']) . ' towards ' . donation_towards($d) . " to {$org}. "
    . "Your kindness helps our children with food, a safe home and education.\n\n"
    . ($d['status'] === 'paid' ? "You can download your receipt here:\n" . site_origin() . url('donate/receipt?ref=' . $d['ref']) . "\n\n" : '')
    . "Warm regards,\n{$org}\n" . setting('phone_1');
?>
<a href="<?= e(url('admin/donations')) ?>" class="small d-inline-block mb-3"><i class="bi bi-arrow-left"></i> All donations</a>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <?= e(paise_to_inr((int) $d['amount_paise'])) ?>
                <span class="ms-2"><?php partial('../admin/status-badge', ['status' => $d['status']]); ?></span>
                <?php if ($d['status'] === 'paid'): ?>
                    <a class="btn btn-sm btn-outline-primary ms-auto" href="<?= e(url('donate/receipt?ref=' . $d['ref'])) ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i> Receipt</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <dl class="detail">
                    <dt>Name</dt><dd><?= e($d['name']) ?></dd>
                    <dt>Email</dt><dd><?= $d['email'] ? '<a href="mailto:' . e($d['email']) . '">' . e($d['email']) . '</a>' : '—' ?></dd>
                    <dt>Phone</dt><dd><?= $d['phone'] ? '<a href="' . e(tel_href($d['phone'])) . '">' . e($d['phone']) . '</a>' : '—' ?></dd>
                    <dt>Gender</dt><dd><?= e(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not' => 'Prefer not to say'][$d['gender'] ?? ''] ?? '—') ?></dd>
                    <dt>PAN</dt><dd><?= e($d['pan'] ?: '—') ?></dd>
                    <dt>Towards</dt><dd><?= e(donation_towards($d)) ?><?= $d['project_title'] ? ' <span class="text-muted small">(project)</span>' : '' ?></dd>
                    <dt>Message</dt><dd><?= $d['message'] ? nl2br(e($d['message'])) : '—' ?></dd>
                    <dt>Form submitted</dt><dd><?= e(fmt_date($d['created_at'])) ?></dd>
                    <dt>Paid at</dt><dd><?= e(fmt_date($d['paid_at'])) ?></dd>
                    <dt>Payment gateway</dt><dd><?= e($d['gateway'] ? gateway_label($d['gateway']) : '—') ?></dd>
                    <dt>Payment ID</dt><dd><?= e($d['gateway_payment_id'] ?: '—') ?></dd>
                    <dt>Order ID</dt><dd><?= e($d['gateway_order_id'] ?: '—') ?></dd>
                    <dt>Reference</dt><dd><?= e(strtoupper($d['ref'])) ?></dd>
                    <?php if ($d['failure_reason'] && $d['status'] !== 'paid'): ?>
                        <dt>Why not paid</dt><dd class="text-danger"><?= e($d['failure_reason']) ?></dd>
                    <?php endif; ?>
                </dl>
                <?php if ($d['gateway_payment_id']): ?>
                    <p class="small text-muted mt-3 mb-0">Look this payment up in the <?= e(gateway_label($d['gateway'])) ?> dashboard<?= $d['gateway'] === 'cashfree' ? ' → Payment Gateway → Transactions, using the Order ID' : ' → Transactions → Payments, using the Payment ID' ?>.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header">Get in touch</div>
            <div class="card-body d-grid gap-2">
                <?php if ($d['phone']): ?>
                    <a class="btn btn-success" href="<?= e($wa) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> WhatsApp <?= e($d['phone']) ?></a>
                    <a class="btn btn-outline-primary" href="<?= e(tel_href($d['phone'])) ?>"><i class="bi bi-telephone"></i> Call</a>
                <?php endif; ?>
                <?php if ($d['email']): ?>
                    <?php partial('../admin/email-button', ['email' => $d['email'], 'subject' => 'Thank you from ' . $org, 'body' => $emailBody, 'label' => 'Email ' . $d['email']]); ?>
                <?php endif; ?>
                <?php if (!$d['phone'] && !$d['email']): ?>
                    <p class="text-muted small mb-0">This donor chose not to share a phone number or email address.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Follow-up notes</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="followed_up" id="followed_up" <?= $d['followed_up'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="followed_up">We have contacted this donor<?= $d['status'] === 'paid' ? ' / sent the 80G receipt' : '' ?></label>
                    </div>
                    <textarea name="admin_notes" class="form-control mb-3" rows="4" placeholder="e.g. Called on 5 Oct, will pay by bank transfer. Receipt no. 123 sent."><?= e($d['admin_notes']) ?></textarea>
                    <button class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>

        <?php if ($history): ?>
            <div class="card">
                <div class="card-header">Other donations by this person</div>
                <div class="list-group list-group-flush">
                    <?php foreach ($history as $h): ?>
                        <a class="list-group-item list-group-item-action d-flex align-items-center" href="<?= e(url('admin/donations/view?id=' . $h['id'])) ?>">
                            <span class="small text-muted me-2"><?= e(fmt_date($h['created_at'], 'd M Y')) ?></span>
                            <?php partial('../admin/status-badge', ['status' => $h['status']]); ?>
                            <strong class="ms-auto"><?= e(paise_to_inr((int) $h['amount_paise'])) ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
