<?php /** @var array $groups @var array $values @var array $errors */ ?>
<?php partial('../admin/errors', ['errors' => $errors]); ?>
<p class="page-lead">Contact details, bank details and other site-wide text. The About section is edited under <a href="<?= e(url('admin/about')) ?>">About Us</a>, the home page figures under <a href="<?= e(url('admin/impact')) ?>">Impact Numbers</a>, and Google / SEO under <a href="<?= e(url('admin/seo')) ?>">SEO</a>.</p>

<?php
$pairs = font_pairs();
$current = setting('font_pair', 'warm');
$allFonts = implode('&', array_map(fn($f) => $f['query'], $pairs));
?>
<link href="https://fonts.googleapis.com/css2?<?= e($allFonts) ?>&amp;display=swap" rel="stylesheet">
<form method="post" class="card mb-4" id="fonts">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="font">
    <div class="card-header"><i class="bi bi-fonts me-1"></i> Website fonts <span class="small text-muted fw-normal ms-2">A display font for big headings, and a text font for everything else</span></div>
    <div class="card-body">
        <div class="font-grid" role="radiogroup" aria-label="Website fonts">
            <?php foreach ($pairs as $key => $f): ?>
                <label class="font-option">
                    <input type="radio" name="font_pair" value="<?= e($key) ?>" <?= $current === $key ? 'checked' : '' ?>>
                    <span class="font-card">
                        <span class="font-card-top"><strong><?= e($f['label']) ?></strong><?php if ($current === $key): ?><em>In use</em><?php endif; ?></span>
                        <span class="font-sample-head" style="font-family: '<?= e($f['head']) ?>', serif">There Is No Joy Greater than Giving</span>
                        <span class="font-sample-body" style="font-family: '<?= e($f['body']) ?>', sans-serif">We give children food, a safe home and education. Donate ₹1,000 today.</span>
                        <span class="font-card-names"><?= e($f['head']) ?><?= $f['head'] !== $f['body'] ? ' + ' . e($f['body']) : '' ?> · <?= e($f['note']) ?></span>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <button class="btn btn-primary mt-3"><i class="bi bi-check-lg"></i> Use these fonts</button>
    </div>
</form>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="site">
    <?php foreach ($groups as $group => $fields): ?>
        <div class="card mb-4">
            <div class="card-header"><?= e($group) ?></div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($fields as $key => [$label, $type, $help]):
                        $val = (string) ($values[$key] ?? '');
                        $wide = in_array($type, ['textarea', 'textarea-lg'], true); ?>
                        <div class="<?= $wide ? 'col-12' : 'col-md-6' ?>">
                            <label class="form-label" for="s_<?= e($key) ?>"><?= e($label) ?></label>
                            <?php if ($wide): ?>
                                <textarea class="form-control" id="s_<?= e($key) ?>" name="<?= e($key) ?>" rows="<?= $type === 'textarea-lg' ? 6 : 3 ?>"><?= e($val) ?></textarea>
                            <?php elseif ($type === 'secret'): ?>
                                <input class="form-control font-monospace" type="password" id="s_<?= e($key) ?>" name="<?= e($key) ?>" autocomplete="new-password"
                                       placeholder="<?= setting($key) !== '' ? '•••••••••••• saved — leave empty to keep' : 'Paste the secret key' ?>">
                            <?php else: ?>
                                <input class="form-control" type="<?= $type === 'number' ? 'number' : ($type === 'email' ? 'email' : 'text') ?>" id="s_<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($val) ?>">
                            <?php endif; ?>
                            <?php if ($help): ?><div class="form-text"><?= e($help) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="settings-savebar">
        <button class="btn btn-primary px-4"><i class="bi bi-check-lg"></i> Save all settings</button>
    </div>
</form>
