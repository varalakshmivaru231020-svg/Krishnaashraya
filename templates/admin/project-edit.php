<?php /** @var array $form @var ?array $project @var array $errors */ ?>
<a href="<?= e(url('admin/projects')) ?>" class="small d-inline-block mb-3"><i class="bi bi-arrow-left"></i> All projects</a>

<?php partial('../admin/errors', ['errors' => $errors]); ?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="title">Project title *</label>
                        <input class="form-control" id="title" name="title" value="<?= e($form['title']) ?>" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="summary">Short description</label>
                        <textarea class="form-control" id="summary" name="summary" rows="4"><?= e($form['summary']) ?></textarea>
                        <div class="form-text">Shown on the project card on the home page and Our Projects page.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="features">Key points (one per line)</label>
                        <textarea class="form-control" id="features" name="features" rows="5" placeholder="Quality education for every child&#10;Access to schools and libraries"><?= e($form['features']) ?></textarea>
                        <div class="form-text">Each line becomes a ✔ tick point.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="description">Full details</label>
                        <textarea class="form-control" id="description" name="description" rows="8"><?= e($form['description']) ?></textarea>
                        <div class="form-text">Shown on the project's own page. Leave a blank line between paragraphs.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body">
                    <label class="form-label">Photo <?= $project ? '' : '*' ?></label>
                    <img id="projectPreview" src="<?= e($form['image'] ? img($form['image']) : '') ?>" class="img-preview mb-2 w-100<?= $form['image'] ? '' : ' d-none' ?>" alt="">
                    <input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/webp" data-preview="#projectPreview">
                    <div class="form-text">JPG, PNG or WebP. Large photos are resized automatically.</div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="ongoing"<?= $form['status'] === 'ongoing' ? ' selected' : '' ?>>Ongoing (accepting donations)</option>
                            <option value="completed"<?= $form['status'] === 'completed' ? ' selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="goal_amount">Fundraising goal (₹)</label>
                        <input class="form-control" id="goal_amount" name="goal_amount" inputmode="numeric" value="<?= e((string) $form['goal_amount']) ?>" placeholder="Optional, e.g. 500000">
                        <div class="form-text">If set, a progress bar shows how much has been raised online.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="sort_order">Display order</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) $form['sort_order'] ?>">
                        <div class="form-text">Lower numbers show first.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="slug">Web address</label>
                        <div class="input-group">
                            <span class="input-group-text small">/projects/</span>
                            <input class="form-control" id="slug" name="slug" value="<?= e($form['slug']) ?>" placeholder="made from the title">
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= $form['is_active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Show on website</label>
                    </div>
                </div>
            </div>
            <button class="btn btn-primary w-100 py-2"><i class="bi bi-check-lg"></i> Save project</button>
        </div>
    </div>
</form>

<?php if ($project): ?>
    <form method="post" class="mt-4" data-confirm="Delete this project? This cannot be undone.">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i> Delete project</button>
    </form>
<?php endif; ?>
