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
        <label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="cf-name" class="form-label">Your Name *</label>
            <input type="text" name="name" class="form-control" id="cf-name" maxlength="120" value="<?= e($old['name'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label for="cf-email" class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" id="cf-email" maxlength="190" value="<?= e($old['email'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label for="cf-phone" class="form-label">Phone Number *</label>
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
            <label for="cf-message" class="form-label">Your Message *</label>
            <textarea class="form-control" id="cf-message" name="message" rows="5" maxlength="3000" required><?= e($old['message'] ?? '') ?></textarea>
        </div>
        <?php if (recaptcha_enabled()): ?>
            <div class="col-12">
                <div class="g-recaptcha" data-sitekey="<?= e(recaptcha_site_key()) ?>" data-callback="contactCaptchaOk" data-expired-callback="contactCaptchaExpired"></div>
            </div>
        <?php endif; ?>
        <div class="col-12">
            <button type="submit" class="btn-primary-gradient w-100 border-0" id="contactSubmit"<?= recaptcha_enabled() ? ' disabled' : '' ?>>Send Message</button>
            <?php if (recaptcha_enabled()): ?>
                <p class="small text-muted mt-2 mb-0 text-center" id="captchaHint">Please tick "I'm not a robot" to send your message.</p>
            <?php endif; ?>
        </div>
    </div>
</form>
<?php if (recaptcha_enabled()): ?>
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
<?php endif; ?>
