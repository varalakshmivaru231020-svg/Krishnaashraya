<?php
/** @var array $q */
$typeLabel = enquiry_types()[$q['type']] ?? $q['type'];
$wa = $q['phone'] ? 'https://wa.me/91' . $q['phone'] . '?text=' . rawurlencode('Namaste ' . $q['name'] . ', this is ' . setting('site_name') . '. Thank you for contacting us.') : '';
?>
<a href="<?= e(url('admin/enquiries')) ?>" class="small d-inline-block mb-3"><i class="bi bi-arrow-left"></i> All enquiries</a>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <?= e($typeLabel) ?>
                <span class="ms-2"><?php partial('../admin/status-badge', ['status' => $q['status']]); ?></span>
                <span class="ms-auto small text-muted fw-normal"><?= e(fmt_date($q['created_at'])) ?></span>
            </div>
            <div class="card-body">
                <dl class="detail mb-4">
                    <dt>Name</dt><dd><?= e($q['name']) ?></dd>
                    <dt>Email</dt><dd><a href="mailto:<?= e($q['email']) ?>"><?= e($q['email']) ?></a></dd>
                    <dt>Phone</dt><dd><?= $q['phone'] ? '<a href="' . e(tel_href($q['phone'])) . '">' . e($q['phone']) . '</a>' : '—' ?></dd>
                </dl>
                <div class="p-3 bg-light rounded-3" style="white-space: pre-wrap"><?= e($q['message']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header">Reply</div>
            <div class="card-body d-grid gap-2">
                <a class="btn btn-outline-primary" href="mailto:<?= e($q['email']) ?>?subject=<?= rawurlencode('Re: your message to ' . setting('site_name')) ?>"><i class="bi bi-envelope"></i> Reply by email</a>
                <?php if ($wa): ?>
                    <a class="btn btn-success" href="<?= e($wa) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> WhatsApp</a>
                    <a class="btn btn-outline-primary" href="<?= e(tel_href($q['phone'])) ?>"><i class="bi bi-telephone"></i> Call</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">Status &amp; notes</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <select name="status" class="form-select mb-3">
                        <?php foreach (['new' => 'New', 'read' => 'Read', 'replied' => 'Replied'] as $k => $v): ?>
                            <option value="<?= $k ?>"<?= $q['status'] === $k ? ' selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="admin_notes" class="form-control mb-3" rows="4" placeholder="Notes for the team"><?= e($q['admin_notes']) ?></textarea>
                    <button class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
        <form method="post" data-confirm="Delete this enquiry permanently?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <button class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i> Delete enquiry</button>
        </form>
    </div>
</div>
