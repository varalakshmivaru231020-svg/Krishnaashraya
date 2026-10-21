<?php /** @var array $donations @var array $filters @var array $summary @var array $pg @var array $projects */ ?>
<div class="card mb-4">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Name, email, phone, payment ID">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <?php foreach (['' => 'All', 'paid' => 'Paid', 'unpaid' => 'Not paid (all)', 'pending' => 'Started, not paid', 'failed' => 'Failed', 'cancelled' => 'Cancelled by donor'] as $k => $v): ?>
                        <option value="<?= e($k) ?>"<?= $filters['status'] === $k ? ' selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">Followed up?</label>
                <select name="followed" class="form-select">
                    <?php foreach (['' => 'Any', '0' => 'Not yet', '1' => 'Yes'] as $k => $v): ?>
                        <option value="<?= e($k) ?>"<?= $filters['followed'] === (string) $k ? ' selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">From</label>
                <input type="date" name="from" class="form-control" value="<?= e($filters['from']) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">To</label>
                <input type="date" name="to" class="form-control" value="<?= e($filters['to']) ?>">
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-primary"><i class="bi bi-funnel"></i></button>
            </div>
            <?php if ($projects): ?>
                <div class="col-md-4">
                    <label class="form-label small">Project</label>
                    <select name="project" class="form-select">
                        <option value="">All projects</option>
                        <option value="general"<?= $filters['project'] === 'general' ? ' selected' : '' ?>>General donation</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= (int) $p['id'] ?>"<?= $filters['project'] === (string) $p['id'] ? ' selected' : '' ?>><?= e(mb_strimwidth($p['title'], 0, 60, '…')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <?php if ($purposes): ?>
                <div class="col-md-4">
                    <label class="form-label small">Purpose</label>
                    <select name="purpose" class="form-select">
                        <option value="">All purposes</option>
                        <?php foreach ($purposes as $p): ?>
                            <option value="<?= (int) $p['id'] ?>"<?= $filters['purpose'] === (string) $p['id'] ? ' selected' : '' ?>><?= e($p['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <div class="text-muted"><?= (int) $summary['cnt'] ?> records · <strong class="text-dark"><?= e(paise_to_inr((int) $summary['paid'])) ?></strong> paid</div>
    <a href="<?= e(url('admin/donations')) ?>" class="small ms-2">Clear filters</a>
    <a href="<?= e(url('admin/donations/export') . qs(['page' => null])) ?>" class="btn btn-outline-primary btn-sm ms-auto"><i class="bi bi-download"></i> Download Excel / CSV</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr><th>Date</th><th>Donor</th><th>Phone</th><th class="num">Amount</th><th>Towards</th><th>Status</th><th>Follow-up</th><th class="text-end">Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($donations as $d): ?>
                <tr>
                    <td class="small text-muted text-nowrap"><?= e(fmt_date($d['created_at'], 'd M Y')) ?><br><?= e(fmt_date($d['created_at'], 'h:i A')) ?></td>
                    <td>
                        <a class="row-link fw-semibold" href="<?= e(url('admin/donations/view?id=' . $d['id'])) ?>"><?= e($d['name']) ?></a><?php if (in_array((int) $d['id'], $newIds ?? [], true)): ?> <span class="new-tag">New</span><?php endif; ?>
                        <div class="small text-muted"><?= e($d['email'] ?: '') ?></div>
                    </td>
                    <td class="text-nowrap"><?= $d['phone'] ? '<a class="row-link" href="' . e(tel_href($d['phone'])) . '">' . e($d['phone']) . '</a>' : '<span class="text-muted">—</span>' ?></td>
                    <td class="num fw-semibold"><?= e(paise_to_inr((int) $d['amount_paise'])) ?></td>
                    <td class="small"><?= e(mb_strimwidth(donation_towards($d), 0, 30, '…')) ?></td>
                    <td><?php partial('../admin/status-badge', ['status' => $d['status']]); ?></td>
                    <td><?= $d['followed_up'] ? '<i class="bi bi-check-circle-fill text-success"></i> Done' : ($d['status'] === 'paid' ? '<span class="text-muted small">—</span>' : '<span class="text-warning small fw-semibold">To do</span>') ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary text-nowrap" href="<?= e(url('admin/donations/view?id=' . $d['id'])) ?>"><i class="bi bi-eye"></i> View</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$donations): ?>
                <tr><td colspan="8"><div class="empty"><i class="bi bi-inbox"></i>No donations match these filters.</div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php partial('../admin/pagination', ['pg' => $pg]); ?>
