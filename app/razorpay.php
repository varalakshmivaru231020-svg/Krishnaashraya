<?php
declare(strict_types=1);

/**
 * Minimal Razorpay client (Orders API + signature checks) using cURL,
 * so no Composer packages are needed on shared hosting.
 * Docs: https://razorpay.com/docs/payments/payment-gateway/web-integration/standard/
 */

/**
 * Razorpay settings live in Admin -> Payment Settings (stored in the database);
 * values in app/config.php are used only as a fallback.
 */
function razorpay_enabled(): bool
{
    return setting('rzp_enabled', '0') === '1';
}

function razorpay_mode(): string
{
    return setting('rzp_mode', 'test') === 'live' ? 'live' : 'test';
}

function razorpay_key_id(): string
{
    return setting('rzp_key_id', (string) config('razorpay.key_id', ''));
}

function razorpay_key_secret(): string
{
    return setting('rzp_key_secret', (string) config('razorpay.key_secret', ''));
}

function razorpay_webhook_secret(): string
{
    return setting('rzp_webhook_secret', (string) config('razorpay.webhook_secret', ''));
}

/** True when online payments can be taken: switched on and both keys entered. */
function razorpay_configured(): bool
{
    return razorpay_enabled() && razorpay_key_id() !== '' && razorpay_key_secret() !== '';
}

/** Calls Razorpay with the given keys to check they work. Returns '' on success, or the error message. */
function razorpay_check_keys(string $keyId, string $secret): string
{
    $ch = curl_init('https://api.razorpay.com/v1/orders?count=1');
    curl_setopt_array($ch, [
        CURLOPT_USERPWD        => $keyId . ':' . $secret,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($body === false) {
        return 'Could not reach Razorpay: ' . $error;
    }
    if ($status === 200) {
        return '';
    }
    $data = json_decode((string) $body, true);
    return $data['error']['description'] ?? ('Razorpay replied with HTTP ' . $status);
}

/** Creates an order and returns Razorpay's response (contains 'id'). Throws on failure. */
function razorpay_create_order(int $amountPaise, string $receipt, array $notes = []): array
{
    $payload = json_encode([
        'amount'   => $amountPaise,
        'currency' => 'INR',
        'receipt'  => substr($receipt, 0, 40),
        'notes'    => array_map(fn($v) => substr((string) $v, 0, 250), $notes),
    ]);

    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_USERPWD        => razorpay_key_id() . ':' . razorpay_key_secret(),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        throw new RuntimeException('Could not reach Razorpay: ' . $error);
    }
    $data = json_decode((string) $body, true);
    if ($status !== 200 || empty($data['id'])) {
        throw new RuntimeException('Razorpay error: ' . ($data['error']['description'] ?? ('HTTP ' . $status)));
    }
    return $data;
}

/** One payment's details from Razorpay (method, bank / UPI reference ...). Throws on failure. */
function razorpay_fetch_payment(string $paymentId): array
{
    $ch = curl_init('https://api.razorpay.com/v1/payments/' . rawurlencode($paymentId));
    curl_setopt_array($ch, [
        CURLOPT_USERPWD        => razorpay_key_id() . ':' . razorpay_key_secret(),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode((string) $body, true);
    if ($status !== 200 || empty($data['id'])) {
        throw new RuntimeException('Razorpay error: ' . ($data['error']['description'] ?? ('HTTP ' . $status)));
    }
    return $data;
}

/** Checks the signature Razorpay Checkout returns to the browser after a successful payment. */
function razorpay_payment_signature_valid(string $orderId, string $paymentId, string $signature): bool
{
    $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, razorpay_key_secret());
    return $signature !== '' && hash_equals($expected, $signature);
}

/** Checks the X-Razorpay-Signature header on webhook calls. */
function razorpay_webhook_signature_valid(string $rawBody, string $signature): bool
{
    $secret = razorpay_webhook_secret();
    if ($secret === '' || $signature === '') {
        return false;
    }
    return hash_equals(hash_hmac('sha256', $rawBody, $secret), $signature);
}
