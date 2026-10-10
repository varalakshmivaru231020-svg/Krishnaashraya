<?php
declare(strict_types=1);

// Step 2 (AJAX): the gateway's checkout finished in the browser. Confirm the payment on the server
// before marking the donation as paid - the browser's word alone is never trusted.
//   Razorpay: check the signature Razorpay Checkout returned.
//   Cashfree: ask Cashfree's API for the order status.

if (!is_post() || !csrf_valid()) {
    json_response(['ok' => false, 'error' => 'Invalid request'], 400);
}

$donation = db_one('SELECT * FROM donations WHERE ref = ?', [input('ref')]);
if (!$donation) {
    json_response(['ok' => false, 'error' => 'Donation not found'], 404);
}

if ($donation['gateway'] === 'cashfree') {
    try {
        $status = cashfree_sync_donation($donation);
    } catch (RuntimeException $ex) {
        json_response(['ok' => false, 'status' => 'pending', 'error' => 'Could not reach Cashfree to confirm the payment.'], 502);
    }
    json_response(['ok' => $status === 'paid', 'status' => $status]);
}

$orderId = input('razorpay_order_id');
$paymentId = input('razorpay_payment_id');
$signature = input('razorpay_signature');

if ($donation['gateway_order_id'] !== $orderId) {
    json_response(['ok' => false, 'error' => 'Donation not found'], 404);
}
if (!razorpay_payment_signature_valid($orderId, $paymentId, $signature)) {
    db_exec("UPDATE donations SET failure_reason = 'Signature check failed' WHERE id = ? AND status = 'pending'", [$donation['id']]);
    json_response(['ok' => false, 'error' => 'Payment could not be verified'], 400);
}

donation_mark_paid((int) $donation['id'], $paymentId, $signature);
json_response(['ok' => true, 'status' => 'paid']);
