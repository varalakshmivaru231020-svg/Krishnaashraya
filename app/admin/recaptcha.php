<?php
declare(strict_types=1);

// Admin -> Spam Protection: Google reCAPTCHA v2 ("I'm not a robot") keys for the contact form.

$errors = [];

if (is_post() && input('action') === 'test') {
    csrf_check();
    $token = (string) ($_POST['g-recaptcha-response'] ?? '');
    if (recaptcha_site_key() === '' || recaptcha_secret_key() === '') {
        flash('error', 'Save both the site key and the secret key first, then tick the box and test.');
    } elseif ($token === '') {
        flash('error', recaptcha_type() === 'v2'
            ? "Tick the \"I'm not a robot\" box first, then click \"Test both keys\"."
            : 'Google did not give this browser a check. Make sure the key type is v3 and this address is in the key\'s domains.');
    } elseif (($err = recaptcha_test_keys(recaptcha_secret_key(), $token)) === '') {
        flash('success', 'Both keys work — Google accepted the check. The contact form is protected.');
    } else {
        flash('error', $err);
    }
    redirect('admin/recaptcha#test');
}

if (is_post()) {
    csrf_check();
    $on = isset($_POST['recaptcha_on']);
    $type = input('recaptcha_type') === 'v3' ? 'v3' : 'v2';
    $siteKey = trim(input('recaptcha_site_key'));
    $secret = trim((string) ($_POST['recaptcha_secret_key'] ?? ''));

    // reCAPTCHA keys are 40 characters of letters, digits, "-" and "_" (they usually start with 6L).
    if ($siteKey !== '' && !preg_match('/^[A-Za-z0-9_\-]{30,60}$/', $siteKey)) {
        $errors[] = 'The site key does not look right. Copy the whole "Site key" from Google (about 40 characters).';
    }
    if ($secret !== '' && !preg_match('/^[A-Za-z0-9_\-]{30,60}$/', $secret)) {
        $errors[] = 'The secret key does not look right. Copy the whole "Secret key" from Google (about 40 characters).';
    }
    if ($siteKey !== '' && $secret !== '' && $siteKey === $secret) {
        $errors[] = 'The site key and the secret key are the same. Google gives you two different keys — paste each in its own box.';
    }
    if ($on && $siteKey === '') {
        $errors[] = 'Enter the site key before switching the checkbox on.';
    }

    if (!$errors) {
        setting_save('recaptcha_on', $on ? '1' : '0');
        setting_save('recaptcha_type', $type);
        setting_save('recaptcha_site_key', $siteKey);
        if (isset($_POST['clear_secret'])) {
            setting_save('recaptcha_secret_key', '');
        } elseif ($secret !== '') {
            setting_save('recaptcha_secret_key', $secret); // an empty box keeps the saved secret
        }
        settings_all(true);

        flash('success', 'Spam protection saved.' . ($on ? ' Now use "Test your keys" to check they work.' : ' reCAPTCHA is switched off.'));
        redirect('admin/recaptcha');
    }
}

admin_render('recaptcha', [
    'title'     => 'Spam Protection',
    'errors'    => $errors,
    'on'        => is_post() ? isset($_POST['recaptcha_on']) : setting('recaptcha_on', '1') === '1',
    'type'      => is_post() ? (input('recaptcha_type') === 'v3' ? 'v3' : 'v2') : recaptcha_type(),
    'siteKey'   => is_post() ? trim(input('recaptcha_site_key')) : recaptcha_site_key(),
    'hasSecret' => recaptcha_secret_key() !== '',
]);
