<?php $banner = page_banner('about'); partial('page-banner', [
    'heading'    => $banner['title'],
    'subheading' => $banner['subtitle'],
    'image'      => $banner['image'],
    'crumbs'     => ['About Us' => 'about'],
]); ?>

<section class="section">
    <div class="container">
        <div class="text-center">
            <?php if (about('about_badge')): ?><span class="section-badge slide-up"><?= e(about('about_badge')) ?></span><?php endif; ?>
            <h2 class="section-title slide-up force-font"><?= e(about('about_title')) ?></h2>
            <?php if (about('about_subtitle')): ?><p class="section-subtitle slide-up"><?= e(about('about_subtitle')) ?></p><?php endif; ?>
        </div>
        <?php partial('about-intro'); ?>
    </div>
</section>

<?php partial('marquee'); ?>

<?php partial('vision-mission'); ?>

<section class="section bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge slide-up">Our Focus Areas</span>
            <h2 class="section-title slide-up force-font">Three pillars of support for holistic development</h2>
        </div>
        <div class="row g-4">
            <?php foreach ([
                ['Food', 'Nutritious meals every day for every child in our care.', 'assets/images/our-activities/food-and-nutrition/food-and-nutrition-img-9.webp', 'food-and-nutrition'],
                ['Shelter', 'A safe, loving home and family for children who have none.', 'assets/images/our-activities/community-support/community-support-img-5.webp', 'community-support'],
                ['Education', 'Quality schooling, tuition and skills for a brighter future.', 'assets/images/our-activities/education-programs/education-programs-img-7.webp', 'education-programs'],
            ] as [$t, $d, $i, $slug]): ?>
                <div class="col-md-4">
                    <a href="<?= e(url($slug)) ?>" class="activity-card slide-up">
                        <div class="activity-card-img"><img src="<?= e(url($i)) ?>" alt="<?= e($t) ?>" loading="lazy"></div>
                        <div class="activity-card-body">
                            <h3><?= e($t) ?></h3>
                            <p><?= e($d) ?></p>
                            <span class="activity-card-meta">See our work <i class="bi bi-arrow-right"></i></span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php $cta = cta('about'); partial('cta-band', [
            'eyebrow'  => $cta['eyebrow'],
            'heading'  => $cta['heading'],
            'text'     => $cta['text'],
            'image'    => $cta['image'],
            'position' => $cta['position'],
            'buttons'  => [
                ['Donate Now', url('donate'), 'primary', 'heart-fill'],
                ['Contact Us', url('contact'), 'outline'],
            ],
        ]); ?>
    </div>
</section>
