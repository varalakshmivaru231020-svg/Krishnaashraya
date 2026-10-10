<?php
declare(strict_types=1);

// Admin -> Banners -> edit one call-to-action panel (photo, which part of the photo shows, texts).

$defaults = cta_defaults();
$key = input('panel');
if (!isset($defaults[$key])) {
    flash('error', 'That panel was not found.');
    redirect('admin/banners#cta');
}
$label = $defaults[$key]['label'];

$errors = [];
if (is_post()) {
    csrf_check();
    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        try {
            $newImage = store_upload($_FILES['image'], 'banners', 1920);
        } catch (RuntimeException $ex) {
            $errors[] = 'Photo: ' . $ex->getMessage();
        }
    }
    if (!$errors) {
        $focus = array_key_exists(input('focus'), photo_focus_options()) ? input('focus') : 'top';
        setting_save("cta_{$key}_focus", $focus);
        setting_save("cta_{$key}_eyebrow", mb_substr(input('eyebrow'), 0, 60));
        setting_save("cta_{$key}_heading", mb_substr(input('heading'), 0, 120));
        setting_save("cta_{$key}_text", mb_substr(input('text'), 0, 300));
        $old = setting("cta_{$key}_image");
        if ($newImage) {
            setting_save("cta_{$key}_image", $newImage);
            delete_upload($old);
        } elseif (isset($_POST['reset_image'])) {
            setting_save("cta_{$key}_image", '');
            delete_upload($old);
        }
        flash('success', "$label panel saved.");
        redirect('admin/banners#cta');
    }
}

$cta = cta($key);
admin_render('banner-cta', [
    'title'    => "$label — call-to-action panel",
    'key'      => $key,
    'label'    => $label,
    'cta'      => $cta,
    'defaults' => $defaults[$key],
    'custom'   => setting("cta_{$key}_image") !== '',
    'errors'   => $errors,
    'viewUrl'  => url($defaults[$key]['page']),
]);
