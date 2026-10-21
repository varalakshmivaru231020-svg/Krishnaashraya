<?php
/** @var string $content */
$admin = current_admin();
$p = current_path();
$newEnquiries = $admin ? (int) db_value("SELECT COUNT(*) FROM enquiries WHERE status = 'new'") : 0;
// New donations the team has not looked at yet (a payment still in progress is not counted until it finishes).
$newDonations = $admin ? (int) db_value("SELECT COUNT(*) FROM donations WHERE seen_by_admin = 0 AND status <> 'pending'") : 0;
$menu = [
    'Overview' => [
        'admin' => ['Dashboard', 'grid-1x2', 0],
    ],
    'Fundraising' => [
        'admin/donations' => ['Donations', 'currency-rupee', $newDonations],
        'admin/purposes'  => ['Donation Purposes', 'bullseye', 0],
        'admin/enquiries' => ['Enquiries', 'chat-left-text', $newEnquiries],
    ],
    'Website content' => [
        'admin/about'        => ['About Us', 'info-circle', 0],
        'admin/banners'      => ['Banners', 'card-image', 0],
        'admin/impact'       => ['Impact Numbers', 'graph-up-arrow', 0],
        'admin/projects'     => ['Projects', 'kanban', 0],
        'admin/activities'   => ['Activities & Gallery', 'images', 0],
        'admin/testimonials' => ['Testimonials', 'quote', 0],
        'admin/popup'        => ['Website Popup', 'window-stack', 0],
    ],
    'Configuration' => [
        'admin/seo'      => ['SEO', 'search', 0],
        'admin/payments' => ['Payment Settings', 'credit-card', 0],
        'admin/recaptcha' => ['Spam Protection', 'shield-check', 0],
        'admin/settings' => ['Site Settings', 'sliders', 0],
        'admin/account'  => ['Account & Users', 'shield-lock', 0],
    ],
];
$initials = $admin ? mb_strtoupper(implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim($admin['name'])), 0, 2)))) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e(($title ?? 'Admin') . ' · Krishnashraya Admin') ?></title>
<?php partial('favicon'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="<?= e(asset('assets/css/bootstrap.min.css')) ?>" rel="stylesheet">
    <link href="<?= e(asset('assets/css/admin.css')) ?>" rel="stylesheet">
    <?= font_tags() /* so banner and slide previews use the website's heading font */ ?>
</head>
<body class="admin">
<?php if ($admin): ?>
<div class="admin-shell">
    <aside class="admin-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="adminSidebar">
        <div class="admin-brand">
            <a href="<?= e(url('admin')) ?>" class="admin-brand-link">
                <span class="admin-brand-logo"><img src="<?= e(url('assets/images/sket-logo-web.webp')) ?>" alt=""></span>
                <span class="admin-brand-text">
                    <strong>Krishnashraya</strong>
                    <small>Admin Console</small>
                </span>
            </a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close"></button>
        </div>

        <nav class="admin-nav">
            <?php foreach ($menu as $section => $items): ?>
                <div class="admin-nav-section"><?= e($section) ?></div>
                <?php foreach ($items as $path => [$label, $icon, $badge]):
                    $active = $path === 'admin' ? $p === 'admin' : ($p === $path || str_starts_with($p, $path . '/')); ?>
                    <a href="<?= e(url($path)) ?>" class="<?= $active ? 'active' : '' ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                        <i class="bi bi-<?= e($icon) ?>"></i>
                        <span><?= e($label) ?></span>
                        <?php if ($badge): ?><span class="admin-nav-badge"><?= $badge ?></span><?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>

        <div class="admin-sidebar-foot">
            <div class="admin-user">
                <span class="admin-avatar"><?= e($initials) ?></span>
                <span class="admin-user-text">
                    <strong><?= e($admin['name']) ?></strong>
                    <small><?= e($admin['email']) ?></small>
                </span>
            </div>
            <form method="post" action="<?= e(url('admin/logout')) ?>">
                <?= csrf_field() ?>
                <button class="admin-logout" title="Log out" aria-label="Log out"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <button class="admin-icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Menu"><i class="bi bi-list"></i></button>
            <div class="admin-topbar-title">
                <span class="admin-crumb">Admin <i class="bi bi-chevron-right"></i></span>
                <h1><?= e($title ?? 'Admin') ?></h1>
            </div>
            <div class="admin-topbar-actions">
                <?php if ($newEnquiries): ?>
                    <a href="<?= e(url('admin/enquiries?status=new')) ?>" class="admin-icon-btn position-relative" title="<?= $newEnquiries ?> new enquiries">
                        <i class="bi bi-bell"></i><span class="admin-dot"></span>
                    </a>
                <?php endif; ?>
                <a href="<?= e(url('')) ?>" target="_blank" class="btn btn-light admin-view-site"><i class="bi bi-box-arrow-up-right"></i> <span class="d-none d-sm-inline">View website</span></a>
            </div>
        </header>

        <div class="admin-content">
            <?php foreach (flashes() as $f): ?>
                <div class="alert admin-alert alert-<?= $f['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
                    <i class="bi bi-<?= $f['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
                    <span><?= e($f['message']) ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
            <?= $content ?>
        </div>
    </main>
</div>
<?php else: ?>
    <?= $content ?>
<?php endif; ?>
<script src="<?= e(asset('assets/js/bootstrap.bundle.min.js')) ?>"></script>
<script>
    // Confirm destructive actions: <form data-confirm="Delete this?">
    document.addEventListener('submit', function (e) {
        var msg = e.target.getAttribute('data-confirm');
        if (msg && !confirm(msg)) e.preventDefault();
    });
    // Image preview for file inputs: <input type="file" data-preview="#imgId">
    document.querySelectorAll('input[type=file][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            var img = document.querySelector(input.getAttribute('data-preview'));
            if (img && input.files[0]) { img.src = URL.createObjectURL(input.files[0]); img.classList.remove('d-none'); }
        });
    });
</script>
</body>
</html>
