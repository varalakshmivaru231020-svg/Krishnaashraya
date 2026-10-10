<?php
declare(strict_types=1);

// Admin -> Banners -> edit the top banner of one inner page (About, Projects, Activities, Contact, Donate).

$defaults = page_banner_defaults();
$key = input('page');
if (!isset($defaults[$key])) {
    flash('error', 'That page was not found.');
    redirect('admin/banners#pages');
}
[$label, $defaultImage, $defaultTitle, $defaultSubtitle] = $defaults[$key];

$errors = [];
if (is_post()) {
    csrf_check();
    $old = setting("banner_{$key}_image");
    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        try {
            $newImage = store_upload($_FILES['image'], 'banners', 1920);
        } catch (RuntimeException $ex) {
            $errors[] = 'Photo: ' . $ex->getMessage();
        }
    }
    if (!$errors) {
        setting_save("banner_{$key}_title", mb_substr(input('title'), 0, 150));
        setting_save("banner_{$key}_subtitle", mb_substr(input('subtitle'), 0, 300));
        if ($newImage) {
            setting_save("banner_{$key}_image", $newImage);
            delete_upload($old);
        } elseif (isset($_POST['reset_image'])) {
            setting_save("banner_{$key}_image", '');
            delete_upload($old);
        }
        flash('success', "$label banner saved.");
        redirect('admin/banners#pages');
    }
}

$banner = page_banner($key);
admin_render('banner-page', [
    'title'    => "$label banner",
    'key'      => $key,
    'label'    => $label,
    'banner'   => is_post() ? array_merge($banner, ['title' => input('title'), 'subtitle' => input('subtitle')]) : $banner,
    'defaults' => ['image' => $defaultImage, 'title' => $defaultTitle, 'subtitle' => $defaultSubtitle],
    'custom'   => setting("banner_{$key}_image") !== '',
    'errors'   => $errors,
    'viewUrl'  => url($key === 'activities' ? 'our-activities' : $key),
]);
