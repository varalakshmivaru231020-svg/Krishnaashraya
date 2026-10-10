<?php
declare(strict_types=1);

// Admin -> Payment Settings: Razorpay and Cashfree credentials. Only one gateway may be on at a time.

$errors = [];

if (is_post()) {
    csrf_check();

    // ---------------------------------------------------------- Razorpay
    $rzpOn = isset($_POST['rzp_enabled']);
    $rzpMode = isset($_POST['rzp_test_mode']) ? 'test' : 'live';
    $rzpKeyId = input('rzp_key_id');
    $rzpSecret = trim((string) ($_POST['rzp_key_secret'] ?? ''));
    $rzpWebhook = trim((string) ($_POST['rzp_webhook_secret'] ?? ''));

    // ---------------------------------------------------------- Cashfree
    $cfOn = isset($_POST['cf_enabled']);
    $cfMode = isset($_POST['cf_test_mode']) ? 'test' : 'live';
    $cfAppId = input('cf_app_id');
    $cfSecret = trim((string) ($_POST['cf_secret_key'] ?? ''));

    if ($rzpOn && $cfOn) {
        $errors[] = 'Only one payment gateway can be switched on at a time. Turn off either Razorpay or Cashfree.';
    }

    if ($rzpKeyId !== '' && !str_starts_with($rzpKeyId, "rzp_{$rzpMode}_")) {
        $errors[] = $rzpMode === 'test'
            ? 'Razorpay: Test Mode is on, so the Key ID must be a test key (starting with rzp_test_). Turn Test Mode off to use live keys.'
            : 'Razorpay: Test Mode is off, so the Key ID must be a live key (starting with rzp_live_). Turn Test Mode on to use test keys.';
    }
    if ($rzpOn && ($rzpKeyId === '' || ($rzpSecret === '' && setting('rzp_key_secret') === ''))) {
        $errors[] = 'Razorpay: enter the Key ID and Key Secret before switching it on.';
    }

    // Cashfree secret keys contain "_test_" (sandbox) or "_prod_" (production).
    if ($cfSecret !== '' && $cfMode === 'test' && str_contains($cfSecret, '_prod_')) {
        $errors[] = 'Cashfree: Test Mode is on, but this is a Production secret key. Turn Test Mode off, or use your Sandbox keys.';
    }
    if ($cfSecret !== '' && $cfMode === 'live' && str_contains($cfSecret, '_test_')) {
        $errors[] = 'Cashfree: Test Mode is off, but this is a Sandbox (test) secret key. Turn Test Mode on, or use your Production keys.';
    }
    if ($cfOn && ($cfAppId === '' || ($cfSecret === '' && cashfree_secret_key() === ''))) {
        $errors[] = 'Cashfree: enter the App ID and Secret Key before switching it on.';
    }

    if (!$errors) {
        setting_save('rzp_enabled', $rzpOn ? '1' : '0');
        setting_save('rzp_mode', $rzpMode);
        setting_save('rzp_key_id', $rzpKeyId);
        // Secrets are never shown again; an empty box keeps the saved one (unless the key itself was cleared).
        if ($rzpSecret !== '' || $rzpKeyId === '') {
            setting_save('rzp_key_secret', $rzpSecret);
        }
        if ($rzpWebhook !== '') {
            setting_save('rzp_webhook_secret', $rzpWebhook);
        }

        setting_save('cf_enabled', $cfOn ? '1' : '0');
        setting_save('cf_mode', $cfMode);
        setting_save('cf_app_id', $cfAppId);
        if ($cfSecret !== '' || $cfAppId === '') {
            setting_save('cf_secret_key', $cfSecret);
        }
        settings_all(true);

        $action = input('action');
        if ($action === 'test_razorpay') {
            $err = setting('rzp_key_id') !== '' && setting('rzp_key_secret') !== ''
                ? razorpay_check_keys(setting('rzp_key_id'), setting('rzp_key_secret'))
                : 'Enter the Key ID and Key Secret first.';
            $err === ''
                ? flash('success', 'Connected to Razorpay successfully (' . ucfirst($rzpMode) . ' Mode).')
                : flash('error', 'Razorpay did not accept these keys: ' . $err);
        } elseif ($action === 'test_cashfree') {
            $err = cashfree_app_id() !== '' && cashfree_secret_key() !== ''
                ? cashfree_check_keys(cashfree_app_id(), cashfree_secret_key(), $cfMode)
                : 'Enter the App ID and Secret Key first.';
            $err === ''
                ? flash('success', 'Connected to Cashfree successfully (' . ($cfMode === 'test' ? 'Sandbox / Test' : 'Production / Live') . ' Mode).')
                : flash('error', 'Cashfree did not accept these keys: ' . $err);
        } else {
            flash('success', 'Payment settings saved.' . (active_gateway() ? ' Donations are now taken through ' . gateway_label(active_gateway()) . '.' : ' Online payments are switched off.'));
        }
        redirect('admin/payments');
    }
}

$posted = is_post();
admin_render('payments', [
    'title'  => 'Payment Settings',
    'errors' => $errors,
    'rzp'    => [
        'enabled' => $posted ? isset($_POST['rzp_enabled']) : razorpay_enabled(),
        'test'    => $posted ? isset($_POST['rzp_test_mode']) : razorpay_mode() === 'test',
        'key_id'  => $posted ? input('rzp_key_id') : razorpay_key_id(),
    ],
    'cf'     => [
        'enabled' => $posted ? isset($_POST['cf_enabled']) : cashfree_enabled(),
        'test'    => $posted ? isset($_POST['cf_test_mode']) : cashfree_mode() === 'test',
        'app_id'  => $posted ? input('cf_app_id') : cashfree_app_id(),
    ],
]);
