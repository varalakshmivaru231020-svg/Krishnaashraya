<?php /** @var array $enquiries @var array $filters @var array $pg */ ?>
<div class="card mb-4">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get">
            <div class="col-md-5">
                <label class="form-label small">Search</label>
                <input type="search" name="q" class="form-control" value="<?= e($filters['q']) ?>" placeholder="Name, email, phone or message">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Type</label>
                <select name="type" class="form-select">
                    <option value="">All types</option>
                    <?php foreach (enquiry_types() as $k => $v): ?>
                        <option value="<?= e($k) ?>"<?= $filters['type'] === $k ? ' selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <?php foreach (['' => 'All', 'new' => 'New', 'read' => 'Read', 'replied' => 'Replied'] as $k => $v): ?>
                        <option value="<?= e($k) ?>"<?= $filters['status'] === $k ? ' selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 d-grid"><button class="btn btn-primary"><i class="bi bi-funnel"></i></button></div>
        </form>
    </div>
</div>

<div class="d-flex align-items-center mb-3">
    <div class="text-muted"><?= (int) $pg['total'] ?> enquiries</div>
    <a href="<?= e(url('admin/enquiries')) ?>" class="small ms-3">Clear filters</a>
    <a href="<?= e(url('admin/enquiries/export') . qs(['page' => null])) ?>" class="btn btn-outline-primary btn-sm ms-auto"><i class="bi bi-download"></i> Download Excel / CSV</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead><tr><th>Date</th><th>From</th><th>Phone</th><th>Type</th><th>Message</th><th>Status</th><th class="text-end">Action</th></tr></thead>
            <tbody>
            <?php foreach ($enquiries as $q): ?>
                <tr<?= $q['status'] === 'new' ? ' class="fw-semibold"' : '' ?>>
                    <td class="small text-muted text-nowrap"><?= e(fmt_date($q['created_at'], 'd M Y')) ?><br><?= e(fmt_date($q['created_at'], 'h:i A')) ?></td>
                    <td><a class="row-link" href="<?= e(url('admin/enquiries/view?id=' . $q['id'])) ?>"><?= e($q['name']) ?></a><div class="small text-muted fw-normal"><?= e($q['email']) ?></div></td>
                    <td class="text-nowrap fw-normal"><?= e($q['phone']) ?></td>
                    <td class="small fw-normal"><?= e(enquiry_types()[$q['type']] ?? $q['type']) ?></td>
                    <td class="small fw-normal" style="max-width:320px"><a class="row-link" href="<?= e(url('admin/enquiries/view?id=' . $q['id'])) ?>"><?= e(mb_strimwidth($q['message'], 0, 90, '…')) ?></a></td>
                    <td><?php partial('../admin/status-badge', ['status' => $q['status']]); ?></td>
                    <td class="text-end"><a class="btn btn-sm btn-outline-primary text-nowrap" href="<?= e(url('admin/enquiries/view?id=' . $q['id'])) ?>"><i class="bi bi-eye"></i> View</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$enquiries): ?>
                <tr><td colspan="7"><div class="empty"><i class="bi bi-inbox"></i>No enquiries match these filters.</div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php partial('../admin/pagination', ['pg' => $pg]); ?>
