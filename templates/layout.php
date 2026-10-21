<?php
/** @var string $content */
$siteName = setting('site_name', 'Sri Krishnashraya Educational Trust');
// Search-engine data: Admin -> SEO overrides, otherwise built from the page itself (app/seo.php).
$seo = seo_for_path(current_path());
$pageTitle = $seo['final_title'] ?? (isset($title) && $title !== '' ? seo_title_with_site($title) : $siteName);
$metaDescription = $seo['final_description'] ?? seo_clip($description ?? setting('footer_about'));
$shareImage = site_origin() . img($seo['image'] ?? 'assets/images/slider/slider-1.webp');
$noIndex = http_response_code() === 404 || str_starts_with(current_path(), 'donate/');
$categories = nav_categories();
$projectsNav = nav_projects();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="base-url" content="<?= e(rtrim((string) config('app.base_url', ''), '/')) ?>">
    <title><?= e($pageTitle) ?></title>
    <?php if ($noIndex): ?>
        <meta name="robots" content="noindex">
    <?php elseif ($seo): ?>
        <link rel="canonical" href="<?= e($seo['url']) ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:image" content="<?= e($shareImage) ?>">
    <?php if ($seo): ?><meta property="og:url" content="<?= e($seo['url']) ?>"><?php endif; ?>
    <meta property="og:locale" content="en_IN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#ff6b00">
<?php partial('favicon'); ?>
    <?php if (current_path() === ''): ?>
        <script type="application/ld+json"><?= seo_organisation_jsonld() ?></script>
    <?php endif; ?>
    <?php if (setting('gsc_verification')): ?>
        <meta name="google-site-verification" content="<?= e(setting('gsc_verification')) ?>">
    <?php endif; ?>
    <?php $gaId = setting('ga_measurement_id'); if ($gaId && !is_local_site()): /* no tracking on a local copy, so test visits don't count */ ?>
        <?php if (str_starts_with($gaId, 'GTM-')): ?>
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',<?= json_encode($gaId, JSON_HEX_TAG) ?>);</script>
        <?php else: ?>
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(rawurlencode($gaId)) ?>"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', <?= json_encode($gaId, JSON_HEX_TAG) ?>);
            </script>
        <?php endif; ?>
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?= font_tags() ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="<?= e(asset('assets/css/bootstrap.min.css')) ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('assets/css/styles.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/site.css')) ?>">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-main sticky-top" id="mainNavbar">
    <div class="container">
        <a href="<?= e(url('')) ?>" class="navbar-brand-custom">
            <img src="<?= e(url('assets/images/sket-logo-web.webp')) ?>" alt="<?= e($siteName) ?>">
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link<?= nav_active('') ?>" href="<?= e(url('')) ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= nav_active('about') ?>" href="<?= e(url('about')) ?>">About Us</a>
                </li>

                <li class="nav-item dropdown nav-split">
                    <div class="nav-split-row">
                        <a class="nav-link<?= nav_active('projects') ?>" href="<?= e(url('projects')) ?>">Our Projects</a>
                        <?php if ($projectsNav): ?>
                            <button class="nav-split-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Show projects"><i class="bi bi-chevron-down"></i></button>
                        <?php endif; ?>
                    </div>
                    <?php if ($projectsNav): ?>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?= e(url('projects')) ?>">All Projects</a></li>
                            <?php foreach ($projectsNav as $p): ?>
                                <li><a class="dropdown-item" href="<?= e(url('projects/' . $p['slug'])) ?>"><?= e(mb_strimwidth($p['title'], 0, 48, '…')) ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>

                <li class="nav-item dropdown nav-split">
                    <?php $activityActive = nav_active('our-activities') ?: (in_array(current_path(), array_column($categories, 'slug'), true) ? ' active' : ''); ?>
                    <div class="nav-split-row">
                        <a class="nav-link<?= $activityActive ?>" href="<?= e(url('our-activities')) ?>">Our Activities</a>
                        <button class="nav-split-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Show activities"><i class="bi bi-chevron-down"></i></button>
                    </div>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= e(url('our-activities')) ?>">All Activities</a></li>
                        <?php foreach ($categories as $c): ?>
                            <li><a class="dropdown-item" href="<?= e(url($c['slug'])) ?>"><?= e($c['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link<?= nav_active('contact') ?>" href="<?= e(url('contact')) ?>">Contact Us</a>
                </li>
                <li class="nav-item ms-lg-3">
                    <a href="<?= e(url('donate')) ?>" class="btn-primary-gradient btn-pulse js-donate">Donate Now</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?= $content ?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= e(url('')) ?>" class="footer-logo-chip"><img src="<?= e(url('assets/images/sket-logo-web.webp')) ?>" alt="<?= e($siteName) ?>"></a>
                <p><?= e(setting('footer_about')) ?></p>
                <div class="footer-badges">
                    <span><i class="bi bi-patch-check-fill"></i> 80G Certified</span>
                    <span><i class="bi bi-patch-check-fill"></i> 12A Registered</span>
                </div>
                <?php if ($socials = social_links()): ?>
                    <div class="footer-social">
                        <?php foreach ($socials as $network => $link): ?>
                            <a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($network)) ?>"><i class="bi bi-<?= e($network === 'twitter' ? 'twitter-x' : $network) ?>"></i></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h3 class="footer-heading">Quick Links</h3>
                <ul class="footer-nav">
                    <li><a href="<?= e(url('')) ?>">Home</a></li>
                    <li><a href="<?= e(url('about')) ?>">About Us</a></li>
                    <li><a href="<?= e(url('projects')) ?>">Our Projects</a></li>
                    <li><a href="<?= e(url('our-activities')) ?>">Our Activities</a></li>
                    <li><a href="<?= e(url('donate')) ?>">Donate</a></li>
                    <li><a href="<?= e(url('contact')) ?>">Contact Us</a></li>
                </ul>
            </div>

            <div>
                <h3 class="footer-heading">Our Activities</h3>
                <ul class="footer-nav">
                    <?php foreach ($categories as $c): ?>
                        <li><a href="<?= e(url($c['slug'])) ?>"><?= e($c['title']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div>
                <h3 class="footer-heading">Get in Touch</h3>
                <ul class="footer-contact-list">
                    <li>
                        <i class="bi bi-geo-alt"></i>
                        <span><strong>Registered Office</strong><?= nl2br(e(setting('address_registered'))) ?></span>
                    </li>
                    <?php if (setting('address_center')): ?>
                        <li>
                            <i class="bi bi-house-heart"></i>
                            <span><strong>Hoskote Center</strong><?= nl2br(e(setting('address_center'))) ?></span>
                        </li>
                    <?php endif; ?>
                    <li>
                        <i class="bi bi-telephone"></i>
                        <span><?php foreach (array_filter([setting('phone_1'), setting('phone_2')]) as $ph): ?><a href="<?= e(tel_href($ph)) ?>"><?= e($ph) ?></a><br><?php endforeach; ?></span>
                    </li>
                    <li>
                        <i class="bi bi-envelope"></i>
                        <span><?php foreach (array_filter([setting('email_1'), setting('email_2')]) as $mail): ?><a href="mailto:<?= e($mail) ?>"><?= e($mail) ?></a><br><?php endforeach; ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bar">
            <p>&copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.</p>
            <p>Made with <i class="bi bi-heart-fill"></i> for the children of Krishnashraya</p>
        </div>
    </div>
</footer>

<div class="back-to-top" id="backToTop" role="button" aria-label="Back to top">
    <i class="bi bi-arrow-up"></i>
</div>

<?php if ($waNumber = preg_replace('/\D/', '', setting('whatsapp_number'))): ?>
    <a href="https://wa.me/<?= e($waNumber) ?>?text=<?= rawurlencode('Hello ' . $siteName . ', I would like to know more.') ?>" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp" title="Chat with us on WhatsApp">
        <i class="bi bi-whatsapp"></i>
    </a>
<?php endif; ?>

<?php partial('site-popup'); ?>

<script src="<?= e(asset('assets/js/bootstrap.bundle.min.js')) ?>"></script>
<script src="https://unpkg.com/sweetalert@2.1.2/dist/sweetalert.min.js"></script>
<script src="<?= e(asset('assets/js/main.js')) ?>"></script>
<?php if (current_path() === 'donate'): ?>
    <?php if (active_gateway() === 'cashfree'): ?>
        <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
    <?php else: ?>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <?php endif; ?>
    <script src="<?= e(asset('assets/js/donate.js')) ?>"></script>
<?php endif; ?>
<?php foreach (flashes() as $f): ?>
    <script>swal(<?= json_encode($f['type'] === 'success' ? 'Thank You!' : 'Please check') ?>, <?= json_encode($f['message']) ?>, <?= json_encode($f['type'] === 'success' ? 'success' : 'error') ?>);</script>
<?php endforeach; ?>
<?php if (!empty($scripts)) echo $scripts; ?>
</body>
</html>
