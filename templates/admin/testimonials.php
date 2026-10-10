<?php /** @var array $testimonials */ ?>
<div class="d-flex align-items-center mb-3">
    <p class="text-muted mb-0">Shown in the "Voices of Change" section on the home page (first 6 visible ones).</p>
    <a href="<?= e(url('admin/testimonials/edit')) ?>" class="btn btn-primary ms-auto text-nowrap"><i class="bi bi-plus-lg"></i> Add testimonial</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Name</th><th>Message</th><th>Order</th><th>Visible</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($testimonials as $t): ?>
                <tr>
                    <td class="text-nowrap"><a class="row-link fw-semibold" href="<?= e(url('admin/testimonials/edit?id=' . $t['id'])) ?>"><?= e($t['name']) ?></a><div class="small text-muted"><?= e($t['role']) ?></div></td>
                    <td class="small" style="max-width:420px"><?= e(mb_strimwidth($t['message'], 0, 140, '…')) ?></td>
                    <td><?= (int) $t['sort_order'] ?></td>
                    <td><?php partial('../admin/status-badge', ['status' => $t['is_active'] ? 'active' : 'hidden']); ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/testimonials/edit?id=' . $t['id'])) ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$testimonials): ?>
                <tr><td colspan="5"><div class="empty"><i class="bi bi-chat-quote"></i>No testimonials yet.</div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
