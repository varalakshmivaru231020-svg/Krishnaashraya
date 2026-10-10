<?php
/**
 * Call-to-action panel: full background photo, centred heading, text and buttons.
 * @var string $heading  @var string $text  @var string $image
 * @var array $buttons  list of [label, url, 'primary'|'outline', icon?]
 */
$eyebrow = $eyebrow ?? '';
$position = $position ?? 'center 12%';
?>
<div class="cta-band" data-reveal>
    <div class="cta-band-bg" aria-hidden="true"><img src="<?= e(img($image)) ?>" alt="" loading="lazy" style="object-position: <?= e($position) ?>"></div>
    <div class="cta-band-body">
        <?php if ($eyebrow): ?><span class="eyebrow"><?= e($eyebrow) ?></span><?php endif; ?>
        <h2 class="force-font"><?= e($heading) ?></h2>
        <p><?= e($text) ?></p>
        <div class="cta-band-actions">
            <?php foreach ($buttons as $b): ?>
                <a href="<?= e($b[1]) ?>" class="<?= ($b[2] ?? 'primary') === 'primary' ? 'btn-primary-gradient' : 'btn-outline-gradient' ?>"><?php if (!empty($b[3])): ?><i class="bi bi-<?= e($b[3]) ?>"></i> <?php endif; ?><?= e($b[0]) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
