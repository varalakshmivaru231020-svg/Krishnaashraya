<?php
/**
 * "Reply by email" button: opens a ready-to-send Gmail message in a new tab (works without an email app on the computer),
 * with a menu for the computer's own email app or copying the address.
 * @var string $email @var string $subject @var string $body @var string $label
 */
$gmail = 'https://mail.google.com/mail/?' . http_build_query(['view' => 'cm', 'fs' => 1, 'to' => $email, 'su' => $subject, 'body' => $body], '', '&', PHP_QUERY_RFC3986);
$mailto = 'mailto:' . rawurlencode($email) . '?subject=' . rawurlencode($subject) . '&body=' . rawurlencode($body);
?>
<div class="btn-group email-btn">
    <a class="btn btn-outline-primary" href="<?= e($gmail) ?>" target="_blank" rel="noopener"><i class="bi bi-envelope"></i> <?= e($label) ?></a>
    <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More email options"></button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="<?= e($gmail) ?>" target="_blank" rel="noopener"><i class="bi bi-google me-2"></i>Reply in Gmail</a></li>
        <li><a class="dropdown-item" href="<?= e($mailto) ?>"><i class="bi bi-envelope-paper me-2"></i>Use my email app (Outlook, Mail…)</a></li>
        <li><button type="button" class="dropdown-item" data-copy="<?= e($email) ?>"><i class="bi bi-clipboard me-2"></i>Copy email address</button></li>
    </ul>
</div>
<script>
    document.querySelectorAll('.email-btn [data-copy]').forEach(function (b) {
        if (b.dataset.ready) return;
        b.dataset.ready = '1';
        b.addEventListener('click', function () {
            var text = b.getAttribute('data-copy'), label = b.innerHTML;
            (navigator.clipboard ? navigator.clipboard.writeText(text) : Promise.reject()).catch(function () { window.prompt('Copy this email address:', text); });
            b.innerHTML = '<i class="bi bi-check2 me-2"></i>Copied ' + text.replace(/[<>&"]/g, '');
            setTimeout(function () { b.innerHTML = label; }, 1800);
        });
    });
</script>
