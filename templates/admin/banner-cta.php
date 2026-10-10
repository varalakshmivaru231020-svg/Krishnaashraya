<?php /** @var string $key @var string $label @var array $cta @var array $defaults @var bool $custom @var array $errors @var string $viewUrl */
$options = photo_focus_options();
?>
<div class="d-flex align-items-center mb-3">
    <a href="<?= e(url('admin/banners#cta')) ?>" class="small"><i class="bi bi-arrow-left"></i> Back to banners</a>
    <a href="<?= e($viewUrl) ?>" target="_blank" class="small ms-auto">View the <?= e($label) ?> <i class="bi bi-box-arrow-up-right"></i></a>
</div>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-xl-7 order-xl-2">
            <div class="card preview-card">
                <div class="card-header d-flex align-items-center">
                    <span><i class="bi bi-display me-1"></i> Live preview</span>
                    <span class="small text-muted fw-normal ms-auto">Same shape as on the website</span>
                </div>
                <div class="cta-preview" id="ctaBox">
                    <img id="ctaImg" src="<?= e(img($cta['image'])) ?>" alt="" style="object-position: <?= e($cta['position']) ?>">
                    <div class="cta-preview-body">
                        <div class="cta-preview-eyebrow" id="pvEyebrow"><?= e($cta['eyebrow']) ?></div>
                        <div class="cta-preview-title" id="pvHeading"><?= e($cta['heading']) ?></div>
                        <div class="cta-preview-text" id="pvText"><?= e($cta['text']) ?></div>
                        <div class="hero-preview-btns justify-content-center"><span class="is-solid">Donate</span><span>Contact</span></div>
                    </div>
                </div>
                <div class="card-body small text-muted">
                    <i class="bi bi-info-circle"></i> The panel is wide and short, so only a band of the photo shows. Use <strong>Which part shows</strong> to keep people's faces in view.
                </div>
            </div>
        </div>

        <div class="col-xl-5 order-xl-1">
            <div class="card">
                <div class="card-header"><?= e($label) ?> panel</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="image">Photo</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">A wide, landscape photo works best (at least 1600 × 600 px). Leave empty to keep the current photo.</div>
                        <?php if ($custom): ?>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="reset_image" id="reset_image" data-original="<?= e(img($defaults['image'])) ?>">
                                <label class="form-check-label small" for="reset_image">Go back to the original photo</label>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-4">
                        <span class="form-label d-block">Which part of the photo shows</span>
                        <div class="focus-picker" role="radiogroup" aria-label="Which part of the photo shows">
                            <?php foreach ($options as $val => [$name, $pos]): ?>
                                <label><input type="radio" name="focus" value="<?= e($val) ?>" data-pos="<?= e($pos) ?>" <?= $cta['focus'] === $val ? 'checked' : '' ?>><span><i class="bi bi-<?= ['top' => 'align-top', 'upper' => 'chevron-double-up', 'center' => 'align-middle', 'lower' => 'chevron-double-down', 'bottom' => 'align-bottom'][$val] ?>"></i><?= e($name) ?></span></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="eyebrow">Small label <span class="opt">optional</span></label>
                        <input class="form-control" id="eyebrow" name="eyebrow" maxlength="60" value="<?= e($cta['eyebrow']) ?>" placeholder="<?= e($defaults['eyebrow']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="heading">Heading</label>
                        <input class="form-control" id="heading" name="heading" maxlength="120" value="<?= e($cta['heading']) ?>" placeholder="<?= e($defaults['heading']) ?>">
                        <div class="form-text">Keep it short so it fits on one line on computers.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="text">Text</label>
                        <textarea class="form-control" id="text" name="text" rows="3" maxlength="300" placeholder="<?= e($defaults['text']) ?>"><?= e($cta['text']) ?></textarea>
                        <div class="form-text">Leave a box empty to use the original wording. The buttons stay the same.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save panel</button>
                        <a href="<?= e(url('admin/banners#cta')) ?>" class="btn btn-light">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    (function () {
        var img = document.getElementById('ctaImg'), current = img.src;
        var fallback = <?= json_encode(['eyebrow' => $defaults['eyebrow'], 'heading' => $defaults['heading'], 'text' => $defaults['text']], JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
        ['eyebrow', 'heading', 'text'].forEach(function (k) {
            var input = document.getElementById(k), out = document.getElementById('pv' + k.charAt(0).toUpperCase() + k.slice(1));
            input.addEventListener('input', function () { out.textContent = input.value.trim() || fallback[k]; });
        });
        document.querySelectorAll('[name="focus"]').forEach(function (r) {
            r.addEventListener('change', function () { img.style.objectPosition = r.getAttribute('data-pos'); });
        });
        var photo = document.getElementById('image'), reset = document.getElementById('reset_image');
        photo.addEventListener('change', function () {
            img.src = photo.files[0] ? URL.createObjectURL(photo.files[0]) : current;
            if (reset) reset.checked = false;
        });
        if (reset) reset.addEventListener('change', function () { img.src = reset.checked ? reset.getAttribute('data-original') : current; });
    })();
</script>
