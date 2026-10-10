<?php
declare(strict_types=1);

// Cashfree -> server notification (PAYMENT_SUCCESS / PAYMENT_FAILED / PAYMENT_USER_DROPPED).
// Catches payments where the donor closed the browser before the thank-you page loaded.
// The signature proves the call came from Cashfree; the payment itself is then confirmed
// by asking Cashfree's API, so a forged or replayed message can never mark a donation paid.

if (!is_post()) {
    http_response_code(405);
    exit;
}

$raw = (string) file_get_contents('php://input');
$timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? '';
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

if (!cashfree_webhook_signature_valid($raw, $timestamp, $signature)) {
    http_response_code(400);
    exit('invalid signature');
}

$event = json_decode($raw, true) ?: [];
$orderId = $event['data']['order']['order_id'] ?? null;

if ($orderId) {
    $donation = db_one("SELECT * FROM donations WHERE gateway_order_id = ? AND gateway = 'cashfree'", [$orderId]);
    if ($donation) {
        try {
            cashfree_sync_donation($donation);
        } catch (RuntimeException $ex) {
            error_log('Cashfree webhook sync failed: ' . $ex->getMessage());
            http_response_code(500); // Cashfree retries later
            exit('retry');
        }
    }
}

http_response_code(200);
echo 'ok';
