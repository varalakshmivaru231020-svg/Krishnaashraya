<?php
$banner = page_banner('contact');
partial('page-banner', [
    'heading'    => $banner['title'],
    'subheading' => $banner['subtitle'],
    'image'      => $banner['image'],
    'crumbs'     => ['Contact Us' => 'contact'],
]);

// Admin can paste an exact embed link in Site Settings; otherwise the map is found from the address.
$mapAddress = preg_replace('/\s+/', ' ', setting('address_center') ?: setting('address_registered'));
$mapSrc = setting('maps_embed') ?: 'https://maps.google.com/maps?q=' . rawurlencode($mapAddress) . '&z=15&output=embed';
?>

<section class="section" id="contact">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="contact-card slide-up">
                    <h2 class="h3 mb-4">Contact Information</h2>
                    <?php partial('contact-info'); ?>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="contact-card slide-up">
                    <?php partial('contact-form'); ?>
                </div>
            </div>
        </div>

        <div class="map-card mt-4 slide-up">
            <div class="map-card-head">
                <div>
                    <h2 class="h5 mb-1"><i class="bi bi-geo-alt-fill"></i> Find us</h2>
                    <p class="mb-0"><?= e($mapAddress) ?></p>
                </div>
                <?php if (setting('maps_url')): ?>
                    <a href="<?= e(setting('maps_url')) ?>" target="_blank" rel="noopener" class="btn-outline-gradient">Get Directions</a>
                <?php endif; ?>
            </div>
            <div class="map-embed">
                <iframe src="<?= e($mapSrc) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map showing <?= e($mapAddress) ?>" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</section>
