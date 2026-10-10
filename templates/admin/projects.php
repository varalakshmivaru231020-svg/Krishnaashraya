<?php /** @var array $projects */ ?>
<div class="d-flex align-items-center mb-3">
    <p class="text-muted mb-0">Projects appear on the home page (ongoing ones), the Our Projects page and in the donation form.</p>
    <a href="<?= e(url('admin/projects/edit')) ?>" class="btn btn-primary ms-auto text-nowrap"><i class="bi bi-plus-lg"></i> Add project</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th></th><th>Project</th><th>Status</th><th class="num">Raised online</th><th>Order</th><th>Visible</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($projects as $p): ?>
                <tr>
                    <td style="width:80px"><img class="thumb" src="<?= e(img($p['image'])) ?>" alt=""></td>
                    <td><a class="row-link fw-semibold" href="<?= e(url('admin/projects/edit?id=' . $p['id'])) ?>"><?= e($p['title']) ?></a><div class="small text-muted">/projects/<?= e($p['slug']) ?></div></td>
                    <td><?= $p['status'] === 'ongoing' ? 'Ongoing' : 'Completed' ?></td>
                    <td class="num"><?= e(paise_to_inr((int) $p['raised_paise'])) ?><?= $p['goal_amount'] ? '<div class="small text-muted">of ' . e(inr($p['goal_amount'])) . '</div>' : '' ?></td>
                    <td><?= (int) $p['sort_order'] ?></td>
                    <td><?php partial('../admin/status-badge', ['status' => $p['is_active'] ? 'active' : 'hidden']); ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('admin/projects/edit?id=' . $p['id'])) ?>">Edit</a>
                        <a class="btn btn-sm btn-light" href="<?= e(url('projects/' . $p['slug'])) ?>" target="_blank" title="View on website"><i class="bi bi-box-arrow-up-right"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$projects): ?>
                <tr><td colspan="7"><div class="empty"><i class="bi bi-kanban"></i>No projects yet. Add your first one.</div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
