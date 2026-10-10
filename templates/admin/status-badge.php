<?php
/** @var string $status */
$labels = [
    'paid'    => 'Paid',
    'pending' => 'Not paid',
    'failed'  => 'Failed',
    'cancelled' => 'Cancelled',
    'new'     => 'New',
    'read'    => 'Read',
    'replied' => 'Replied',
    'active'  => 'Visible',
    'hidden'  => 'Hidden',
];
?>
<span class="badge-status badge-<?= e($status) ?>"><?= e($labels[$status] ?? ucfirst($status)) ?></span>
