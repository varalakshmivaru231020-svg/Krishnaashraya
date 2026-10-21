<?php
$old = $_SESSION['contact_old'] ?? [];
$errorsList = $_SESSION['contact_errors'] ?? [];
unset($_SESSION['contact_old'], $_SESSION['contact_errors']);
$type = $old['type'] ?? ($_GET['type'] ?? 'general');
?>
<h2 class="h3 mb-4">Send us a Message</h2>
<?php if ($errorsList): ?>
    <div class="alert alert-danger">
        <?php foreach ($errorsList as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
    </div>
<?php endif; ?>
<form method="post" action="<?= e(url('contact')) ?>" id="contactForm">
    <?= csrf_field() ?>
    <div class="hp-field" aria-hidden="true">
        <?php /* Bot trap: hidden from people, filled in by spam bots. Deliberately given a name and label that browser
                 autofill does not recognise (a field called "website" got filled in by Chrome for real visitors). */ ?>
        <label>Do not type here <input type="text" name="ks_hp_trap" value="" tabindex="-1" autocomplete="off" data-lpignore="true" data-1p-ignore></label>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="cf-name" class="form-label">Your Name <span class="req-star" aria-hidden="true">*</span></label>
            <input type="text" name="name" class="form-control" id="cf-name" maxlength="120" value="<?= e($old['name'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label for="cf-email" class="form-label">Email Address <span class="req-star" aria-hidden="true">*</span></label>
            <input type="email" name="email" class="form-control" id="cf-email" maxlength="190" value="<?= e($old['email'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label for="cf-phone" class="form-label">Phone Number <span class="req-star" aria-hidden="true">*</span></label>
            <input type="tel" name="phone" class="form-control" id="cf-phone" maxlength="14" inputmode="numeric" value="<?= e($old['phone'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label for="cf-type" class="form-label">I'm writing about</label>
            <select name="type" id="cf-type" class="form-select">
                <?php foreach (enquiry_types() as $key => $label): ?>
                    <option value="<?= e($key) ?>"<?= $type === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label for="cf-message" class="form-label">Your Message <span class="req-star" aria-hidden="true">*</span></label>
            <textarea class="form-control" id="cf-message" name="message" rows="5" maxlength="3000" required><?= e($old['message'] ?? '') ?></textarea>
        </div>
        <?php if (recaptcha_enabled() && recaptcha_type() === 'v2'): ?>
            <div class="col-12">
                <div class="g-recaptcha" data-sitekey="<?= e(recaptcha_site_key()) ?>" data-callback="contactCaptchaOk" data-expired-callback="contactCaptchaExpired"></div>
            </div>
        <?php endif; ?>
        <div class="col-12">
            <button type="submit" class="btn-primary-gradient w-100 border-0" id="contactSubmit"<?= recaptcha_enabled() && recaptcha_type() === 'v2' ? ' disabled' : '' ?>>Send Message</button>
            <?php if (recaptcha_enabled() && recaptcha_type() === 'v2'): ?>
                <p class="small text-muted mt-2 mb-0 text-center" id="captchaHint">Please tick "I'm not a robot" to send your message.</p>
            <?php elseif (recaptcha_enabled()): ?>
                <p class="recaptcha-note">This form is protected by reCAPTCHA. Google's <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Privacy Policy</a> and <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Terms</a> apply.</p>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php if (recaptcha_enabled() && recaptcha_type() === 'v2'): ?>
    <script>
        function contactCaptchaOk() {
            document.getElementById('contactSubmit').disabled = false;
            var hint = document.getElementById('captchaHint'); if (hint) hint.hidden = true;
        }
        function contactCaptchaExpired() {
            document.getElementById('contactSubmit').disabled = true;
            var hint = document.getElementById('captchaHint'); if (hint) hint.hidden = false;
        }
    </script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php elseif (recaptcha_enabled()): ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= e(rawurlencode(recaptcha_site_key())) ?>"></script>
    <script>
        // reCAPTCHA v3: just before sending, get a fresh token from Google (no checkbox).
        (function () {
            var form = document.getElementById('contactForm'), sending = false;
            form.addEventListener('submit', function (e) {
                if (sending) return;
                e.preventDefault();
                if (!form.checkValidity()) { form.reportValidity(); return; }
                var btn = document.getElementById('contactSubmit');
                btn.disabled = true; btn.textContent = 'Sending…';
                function send(token) {
                    var input = form.querySelector('[name="g-recaptcha-response"]') || form.appendChild(Object.assign(document.createElement('input'), { type: 'hidden', name: 'g-recaptcha-response' }));
                    input.value = token || '';
                    sending = true; form.submit();
                }
                if (!window.grecaptcha) return send('');
                grecaptcha.ready(function () {
                    grecaptcha.execute(<?= json_encode(recaptcha_site_key(), JSON_HEX_TAG) ?>, { action: 'contact' }).then(send, function () { send(''); });
                });
            });
        })();
    </script>
<?php endif; ?>
