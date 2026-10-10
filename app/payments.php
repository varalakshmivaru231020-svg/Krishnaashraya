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
