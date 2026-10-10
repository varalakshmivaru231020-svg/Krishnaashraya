<?php /** @var array $form @var ?array $category @var array $photos @var array $errors */ ?>
<a href="<?= e(url('admin/activities')) ?>" class="small d-inline-block mb-3"><i class="bi bi-arrow-left"></i> All activities</a>

<?php partial('../admin/errors', ['errors' => $errors]); ?>

<form method="post" enctype="multipart/form-data" class="mb-5">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="title">Activity name *</label>
                        <input class="form-control" id="title" name="title" value="<?= e($form['title']) ?>" maxlength="150" required placeholder="e.g. Education Programs">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Short description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= e($form['description']) ?></textarea>
                        <div class="form-text">Shown on the Our Activities page and under the page title.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="slug">Web address</label>
                            <div class="input-group">
                                <span class="input-group-text small">/</span>
                                <input class="form-control" id="slug" name="slug" value="<?= e($form['slug']) ?>" placeholder="made from the name">
                            </div>
                            <?php if ($category): ?><div class="form-text">Changing this breaks old links to the page.</div><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="sort_order">Display order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) $form['sort_order'] ?>">
                        </div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= $form['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Show on website</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <label class="form-label">Cover photo</label>
                    <img id="coverPreview" src="<?= e($form['cover_image'] ? img($form['cover_image']) : '') ?>" class="img-preview mb-2 w-100<?= $form['cover_image'] ? '' : ' d-none' ?>" alt="">
                    <input type="file" class="form-control" name="cover_image" accept="image/jpeg,image/png,image/webp" data-preview="#coverPreview">
                    <div class="form-text">Used on cards and menus. You can also pick one of the gallery photos below.</div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <label class="form-label">Page banner (optional)</label>
                    <img id="bannerPreview" src="<?= e($form['banner_image'] ? img($form['banner_image']) : '') ?>" class="img-preview mb-2 w-100<?= $form['banner_image'] ? '' : ' d-none' ?>" alt="">
                    <input type="file" class="form-control" name="banner_image" accept="image/jpeg,image/png,image/webp" data-preview="#bannerPreview">
                    <div class="form-text">Wide photo behind the page title. If empty, the cover photo is used.</div>
                    <?php if ($form['banner_image']): ?>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_banner" id="remove_banner">
                            <label class="form-check-label small" for="remove_banner">Remove banner</label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <button class="btn btn-primary w-100 py-2"><i class="bi bi-check-lg"></i> <?= $category ? 'Save activity' : 'Create activity' ?></button>
        </div>
    </div>
</form>

<?php if ($category): ?>
    <h2 class="h5 mb-3" id="photos">Gallery photos <span class="text-muted fw-normal">(<?= count($photos) ?>)</span></h2>

    <div class="card mb-4">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="upload">
                <div class="col-md-6">
                    <label class="form-label">Add photos</label>
                    <input type="file" class="form-control" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required>
                    <div class="form-text">You can select many photos at once. Up to 10 MB each; they are resized automatically.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Caption (optional)</label>
                    <input class="form-control" name="caption" maxlength="255" placeholder="e.g. Annual Day 2026">
                    <div class="form-text">&nbsp;</div>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
                    <div class="form-text">&nbsp;</div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($photos): ?>
        <form method="post" id="photosForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="photos">
            <div class="photo-admin-grid mb-3">
                <?php foreach ($photos as $ph): $isCover = $ph['image'] === $category['cover_image']; ?>
                    <div class="photo-admin">
                        <a href="<?= e(img($ph['image'])) ?>" target="_blank"><img src="<?= e(img($ph['image'])) ?>" alt="" loading="lazy"></a>
                        <div class="p-2">
                            <input class="form-control form-control-sm" name="caption[<?= (int) $ph['id'] ?>]" value="<?= e($ph['caption']) ?>" placeholder="Caption" maxlength="255">
                            <div class="d-flex gap-1 align-items-center">
                                <input type="number" class="form-control form-control-sm" style="width:72px" name="order[<?= (int) $ph['id'] ?>]" value="<?= (int) $ph['sort_order'] ?>" title="Display order">
                                <?php if ($isCover): ?>
                                    <span class="badge bg-warning text-dark ms-auto">Cover</span>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-light ms-auto" form="coverForm<?= (int) $ph['id'] ?>" title="Use as cover photo"><i class="bi bi-star"></i></button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-light text-danger" form="deleteForm<?= (int) $ph['id'] ?>" title="Delete photo"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save captions &amp; order</button>
        </form>

        <?php foreach ($photos as $ph): ?>
            <form method="post" id="deleteForm<?= (int) $ph['id'] ?>" data-confirm="Delete this photo?" class="d-none">
                <?= csrf_field() ?><input type="hidden" name="action" value="photo-delete"><input type="hidden" name="photo_id" value="<?= (int) $ph['id'] ?>">
            </form>
            <form method="post" id="coverForm<?= (int) $ph['id'] ?>" class="d-none">
                <?= csrf_field() ?><input type="hidden" name="action" value="photo-cover"><input type="hidden" name="photo_id" value="<?= (int) $ph['id'] ?>">
            </form>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card"><div class="empty"><i class="bi bi-images"></i>No photos yet. Upload some above.</div></div>
    <?php endif; ?>

    <form method="post" class="mt-5" data-confirm="Delete this activity and ALL its photos? This cannot be undone.">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i> Delete this activity</button>
    </form>
<?php endif; ?>
