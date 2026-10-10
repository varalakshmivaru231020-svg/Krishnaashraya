<?php
/** @var array $donation */
$status = $donation['status'];
$firstName = explode(' ', trim($donation['name']))[0];
$amount = paise_to_inr((int) $donation['amount_paise']);
$org = setting('site_name', 'Sri Krishnashraya Educational Trust');
$towards = donation_towards($donation);
$shareText = rawurlencode("I just donated to {$org} to support food, shelter and education for children. You can help too: " . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] . url('donate') : ''));
?>
<section class="thanks-page">
    <div class="container">
        <div class="thanks-card">
            <?php if ($status === 'paid'): ?>
                <div class="thanks-check" aria-hidden="true">
                    <svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="M15 27l7 7 15-16"/></svg>
                </div>
                <p class="thanks-eyebrow">Payment successful</p>
                <h1 class="force-font">Thank you, <?= e($firstName) ?>!</h1>
                <p class="thanks-lead">
                    Your payment of <strong><?= e($amount) ?></strong> towards <strong><?= e($org) ?></strong> has been received.
                    Because of you, a child at Krishnashraya will have more meals, a safe home and the chance to learn.
                </p>
                <blockquote class="thanks-quote">“There is no joy greater than the joy of giving.” — with heartfelt gratitude from all our children and the Krishnashraya family 🙏</blockquote>
            <?php elseif ($status === 'cancelled'): ?>
                <div class="thank-icon wait"><i class="bi bi-x-circle"></i></div>
                <p class="thanks-eyebrow">Payment cancelled</p>
                <h1 class="force-font">No problem, <?= e($firstName) ?></h1>
                <p class="thanks-lead">The payment window was closed before paying, so no money was taken. Whenever you are ready, you can try again in a minute.</p>
            <?php elseif ($status === 'failed'): ?>
                <div class="thank-icon fail"><i class="bi bi-x-lg"></i></div>
                <p class="thanks-eyebrow text-danger">Payment not completed</p>
                <h1 class="force-font">The payment did not go through</h1>
                <p class="thanks-lead">No money was taken from your account.<?= $donation['failure_reason'] ? ' Reason: ' . e($donation['failure_reason']) . '.' : '' ?> You can try again, or donate by bank transfer / UPI.</p>
            <?php else: ?>
                <div class="thank-icon wait"><i class="bi bi-hourglass-split"></i></div>
                <p class="thanks-eyebrow">Almost done</p>
                <h1 class="force-font">We're confirming your payment</h1>
                <p class="thanks-lead">Thank you, <?= e($firstName) ?>. If money was taken from your account, it will be confirmed automatically within a few minutes. Our team has your details and will contact you if anything is needed.</p>
            <?php endif; ?>

            <div class="receipt" id="receipt">
                <div class="receipt-head">
                    <img src="<?= e(url('assets/images/sket-logo-web.webp')) ?>" alt="">
                    <div>
                        <strong><?= e($org) ?></strong>
                        <span>Donation <?= $status === 'paid' ? 'receipt' : 'details' ?></span>
                    </div>
                </div>
                <table>
                    <tr><td>Donor</td><td><?= e($donation['name']) ?></td></tr>
                    <tr><td>Amount</td><td class="receipt-amount"><?= e($amount) ?></td></tr>
                    <tr><td>Towards</td><td><?= e($towards) ?></td></tr>
                    <?php if ($donation['gateway_payment_id']): ?>
                        <tr><td>Payment ID</td><td><?= e($donation['gateway_payment_id']) ?></td></tr>
                    <?php endif; ?>
                    <tr><td>Reference no.</td><td><?= e(strtoupper($donation['ref'])) ?></td></tr>
                    <tr><td>Date</td><td><?= e(fmt_date($donation['paid_at'] ?: $donation['created_at'])) ?></td></tr>
                    <?php if ($donation['pan']): ?>
                        <tr><td>PAN</td><td><?= e($donation['pan']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>

            <?php if ($status === 'paid'): ?>
                <?php if ($donation['email']): ?>
                    <p class="thanks-note"><i class="bi bi-receipt"></i> Your official <strong>80G tax-exemption receipt</strong> will be sent to <strong><?= e($donation['email']) ?></strong> by our team<?= $donation['pan'] ? '' : ' — please reply with your PAN when we contact you' ?>.</p>
                <?php else: ?>
                    <p class="thanks-note"><i class="bi bi-receipt"></i> Keep this page as your confirmation (use <strong>Print / Save receipt</strong>). For an official <strong>80G tax-exemption receipt</strong>, please
                        <a href="<?= e(url('contact?type=donation')) ?>">send us your email and PAN</a> with reference <strong><?= e(strtoupper($donation['ref'])) ?></strong>.</p>
                <?php endif; ?>
                <div class="thanks-actions">
                    <button type="button" class="btn-outline-gradient" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save receipt</button>
                    <a class="btn-outline-gradient" href="https://wa.me/?text=<?= $shareText ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Share with friends</a>
                    <a class="btn-primary-gradient" href="<?= e(url('')) ?>"><i class="bi bi-house"></i> Back to Home</a>
                </div>
            <?php else: ?>
                <div class="thanks-actions">
                    <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient"><i class="bi bi-arrow-repeat"></i> Try Again</a>
                    <a href="<?= e(url('contact?type=donation')) ?>" class="btn-outline-gradient">Contact Us</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php if ($status === 'pending' && $donation['gateway_order_id']): ?>
<script>
    (function () {
        var tries = parseInt(sessionStorage.getItem('thanks-tries') || '0', 10);
        if (tries < 6) { sessionStorage.setItem('thanks-tries', tries + 1); setTimeout(function () { location.reload(); }, 5000); }
    })();
</script>
<?php endif; ?>
