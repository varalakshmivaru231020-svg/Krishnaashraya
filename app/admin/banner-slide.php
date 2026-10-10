<?php
declare(strict_types=1);

// Admin -> Banners -> add or edit one home page slide.

$id = (int) input('id');
$slide = $id ? db_one('SELECT * FROM home_slides WHERE id = ?', [$id]) : null;
if ($id && !$slide) {
    flash('error', 'That slide was not found.');
    redirect('admin/banners');
}

$errors = [];
$form = $slide ?? ['image' => '', 'title' => "There Is No Joy\nGreater than the Joy\nof Giving", 'subtitle' => 'Empowering Lives Through Education and Care', 'is_active' => 1];

if (is_post()) {
    csrf_check();
    $form['title'] = mb_substr(str_replace("\r\n", "\n", trim((string) ($_POST['title'] ?? ''))), 0, 255);
    $form['subtitle'] = mb_substr(input('subtitle'), 0, 255);
    $form['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    $newImage = null;
    if (!empty($_FILES['image']['name'])) {
        try {
            $newImage = store_upload($_FILES['image'], 'banners', 1920);
        } catch (RuntimeException $ex) {
            $errors[] = 'Photo: ' . $ex->getMessage();
        }
    } elseif (!$slide) {
        $errors[] = 'Choose a photo for the new slide.';
    }

    if (!$errors) {
        if ($slide) {
            db_exec(
                'UPDATE home_slides SET image = ?, title = ?, subtitle = ?, is_active = ? WHERE id = ?',
                [$newImage ?? $slide['image'], $form['title'] ?: null, $form['subtitle'] ?: null, $form['is_active'], $id]
            );
            if ($newImage) {
                delete_upload($slide['image']);
            }
            flash('success', 'Slide saved.');
        } else {
            $order = (int) db_value('SELECT COALESCE(MAX(sort_order), 0) FROM home_slides') + 10;
            db_exec(
                'INSERT INTO home_slides (image, title, subtitle, sort_order, is_active) VALUES (?, ?, ?, ?, ?)',
                [$newImage, $form['title'] ?: null, $form['subtitle'] ?: null, $order, $form['is_active']]
            );
            flash('success', 'Slide added to the home page slider.');
        }
        redirect('admin/banners');
    }
}

admin_render('banner-slide', [
    'title'  => $slide ? 'Edit slide' : 'Add slide',
    'slide'  => $slide,
    'form'   => $form,
    'errors' => $errors,
]);
