<?php /** @var array $errors @var bool $on @var string $type @var string $siteKey @var bool $hasSecret */
$host = strtolower(explode(':', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'))[0]);
if (!$on || $siteKey === '') {
    $status = ['off', 'Off', 'The contact form has no spam protection.'];
} elseif ($type === 'v3') {
    $status = $hasSecret
        ? ['full', 'Protected (v3, invisible)', 'Google quietly scores every visitor; messages that look like bots are blocked. There is no checkbox.']
        : ['off', 'Not active yet', 'reCAPTCHA v3 needs the secret key — add it below.'];
} else {
    $status = $hasSecret
        ? ['full', 'Fully protected', 'Visitors must tick the box, and the server double-checks every message with Google.']
        : ['half', 'Checkbox only', 'Visitors must tick the box, but the server cannot double-check it. Add the secret key for full protection.'];
}
?>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<div class="rc-status is-<?= $status[0] ?>">
    <i class="bi bi-<?= $status[0] === 'full' ? 'shield-fill-check' : ($status[0] === 'half' ? 'shield-exclamation' : 'shield-slash') ?>"></i>
    <div><strong><?= e($status[1]) ?></strong><span><?= e($status[2]) ?></span></div>
</div>

<div class="row g-4">
    <div class="col-xl-7">
        <form method="post" autocomplete="off" class="card">
            <?= csrf_field() ?>
            <div class="card-header pay-head"><i class="bi bi-shield-check"></i> Google reCAPTCHA</div>
            <div class="card-body">
                <div class="form-check form-switch pay-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="recaptcha_on" name="recaptcha_on" <?= $on ? 'checked' : '' ?>>
                    <label class="form-check-label" for="recaptcha_on">Protect the contact form with reCAPTCHA</label>
                </div>

                <span class="form-label d-block">Key type <span class="opt">must match the type you chose in Google</span></span>
                <div class="mode-switch mb-4">
                    <label><input type="radio" name="recaptcha_type" value="v2" <?= $type === 'v2' ? 'checked' : '' ?>><span><i class="bi bi-check2-square"></i> v2 — “I'm not a robot” checkbox</span></label>
                    <label><input type="radio" name="recaptcha_type" value="v3" <?= $type === 'v3' ? 'checked' : '' ?>><span><i class="bi bi-incognito"></i> v3 — invisible, no checkbox</span></label>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="recaptcha_site_key">Site key</label>
                    <input class="form-control font-monospace" id="recaptcha_site_key" name="recaptcha_site_key" value="<?= e($siteKey) ?>" placeholder="6Lxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" spellcheck="false">
                    <div class="form-text">The public key. It is placed in the web page.</div>
                </div>
                <div class="mb-2">
                    <label class="form-label" for="recaptcha_secret_key">Secret key</label>
                    <div class="secret-field">
                        <input type="password" class="form-control font-monospace" id="recaptcha_secret_key" name="recaptcha_secret_key" autocomplete="new-password" spellcheck="false"
                               placeholder="<?= $hasSecret ? '••••••••• (saved — leave empty to keep)' : '6Lxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx' ?>">
                        <button type="button" class="secret-eye" data-toggle-secret="#recaptcha_secret_key" aria-label="Show or hide the secret"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="form-text">The private key. It stays on the server and is never shown on the website.</div>
                    <?php if ($hasSecret): ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="clear_secret" id="clear_secret">
                            <label class="form-check-label small" for="clear_secret">Remove the saved secret key</label>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-primary" name="action" value="save"><i class="bi bi-check-lg"></i> Save</button>
                </div>
            </div>
        </form>

        <form method="post" class="card mt-4" id="test">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="test">
            <div class="card-header pay-head"><i class="bi bi-plug"></i> Test your keys</div>
            <div class="card-body">
                <?php if ($siteKey === ''): ?>
                    <p class="text-muted mb-0">Save a site key first.</p>
                <?php elseif ($type === 'v2'): ?>
                    <ol class="rc-test-steps">
                        <li>Tick the box below. <span class="text-muted">If it says <em>“Invalid domain for site key”</em>, add <code><?= e($host) ?></code> to the key's domains in Google. If it says <em>“Invalid key type”</em>, your key is v3 — choose <strong>v3</strong> above.</span></li>
                        <li>Click <strong>Test both keys</strong>. Google checks the tick with your secret key.</li>
                    </ol>
                    <div class="g-recaptcha mb-3" data-sitekey="<?= e($siteKey) ?>" data-callback="rcTicked" data-expired-callback="rcExpired"></div>
                    <button class="btn btn-outline-primary" id="rcTestBtn" disabled><i class="bi bi-plug"></i> Test both keys</button>
                    <?php if (!$hasSecret): ?><div class="form-text mt-2">Add the secret key above to test both keys. Until then, ticking the box only shows the site key works on this address.</div><?php endif; ?>
                    <script>
                        function rcTicked() { document.getElementById('rcTestBtn').disabled = <?= $hasSecret ? 'false' : 'true' ?>; }
                        function rcExpired() { document.getElementById('rcTestBtn').disabled = true; }
                    </script>
                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                <?php else: ?>
                    <p class="small text-muted">v3 has no checkbox. Click the button: your browser gets a check from Google with the site key, and the server verifies it with the secret key.
                        If Google reports a problem with the domain, add <code><?= e($host) ?></code> to the key's domains.</p>
                    <input type="hidden" name="g-recaptcha-response" id="rcToken">
                    <button type="button" class="btn btn-outline-primary" id="rcTestBtn"<?= $hasSecret ? '' : ' disabled' ?>><i class="bi bi-plug"></i> Test both keys</button>
                    <?php if (!$hasSecret): ?><div class="form-text mt-2">Add the secret key above first.</div><?php endif; ?>
                    <script src="https://www.google.com/recaptcha/api.js?render=<?= e(rawurlencode($siteKey)) ?>"></script>
                    <script>
                        document.getElementById('rcTestBtn').addEventListener('click', function () {
                            var btn = this, form = document.getElementById('test');
                            btn.disabled = true; btn.innerHTML = 'Checking…';
                            if (!window.grecaptcha) { form.submit(); return; }
                            grecaptcha.ready(function () {
                                grecaptcha.execute(<?= json_encode($siteKey, JSON_HEX_TAG) ?>, { action: 'admin_test' }).then(function (t) {
                                    document.getElementById('rcToken').value = t; form.submit();
                                }, function () { form.submit(); });
                            });
                        });
                    </script>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="col-xl-5">
        <div class="card">
            <div class="card-header pay-head"><i class="bi bi-list-ol"></i> How to get the keys</div>
            <div class="card-body rc-steps">
                <ol>
                    <li>Open <a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener">google.com/recaptcha/admin</a> and sign in with the trust's Google account.</li>
                    <li><strong>Label:</strong> Krishnashraya website</li>
                    <li><strong>Type:</strong> choose either
                        <strong>Score based (v3)</strong> — invisible, nothing for visitors to do, or
                        <strong>Challenge (v2) → “I'm not a robot” Checkbox</strong>. Then pick the same type on this page.</li>
                    <li><strong>Domains:</strong> add <code>krishnashraya.in</code><?= in_array($host, ['localhost', '127.0.0.1'], true) ? ', and <code>localhost</code> to test on this computer' : ($host !== 'krishnashraya.in' && $host !== 'www.krishnashraya.in' ? ' and <code>' . e($host) . '</code>' : '') ?>.</li>
                    <li>Click <strong>Submit</strong>. If Google asks for a Google Cloud project, accept — normal use is free.</li>
                    <li>Copy the <strong>Site key</strong> and <strong>Secret key</strong> into the boxes on this page and click <strong>Save</strong>.</li>
                    <li>Under <strong>Test your keys</strong>, click <strong>Test both keys</strong> (for v2, tick the box first).</li>
                </ol>
                <p class="small text-muted mb-0">“Invisible” v2 keys are not supported — use v3 or the v2 checkbox.</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-toggle-secret]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.querySelector(btn.getAttribute('data-toggle-secret'));
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    });
</script>
