<?php
declare(strict_types=1);

// Razorpay -> server notification. Catches payments where the donor closed the browser
// before the thank-you page loaded. Set up in Admin -> Payment Settings, which shows the
// URL, events and secret to enter in Razorpay Dashboard -> Webhooks.

if (!is_post()) {
    http_response_code(405);
    exit;
}

$raw = (string) file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if (!razorpay_webhook_signature_valid($raw, $signature)) {
    http_response_code(400);
    exit('invalid signature');
}

$event = json_decode($raw, true) ?: [];
$payment = $event['payload']['payment']['entity'] ?? null;
$orderId = $payment['order_id'] ?? ($event['payload']['order']['entity']['id'] ?? null);

if ($orderId) {
    $donation = db_one("SELECT * FROM donations WHERE gateway_order_id = ? AND gateway = 'razorpay'", [$orderId]);
    if ($donation) {
        switch ($event['event'] ?? '') {
            case 'payment.captured':
            case 'order.paid':
                if ($payment && (int) $payment['amount'] === (int) $donation['amount_paise']) {
                    donation_mark_paid((int) $donation['id'], (string) $payment['id']);
                }
                break;
            case 'payment.failed':
                donation_mark_failed((int) $donation['id'], (string) ($payment['error_description'] ?? 'Payment failed'));
                break;
        }
    }
}

http_response_code(200);
echo 'ok';
