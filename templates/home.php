<?php
/** @var array $projects @var array $testimonials @var array $categories @var array $slides @var array $impact */
?>
<?php if ($slides): ?>
<div id="heroCarousel" class="carousel slide carousel-fade hero-slider" data-bs-ride="carousel" data-bs-interval="6500" data-bs-pause="false"<?= count($slides) === 1 ? ' data-single' : '' ?>>
    <?php if (count($slides) > 1): ?>
        <div class="carousel-indicators">
            <?php foreach ($slides as $i => $slide): ?>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>"<?= $i === 0 ? ' class="active" aria-current="true"' : '' ?> aria-label="Slide <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="carousel-inner">
        <?php foreach ($slides as $i => $slide):
            $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $slide['title'])), 'strlen'));
            $tag = $i === 0 ? 'h1' : 'div'; ?>
            <?php $isVideo = ($slide['media_type'] ?? 'image') === 'video' && $slide['video']; ?>
            <div class="carousel-item<?= $i === 0 ? ' active' : '' ?><?= $isVideo ? ' is-video' : '' ?>">
                <?php if ($isVideo): ?>
                    <video class="d-block w-100 hero-video" muted playsinline preload="<?= $i === 0 ? 'auto' : 'metadata' ?>"<?php if ($slide['image']): ?> poster="<?= e(img($slide['image'])) ?>"<?php endif; ?> aria-label="<?= e(implode(' ', $lines) ?: 'Video from Krishnashraya') ?>">
                        <source src="<?= e(url($slide['video'])) ?>" type="video/<?= str_ends_with($slide['video'], '.webm') ? 'webm' : 'mp4' ?>">
                    </video>
                <?php else: ?>
                    <img src="<?= e(img($slide['image'])) ?>" class="d-block w-100" alt="<?= e(implode(' ', $lines) ?: 'Children at Krishnashraya') ?>"<?= $i ? ' loading="lazy"' : '' ?>>
                <?php endif; ?>
                <div class="carousel-overlay">
                    <div class="container carousel-content">
                        <p class="hero-kicker"><span></span><?= e(setting('site_name', 'Sri Krishnashraya Educational Trust')) ?></p>
                        <?php if ($lines): ?>
                            <<?= $tag ?> class="hero-title force-font">
                                <?php foreach ($lines as $n => $line): ?>
                                    <span class="hero-line" style="--i: <?= $n ?>"><?= e($line) ?></span>
                                <?php endforeach; ?>
                            </<?= $tag ?>>
                        <?php endif; ?>
                        <?php if ($slide['subtitle']): ?>
                            <p class="hero-subtitle" style="--i: <?= count($lines) ?>"><?= e($slide['subtitle']) ?></p>
                        <?php endif; ?>
                        <div class="hero-buttons" style="--i: <?= count($lines) + 1 ?>">
                            <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient">Donate Now</a>
                            <a href="<?= e(url('about')) ?>" class="btn-outline-gradient">About Krishnashraya</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php if (array_filter($slides, fn($s) => ($s['media_type'] ?? '') === 'video' && $s['video'])): ?>
<script>
    // Video slides: hold the slider while a video plays, then move on when it ends.
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('heroCarousel');
        if (!el || !window.bootstrap) return;
        var carousel = bootstrap.Carousel.getOrCreateInstance(el);
        var single = el.hasAttribute('data-single');
        var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function start(item) {
            el.querySelectorAll('.hero-video').forEach(function (v) { if (!item.contains(v)) v.pause(); });
            var v = item.querySelector('.hero-video');
            if (!v) { carousel.cycle(); return; }
            if (calm && v.poster) { carousel.cycle(); return; }   // still cover photo only
            carousel.pause();
            v.loop = single;
            v.currentTime = 0;
            var p = v.play();
            if (p && p.catch) p.catch(function () { carousel.cycle(); });
        }
        el.querySelectorAll('.hero-video').forEach(function (v) {
            v.addEventListener('ended', function () { if (!single) { carousel.next(); carousel.cycle(); } });
            v.addEventListener('error', function () { carousel.cycle(); }, true);
        });
        el.addEventListener('slid.bs.carousel', function (e) { start(e.relatedTarget); });
        start(el.querySelector('.carousel-item.active'));
    });
</script>
<?php endif; ?>
<?php endif; ?>

<section id="about" class="section">
    <div class="container">
        <div class="text-center">
            <?php if (about('about_badge')): ?><span class="section-badge slide-up"><?= e(about('about_badge')) ?></span><?php endif; ?>
            <h2 class="section-title slide-up force-font"><?= e(about('about_title')) ?></h2>
            <?php if (about('about_subtitle')): ?><p class="section-subtitle slide-up"><?= e(about('about_subtitle')) ?></p><?php endif; ?>
        </div>
        <?php partial('about-intro'); ?>
    </div>
</section>

<?php if ($projects): ?>
<section id="projects" class="section projects-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title force-font">On-Going Projects</h2>
            <p class="section-subtitle">Making a real difference in the lives of underprivileged children</p>
        </div>
        <?php foreach ($projects as $project): ?>
            <?php partial('project-card', ['project' => $project]); ?>
        <?php endforeach; ?>
        <div class="text-center mt-5">
            <a href="<?= e(url('projects')) ?>" class="btn-outline-gradient">View All Projects</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($impact): ?>
<section id="impact" class="section impact22-section has-blobs">
    <div class="container">
        <h2 class="stats-heading11"><?= e(setting('impact_heading', 'Your Contribution Creates Measurable Change')) ?></h2>
        <div class="row justify-content-center">
            <?php foreach ($impact as $stat): ?>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="stat-card11">
                        <div class="stat-icon11"><i class="bi bi-<?= e($stat['icon']) ?>"></i></div>
                        <div class="stat-number11" data-target="<?= (int) $stat['value'] ?>" data-prefix="<?= e($stat['prefix']) ?>" data-suffix="<?= e($stat['suffix']) ?>"><?= e(($stat['prefix'] ?? '') . '0') ?></div>
                        <div class="stat-label11"><?= e($stat['label']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient btn-pulse js-donate">Donate Now</a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php partial('marquee'); ?>

<?php partial('vision-mission'); ?>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <?php foreach ([
                ['Food', 'Our mission is to give nutritious food to all underprivileged children.', 'assets/images/our-activities/food-and-nutrition/food-and-nutrition-img-9.webp'],
                ['Shelter', 'To give shelter to all homeless children.', 'assets/images/our-activities/community-support/community-support-img-5.webp'],
                ['Education', 'To provide quality education to underprivileged children.', 'assets/images/our-activities/education-programs/education-programs-img-7.webp'],
            ] as [$focusTitle, $focusText, $focusImg]): ?>
                <div class="col-md-4">
                    <div class="focus-card slide-up">
                        <img src="<?= e(url($focusImg)) ?>" class="focus-img" alt="<?= e($focusTitle) ?>" loading="lazy">
                        <div class="focus-overlay">
                            <h3 class="card-title"><?= e($focusTitle) ?></h3>
                            <p class="card-text"><?= e($focusText) ?></p>
                            <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient btn-pulse js-donate">Donate Now</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section volunteer-section">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge slide-up">Get Involved</span>
            <h2 class="section-title slide-up force-font">Be the Reason Someone Smiles Today</h2>
            <p class="section-subtitle slide-up">Your support can transform lives and create lasting change in our community</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="focus-card slide-up">
                    <img src="<?= e(url('assets/images/our-activities/extracurricular-activities/extracurricular-activities-img-7.webp')) ?>" class="focus-img" alt="Donate" loading="lazy">
                    <div class="focus-overlay">
                        <h3 class="card-title">Donate</h3>
                        <p class="card-text">Your financial support helps us provide food, shelter, and education to children in need.</p>
                        <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient btn-pulse js-donate">Donate Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="focus-card slide-up">
                    <img src="<?= e(url('assets/images/our-activities/donor-testimonials/donor-testimonials-img-9.webp')) ?>" class="focus-img" alt="Volunteer" loading="lazy">
                    <div class="focus-overlay">
                        <h3 class="card-title">Volunteer</h3>
                        <p class="card-text">Join our team of dedicated volunteers and make a direct impact in children's lives.</p>
                        <a href="<?= e(url('contact?type=volunteer')) ?>" class="btn-primary-gradient btn-pulse">Become a Volunteer</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="focus-card slide-up">
                    <img src="<?= e(url('assets/images/our-activities/education-programs/education-programs-img-10.webp')) ?>" class="focus-img" alt="Sponsor a child" loading="lazy">
                    <div class="focus-overlay">
                        <h3 class="card-title">Sponsor a Child</h3>
                        <p class="card-text">Create a lasting bond by sponsoring a child's education, nutrition, and overall development.</p>
                        <a href="<?= e(url('contact?type=sponsor')) ?>" class="btn-primary-gradient btn-pulse">Sponsor a Child</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($testimonials): ?>
<section class="section has-blobs">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge slide-up">Testimonials</span>
            <h2 class="section-title slide-up force-font">Voices of Change</h2>
            <p class="section-subtitle slide-up">Hear from those whose lives have been touched by Krishnashraya</p>
        </div>
        <div class="row g-4">
            <?php foreach ($testimonials as $t): ?>
                <div class="col-md-4">
                    <div class="testimonial-card slide-up">
                        <i class="bi bi-quote quote-icon"></i>
                        <p class="testimonial-text">"<?= e($t['message']) ?>"</p>
                        <div class="testimonial-author">
                            <?php if ($t['photo']): ?>
                                <img class="author-avatar" src="<?= e(img($t['photo'])) ?>" alt="<?= e($t['name']) ?>" style="object-fit:cover">
                            <?php else: ?>
                                <div class="author-avatar"><?= e(mb_strtoupper(mb_substr($t['name'], 0, 1))) ?></div>
                            <?php endif; ?>
                            <div class="author-info">
                                <h5><?= e($t['name']) ?></h5>
                                <p><?= e($t['role']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient btn-pulse js-donate">Donate Now</a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section gallery-section" id="activities">
    <div class="container">
        <div class="text-center mb-5">
            <span class="section-badge slide-up">Gallery</span>
            <h2 class="section-title slide-up force-font">Our Activities &amp; Moments</h2>
            <p class="section-subtitle slide-up">Capturing the joy, learning, and growth of our children</p>
        </div>
        <div class="row g-4">
            <?php foreach (array_slice($categories, 0, 6) as $c): ?>
                <div class="col-md-4">
                    <a href="<?= e(url($c['slug'])) ?>" class="gallery-item slide-up d-block">
                        <img src="<?= e(img($c['cover_image'])) ?>" alt="<?= e($c['title']) ?>" loading="lazy">
                        <div class="gallery-overlay"><h5><?= e($c['title']) ?></h5></div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5">
            <a href="<?= e(url('our-activities')) ?>" class="btn-primary-gradient btn-pulse">View All Activities</a>
        </div>
    </div>
</section>

<section class="section cta-section">
    <div class="container">
        <div class="cta-content text-center">
            <h2 class="cta-main-heading force-font">Join Hands With Sri Krishnashraya Educational Trust To Raise Funds For Education &amp; Training</h2>
            <p class="cta-subheading">
                Sri Krishnashraya Educational Trust based in Bangalore is committed to impacting and improving the lives of children. To take our objective a step further, we have begun a funding campaign to raise funds for education.
            </p>
            <div class="cta-inner-box">
                <p class="cta-inner-text">
                    To meet our objective of transforming lives, we cannot do that without you. Join us in helping many with your generosity and financial support.
                </p>
                <div>
                    <a class="btn-white js-donate" href="<?= e(url('donate')) ?>">Donate Now</a>
                    <a class="btn-outline-white mt-4 mt-md-0 d-inline-block" href="<?= e(url('contact?type=volunteer')) ?>">Become a Volunteer</a>
                </div>
            </div>
            <div class="benefit-items">
                <div class="benefit-item">
                    <div class="benefit-icon is-icon"><i class="bi bi-receipt-cutoff"></i></div>
                    <h4 class="benefit-title">80G Tax Benefits</h4>
                    <p class="benefit-text">Get up to 50% tax exemption on donations</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon is-icon"><i class="bi bi-bullseye"></i></div>
                    <h4 class="benefit-title">Direct Impact</h4>
                    <p class="benefit-text">Your support directly helps children's lives</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon is-icon"><i class="bi bi-patch-check"></i></div>
                    <h4 class="benefit-title">Full Transparency</h4>
                    <p class="benefit-text">Regular updates on how funds are utilized</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="donate" class="section contact-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title force-font">Support Education &amp; Shelter</h2>
            <p class="section-subtitle">We'd love to hear from you. Reach out to us for any queries or support.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="contact-card">
                    <h3 class="footer-title mb-4">Contact Information</h3>
                    <?php partial('contact-info'); ?>
                    <div class="note-box">
                        <p><i class="bi bi-info-circle-fill"></i> You are welcome to visit us and support in any way you can.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="contact-card from-right">
                    <?php partial('bank-details'); ?>
                </div>
            </div>
        </div>
    </div>
</section>
