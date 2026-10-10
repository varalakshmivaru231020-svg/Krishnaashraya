<?php
/** @var array $project @var array $others */
$features = lines($project['features']);
$goal = (int) ($project['goal_amount'] ?? 0);
$raised = (int) $project['raised'];
$ongoing = $project['status'] === 'ongoing';
$donateUrl = url('donate?project=' . (int) $project['id']);
$presets = array_slice(array_values(array_filter(array_map('intval', explode(',', setting('donate_presets', '1000,2000,5000,10000'))))), 0, 3);
$pageUrl = site_origin() . url('projects/' . $project['slug']);
?>
<section class="project-hero" style="background-image:url('<?= e(img($project['image'])) ?>')">
    <div class="container">
        <div class="breadcrumb-lite"><a href="<?= e(url('')) ?>">Home</a><span class="mx-1">/</span><a href="<?= e(url('projects')) ?>">Our Projects</a></div>
        <span class="project-hero-status<?= $ongoing ? '' : ' is-completed' ?>"><i class="bi bi-<?= $ongoing ? 'broadcast' : 'check2-circle' ?>"></i> <?= $ongoing ? 'Ongoing project' : 'Completed project' ?></span>
        <h1 class="force-font"><?= e($project['title']) ?></h1>
        <?php if ($ongoing): ?>
            <div class="project-hero-actions">
                <a href="<?= e($donateUrl) ?>" class="btn-primary-gradient">Support this project</a>
                <a href="#project-story" class="btn-outline-gradient">Read more</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section project-detail" id="project-story">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <figure class="project-detail-photo img-reveal" data-reveal>
                    <img src="<?= e(img($project['image'])) ?>" alt="<?= e($project['title']) ?>">
                </figure>

                <div class="project-detail-text" data-reveal>
                    <span class="eyebrow">About this project</span>
                    <?= paragraphs((string) $project['summary'], 'lead-text') ?>
                </div>

                <?php if ($features): ?>
                    <div class="project-detail-block" data-reveal>
                        <h2 class="force-font">What your support provides</h2>
                        <div class="feature-grid">
                            <?php foreach ($features as $i => $f): ?>
                                <div class="feature-item" data-reveal style="--d: <?= $i ?>">
                                    <span class="feature-icon"><i class="bi bi-check2"></i></span>
                                    <span><?= e($f) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (trim((string) $project['description']) !== ''): ?>
                    <div class="project-detail-block" data-reveal>
                        <h2 class="force-font">The difference it makes</h2>
                        <?= paragraphs((string) $project['description']) ?>
                    </div>
                <?php endif; ?>

                <div class="project-share" data-reveal>
                    <span>Share this project</span>
                    <a href="https://wa.me/?text=<?= rawurlencode($project['title'] . ' — ' . $pageUrl) ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($pageUrl) ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="mailto:?subject=<?= rawurlencode($project['title']) ?>&amp;body=<?= rawurlencode($pageUrl) ?>" aria-label="Share by email"><i class="bi bi-envelope"></i></a>
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="donate-aside">
                    <?php if ($ongoing): ?>
                        <h2>Support this project</h2>
                        <p>Your gift goes directly to this project and is eligible for 80G tax benefits.</p>
                        <?php if ($goal > 0): ?>
                            <div class="aside-progress">
                                <div class="d-flex justify-content-between"><strong><?= e(inr($raised)) ?></strong><span>of <?= e(inr($goal)) ?></span></div>
                                <div class="project-progress"><span style="width: <?= min(100, (int) round($raised * 100 / $goal)) ?>%"></span></div>
                                <small><?= min(100, (int) round($raised * 100 / $goal)) ?>% of the goal raised online</small>
                            </div>
                        <?php endif; ?>
                        <?php if ($presets): ?>
                            <div class="quick-amounts">
                                <?php foreach ($presets as $amt): ?>
                                    <a href="<?= e($donateUrl . '&amount=' . $amt) ?>"><?= e(inr($amt)) ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <a href="<?= e($donateUrl) ?>" class="btn-primary-gradient w-100">Donate to this project</a>
                    <?php else: ?>
                        <h2>This project is complete</h2>
                        <p>Thank you to everyone who made it possible. You can keep helping our children through our ongoing work.</p>
                        <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient w-100">Donate to the general fund</a>
                    <?php endif; ?>
                    <ul class="aside-trust">
                        <li><i class="bi bi-receipt"></i> 80G tax-exemption receipt</li>
                        <li><i class="bi bi-shield-lock"></i> Secure online payment</li>
                        <li><i class="bi bi-heart"></i> Goes straight to the children</li>
                    </ul>
                    <a href="<?= e(url('contact?type=sponsor')) ?>" class="aside-question"><i class="bi bi-chat-dots"></i> Have a question? Talk to us</a>
                </aside>
            </div>
        </div>
    </div>
</section>

<?php if ($others): ?>
<section class="section bg-light">
    <div class="container">
        <div class="section-head" data-reveal>
            <div>
                <span class="eyebrow">Keep exploring</span>
                <h2 class="force-font">Other projects</h2>
            </div>
            <a href="<?= e(url('projects')) ?>" class="link-arrow">All projects <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach (array_slice($others, 0, 3) as $p): ?>
                <div class="col-md-6 col-xl-4"><?php partial('project-tile', ['project' => $p]); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
