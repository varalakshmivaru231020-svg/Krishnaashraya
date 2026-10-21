<?php /** @var array $slides @var array $pages @var array $ctas */
$visible = count(array_filter($slides, fn($s) => $s['is_active']));
$processing = count(array_filter($slides, fn($s) => ($s['video_status'] ?? '') === 'processing'));
?>
<ul class="nav admin-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-slides" data-bs-toggle="tab" data-bs-target="#pane-slides" type="button" role="tab" aria-controls="pane-slides" aria-selected="true">
            <i class="bi bi-collection-play"></i> Home page slider <span class="tab-count"><?= count($slides) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-pages" data-bs-toggle="tab" data-bs-target="#pane-pages" type="button" role="tab" aria-controls="pane-pages" aria-selected="false">
            <i class="bi bi-window"></i> Page banners <span class="tab-count"><?= count($pages) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-cta" data-bs-toggle="tab" data-bs-target="#pane-cta" type="button" role="tab" aria-controls="pane-cta" aria-selected="false">
            <i class="bi bi-megaphone"></i> CTA panels <span class="tab-count"><?= count($ctas) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- ============================================ Home page slider -->
    <div class="tab-pane fade show active" id="pane-slides" role="tabpanel" aria-labelledby="tab-slides" tabindex="0">
        <div class="card">
            <div class="card-header d-flex flex-wrap align-items-center gap-2">
                <div>
                    <div>Home page slider</div>
                    <div class="small text-muted fw-normal"><?= $visible ?> of <?= count($slides) ?> slides showing · photos change every 6 seconds, videos play to the end · top to bottom</div>
                </div>
                <a href="<?= e(url('admin/banners/slide')) ?>" class="btn btn-primary ms-auto"><i class="bi bi-plus-lg"></i> Add slide</a>
            </div>
            <?php if ($slides): ?>
                <div class="table-responsive">
                    <table class="table align-middle slide-table">
                        <thead>
                        <tr><th style="width:60px">#</th><th>Slide</th><th>Status</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($slides as $i => $s): $lines = preg_split('/\R/', (string) $s['title']); ?>
                            <tr class="<?= $s['is_active'] ? '' : 'is-hidden' ?>">
                                <td class="text-muted fw-semibold"><?= $i + 1 ?></td>
                                <td>
                                    <div class="slide-cell">
                                        <?php $isVid = $s['media_type'] === 'video' && $s['video']; ?>
                                        <a href="<?= e(url('admin/banners/slide?id=' . $s['id'])) ?>" class="slide-thumb<?= $isVid ? ' is-video' : '' ?>"<?php if ($s['image'] || !$isVid): ?> style="background-image:url('<?= e(img($s['image'])) ?>')"<?php endif; ?> aria-label="Edit slide <?= $i + 1 ?>">
                                            <?php if ($isVid && !$s['image']): ?><video src="<?= e(url($s['video'])) ?>#t=1" muted playsinline preload="metadata"></video><?php endif; ?>
                                            <?php if ($isVid): ?><span class="slide-thumb-play"><i class="bi bi-play-fill"></i></span><?php endif; ?>
                                        </a>
                                        <div class="min-w-0">
                                            <a href="<?= e(url('admin/banners/slide?id=' . $s['id'])) ?>" class="row-link fw-semibold d-block text-truncate"><?php if ($isVid): ?><span class="media-tag"><i class="bi bi-camera-video-fill"></i> Video</span><?php endif; ?><?php if ($isVid && $s['video_status'] === 'processing'): ?><span class="media-tag is-busy"><span class="spinner-border spinner-border-sm"></span> Making web-ready…</span><?php elseif ($isVid && $s['video_status'] === 'failed'): ?><span class="media-tag is-bad" title="<?= e((string) $s['video_note']) ?>"><i class="bi bi-exclamation-triangle-fill"></i> Video problem</span><?php endif; ?><?= e(implode(' ', array_map('trim', $lines)) ?: 'No heading') ?></a>
                                            <div class="small text-muted text-truncate"><?= e($s['subtitle'] ?: 'No sub-heading') ?></div>
                                            <?php if ($isVid && $s['video_note']): ?><div class="small text-truncate <?= $s['video_status'] === 'failed' ? 'text-danger' : 'text-success' ?>"><?= e($s['video_note']) ?></div><?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php partial('../admin/status-badge', ['status' => $s['is_active'] ? 'active' : 'hidden']); ?></td>
                                <td>
                                    <div class="row-actions">
                                        <button class="icon-action" form="act-up-<?= $s['id'] ?>" title="Move up" aria-label="Move up"<?= $i === 0 ? ' disabled' : '' ?>><i class="bi bi-arrow-up"></i></button>
                                        <button class="icon-action" form="act-down-<?= $s['id'] ?>" title="Move down" aria-label="Move down"<?= $i === count($slides) - 1 ? ' disabled' : '' ?>><i class="bi bi-arrow-down"></i></button>
                                        <button class="icon-action" form="act-toggle-<?= $s['id'] ?>" title="<?= $s['is_active'] ? 'Hide from home page' : 'Show on home page' ?>" aria-label="<?= $s['is_active'] ? 'Hide' : 'Show' ?>"><i class="bi bi-<?= $s['is_active'] ? 'eye-slash' : 'eye' ?>"></i></button>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/banners/slide?id=' . $s['id'])) ?>"><i class="bi bi-pencil"></i> Edit</a>
                                        <button class="icon-action is-danger" form="act-delete-<?= $s['id'] ?>" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>
                                    </div>
                                    <?php foreach (['up', 'down', 'toggle', 'delete'] as $a): ?>
                                        <form method="post" id="act-<?= $a ?>-<?= $s['id'] ?>" class="d-none"<?= $a === 'delete' ? ' data-confirm="Delete this slide? This cannot be undone."' : '' ?>>
                                            <?= csrf_field() ?><input type="hidden" name="action" value="<?= $a ?>"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                        </form>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty"><i class="bi bi-collection-play"></i>No slides yet. The home page starts with the About section.<br>
                    <a href="<?= e(url('admin/banners/slide')) ?>" class="btn btn-primary mt-3"><i class="bi bi-plus-lg"></i> Add the first slide</a></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================ Page banners -->
    <div class="tab-pane fade" id="pane-pages" role="tabpanel" aria-labelledby="tab-pages" tabindex="0">
        <p class="text-muted small mb-3">The photo and heading at the top of each page. Activity and project pages use their own photos, set under
            <a href="<?= e(url('admin/activities')) ?>">Activities &amp; Gallery</a> and <a href="<?= e(url('admin/projects')) ?>">Projects</a>.</p>
        <div class="row g-4">
            <?php foreach ($pages as $key => $b): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="card banner-card h-100">
                        <a href="<?= e(url('admin/banners/page?page=' . $key)) ?>" class="banner-card-img" style="background-image:url('<?= e(img($b['image'])) ?>')">
                            <span><?= e($b['title']) ?></span>
                        </a>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong class="text-dark"><?= e($b['label']) ?> page</strong>
                                <span class="badge-status <?= $b['custom'] ? 'badge-active' : 'badge-read' ?> ms-auto"><?= $b['custom'] ? 'Custom photo' : 'Original photo' ?></span>
                            </div>
                            <p class="small text-muted mb-3 flex-grow-1"><?= e(mb_strimwidth($b['subtitle'], 0, 110, '…')) ?></p>
                            <a href="<?= e(url('admin/banners/page?page=' . $key)) ?>" class="btn btn-outline-primary btn-sm align-self-start"><i class="bi bi-pencil"></i> Edit banner</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ============================================ CTA panels -->
    <div class="tab-pane fade" id="pane-cta" role="tabpanel" aria-labelledby="tab-cta" tabindex="0">
        <p class="text-muted small mb-3">The full-photo boxes with buttons near the bottom of pages. Change the photo, choose which part of it shows (so faces stay in view), and edit the wording.</p>
        <div class="row g-4">
            <?php foreach ($ctas as $c): ?>
                <div class="col-md-6">
                    <div class="card banner-card h-100">
                        <a href="<?= e(url('admin/banners/cta?panel=' . $c['key'])) ?>" class="banner-card-img cta-card-img">
                            <img src="<?= e(img($c['image'])) ?>" alt="" style="object-position: <?= e($c['position']) ?>">
                            <span><?= e($c['heading']) ?></span>
                        </a>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong class="text-dark"><?= e($c['label']) ?></strong>
                                <span class="badge-status <?= $c['custom'] ? 'badge-active' : 'badge-read' ?> ms-auto"><?= $c['custom'] ? 'Custom photo' : 'Original photo' ?></span>
                            </div>
                            <p class="small text-muted mb-3 flex-grow-1"><?= e(mb_strimwidth($c['text'], 0, 110, '…')) ?></p>
                            <a href="<?= e(url('admin/banners/cta?panel=' . $c['key'])) ?>" class="btn btn-outline-primary btn-sm align-self-start"><i class="bi bi-pencil"></i> Edit panel</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if ($processing): ?>
<script>
    // A video is being made web-ready: refresh until it is done (only while this tab is visible).
    setInterval(function () { if (!document.hidden) location.reload(); }, 8000);
</script>
<?php endif; ?>
<script>
    // Remember the open tab (so returning from an edit screen lands on the same tab).
    (function () {
        var hash = { '#pages': '#tab-pages', '#cta': '#tab-cta' }[location.hash] || null;
        if (hash && window.bootstrap) bootstrap.Tab.getOrCreateInstance(document.querySelector(hash)).show();
        document.querySelectorAll('.admin-tabs [data-bs-toggle="tab"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', function () {
                history.replaceState(null, '', { 'tab-pages': '#pages', 'tab-cta': '#cta' }[btn.id] || location.pathname);
            });
        });
    })();
</script>
