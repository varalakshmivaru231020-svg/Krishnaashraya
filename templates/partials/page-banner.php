<?php
/** @var string $heading */
$bannerStyle = !empty($image) ? ' style="background-image:url(\'' . e(img($image)) . '\')"' : '';
?>
<section class="page-banner"<?= $bannerStyle ?>>
    <div class="container">
        <?php if (!empty($crumbs)): ?>
            <div class="breadcrumb-lite">
                <a href="<?= e(url('')) ?>">Home</a>
                <?php foreach ($crumbs as $label => $href): ?>
                    <span class="mx-1">/</span><a href="<?= e(url($href)) ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <h1 class="force-font"><?= e($heading) ?></h1>
        <?php if (!empty($subheading)): ?>
            <p><?= e($subheading) ?></p>
        <?php endif; ?>
    </div>
</section>
