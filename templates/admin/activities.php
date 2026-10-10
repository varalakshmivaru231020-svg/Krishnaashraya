<?php /** @var array $categories */ ?>
<div class="d-flex align-items-center mb-3">
    <p class="text-muted mb-0">Each activity has its own page and photo gallery, and appears in the "Our Activities" menu.</p>
    <a href="<?= e(url('admin/activities/edit')) ?>" class="btn btn-primary ms-auto text-nowrap"><i class="bi bi-plus-lg"></i> Add activity</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th></th><th>Activity</th><th class="num">Photos</th><th>Order</th><th>Visible</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td style="width:80px"><img class="thumb" src="<?= e(img($c['cover_image'])) ?>" alt=""></td>
                    <td><a class="row-link fw-semibold" href="<?= e(url('admin/activities/edit?id=' . $c['id'])) ?>"><?= e($c['title']) ?></a><div class="small text-muted">/<?= e($c['slug']) ?></div></td>
                    <td class="num"><?= (int) $c['photo_count'] ?></td>
                    <td><?= (int) $c['sort_order'] ?></td>
                    <td><?php partial('../admin/status-badge', ['status' => $c['is_active'] ? 'active' : 'hidden']); ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-primary" href="<?= e(url('admin/activities/edit?id=' . $c['id'])) ?>#photos"><i class="bi bi-images"></i> Photos</a>
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/activities/edit?id=' . $c['id'])) ?>">Edit</a>
                        <a class="btn btn-sm btn-light" href="<?= e(url($c['slug'])) ?>" target="_blank" title="View on website"><i class="bi bi-box-arrow-up-right"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$categories): ?>
                <tr><td colspan="6"><div class="empty"><i class="bi bi-images"></i>No activities yet.</div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
