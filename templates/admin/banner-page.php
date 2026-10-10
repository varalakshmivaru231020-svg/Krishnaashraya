<?php /** @var string $key @var string $label @var array $banner @var array $defaults @var bool $custom @var array $errors @var string $viewUrl */ ?>
<div class="d-flex align-items-center mb-3">
    <a href="<?= e(url('admin/banners#pages')) ?>" class="small"><i class="bi bi-arrow-left"></i> Back to banners</a>
    <a href="<?= e($viewUrl) ?>" target="_blank" class="small ms-auto">View the <?= e($label) ?> page <i class="bi bi-box-arrow-up-right"></i></a>
</div>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-xl-7 order-xl-2">
            <div class="card preview-card">
                <div class="card-header d-flex align-items-center">
                    <span><i class="bi bi-display me-1"></i> Live preview</span>
                    <span class="small text-muted fw-normal ms-auto">Top of the <?= e($label) ?> page</span>
                </div>
                <div class="hero-preview is-page" id="prevBox" style="background-image:url('<?= e(img($banner['image'])) ?>')">
                    <div class="hero-preview-inner">
                        <div class="hero-preview-crumb">Home / <?= e($label) ?></div>
                        <div class="hero-preview-title" id="prevTitle"><?= e($banner['title']) ?></div>
                        <div class="hero-preview-sub" id="prevSub"><?= e($banner['subtitle']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 order-xl-1">
            <div class="card">
                <div class="card-header"><?= e($label) ?> page banner</div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label" for="image">Photo</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Landscape photo, at least <strong>1600 × 500 px</strong>. Leave empty to keep the current photo.</div>
                        <?php if ($custom): ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="reset_image" id="reset_image" data-original="<?= e(img($defaults['image'])) ?>">
                                <label class="form-check-label small" for="reset_image">Go back to the original photo</label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="title">Heading</label>
                        <input class="form-control" id="title" name="title" maxlength="150" value="<?= e($banner['title']) ?>" placeholder="<?= e($defaults['title']) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="subtitle">Sub-heading</label>
                        <textarea class="form-control" id="subtitle" name="subtitle" rows="2" maxlength="300" placeholder="<?= e($defaults['subtitle']) ?>"><?= e($banner['subtitle']) ?></textarea>
                        <div class="form-text">Leave a box empty to use the original wording.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save banner</button>
                        <a href="<?= e(url('admin/banners#pages')) ?>" class="btn btn-light">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php partial('../admin/banner-preview-js', ['defaults' => $defaults]); ?>
