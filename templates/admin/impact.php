<?php /** @var array $stats @var string $heading @var array $icons */
$shown = count(array_filter($stats, fn($s) => $s['is_active']));
?>
<p class="page-lead">The highlighted figures on the home page. Numbers count up when visitors scroll to them. Four figures fit neatly in one row.</p>

<div class="card mb-4">
    <div class="card-body">
        <form method="post" class="row g-2 align-items-end">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="heading">
            <div class="col-md">
                <label class="form-label" for="impact_heading">Section heading</label>
                <input class="form-control" id="impact_heading" name="impact_heading" maxlength="150" value="<?= e($heading) ?>">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-outline-primary"><i class="bi bi-check-lg"></i> Save heading</button>
            </div>
        </form>
    </div>
</div>

<div class="card list-card">
    <div class="card-header">
        <div>
            Figures
            <span class="sub"><?= $shown ?> of <?= count($stats) ?> showing on the home page</span>
        </div>
        <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#statModal" data-mode="add"><i class="bi bi-plus-lg"></i> Add figure</button>
    </div>
    <?php if ($stats): ?>
        <div class="table-responsive">
            <table class="table list-table">
                <thead>
                <tr><th style="width:56px">#</th><th>Figure</th><th>Label</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($stats as $i => $s): $id = (int) $s['id']; ?>
                    <tr class="<?= $s['is_active'] ? '' : 'is-hidden' ?>">
                        <td class="order-no"><?= $i + 1 ?></td>
                        <td>
                            <span class="stat-preview">
                                <span class="ico"><i class="bi bi-<?= e($s['icon']) ?>"></i></span>
                                <span class="num"><?= e(($s['prefix'] ?? '') . inr((int) $s['value'], false) . ($s['suffix'] ?? '')) ?></span>
                            </span>
                        </td>
                        <td><div class="row-main"><span class="title"><?= e($s['label']) ?></span></div></td>
                        <td><?php partial('../admin/status-badge', ['status' => $s['is_active'] ? 'active' : 'hidden']); ?></td>
                        <td>
                            <div class="row-actions">
                                <button class="icon-action" form="im-up-<?= $id ?>" title="Move left / up" aria-label="Move up"<?= $i === 0 ? ' disabled' : '' ?>><i class="bi bi-arrow-up"></i></button>
                                <button class="icon-action" form="im-down-<?= $id ?>" title="Move right / down" aria-label="Move down"<?= $i === count($stats) - 1 ? ' disabled' : '' ?>><i class="bi bi-arrow-down"></i></button>
                                <button class="icon-action" form="im-toggle-<?= $id ?>" title="<?= $s['is_active'] ? 'Hide from home page' : 'Show on home page' ?>" aria-label="<?= $s['is_active'] ? 'Hide' : 'Show' ?>"><i class="bi bi-<?= $s['is_active'] ? 'eye-slash' : 'eye' ?>"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statModal" data-mode="edit"
                                        data-id="<?= $id ?>" data-icon="<?= e($s['icon']) ?>" data-prefix="<?= e($s['prefix']) ?>" data-value="<?= (int) $s['value'] ?>"
                                        data-suffix="<?= e($s['suffix']) ?>" data-label="<?= e($s['label']) ?>" data-active="<?= (int) $s['is_active'] ?>"><i class="bi bi-pencil"></i> Edit</button>
                                <button class="icon-action is-danger" form="im-delete-<?= $id ?>" title="Delete" aria-label="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                            <?php foreach (['up', 'down', 'toggle', 'delete'] as $a): ?>
                                <form method="post" id="im-<?= $a ?>-<?= $id ?>" class="d-none"<?= $a === 'delete' ? ' data-confirm="Delete &quot;' . e($s['label']) . '&quot;?"' : '' ?>>
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
        <div class="empty"><i class="bi bi-graph-up-arrow"></i>No figures yet — the section is hidden on the home page until you add one.</div>
    <?php endif; ?>
</div>

<!-- Add / edit form -->
<div class="modal fade" id="statModal" tabindex="-1" aria-labelledby="statModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="">
            <div class="modal-header">
                <h2 class="modal-title" id="statModalTitle">Add figure</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="sm-prefix">Before number</label>
                        <input class="form-control" id="sm-prefix" name="prefix" maxlength="10" placeholder="e.g. ₹">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="sm-value">Number <span class="text-danger">*</span></label>
                        <input class="form-control" id="sm-value" name="value" inputmode="numeric" required placeholder="e.g. 60000">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="sm-suffix">After number</label>
                        <input class="form-control" id="sm-suffix" name="suffix" maxlength="10" placeholder="e.g. +  or  %">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="sm-label">Label <span class="text-danger">*</span></label>
                        <input class="form-control" id="sm-label" name="label" maxlength="120" required placeholder="e.g. Children helped">
                    </div>
                    <div class="col-12">
                        <span class="form-label d-block">Icon</span>
                        <div class="icon-picker">
                            <?php foreach ($icons as $ico): ?>
                                <label title="<?= e($ico) ?>"><input type="radio" name="icon" value="<?= e($ico) ?>"><span><i class="bi bi-<?= e($ico) ?>"></i></span></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-12 d-flex align-items-center gap-3 flex-wrap">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="sm-active" name="is_active" checked>
                            <label class="form-check-label" for="sm-active">Show on the home page</label>
                        </div>
                        <span class="ms-auto small text-muted">Preview:</span>
                        <span class="stat-preview"><span class="ico"><i class="bi" id="sm-prev-ico"></i></span><span class="num" id="sm-prev-num"></span></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> <span data-save-label>Add figure</span></button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        var m = document.getElementById('statModal');
        function f(name) { return m.querySelector('[name="' + name + '"]'); }
        function preview() {
            var n = f('value').value.replace(/[^\d]/g, '');
            m.querySelector('#sm-prev-num').textContent = f('prefix').value + (n ? Number(n).toLocaleString('en-IN') : '0') + f('suffix').value;
            var ico = m.querySelector('[name="icon"]:checked');
            m.querySelector('#sm-prev-ico').className = 'bi bi-' + (ico ? ico.value : 'star-fill');
        }
        m.addEventListener('show.bs.modal', function (e) {
            var b = e.relatedTarget, edit = b && b.getAttribute('data-mode') === 'edit';
            function d(k, def) { return edit ? (b.getAttribute('data-' + k) || '') : def; }
            m.querySelector('.modal-title').textContent = edit ? 'Edit figure' : 'Add figure';
            m.querySelector('[data-save-label]').textContent = edit ? 'Save changes' : 'Add figure';
            f('id').value = d('id', '');
            f('prefix').value = d('prefix', '');
            f('value').value = d('value', '');
            f('suffix').value = d('suffix', '');
            f('label').value = d('label', '');
            f('is_active').checked = edit ? b.getAttribute('data-active') === '1' : true;
            var icon = d('icon', 'star-fill');
            m.querySelectorAll('[name="icon"]').forEach(function (r) { r.checked = r.value === icon; });
            preview();
        });
        m.addEventListener('shown.bs.modal', function () { f('value').focus(); });
        m.addEventListener('input', preview);
        m.addEventListener('change', preview);
    })();
</script>
