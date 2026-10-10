<?php /** @var array $form @var ?array $item @var array $errors */ ?>
<a href="<?= e(url('admin/testimonials')) ?>" class="small d-inline-block mb-3"><i class="bi bi-arrow-left"></i> All testimonials</a>
<?php partial('../admin/errors', ['errors' => $errors]); ?>

<div class="row">
    <div class="col-lg-8">
        <form method="post" enctype="multipart/form-data" class="card">
            <div class="card-body">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Name *</label>
                        <input class="form-control" id="name" name="name" value="<?= e($form['name']) ?>" required maxlength="120">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="role">Who they are</label>
                        <input class="form-control" id="role" name="role" value="<?= e($form['role']) ?>" maxlength="120" placeholder="Donor, Volunteer, Parent…">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="message">What they said *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required><?= e($form['message']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Photo (optional)</label>
                        <div class="d-flex align-items-center gap-3">
                            <img id="tPreview" src="<?= e($form['photo'] ? img($form['photo']) : '') ?>" class="rounded-circle<?= $form['photo'] ? '' : ' d-none' ?>" style="width:56px;height:56px;object-fit:cover" alt="">
                            <input type="file" class="form-control" name="photo" accept="image/jpeg,image/png,image/webp" data-preview="#tPreview">
                        </div>
                        <?php if ($form['photo']): ?>
                            <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo"><label class="form-check-label small" for="remove_photo">Remove photo</label></div>
                        <?php endif; ?>
                        <div class="form-text">Without a photo, the first letter of the name is shown.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="sort_order">Order</label>
                        <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) $form['sort_order'] ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= $form['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Visible</label>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary mt-4"><i class="bi bi-check-lg"></i> Save</button>
            </div>
        </form>
        <?php if ($item): ?>
            <form method="post" class="mt-4" data-confirm="Delete this testimonial?">
                <?= csrf_field() ?><input type="hidden" name="action" value="delete">
                <button class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i> Delete testimonial</button>
            </form>
        <?php endif; ?>
    </div>
</div>
