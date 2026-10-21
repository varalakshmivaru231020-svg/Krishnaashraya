<?php
declare(strict_types=1);

/**
 * Gateway-neutral payment helpers. Two gateways are supported (Razorpay and Cashfree);
 * both are set up in Admin -> Payment Settings and only one can be switched on at a time.
 */

/** 'razorpay', 'cashfree', or '' when online payments are off. */
function active_gateway(): string
{
    if (razorpay_configured()) {
        return 'razorpay';
    }
    if (cashfree_configured()) {
        return 'cashfree';
    }
    return '';
}

function gateway_label(?string $gateway): string
{
    return ['razorpay' => 'Razorpay', 'cashfree' => 'Cashfree'][$gateway ?? ''] ?? 'Online payment';
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/** e.g. "https://www.krishnashraya.in" — used for links the gateways call back. */
function site_origin(): string
{
    return (is_https() ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

/** True on a developer's computer, where gateway webhooks cannot reach the site. */
function is_local_site(): bool
{
    $host = strtolower(explode(':', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'))[0]);
    return in_array($host, ['localhost', '127.0.0.1', '::1'], true) || str_ends_with($host, '.local') || str_ends_with($host, '.test');
}

/** Marks a donation paid exactly once. Returns true if this call changed it. */
function donation_mark_paid(int $donationId, string $paymentId, ?string $signature = null): bool
{
    $changed = db_exec(
        "UPDATE donations SET status = 'paid', gateway_payment_id = ?, gateway_signature = COALESCE(?, gateway_signature),
                failure_reason = NULL, paid_at = NOW()
          WHERE id = ? AND status <> 'paid'",
        [$paymentId, $signature, $donationId]
    ) > 0;

    if ($changed) {
        $d = db_one('SELECT * FROM donations WHERE id = ?', [$donationId]);
        donation_receipt_no($d);
        notify_admin(
            'New donation: ' . paise_to_inr((int) $d['amount_paise']) . ' from ' . $d['name'],
            "Name: {$d['name']}\nEmail: " . ($d['email'] ?: '-') . "\nPhone: " . ($d['phone'] ?: '-') . "\nAmount: " . paise_to_inr((int) $d['amount_paise'])
            . "\nPaid via: " . gateway_label($d['gateway']) . "\nPayment ID: {$paymentId}\nPAN: " . ($d['pan'] ?: '-')
            . "\nMessage: " . ($d['message'] ?: '-')
        );
    }
    return $changed;
}

/** Records a failed attempt (e.g. bank declined), but never overwrites a paid donation. */
function donation_mark_failed(int $donationId, string $reason): void
{
    db_exec(
        "UPDATE donations SET status = 'failed', failure_reason = ? WHERE id = ? AND status <> 'paid'",
        [mb_substr($reason, 0, 250), $donationId]
    );
}

/** The donor closed the payment window. Only an untouched (pending) donation becomes "cancelled". */
function donation_mark_cancelled(int $donationId, string $reason = 'Donor closed the payment window'): void
{
    db_exec(
        "UPDATE donations SET status = 'cancelled', failure_reason = ? WHERE id = ? AND status = 'pending'",
        [mb_substr($reason, 0, 250), $donationId]
    );
}

/**
 * How a paid donation was paid, for the receipt: ['mode' => 'UPI', 'txn' => bank / UPI reference or payment ID, 'payment_id' => ...].
 * Asked from the gateway the first time and then kept with the donation.
 */
function donation_payment_details(array $d): array
{
    $paymentId = (string) $d['gateway_payment_id'];
    $out = ['mode' => $d['payment_method'] ?: 'Online', 'txn' => $d['payment_ref'] ?: ($paymentId ?: '—'), 'payment_id' => $paymentId];
    if ($d['payment_method'] || $d['status'] !== 'paid') {
        return $out;
    }
    $method = $ref = '';
    try {
        if ($d['gateway'] === 'razorpay' && str_starts_with($paymentId, 'pay_') && razorpay_configured()) {
            $p = razorpay_fetch_payment($paymentId);
            $method = ['upi' => 'UPI', 'card' => 'Card', 'netbanking' => 'Net Banking', 'wallet' => 'Wallet', 'emi' => 'EMI', 'paylater' => 'Pay Later'][$p['method'] ?? ''] ?? ucfirst((string) ($p['method'] ?? ''));
            if (($p['method'] ?? '') === 'card' && !empty($p['card']['network'])) {
                $method .= ' (' . $p['card']['network'] . ')';
            } elseif (($p['method'] ?? '') === 'netbanking' && !empty($p['bank'])) {
                $method .= ' (' . $p['bank'] . ')';
            } elseif (($p['method'] ?? '') === 'wallet' && !empty($p['wallet'])) {
                $method .= ' (' . ucfirst((string) $p['wallet']) . ')';
            }
            $a = $p['acquirer_data'] ?? [];
            $ref = (string) ($a['rrn'] ?? $a['upi_transaction_id'] ?? $a['bank_transaction_id'] ?? $a['auth_code'] ?? '');
        } elseif ($d['gateway'] === 'cashfree' && $d['gateway_order_id'] && cashfree_configured()) {
            [$status, $payments] = cashfree_request('GET', '/orders/' . rawurlencode($d['gateway_order_id']) . '/payments');
            foreach ($status === 200 && is_array($payments) ? $payments : [] as $p) {
                if (($p['payment_status'] ?? '') === 'SUCCESS') {
                    $group = (string) ($p['payment_group'] ?? '');
                    $method = ['upi' => 'UPI', 'credit_card' => 'Credit Card', 'debit_card' => 'Debit Card', 'net_banking' => 'Net Banking', 'wallet' => 'Wallet',
                               'cardless_emi' => 'EMI', 'credit_card_emi' => 'EMI', 'debit_card_emi' => 'EMI', 'pay_later' => 'Pay Later'][$group] ?? ucwords(str_replace('_', ' ', $group));
                    $ref = (string) ($p['bank_reference'] ?? '');
                    break;
                }
            }
        }
    } catch (Throwable $ex) {
        error_log('Payment details for receipt failed: ' . $ex->getMessage());
    }
    if ($method !== '') {
        db_exec('UPDATE donations SET payment_method = ?, payment_ref = ? WHERE id = ?', [mb_substr($method, 0, 40), mb_substr($ref, 0, 64) ?: null, $d['id']]);
        $out['mode'] = $method;
        $out['txn'] = $ref ?: ($paymentId ?: '—');
    }
    return $out;
}
