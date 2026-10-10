<?php
/** @var array $stats @var array $recentDonations @var array $recentEnquiries @var array $monthly @var array $counts @var int $newEnquiries */
$byMonth = array_column($monthly, null, 'ym');
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("first day of -$i month"));
    $months[$ym] = (int) ($byMonth[$ym]['total'] ?? 0);
}
$maxMonth = max(1, max($months));
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card stat-tile h-100">
            <i class="bi bi-currency-rupee"></i>
            <div class="label">Total received online</div>
            <div class="value"><?= e(paise_to_inr((int) $stats['total_paid'])) ?></div>
            <div class="hint"><?= (int) $stats['count_paid'] ?> donation<?= (int) $stats['count_paid'] === 1 ? '' : 's' ?> from <?= (int) $stats['donors'] ?> donor<?= (int) $stats['donors'] === 1 ? '' : 's' ?></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-tile h-100">
            <i class="bi bi-calendar3"></i>
            <div class="label">This month</div>
            <div class="value"><?= e(paise_to_inr((int) $stats['month_paid'])) ?></div>
            <div class="hint"><?= (int) $stats['month_count'] ?> donation<?= (int) $stats['month_count'] === 1 ? '' : 's' ?> in <?= date('F') ?></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <a href="<?= e(url('admin/donations?status=unpaid&followed=0')) ?>" class="card stat-tile h-100 text-decoration-none">
            <i class="bi bi-telephone-outbound"></i>
            <div class="label">Unfinished donations to follow up</div>
            <div class="value"><?= (int) $stats['to_follow_up'] ?></div>
            <div class="hint">Filled the form but didn't pay</div>
        </a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="<?= e(url('admin/enquiries?status=new')) ?>" class="card stat-tile h-100 text-decoration-none">
            <i class="bi bi-envelope-open"></i>
            <div class="label">New enquiries</div>
            <div class="value"><?= $newEnquiries ?></div>
            <div class="hint">Waiting for a reply</div>
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                Recent donations
                <a href="<?= e(url('admin/donations')) ?>" class="ms-auto small">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead><tr><th>Donor</th><th>Phone</th><th class="num">Amount</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentDonations as $d): ?>
                        <tr>
                            <td><a class="row-link fw-semibold" href="<?= e(url('admin/donations/view?id=' . $d['id'])) ?>"><?= e($d['name']) ?></a><div class="small text-muted"><?= e($d['email'] ?: '') ?></div></td>
                            <td><?= e($d['phone'] ?: '—') ?></td>
                            <td class="num"><?= e(paise_to_inr((int) $d['amount_paise'])) ?></td>
                            <td><?php partial('../admin/status-badge', ['status' => $d['status']]); ?></td>
                            <td class="small text-muted text-nowrap"><?= e(fmt_date($d['created_at'], 'd M, h:i A')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$recentDonations): ?>
                        <tr><td colspan="5"><div class="empty"><i class="bi bi-inbox"></i>No donations yet.</div></td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Online donations — last 6 months</div>
            <div class="card-body">
                <table class="table table-sm">
                    <tbody>
                    <?php foreach ($months as $ym => $paise): ?>
                        <tr>
                            <td style="width:110px" class="text-muted"><?= e(date('M Y', strtotime($ym . '-01'))) ?></td>
                            <td>
                                <div style="background:#fff1e6;border-radius:6px;height:22px">
                                    <div style="background:#ff6b00;border-radius:6px;height:22px;width:<?= $paise ? max(2, (int) round($paise * 100 / $maxMonth)) : 0 ?>%"></div>
                                </div>
                            </td>
                            <td class="num fw-semibold" style="width:130px"><?= e(paise_to_inr($paise)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                Latest enquiries
                <a href="<?= e(url('admin/enquiries')) ?>" class="ms-auto small">View all</a>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($recentEnquiries as $q): ?>
                    <a href="<?= e(url('admin/enquiries/view?id=' . $q['id'])) ?>" class="list-group-item list-group-item-action py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong><?= e($q['name']) ?></strong>
                            <?php partial('../admin/status-badge', ['status' => $q['status']]); ?>
                        </div>
                        <div class="small text-muted"><?= e(enquiry_types()[$q['type']] ?? $q['type']) ?> · <?= e(fmt_date($q['created_at'], 'd M')) ?></div>
                        <div class="small text-truncate"><?= e($q['message']) ?></div>
                    </a>
                <?php endforeach; ?>
                <?php if (!$recentEnquiries): ?>
                    <div class="empty"><i class="bi bi-inbox"></i>No enquiries yet.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Website content</div>
            <div class="list-group list-group-flush">
                <a class="list-group-item list-group-item-action d-flex" href="<?= e(url('admin/projects')) ?>"><i class="bi bi-kanban me-2"></i> Projects <span class="ms-auto fw-semibold"><?= $counts['projects'] ?></span></a>
                <a class="list-group-item list-group-item-action d-flex" href="<?= e(url('admin/activities')) ?>"><i class="bi bi-images me-2"></i> Activity categories <span class="ms-auto fw-semibold"><?= $counts['categories'] ?></span></a>
                <a class="list-group-item list-group-item-action d-flex" href="<?= e(url('admin/activities')) ?>"><i class="bi bi-image me-2"></i> Gallery photos <span class="ms-auto fw-semibold"><?= $counts['photos'] ?></span></a>
                <a class="list-group-item list-group-item-action d-flex" href="<?= e(url('admin/testimonials')) ?>"><i class="bi bi-chat-quote me-2"></i> Testimonials <span class="ms-auto fw-semibold"><?= $counts['testimonials'] ?></span></a>
            </div>
        </div>
    </div>
</div>
