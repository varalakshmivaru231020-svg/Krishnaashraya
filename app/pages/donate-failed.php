<?php
declare(strict_types=1);

// Records why a payment attempt did not complete (card declined, or the donor closed the window), for follow-up.
// Only the donor's own browser session may report this, and a paid donation is never changed.

if (!is_post() || !csrf_valid()) {
    json_response(['ok' => false], 400);
}

$ref = input('ref');
if (!in_array($ref, $_SESSION['donation_refs'] ?? [], true)) {
    json_response(['ok' => false], 403);
}

$id = (int) db_value('SELECT id FROM donations WHERE ref = ?', [$ref]);
if ($id && input('type') === 'cancelled') {
    donation_mark_cancelled($id);
} elseif ($id) {
    donation_mark_failed($id, input('reason', 'Payment failed'));
}
json_response(['ok' => true]);
