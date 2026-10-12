<?php /** @var ?array $slide @var array $form @var array $errors */
$isVideo = ($form['media_type'] ?? 'image') === 'video';
$maxMb = (int) round(video_upload_limit() / 1024 ** 2);
?>
<a href="<?= e(url('admin/banners')) ?>" class="small d-inline-block mb-3"><i class="bi bi-arrow-left"></i> Back to banners</a>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<form method="post" enctype="multipart/form-data" id="slideForm">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-xl-7 order-xl-2">
            <div class="card preview-card">
                <div class="card-header d-flex align-items-center">
                    <span><i class="bi bi-display me-1"></i> Live preview</span>
                    <span class="small text-muted fw-normal ms-auto">How it looks on the home page</span>
                </div>
                <div class="hero-preview is-slide" id="prevBox" style="background-image:url('<?= e($form['image'] ? img($form['image']) : '') ?>')">
                    <video class="hero-preview-video" id="prevVideo" muted loop playsinline autoplay
                        <?= $isVideo && $form['video'] ? 'src="' . e(url($form['video'])) . '"' : 'hidden' ?>></video>
                    <div class="hero-preview-inner">
                        <div class="hero-preview-title" id="prevTitle"><?= nl2br(e($form['title'])) ?></div>
                        <div class="hero-preview-sub" id="prevSub"><?= e($form['subtitle']) ?></div>
                        <div class="hero-preview-btns"><span class="is-solid">Donate Now</span><span>About Krishnashraya</span></div>
                    </div>
                    <div class="hero-preview-empty" id="prevEmpty"<?= ($isVideo ? $form['video'] : $form['image']) ? ' hidden' : '' ?>>
                        <i class="bi bi-<?= $isVideo ? 'camera-video' : 'image' ?>"></i> <span><?= $isVideo ? 'Choose a video to see the preview' : 'Choose a photo to see the preview' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 order-xl-1">
            <div class="card">
                <div class="card-header"><?= $slide ? 'Slide details' : 'New slide' ?></div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label d-block">Slide shows</label>
                        <div class="media-switch" role="radiogroup" aria-label="Slide shows">
                            <input type="radio" class="btn-check" name="media_type" id="mt_image" value="image"<?= $isVideo ? '' : ' checked' ?>>
                            <label for="mt_image"><i class="bi bi-image"></i> Photo</label>
                            <input type="radio" class="btn-check" name="media_type" id="mt_video" value="video"<?= $isVideo ? ' checked' : '' ?>>
                            <label for="mt_video"><i class="bi bi-camera-video"></i> Video</label>
                        </div>
                    </div>

                    <div class="mb-4" data-show="video"<?= $isVideo ? '' : ' hidden' ?>>
                        <label class="form-label" for="video">Video <?= $isVideo && $form['video'] ? '' : '<span class="text-danger">*</span>' ?></label>
                        <?php if ($form['video']): ?>
                            <div class="current-file"><i class="bi bi-film"></i> <span class="text-truncate"><?= e(basename($form['video'])) ?></span>
                                <?php if (is_file(ROOT . '/' . $form['video'])): ?><span class="text-muted ms-auto"><?= e(number_format(filesize(ROOT . '/' . $form['video']) / 1024 ** 2, 1)) ?> MB</span><?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="video" name="video" accept="video/mp4,video/webm" data-max="<?= video_upload_limit() ?>">
                        <div class="form-text">MP4 (or WebM), landscape, up to <strong><?= $maxMb ?> MB</strong>. A short 10–30 second clip works best. It plays without sound, then the slider moves to the next slide.<?= $form['video'] ? ' Leave empty to keep the current video.' : '' ?></div>
                        <div class="invalid-feedback d-block" id="videoTooBig" hidden></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="image">
                            <span data-show="image"<?= $isVideo ? ' hidden' : '' ?>>Photo <?= $form['image'] ? '' : '<span class="text-danger">*</span>' ?></span>
                            <span data-show="video"<?= $isVideo ? '' : ' hidden' ?>>Cover photo <span class="text-muted fw-normal">(optional)</span></span>
                        </label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">
                            <span data-show="image"<?= $isVideo ? ' hidden' : '' ?>>Landscape photo, at least <strong>1600 × 700 px</strong>. JPG, PNG or WebP up to 10 MB — it is resized automatically.<?= $form['image'] ? ' Leave empty to keep the current photo.' : '' ?></span>
                            <span data-show="video"<?= $isVideo ? '' : ' hidden' ?>>Shown while the video loads, on slow connections and to visitors who turn off motion. A still from the video is ideal.</span>
                        </div>
                        <?php if ($slide && $form['image']): ?>
                            <div class="form-check mt-2" data-show="video"<?= $isVideo ? '' : ' hidden' ?>>
                                <input class="form-check-input" type="checkbox" id="remove_cover" name="remove_cover">
                                <label class="form-check-label small" for="remove_cover">Remove the current cover photo</label>
                            </div>
                        <?php endif; ?>
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
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-primary" id="slideSave"><i class="bi bi-check-lg"></i> <?= $slide ? 'Save slide' : 'Add slide' ?></button>
                        <a href="<?= e(url('admin/banners')) ?>" class="btn btn-light">Cancel</a>
                        <span class="small text-muted ms-1" id="slideUploading" hidden><span class="spinner-border spinner-border-sm me-1"></span> Uploading — please keep this page open…</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php partial('../admin/banner-preview-js'); ?>
<script>
    // Photo / Video switch, the video preview and a size check before uploading.
    (function () {
        var form = document.getElementById('slideForm');
        var box = document.getElementById('prevBox');
        var vid = document.getElementById('prevVideo');
        var empty = document.getElementById('prevEmpty');
        var input = document.getElementById('video');
        var tooBig = document.getElementById('videoTooBig');
        var savedVideo = vid.getAttribute('src');
        var hasPhoto = <?= $form['image'] ? 'true' : 'false' ?>;

        function mode() { return document.getElementById('mt_video').checked ? 'video' : 'image'; }
        function refresh() {
            var m = mode();
            document.querySelectorAll('[data-show]').forEach(function (el) { el.hidden = el.getAttribute('data-show') !== m; });
            var hasVideo = !!vid.getAttribute('src');
            vid.hidden = m !== 'video' || !hasVideo;
            if (m === 'video' && hasVideo) { vid.play().catch(function () {}); } else { vid.pause(); }
            var photoChosen = hasPhoto || (document.getElementById('image').files.length > 0);
            empty.hidden = m === 'video' ? hasVideo : photoChosen;
            empty.querySelector('span').textContent = m === 'video' ? 'Choose a video to see the preview' : 'Choose a photo to see the preview';
            empty.querySelector('i').className = 'bi bi-' + (m === 'video' ? 'camera-video' : 'image');
        }
        document.querySelectorAll('input[name="media_type"]').forEach(function (r) { r.addEventListener('change', refresh); });
        document.getElementById('image').addEventListener('change', refresh);

        input.addEventListener('change', function () {
            var f = input.files[0];
            tooBig.hidden = true;
            if (!f) { savedVideo ? vid.setAttribute('src', savedVideo) : vid.removeAttribute('src'); refresh(); return; }
            if (f.size > +input.getAttribute('data-max')) {
                tooBig.textContent = 'This video is ' + (f.size / 1048576).toFixed(1) + ' MB — the limit is <?= $maxMb ?> MB. Please shorten or compress it first.';
                tooBig.hidden = false;
                input.value = '';
                return;
            }
            vid.src = URL.createObjectURL(f);
            refresh();
        });

        form.addEventListener('submit', function () {
            if (input.files.length || document.getElementById('image').files.length) {
                document.getElementById('slideUploading').hidden = false;
                document.getElementById('slideSave').disabled = true;
            }
        });
        refresh();
    })();
</script>
