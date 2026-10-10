<?php /** @var ?array $slide @var array $form @var array $errors */ ?>
<a href="<?= e(url('admin/banners')) ?>" class="small d-inline-block mb-3"><i class="bi bi-arrow-left"></i> Back to banners</a>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-xl-7 order-xl-2">
            <div class="card preview-card">
                <div class="card-header d-flex align-items-center">
                    <span><i class="bi bi-display me-1"></i> Live preview</span>
                    <span class="small text-muted fw-normal ms-auto">How it looks on the home page</span>
                </div>
                <div class="hero-preview" id="prevBox" style="background-image:url('<?= e($form['image'] ? img($form['image']) : '') ?>')">
                    <div class="hero-preview-inner">
                        <div class="hero-preview-title" id="prevTitle"><?= nl2br(e($form['title'])) ?></div>
                        <div class="hero-preview-sub" id="prevSub"><?= e($form['subtitle']) ?></div>
                        <div class="hero-preview-btns"><span class="is-solid">Donate Now</span><span>About Krishnashraya</span></div>
                    </div>
                    <div class="hero-preview-empty" id="prevEmpty"<?= $form['image'] ? ' hidden' : '' ?>><i class="bi bi-image"></i> Choose a photo to see the preview</div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 order-xl-1">
            <div class="card">
                <div class="card-header"><?= $slide ? 'Slide details' : 'New slide' ?></div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label" for="image">Photo <?= $slide ? '' : '<span class="text-danger">*</span>' ?></label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/webp"<?= $slide ? '' : ' required' ?>>
                        <div class="form-text">Landscape photo, at least <strong>1600 × 700 px</strong>. JPG, PNG or WebP up to 10 MB — it is resized automatically.<?= $slide ? ' Leave empty to keep the current photo.' : '' ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="title">Heading</label>
                        <textarea class="form-control" id="title" name="title" rows="3" maxlength="255"><?= e($form['title']) ?></textarea>
                        <div class="form-text">Press Enter to start a new line. Keep it short — 2 or 3 lines read best.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="subtitle">Sub-heading</label>
                        <input class="form-control" id="subtitle" name="subtitle" maxlength="255" value="<?= e($form['subtitle']) ?>">
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= $form['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Show this slide on the home page</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> <?= $slide ? 'Save slide' : 'Add slide' ?></button>
                        <a href="<?= e(url('admin/banners')) ?>" class="btn btn-light">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php partial('../admin/banner-preview-js'); ?>
