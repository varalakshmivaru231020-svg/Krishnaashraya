<?php
declare(strict_types=1);

// Admin -> About Us: the About section of the home page and the About Us page.

$fields = [
    'about_badge' => 30, 'about_title' => 150, 'about_subtitle' => 200,
    'about_heading' => 150, 'about_text' => 5000, 'about_quote' => 300,
    'about_stat1_value' => 20, 'about_stat1_label' => 60,
    'about_stat2_value' => 20, 'about_stat2_label' => 60,
    'about_stat3_value' => 20, 'about_stat3_label' => 60,
    'about_mission_label' => 40, 'mission_short' => 200,
    'vm_title' => 150, 'vision' => 1000, 'mission' => 1000, 'goals' => 2000, 'our_work' => 5000,
];

$errors = [];
if (is_post()) {
    csrf_check();
    if (trim((string) ($_POST['about_title'] ?? '')) === '') {
        $errors[] = 'The section title cannot be empty.';
    }

    $newImage = null;
    if (!$errors && !empty($_FILES['about_image']['name'])) {
        try {
            $newImage = store_upload($_FILES['about_image'], 'site');
        } catch (RuntimeException $ex) {
            $errors[] = 'Photo: ' . $ex->getMessage();
        }
    }

    if (!$errors) {
        foreach ($fields as $key => $max) {
            setting_save($key, mb_substr(str_replace("\r\n", "\n", trim((string) ($_POST[$key] ?? ''))), 0, $max));
        }
        if ($newImage) {
            $old = setting('about_image');
            setting_save('about_image', $newImage);
            delete_upload($old);
        }
        flash('success', 'About section saved. The home page and About Us page are updated.');
        redirect('admin/about');
    }
}

$values = [];
foreach (array_keys($fields) as $key) {
    $values[$key] = is_post() ? (string) ($_POST[$key] ?? '') : about($key);
}

admin_render('about', [
    'title'  => 'About Us',
    'v'      => $values,
    'image'  => about('about_image'),
    'errors' => $errors,
]);
