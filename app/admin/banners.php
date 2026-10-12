<?php
declare(strict_types=1);

// Admin -> Banners: overview of the home slider and the inner page banners.
// Editing happens on admin/banners/slide and admin/banners/page; this page handles the quick actions.

if (is_post()) {
    csrf_check();
    $action = input('action');
    $slide = db_one('SELECT * FROM home_slides WHERE id = ?', [(int) input('id')]);

    if ($slide && $action === 'toggle') {
        db_exec('UPDATE home_slides SET is_active = 1 - is_active WHERE id = ?', [$slide['id']]);
        flash('success', $slide['is_active'] ? 'Slide hidden from the home page.' : 'Slide is now showing on the home page.');
    } elseif ($slide && $action === 'delete') {
        db_exec('DELETE FROM home_slides WHERE id = ?', [$slide['id']]);
        delete_upload($slide['image']);
        delete_upload($slide['video']);
        flash('success', 'Slide deleted.');
    } elseif ($slide && in_array($action, ['up', 'down'], true)) {
        move_row('home_slides', (int) $slide['id'], $action);
    }
    redirect('admin/banners');
}

$pages = [];
foreach (page_banner_defaults() as $key => [$label]) {
    $pages[$key] = page_banner($key) + ['label' => $label, 'custom' => setting("banner_{$key}_image") !== ''];
}

admin_render('banners', [
    'title'  => 'Banners',
    'slides' => db_all('SELECT * FROM home_slides ORDER BY sort_order, id'),
    'pages'  => $pages,
    'ctas'   => array_map(fn($k) => cta($k) + ['key' => $k, 'custom' => setting("cta_{$k}_image") !== ''], array_keys(cta_defaults())),
]);
