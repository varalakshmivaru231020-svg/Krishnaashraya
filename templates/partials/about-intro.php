<?php /** The About block, shared by the home page and the About Us page. Edited in Admin -> About Us. */ ?>
<div class="row align-items-center g-5">
    <div class="col-lg-6">
        <div class="slide-up">
            <?php if (about('about_heading')): ?>
                <h3 class="mb-4"><?= e(about('about_heading')) ?></h3>
            <?php endif; ?>
            <?= paragraphs(about('about_text')) ?>
            <?php if (about('about_quote')): ?>
                <div class="quote-box">
                    <i class="bi bi-quote"></i>
                    <?= e(about('about_quote')) ?>
                </div>
            <?php endif; ?>
            <div class="stats-row">
                <?php foreach ([1, 2, 3] as $n):
                    $value = about("about_stat{$n}_value");
                    $label = about("about_stat{$n}_label");
                    if ($value === '' && $label === '') continue;
                    $fig = split_figure($value); ?>
                    <div class="stat-item">
                        <?php if ($fig): ?>
                            <div class="stat-number counter" data-target="<?= $fig[1] ?>" data-prefix="<?= e($fig[0]) ?>" data-suffix="<?= e($fig[2]) ?>"><?= e($fig[0]) ?>0</div>
                        <?php else: ?>
                            <div class="stat-number"><?= e($value) ?></div>
                        <?php endif; ?>
                        <div class="stat-label"><?= e($label) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-4 text-center text-md-start">
                <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient btn-pulse js-donate">Donate Now</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="about-visual">
            <span class="about-ring" aria-hidden="true"></span>
            <span class="about-dots" aria-hidden="true"></span>
        <div class="about-image-card scale-in">
            <img src="<?= e(img(about('about_image'))) ?>" alt="<?= e(about('about_heading') ?: 'Children at Krishnashraya') ?>">
            <?php if (about('mission_short')): ?>
                <div class="mission-float-card">
                    <h5><i class="bi bi-bullseye me-2"></i><?= e(about('about_mission_label')) ?></h5>
                    <p class="mb-0"><?= e(about('mission_short')) ?></p>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div>
</div>
