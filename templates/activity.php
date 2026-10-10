<?php
/** @var array $category @var array $photos @var int $page @var int $pages @var array $others */
partial('page-banner', [
    'heading'    => $category['title'],
    'subheading' => $category['description'],
    'image'      => $category['banner_image'] ?: $category['cover_image'],
    'crumbs'     => ['Our Activities' => 'our-activities'],
]);
?>

<section class="section pb-0">
    <div class="container">
        <div class="activity-intro" data-reveal>
            <div>
                <span class="eyebrow">Our activities</span>
                <h2 class="force-font"><?= e($category['title']) ?> at Krishnashraya</h2>
                <?php if ($category['description']): ?><p class="lead-text"><?= e($category['description']) ?></p><?php endif; ?>
                <p>The photos below are moments from this part of our work with the children of <?= e(setting('site_name', 'Sri Krishnashraya Educational Trust')) ?>.
                    Every activity is run with care by our team and supported by people like you — donors who give what they can, and volunteers who give their time.</p>
            </div>
            <aside class="activity-help">
                <h3>How you can help</h3>
                <ul>
                    <li><i class="bi bi-heart"></i> Donate — even a small gift keeps this going</li>
                    <li><i class="bi bi-people"></i> Volunteer your time or skills</li>
                    <li><i class="bi bi-chat-dots"></i> Visit us and meet the children</li>
                </ul>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient btn-compact">Donate</a>
                    <a href="<?= e(url('contact?type=volunteer')) ?>" class="btn-outline-gradient btn-compact">Volunteer</a>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($photos): ?>
            <div class="row g-4 photo-grid">
                <?php foreach ($photos as $photo): ?>
                    <div class="col-6 col-md-4">
                        <a href="<?= e(img($photo['image'])) ?>" class="js-lightbox" data-caption="<?= e($photo['caption'] ?: $category['title']) ?>">
                            <div class="gallery-item slide-up">
                                <img src="<?= e(img($photo['image'])) ?>" alt="<?= e($photo['caption'] ?: $category['title']) ?>" loading="lazy">
                                <div class="gallery-overlay"><h5><?= e($photo['caption'] ?: $category['title']) ?></h5></div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($pages > 1): ?>
                <nav class="mt-5" aria-label="Gallery pages">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item<?= $i === $page ? ' active' : '' ?>">
                                <a class="page-link" href="<?= e(url($category['slug']) . ($i > 1 ? '?page=' . $i : '')) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-images"></i>Photos for this activity will be added soon.</div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient btn-pulse js-donate">Support Our Children</a>
        </div>
    </div>
</section>

<?php if ($others): ?>
<section class="section bg-light">
    <div class="container text-center">
        <h2 class="h4 mb-4">More activities</h2>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <?php foreach ($others as $o): ?>
                <a href="<?= e(url($o['slug'])) ?>" class="btn-secondary-outline mt-0"><?= e($o['title']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php partial('lightbox'); ?>
