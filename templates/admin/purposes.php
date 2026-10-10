<?php /** @var array $purposes */
$active = count(array_filter($purposes, fn($p) => $p['is_active']));
?>
<p class="page-lead">The choices donors see under <strong>“Donate towards”</strong> on the Donate page. Ongoing <a href="<?= e(url('admin/projects')) ?>">projects</a> are listed there automatically as well.</p>

<div class="card list-card">
    <div class="card-header">
        <div>
            All purposes
            <span class="sub"><?= $active ?> of <?= count($purposes) ?> showing on the Donate page</span>
        </div>
        <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#purposeModal" data-mode="add"><i class="bi bi-plus-lg"></i> Add purpose</button>
    </div>
    <?php if ($purposes): ?>
        <div class="table-responsive">
            <table class="table list-table">
                <thead>
                <tr><th style="width:56px">#</th><th>Purpose</th><th>Status</th><th class="num">Raised</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($purposes as $i => $p): $id = (int) $p['id']; ?>
                    <tr class="<?= $p['is_active'] ? '' : 'is-hidden' ?>">
                        <td class="order-no"><?= $i + 1 ?></td>
                        <td>
                            <div class="row-main">
                                <span class="title"><?= e($p['title']) ?></span>
                                <span class="desc"><?= e($p['description'] ?: 'No description') ?></span>
                            </div>
                        </td>
                        <td><?php partial('../admin/status-badge', ['status' => $p['is_active'] ? 'active' : 'hidden']); ?></td>
                        <td class="num">
                            <strong><?= e(paise_to_inr((int) $p['raised_paise'])) ?></strong>
                            <div class="small text-muted"><?= (int) $p['donations'] ?> donation<?= (int) $p['donations'] === 1 ? '' : 's' ?></div>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button class="icon-action" form="pu-up-<?= $id ?>" title="Move up" aria-label="Move up"<?= $i === 0 ? ' disabled' : '' ?>><i class="bi bi-arrow-up"></i></button>
                                <button class="icon-action" form="pu-down-<?= $id ?>" title="Move down" aria-label="Move down"<?= $i === count($purposes) - 1 ? ' disabled' : '' ?>><i class="bi bi-arrow-down"></i></button>
                                <button class="icon-action" form="pu-toggle-<?= $id ?>" title="<?= $p['is_active'] ? 'Hide from Donate page' : 'Show on Donate page' ?>" aria-label="<?= $p['is_active'] ? 'Hide' : 'Show' ?>"><i class="bi bi-<?= $p['is_active'] ? 'eye-slash' : 'eye' ?>"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#purposeModal" data-mode="edit"
                                        data-id="<?= $id ?>" data-title="<?= e($p['title']) ?>" data-description="<?= e($p['description']) ?>" data-active="<?= (int) $p['is_active'] ?>"><i class="bi bi-pencil"></i> Edit</button>
                                <button class="icon-action is-danger" form="pu-delete-<?= $id ?>" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                            <?php foreach (['up', 'down', 'toggle', 'delete'] as $a): ?>
                                <form method="post" id="pu-<?= $a ?>-<?= $id ?>" class="d-none"<?= $a === 'delete'
                                    ? ' data-confirm="Delete &quot;' . e($p['title']) . '&quot;?' . ((int) $p['donations'] ? ' Its ' . (int) $p['donations'] . ' past donation(s) are kept and will show as General Donation. Tip: hiding it keeps the link instead.' : '') . '"'
                                    : '' ?>>
                                    <?= csrf_field() ?><input type="hidden" name="action" value="<?= $a ?>"><input type="hidden" name="id" value="<?= $id ?>">
                                </form>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty"><i class="bi bi-bullseye"></i>No purposes yet — donors will only see “General Donation”.</div>
    <?php endif; ?>
</div>

<!-- Add / edit form -->
<div class="modal fade" id="purposeModal" tabindex="-1" aria-labelledby="purposeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="">
            <div class="modal-header">
                <h2 class="modal-title" id="purposeModalTitle">Add purpose</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="pm-title">Name <span class="text-danger">*</span></label>
                    <input class="form-control" id="pm-title" name="title" maxlength="120" required placeholder="e.g. Health Care">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="pm-desc">Short description <span class="text-muted fw-normal">(optional)</span></label>
                    <input class="form-control" id="pm-desc" name="description" maxlength="255" placeholder="e.g. Medicines and check-ups for the children">
                    <div class="form-text">Shown under the choice on the Donate page.</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="pm-active" name="is_active" checked>
                    <label class="form-check-label" for="pm-active">Show on the Donate page</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> <span data-save-label>Add purpose</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('purposeModal').addEventListener('show.bs.modal', function (e) {
        var b = e.relatedTarget, m = this, edit = b && b.getAttribute('data-mode') === 'edit';
        m.querySelector('.modal-title').textContent = edit ? 'Edit purpose' : 'Add purpose';
        m.querySelector('[data-save-label]').textContent = edit ? 'Save changes' : 'Add purpose';
        m.querySelector('[name="id"]').value = edit ? b.getAttribute('data-id') : '';
        m.querySelector('[name="title"]').value = edit ? b.getAttribute('data-title') : '';
        m.querySelector('[name="description"]').value = edit ? b.getAttribute('data-description') : '';
        m.querySelector('[name="is_active"]').checked = edit ? b.getAttribute('data-active') === '1' : true;
    });
    document.getElementById('purposeModal').addEventListener('shown.bs.modal', function () { this.querySelector('[name="title"]').focus(); });
</script>
