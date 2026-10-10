<?php
/** @var array $categories */
$banner = page_banner('activities');
partial('page-banner', [
    'heading'    => $banner['title'],
    'subheading' => $banner['subtitle'],
    'image'      => $banner['image'],
    'crumbs'     => ['Our Activities' => 'our-activities'],
]);
?>

<section class="section">
    <div class="container">
        <div class="projects-intro" data-reveal>
            <div>
                <span class="eyebrow">Life at Krishnashraya</span>
                <h2 class="force-font">Everyday moments that shape a childhood</h2>
                <p>From classrooms and kitchens to sports days and festivals, these are the activities that fill our children's days.
                    Each one is made possible by donors, volunteers and well-wishers. Open any activity to see the photos.</p>
            </div>
            <div class="projects-counts">
                <div><strong><?= count($categories) ?></strong><span>Activities</span></div>
                <div><strong><?= array_sum(array_map(fn($c) => (int) $c['photo_count'], $categories)) ?></strong><span>Photos</span></div>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $c): ?>
                <div class="col-md-6 col-lg-4">
                    <a href="<?= e(url($c['slug'])) ?>" class="activity-card slide-up">
                        <div class="activity-card-img">
                            <img src="<?= e(img($c['cover_image'])) ?>" alt="<?= e($c['title']) ?>" loading="lazy">
                        </div>
                        <div class="activity-card-body">
                            <h3><?= e($c['title']) ?></h3>
                            <?php if ($c['description']): ?>
                                <p><?= e(mb_strimwidth($c['description'], 0, 140, '…')) ?></p>
                            <?php endif; ?>
                            <span class="activity-card-meta"><?= (int) $c['photo_count'] ?> photos · View more <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
