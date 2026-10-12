<?php
/** A slowly scrolling band of what Krishnashraya does — activity names plus core values. Decorative, so hidden from screen readers. */
$items = array_column(nav_categories(), 'title');
$items = array_values(array_unique(array_merge(['Education', 'Nutrition', 'Shelter', 'Care'], array_slice($items, 0, 6), ['Love', 'Hope'])));
?>
<div class="marquee-wrap" aria-hidden="true">
    <div class="marquee">
        <div class="marquee-track">
            <?php for ($copy = 0; $copy < 4; $copy++): foreach ($items as $item): ?>
                <div class="marquee-item">
                    <span><?= e($item) ?></span>
                    <i class="bi bi-asterisk"></i>
                </div>
            <?php endforeach; endfor; ?>
        </div>
    </div>
</div>
