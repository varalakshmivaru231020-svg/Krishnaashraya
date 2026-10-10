<?php
declare(strict_types=1);

/**
 * Minimal Cashfree Payment Gateway client (PG API, version 2023-08-01) using cURL.
 * Docs: https://www.cashfree.com/docs/api-reference/payments/latest/orders/create
 *
 * Flow: create an order on the server -> open Cashfree Checkout in the browser with the
 * payment_session_id -> ask Cashfree's API (never the browser) whether the order is PAID.
 * Settings live in Admin -> Payment Settings.
 */

const CASHFREE_API_VERSION = '2023-08-01';

function cashfree_enabled(): bool
{
    return setting('cf_enabled', '0') === '1';
}

/** 'test' (sandbox) or 'live' (production). */
function cashfree_mode(): string
{
    return setting('cf_mode', 'test') === 'live' ? 'live' : 'test';
}

function cashfree_app_id(): string
{
    return setting('cf_app_id');
}

function cashfree_secret_key(): string
{
    return setting('cf_secret_key');
}

function cashfree_configured(): bool
{
    return cashfree_enabled() && cashfree_app_id() !== '' && cashfree_secret_key() !== '';
}

function cashfree_api_base(string $mode): string
{
    return $mode === 'live' ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg';
}

/**
 * Calls the Cashfree API. Returns [httpStatus, decodedBody]. Throws only when Cashfree can't be reached.
 * $creds = [appId, secret, mode] to test keys that are not saved yet; defaults to the saved settings.
 */
function cashfree_request(string $method, string $path, ?array $body = null, ?array $creds = null): array
{
    [$appId, $secret, $mode] = $creds ?? [cashfree_app_id(), cashfree_secret_key(), cashfree_mode()];

    $ch = curl_init(cashfree_api_base($mode) . $path);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'x-client-id: ' . $appId,
            'x-client-secret: ' . $secret,
            'x-api-version: ' . CASHFREE_API_VERSION,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException('Could not reach Cashfree: ' . $error);
    }
    return [$status, json_decode((string) $raw, true) ?: []];
}

/** Checks a set of keys. Returns '' when Cashfree accepts them, otherwise the reason. */
function cashfree_check_keys(string $appId, string $secret, string $mode): string
{
    try {
        // Looking up an order that can't exist: valid keys get "404 not found", wrong keys get "401".
        [$status, $data] = cashfree_request('GET', '/orders/key-check-' . bin2hex(random_bytes(4)), null, [$appId, $secret, $mode]);
    } catch (RuntimeException $ex) {
        return $ex->getMessage();
    }
    if (in_array($status, [200, 404], true)) {
        return '';
    }
    return $data['message'] ?? ('Cashfree replied with HTTP ' . $status);
}

/** Creates an order; returns Cashfree's response (contains payment_session_id). Throws on failure. */
function cashfree_create_order(string $orderId, int $amountPaise, array $customer, string $note, string $ref): array
{
    // Cashfree requires a customer phone number. Donors may skip it, so the trust's own number is used then.
    $phone = $customer['phone'] ?: substr(preg_replace('/\D/', '', setting('phone_1', '9999999999')), -10);
    $details = [
        'customer_id'    => 'donor_' . ($customer['phone'] ?: $customer['ref']),
        'customer_name'  => mb_substr($customer['name'], 0, 100),
        'customer_phone' => $phone,
    ];
    if ($customer['email'] !== '') {
        $details['customer_email'] = $customer['email'];
    }
    $body = [
        'order_id'         => $orderId,
        'order_amount'     => round($amountPaise / 100, 2),
        'order_currency'   => 'INR',
        'customer_details' => $details,
        'order_note'       => mb_substr($note, 0, 200),
    ];
    // Cashfree only calls back to https addresses, so these are added once the site is live.
    if (is_https()) {
        $body['order_meta'] = [
            'return_url' => site_origin() . url('donate/thank-you') . '?ref=' . rawurlencode($ref),
            'notify_url' => site_origin() . url('donate/webhook/cashfree'),
        ];
    }

    [$status, $data] = cashfree_request('POST', '/orders', $body);
    if ($status !== 200 || empty($data['payment_session_id'])) {
        throw new RuntimeException('Cashfree error: ' . ($data['message'] ?? ('HTTP ' . $status)));
    }
    return $data;
}

/**
 * Asks Cashfree for the real state of a donation's order and updates the donation.
 * Returns the donation's status afterwards: 'paid', 'failed', 'cancelled' or 'pending'.
 */
function cashfree_sync_donation(array $donation): string
{
    if ($donation['status'] === 'paid' || empty($donation['gateway_order_id'])) {
        return $donation['status'];
    }
    $orderId = rawurlencode($donation['gateway_order_id']);

    [$status, $order] = cashfree_request('GET', '/orders/' . $orderId);
    if ($status !== 200) {
        return $donation['status'];
    }

    [, $payments] = cashfree_request('GET', '/orders/' . $orderId . '/payments');
    $payments = is_array($payments) && array_is_list($payments) ? $payments : [];

    if (($order['order_status'] ?? '') === 'PAID') {
        $paidPaise = (int) round(((float) ($order['order_amount'] ?? 0)) * 100);
        if ($paidPaise !== (int) $donation['amount_paise']) {
            error_log("Cashfree amount mismatch for donation {$donation['id']}: expected {$donation['amount_paise']}, got {$paidPaise}");
            return $donation['status'];
        }
        $paymentId = '';
        foreach ($payments as $p) {
            if (($p['payment_status'] ?? '') === 'SUCCESS') {
                $paymentId = (string) $p['cf_payment_id'];
                break;
            }
        }
        donation_mark_paid((int) $donation['id'], $paymentId !== '' ? $paymentId : (string) ($order['cf_order_id'] ?? $donation['gateway_order_id']));
        return 'paid';
    }

    // Not paid: record why the latest attempt failed, so the team can follow up.
    $latest = null;
    foreach ($payments as $p) {
        if ($latest === null || (string) ($p['payment_time'] ?? '') >= (string) ($latest['payment_time'] ?? '')) {
            $latest = $p;
        }
    }
    $latestStatus = $latest['payment_status'] ?? '';
    if (in_array($latestStatus, ['USER_DROPPED', 'CANCELLED'], true)) {
        donation_mark_cancelled((int) $donation['id']);
    } elseif ($latestStatus === 'FAILED') {
        donation_mark_failed((int) $donation['id'], ($latest['payment_message'] ?? '') ?: 'Payment failed');
    }
    return (string) db_value('SELECT status FROM donations WHERE id = ?', [$donation['id']]);
}

/** Checks the x-webhook-signature header: base64(HMAC-SHA256(timestamp + raw body, secret key)). */
function cashfree_webhook_signature_valid(string $rawBody, string $timestamp, string $signature): bool
{
    $secret = cashfree_secret_key();
    if ($secret === '' || $timestamp === '' || $signature === '') {
        return false;
    }
    return hash_equals(base64_encode(hash_hmac('sha256', $timestamp . $rawBody, $secret, true)), $signature);
}
