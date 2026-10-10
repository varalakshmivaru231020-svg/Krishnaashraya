<?php $mapsUrl = setting('maps_url'); ?>
<div class="contact-item">
    <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
    <div class="contact-info">
        <a href="<?= e($mapsUrl ?: '#') ?>" target="_blank" rel="noopener">
            <h5>Registered Office</h5>
            <p><?= nl2br(e(setting('address_registered'))) ?></p>
        </a>
        <?php if (setting('address_center')): ?>
            <a href="<?= e($mapsUrl ?: '#') ?>" target="_blank" rel="noopener">
                <h5 class="mt-3">Hoskote Center</h5>
                <p><?= nl2br(e(setting('address_center'))) ?></p>
            </a>
        <?php endif; ?>
    </div>
</div>
<div class="contact-item">
    <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
    <div class="contact-info">
        <h5>Phone</h5>
        <?php foreach (array_filter([setting('phone_1'), setting('phone_2')]) as $ph): ?>
            <a href="<?= e(tel_href($ph)) ?>"><p><?= e($ph) ?></p></a>
        <?php endforeach; ?>
    </div>
</div>
<div class="contact-item">
    <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
    <div class="contact-info">
        <h5>Email</h5>
        <?php foreach (array_filter([setting('email_1'), setting('email_2')]) as $mail): ?>
            <a href="mailto:<?= e($mail) ?>"><p><?= e($mail) ?></p></a>
        <?php endforeach; ?>
    </div>
</div>
<?php if (setting('visiting_hours')): ?>
    <div class="contact-item">
        <div class="contact-icon"><i class="bi bi-clock-fill"></i></div>
        <div class="contact-info">
            <h5>Visiting Hours</h5>
            <p><?= nl2br(e(setting('visiting_hours'))) ?></p>
        </div>
    </div>
<?php endif; ?>
