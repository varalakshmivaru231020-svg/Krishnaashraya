<?php
// The announcement popup set in Admin -> Website Popup. "?popup_preview=1" shows it straight away to a logged-in admin.
$preview = isset($_GET['popup_preview']) && current_admin();
if (!$preview && !popup_visible()) {
    return;
}
$p = popup();
$link = popup_link($p['link']);
$poster = $p['layout'] === 'poster' && $p['image'] !== '';
$external = str_starts_with($link, 'http');
// Changing the photo or wording gives a new key, so visitors who closed the old popup see the new one.
$key = 'ks_popup_' . substr(md5(implode('|', [$p['layout'], $p['image'], $p['title'], $p['text'], $p['button'], $p['link']])), 0, 10);
$label = $p['title'] !== '' ? $p['title'] : 'Announcement';
?>
<div class="modal fade site-popup<?= $poster ? ' is-poster' : '' ?>" id="sitePopup" tabindex="-1" aria-label="<?= e($label) ?>" aria-hidden="true"
     data-key="<?= e($key) ?>" data-frequency="<?= e($p['frequency']) ?>" data-delay="<?= (int) $p['delay'] ?>"<?= $preview ? ' data-preview' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="site-popup-close" data-bs-dismiss="modal" aria-label="Close"><i class="bi bi-x-lg"></i></button>
            <?php if ($poster): ?>
                <?php if ($link): ?><a href="<?= e($link) ?>" class="site-popup-poster"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>><?php else: ?><div class="site-popup-poster"><?php endif; ?>
                    <img src="<?= e(img($p['image'])) ?>" alt="<?= e($label) ?>">
                <?= $link ? '</a>' : '</div>' ?>
                <?php if ($p['title'] !== '' || ($link && $p['button'] !== '')): ?>
                    <div class="site-popup-body site-popup-foot">
                        <?php if ($p['title'] !== ''): ?><h2 class="site-popup-title force-font"><?= e($p['title']) ?></h2><?php endif; ?>
                        <?php if ($link && $p['button'] !== ''): ?>
                            <a href="<?= e($link) ?>" class="btn-primary-gradient site-popup-btn"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>><?= e($p['button']) ?> <i class="bi bi-arrow-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($p['image']): ?>
                    <div class="site-popup-media"><img src="<?= e(img($p['image'])) ?>" alt=""></div>
                <?php endif; ?>
                <div class="site-popup-body<?= $p['image'] ? '' : ' no-media' ?>">
                    <?php if ($p['title'] !== ''): ?><h2 class="site-popup-title force-font"><?= e($p['title']) ?></h2><?php endif; ?>
                    <?php if ($p['text'] !== ''): ?><p class="site-popup-text"><?= nl2br(e($p['text'])) ?></p><?php endif; ?>
                    <?php if ($link && $p['button'] !== ''): ?>
                        <a href="<?= e($link) ?>" class="btn-primary-gradient site-popup-btn"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>><?= e($p['button']) ?> <i class="bi bi-arrow-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('sitePopup');
        if (!el || !window.bootstrap) return;
        var key = el.getAttribute('data-key'), often = el.getAttribute('data-frequency'), preview = el.hasAttribute('data-preview');
        var today = new Date().toDateString();
        function seen() {
            try {
                if (often === 'session') return sessionStorage.getItem(key) === '1';
                if (often === 'day') return localStorage.getItem(key) === today;
            } catch (e) {}
            return false;
        }
        function remember() {
            try {
                if (often === 'session') sessionStorage.setItem(key, '1');
                if (often === 'day') localStorage.setItem(key, today);
            } catch (e) {}
        }
        if (!preview && seen()) return;
        el.addEventListener('shown.bs.modal', remember);
        function open() {
            // Wait while another message (e.g. "Thank you") or dialog is open.
            if (document.querySelector('.swal-overlay--show-modal, .modal.show')) { setTimeout(open, 1500); return; }
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
        setTimeout(open, preview ? 400 : (+el.getAttribute('data-delay') || 0) * 1000);
    });
</script>
