<?php
/** @var array $errors @var array $rzp @var array $cf */
$local = is_local_site();
$liveOrigin = $local ? 'https://www.krishnashraya.in' : site_origin();
$hasRzpSecret = setting('rzp_key_secret') !== '';
$hasRzpWebhook = setting('rzp_webhook_secret') !== '';
$hasCfSecret = cashfree_secret_key() !== '';

// Shown inside each gateway card: what a webhook is, in plain words.
$webhookIntro = function (string $gateway) use ($local, $liveOrigin) {
    ?>
    <div class="webhook-explain">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <strong>What is a webhook?</strong>
            When a donor pays, <?= e($gateway) ?> also sends a short message <em>directly to this website</em> saying “payment received”.
            That way the donation is marked <strong>Paid</strong> even if the donor's internet drops or they close the page before the thank-you page appears.
            Without it, those few donations would show as “Not paid” until you check them. It is optional, but recommended.
            <?php if ($local): ?>
                <div class="webhook-local"><i class="bi bi-laptop"></i> You are running the site on your computer, which <?= e($gateway) ?> cannot reach.
                    Set this up after the site is live, using your real web address (e.g. <code><?= e($liveOrigin) ?></code>).</div>
            <?php endif; ?>
        </div>
    </div>
    <?php
};
?>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<div class="pay-intro">
    <i class="bi bi-shield-check"></i>
    <div>
        Choose <strong>one</strong> payment gateway for the Donate page. Switching one on switches the other off.
        Start with <strong>Test Mode</strong> to try donations without real money, then turn Test Mode off and use your live keys.
    </div>
</div>

<form method="post" autocomplete="off" class="pay-settings">
    <?= csrf_field() ?>

    <!-- ===================================================== Razorpay -->
    <div class="card mb-4 gateway-card" data-gateway="rzp">
        <div class="card-header pay-head"><i class="bi bi-credit-card-2-front"></i> Razorpay</div>
        <div class="card-body">
            <div class="pay-row">
                <span class="pay-status" data-status></span>
                <div class="form-check form-switch pay-switch ms-auto">
                    <input class="form-check-input" type="checkbox" role="switch" id="rzp_enabled" name="rzp_enabled" data-enable <?= $rzp['enabled'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="rzp_enabled">Enable Razorpay</label>
                </div>
            </div>
            <div class="pay-divider"></div>
            <div class="form-check form-switch pay-switch mb-4">
                <input class="form-check-input" type="checkbox" role="switch" id="rzp_test_mode" name="rzp_test_mode" data-test <?= $rzp['test'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="rzp_test_mode">Test Mode (use test keys)</label>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="rzp_key_id">Key ID</label>
                    <input class="form-control" id="rzp_key_id" name="rzp_key_id" value="<?= e($rzp['key_id']) ?>" spellcheck="false"
                           data-ph-test="rzp_test_xxxxxxxxxx" data-ph-live="rzp_live_xxxxxxxxxx" placeholder="<?= $rzp['test'] ? 'rzp_test_xxxxxxxxxx' : 'rzp_live_xxxxxxxxxx' ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="rzp_key_secret">Key Secret</label>
                    <div class="secret-field">
                        <input type="password" class="form-control" id="rzp_key_secret" name="rzp_key_secret" autocomplete="new-password" spellcheck="false"
                               placeholder="<?= $hasRzpSecret ? '••••••••• (saved — leave empty to keep)' : 'xxxxxxxxxxxxxxxxxxxxxxxx' ?>">
                        <button type="button" class="secret-eye" data-toggle-secret="#rzp_key_secret" aria-label="Show or hide the secret"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
            </div>
            <p class="form-text mt-3 mb-0">
                Find your keys in <a href="https://dashboard.razorpay.com/app/website-app-settings/api-keys" target="_blank" rel="noopener">Razorpay Dashboard → Account &amp; Settings → API Keys</a>.
                Switch the dashboard to Test Mode to get test keys.
            </p>

            <details class="webhook-box"<?= $hasRzpWebhook ? '' : ' open' ?>>
                <summary><i class="bi bi-broadcast"></i> Webhook <span class="text-muted fw-normal">(recommended)</span>
                    <?php if ($hasRzpWebhook): ?><span class="badge-status badge-paid ms-2">Set up</span><?php endif; ?></summary>
                <?php $webhookIntro('Razorpay'); ?>
                <p class="small mb-2">In <strong>Razorpay Dashboard → Accounts &amp; Settings → Webhooks → Add New Webhook</strong>, enter:</p>
                <dl class="detail mb-3">
                    <dt>Webhook URL</dt><dd><code><?= e($liveOrigin . url('donate/webhook')) ?></code></dd>
                    <dt>Secret</dt><dd>Make up any long password (e.g. <code>ks-webhook-7f3a9c21</code>), type it in Razorpay <em>and</em> in the box below</dd>
                    <dt>Active events</dt><dd><code>payment.captured</code>, <code>payment.failed</code>, <code>order.paid</code></dd>
                </dl>
                <label class="form-label" for="rzp_webhook_secret">Webhook Secret</label>
                <div class="secret-field">
                    <input type="password" class="form-control" id="rzp_webhook_secret" name="rzp_webhook_secret" autocomplete="new-password"
                           placeholder="<?= $hasRzpWebhook ? '••••••••• (saved — leave empty to keep)' : 'The same secret you type in Razorpay' ?>">
                    <button type="button" class="secret-eye" data-toggle-secret="#rzp_webhook_secret" aria-label="Show or hide the secret"><i class="bi bi-eye"></i></button>
                </div>
            </details>

            <div class="mt-4">
                <button class="btn btn-outline-primary btn-sm" name="action" value="test_razorpay"><i class="bi bi-plug"></i> Save &amp; test Razorpay keys</button>
            </div>
        </div>
    </div>

    <!-- ===================================================== Cashfree -->
    <div class="card mb-4 gateway-card" data-gateway="cf">
        <div class="card-header pay-head"><i class="bi bi-wallet2"></i> Cashfree</div>
        <div class="card-body">
            <div class="pay-row">
                <span class="pay-status" data-status></span>
                <div class="form-check form-switch pay-switch ms-auto">
                    <input class="form-check-input" type="checkbox" role="switch" id="cf_enabled" name="cf_enabled" data-enable <?= $cf['enabled'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="cf_enabled">Enable Cashfree</label>
                </div>
            </div>
            <div class="pay-divider"></div>
            <div class="form-check form-switch pay-switch mb-4">
                <input class="form-check-input" type="checkbox" role="switch" id="cf_test_mode" name="cf_test_mode" data-test <?= $cf['test'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="cf_test_mode">Test Mode (Sandbox — use test keys)</label>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="cf_app_id">App ID (Client ID)</label>
                    <input class="form-control" id="cf_app_id" name="cf_app_id" value="<?= e($cf['app_id']) ?>" spellcheck="false"
                           data-ph-test="TEST1234567890abcdef" data-ph-live="1234567890abcdef" placeholder="<?= $cf['test'] ? 'TEST1234567890abcdef' : '1234567890abcdef' ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="cf_secret_key">Secret Key (Client Secret)</label>
                    <div class="secret-field">
                        <input type="password" class="form-control" id="cf_secret_key" name="cf_secret_key" autocomplete="new-password" spellcheck="false"
                               data-ph-test="cfsk_ma_test_xxxxxxxxxxxx" data-ph-live="cfsk_ma_prod_xxxxxxxxxxxx"
                               placeholder="<?= $hasCfSecret ? '••••••••• (saved — leave empty to keep)' : ($cf['test'] ? 'cfsk_ma_test_xxxxxxxxxxxx' : 'cfsk_ma_prod_xxxxxxxxxxxx') ?>"
                               <?= $hasCfSecret ? 'data-saved' : '' ?>>
                        <button type="button" class="secret-eye" data-toggle-secret="#cf_secret_key" aria-label="Show or hide the secret"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
            </div>
            <p class="form-text mt-3 mb-0">
                Find your keys in the <a href="https://merchant.cashfree.com/merchants/login" target="_blank" rel="noopener">Cashfree Merchant Dashboard</a> → <strong>Developers → API Keys</strong> (Payment Gateway).
                Switch the dashboard to Test / Sandbox to get test keys.
                Before going live, also add your website address under <strong>Developers → Whitelisting</strong>, or Cashfree will block the payment window.
            </p>

            <details class="webhook-box">
                <summary><i class="bi bi-broadcast"></i> Webhook <span class="text-muted fw-normal">(recommended)</span></summary>
                <?php $webhookIntro('Cashfree'); ?>
                <p class="small mb-2">
                    Once the site is live on <strong>https</strong>, the website already tells Cashfree where to send this message with every donation — nothing to type.
                    To add it in the dashboard as well, go to <strong>Cashfree Dashboard → Developers → Webhooks → Add Webhook Endpoint</strong>:
                </p>
                <dl class="detail mb-0">
                    <dt>Endpoint URL</dt><dd><code><?= e($liveOrigin . url('donate/webhook/cashfree')) ?></code></dd>
                    <dt>Events</dt><dd>Payment success, Payment failed, Payment user dropped (API version <code><?= e(CASHFREE_API_VERSION) ?></code>)</dd>
                    <dt>Secret</dt><dd>Not needed — Cashfree signs these messages with your Secret Key above</dd>
                </dl>
            </details>

            <div class="mt-4">
                <button class="btn btn-outline-primary btn-sm" name="action" value="test_cashfree"><i class="bi bi-plug"></i> Save &amp; test Cashfree keys</button>
            </div>
        </div>
    </div>

    <div class="settings-savebar">
        <button class="btn btn-primary px-4" name="action" value="save"><i class="bi bi-check-lg"></i> Save payment settings</button>
    </div>
</form>

<script>
    document.querySelectorAll('[data-toggle-secret]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.getAttribute('data-toggle-secret'));
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });

    (function () {
        var cards = Array.prototype.slice.call(document.querySelectorAll('.gateway-card'));

        function refresh(card) {
            var on = card.querySelector('[data-enable]').checked;
            var test = card.querySelector('[data-test]').checked;
            var status = card.querySelector('[data-status]');
            status.className = 'pay-status ' + (!on ? 'is-off' : (test ? 'is-test' : 'is-live'));
            status.textContent = !on ? 'Disabled' : (test ? 'Test Mode' : 'Live Mode');
            card.classList.toggle('is-active', on);
            card.querySelectorAll('[data-ph-test]').forEach(function (input) {
                if (input.hasAttribute('data-saved')) return;
                input.placeholder = input.getAttribute(test ? 'data-ph-test' : 'data-ph-live');
            });
        }

        cards.forEach(function (card) {
            var enable = card.querySelector('[data-enable]');
            enable.addEventListener('change', function () {
                // Only one gateway at a time: switching this one on switches the others off.
                if (enable.checked) {
                    cards.forEach(function (other) {
                        if (other !== card) { other.querySelector('[data-enable]').checked = false; refresh(other); }
                    });
                }
                refresh(card);
            });
            card.querySelector('[data-test]').addEventListener('change', function () { refresh(card); });
            refresh(card);
        });
    })();
</script>
