<?php
declare(strict_types=1);

// Step 1 (AJAX): save the donor's details, then create an order with the active gateway (Razorpay or Cashfree).
// The donation is recorded as "pending" first, so the trust can follow up even if payment is never finished.

if (!is_post()) {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}
if (!csrf_valid()) {
    json_response(['ok' => false, 'error' => 'Your session expired. Please refresh the page and try again.'], 419);
}

$firstName = mb_substr(input('first_name'), 0, 60);
$lastName = mb_substr(input('last_name'), 0, 60);
$name = trim($firstName . ' ' . $lastName);
$gender = in_array(input('gender'), ['male', 'female', 'other', 'prefer_not'], true) ? input('gender') : null;
$email = mb_substr(input('email'), 0, 190);
$phoneInput = input('phone');
$phone = $phoneInput === '' ? null : normalize_phone($phoneInput);
$pan = strtoupper(preg_replace('/\s/', '', input('pan')));
$message = mb_substr(input('message'), 0, 1000);
$amountInput = input('amount');

$min = donation_min_rupees();
$max = (int) config('donation.max_rupees', 1000000);

// Only the first name and the amount are required. Optional fields are checked only when filled in.
// Every problem is reported at once, with the field it belongs to, so the form can highlight them.
$errors = [];
if ($firstName === '') {
    $errors['first_name'] = 'Please enter your first name.';
}
if ($phoneInput !== '' && $phone === null) {
    $errors['phone'] = 'Please enter a valid 10-digit mobile number, or leave it empty.';
}
if ($email !== '' && !valid_email($email)) {
    $errors['email'] = 'Please enter a valid email address, or leave it empty.';
}
if ($pan !== '' && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan)) {
    $errors['pan'] = 'PAN should look like ABCDE1234F, or leave it empty.';
}
// Whole rupees only: no empty, zero, negative, decimal or text amounts.
if (!preg_match('/^\d{1,9}$/', $amountInput) || (int) $amountInput < $min || (int) $amountInput > $max) {
    $errors['amount'] = 'Please choose an amount, or enter a whole number between ' . inr($min) . ' and ' . inr($max) . '.';
}
if ($errors) {
    json_response(['ok' => false, 'error' => reset($errors), 'errors' => $errors], 422);
}
$rupees = (int) $amountInput;

// "Donate towards": a purpose ("purpose:3") or an ongoing project ("project:2"). Anything else = General.
$projectId = null;
$purposeId = null;
if (preg_match('/^(purpose|project):(\d+)$/', input('towards'), $m)) {
    if ($m[1] === 'project') {
        $projectId = (int) db_value("SELECT id FROM projects WHERE id = ? AND is_active = 1 AND status = 'ongoing'", [(int) $m[2]]) ?: null;
    } else {
        $purposeId = (int) db_value('SELECT id FROM donation_purposes WHERE id = ? AND is_active = 1', [(int) $m[2]]) ?: null;
    }
}

if (rate_limited('donate', 10, 600)) {
    json_response(['ok' => false, 'error' => 'Too many attempts. Please wait a few minutes and try again.'], 429);
}

$ref = bin2hex(random_bytes(8));
$amountPaise = $rupees * 100;

db_exec(
    'INSERT INTO donations (ref, name, email, phone, gender, pan, amount_paise, project_id, purpose_id, message, ip)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    [$ref, $name, $email ?: null, $phone, $gender, $pan ?: null, $amountPaise, $projectId, $purposeId, $message ?: null, client_ip()]
);
$donationId = (int) db()->lastInsertId();

$gateway = active_gateway();
if ($gateway === '') {
    db_exec("UPDATE donations SET failure_reason = 'Online payments not configured' WHERE id = ?", [$donationId]);
    json_response([
        'ok'    => false,
        'saved' => true,
        'error' => 'Thank you! We have saved your details and our team will contact you. Online payment is not switched on yet - you can also donate by bank transfer or UPI using the bank details on this page.',
    ], 503);
}
db_exec('UPDATE donations SET gateway = ? WHERE id = ?', [$gateway, $donationId]);

$towardsTitle = $projectId
    ? (string) db_value('SELECT title FROM projects WHERE id = ?', [$projectId])
    : ($purposeId ? (string) db_value('SELECT title FROM donation_purposes WHERE id = ?', [$purposeId]) : '');
$description = $towardsTitle !== '' ? mb_strimwidth('Donation: ' . $towardsTitle, 0, 200, '…') : 'Donation';

try {
    if ($gateway === 'cashfree') {
        $orderId = 'KSET-' . strtoupper($ref);
        $order = cashfree_create_order($orderId, $amountPaise, ['name' => $name, 'email' => $email, 'phone' => $phone, 'ref' => $ref], $description, $ref);
        db_exec('UPDATE donations SET gateway_order_id = ? WHERE id = ?', [$orderId, $donationId]);
        $_SESSION['donation_refs'][] = $ref;
        json_response([
            'ok'                 => true,
            'gateway'            => 'cashfree',
            'ref'                => $ref,
            'payment_session_id' => $order['payment_session_id'],
            'mode'               => cashfree_mode() === 'live' ? 'production' : 'sandbox',
        ]);
    }

    $order = razorpay_create_order($amountPaise, 'donation_' . $donationId, [
        'donation_id' => $donationId,
        'ref'         => $ref,
        'name'        => $name,
        'email'       => $email,
        'phone'       => $phone,
    ]);
} catch (RuntimeException $ex) {
    error_log(gateway_label($gateway) . ' order failed: ' . $ex->getMessage());
    db_exec('UPDATE donations SET failure_reason = ? WHERE id = ?', [mb_substr($ex->getMessage(), 0, 250), $donationId]);
    json_response(['ok' => false, 'error' => 'We could not start the payment right now. Your details are saved and our team will contact you, or please try again in a few minutes.'], 502);
}

db_exec('UPDATE donations SET gateway_order_id = ? WHERE id = ?', [$order['id'], $donationId]);
$_SESSION['donation_refs'][] = $ref;

json_response([
    'ok'          => true,
    'gateway'     => 'razorpay',
    'ref'         => $ref,
    'key'         => razorpay_key_id(),
    'order_id'    => $order['id'],
    'amount'      => $amountPaise,
    'org_name'    => setting('site_name', 'Sri Krishnashraya Educational Trust'),
    'description' => $description,
    'logo'        => site_origin() . url('assets/images/sket-logo-web.webp'),
    // Optional details are only pre-filled when given; Razorpay asks for anything it needs itself.
    'prefill'     => array_filter(['name' => $name, 'email' => $email, 'contact' => $phone ? '+91' . $phone : '']),
]);
