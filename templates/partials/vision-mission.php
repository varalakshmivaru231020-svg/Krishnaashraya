<?php
/** Vision, Mission & Goals + Our Work. Shared by the home page and the About Us page; edited in Admin -> About Us. */
$goals = lines(about('goals'));
$cards = array_filter([
    ['01', 'eye', 'Our Vision', about('vision'), []],
    ['02', 'rocket-takeoff', 'Our Mission', about('mission'), []],
    $goals ? ['03', 'flag', 'Our Goals', '', $goals] : null,
]);
$col = count($cards) === 3 ? 'col-lg-4' : 'col-md-6';
// Fixed, hand-picked photos (not taken from the activity list, so a test or half-finished activity can't appear here).
$workImages = ['assets/images/our-activities/education-programs/education-programs-img-7.webp', 'assets/images/our-activities/food-and-nutrition/food-and-nutrition-img-9.webp'];
$stat = split_figure(about('about_stat1_value'));
?>
<section class="section vm-section has-blobs" id="vision-mission">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge">Vision, Mission &amp; Goals</span>
            <h2 class="section-title force-font"><?= e(about('vm_title')) ?></h2>
        </div>
        <div class="row g-4 justify-content-center">
            <?php foreach ($cards as [$no, $icon, $heading, $text, $list]): ?>
                <div class="<?= $col ?>">
                    <article class="vm-card" data-reveal>
                        <span class="vm-no" aria-hidden="true"><?= $no ?></span>
                        <div class="vm-icon"><i class="bi bi-<?= $icon ?>"></i></div>
                        <h3><?= e($heading) ?></h3>
                        <?php if ($list): ?>
                            <ul class="vm-goals">
                                <?php foreach ($list as $g): ?><li><i class="bi bi-check2-circle"></i><span><?= e($g) ?></span></li><?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p><?= nl2br(e($text)) ?></p>
                        <?php endif; ?>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section our-work-section" id="our-work">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6 work-copy" data-reveal="left">
                <span class="eyebrow">Our Work</span>
                <h2 class="section-title force-font">Krishnashraya — Our Work</h2>
                <?= paragraphs(about('our_work')) ?>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient">Donate Now</a>
                    <a href="<?= e(url('our-activities')) ?>" class="link-arrow align-self-center">See our activities <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="work-collage">
                    <figure class="work-img work-img-1 img-reveal"><img src="<?= e(img($workImages[0])) ?>" alt="Children at Krishnashraya" loading="lazy"></figure>
                    <figure class="work-img work-img-2 img-reveal"><img src="<?= e(img($workImages[1])) ?>" alt="Daily life at Krishnashraya" loading="lazy"></figure>
                    <?php if ($stat): ?>
                        <div class="work-badge"><strong><?= e(about('about_stat1_value')) ?></strong><span><?= e(about('about_stat1_label')) ?></span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
