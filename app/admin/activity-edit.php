<?php
declare(strict_types=1);

$id = (int) input('id');
$category = $id ? db_one('SELECT * FROM activity_categories WHERE id = ?', [$id]) : null;
if ($id && !$category) {
    flash('error', 'That activity was not found.');
    redirect('admin/activities');
}

$errors = [];
$form = $category ?? ['title' => '', 'slug' => '', 'description' => '', 'cover_image' => '', 'banner_image' => '', 'sort_order' => 0, 'is_active' => 1];
$back = 'admin/activities/edit?id=' . $id;

if (is_post()) {
    csrf_check();
    $action = input('action', 'save');

    // ---------------------------------------------------------- photo actions (existing category only)
    if ($category && $action === 'upload') {
        $files = uploaded_files('photos');
        if (!$files) {
            flash('error', 'Choose one or more photos to upload.');
            redirect($back . '#photos');
        }
        $caption = mb_substr(input('caption'), 0, 255);
        $order = (int) db_value('SELECT COALESCE(MAX(sort_order), 0) FROM activity_photos WHERE category_id = ?', [$id]);
        $ok = 0;
        $failed = [];
        foreach ($files as $file) {
            try {
                $path = store_upload($file, 'activities');
                $order += 10;
                db_exec('INSERT INTO activity_photos (category_id, image, caption, sort_order) VALUES (?, ?, ?, ?)', [$id, $path, $caption ?: null, $order]);
                $ok++;
            } catch (RuntimeException $ex) {
                $failed[] = $file['name'] . ': ' . $ex->getMessage();
            }
        }
        if (!$category['cover_image'] && $ok) {
            db_exec('UPDATE activity_categories SET cover_image = (SELECT image FROM activity_photos WHERE category_id = ? ORDER BY id LIMIT 1) WHERE id = ?', [$id, $id]);
        }
        if ($ok) {
            flash('success', $ok . ' photo' . ($ok > 1 ? 's' : '') . ' uploaded.');
        }
        foreach ($failed as $msg) {
            flash('error', $msg);
        }
        redirect($back . '#photos');
    }

    if ($category && $action === 'photos') {
        $captions = $_POST['caption'] ?? [];
        $orders = $_POST['order'] ?? [];
        foreach ((array) $captions as $photoId => $caption) {
            db_exec(
                'UPDATE activity_photos SET caption = ?, sort_order = ? WHERE id = ? AND category_id = ?',
                [mb_substr(trim((string) $caption), 0, 255) ?: null, (int) ($orders[$photoId] ?? 0), (int) $photoId, $id]
            );
        }
        flash('success', 'Photo captions and order saved.');
        redirect($back . '#photos');
    }

    if ($category && in_array($action, ['photo-delete', 'photo-cover'], true)) {
        $photo = db_one('SELECT * FROM activity_photos WHERE id = ? AND category_id = ?', [(int) input('photo_id'), $id]);
        if ($photo && $action === 'photo-cover') {
            db_exec('UPDATE activity_categories SET cover_image = ? WHERE id = ?', [$photo['image'], $id]);
            flash('success', 'Cover photo changed.');
        } elseif ($photo) {
            db_exec('DELETE FROM activity_photos WHERE id = ?', [$photo['id']]);
            $stillUsed = (int) db_value('SELECT COUNT(*) FROM activity_categories WHERE cover_image = ? OR banner_image = ?', [$photo['image'], $photo['image']]);
            if (!$stillUsed) {
                delete_upload($photo['image']);
            }
            flash('success', 'Photo deleted.');
        }
        redirect($back . '#photos');
    }

    if ($category && $action === 'delete') {
        $photos = db_all('SELECT image FROM activity_photos WHERE category_id = ?', [$id]);
        db_exec('DELETE FROM activity_categories WHERE id = ?', [$id]);
        foreach ($photos as $p) {
            delete_upload($p['image']);
        }
        delete_upload($category['cover_image']);
        delete_upload($category['banner_image']);
        flash('success', 'Activity and its photos deleted.');
        redirect('admin/activities');
    }

    // ---------------------------------------------------------- save details
    $form = array_merge($form, [
        'title'       => mb_substr(input('title'), 0, 150),
        'description' => input('description'),
        'sort_order'  => (int) input('sort_order'),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ]);
    if ($form['title'] === '') {
        $errors[] = 'Enter a name for the activity.';
    }

    $uploads = [];
    foreach (['cover_image' => 'activities', 'banner_image' => 'banners'] as $field => $folder) {
        if (!$errors && !empty($_FILES[$field]['name'])) {
            try {
                $uploads[$field] = store_upload($_FILES[$field], $folder, $field === 'banner_image' ? 1920 : 1600);
            } catch (RuntimeException $ex) {
                $errors[] = $ex->getMessage();
            }
        }
    }

    if (!$errors) {
        $slugInput = input('slug');
        $slug = unique_slug('activity_categories', $slugInput !== '' ? $slugInput : $form['title'], $id ?: null);
        $cover = $uploads['cover_image'] ?? ($category['cover_image'] ?? null);
        $banner = isset($_POST['remove_banner']) ? null : ($uploads['banner_image'] ?? ($category['banner_image'] ?? null));

        if ($category) {
            db_exec(
                'UPDATE activity_categories SET title = ?, slug = ?, description = ?, cover_image = ?, banner_image = ?, sort_order = ?, is_active = ? WHERE id = ?',
                [$form['title'], $slug, $form['description'], $cover, $banner, $form['sort_order'], $form['is_active'], $id]
            );
            if (isset($uploads['banner_image']) || isset($_POST['remove_banner'])) {
                delete_upload($category['banner_image']);
            }
        } else {
            db_exec(
                'INSERT INTO activity_categories (title, slug, description, cover_image, banner_image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$form['title'], $slug, $form['description'], $cover, $banner, $form['sort_order'], $form['is_active']]
            );
            $id = (int) db()->lastInsertId();
        }
        flash('success', $category ? 'Activity saved.' : 'Activity created. Now add photos below.');
        redirect('admin/activities/edit?id=' . $id . ($category ? '' : '#photos'));
    }
    foreach ($uploads as $path) {
        delete_upload($path);
    }
    $form['slug'] = input('slug');
}

admin_render('activity-edit', [
    'title'    => $category ? 'Edit: ' . $category['title'] : 'Add activity',
    'form'     => $form,
    'category' => $category,
    'photos'   => $category ? db_all('SELECT * FROM activity_photos WHERE category_id = ? ORDER BY sort_order, id', [$id]) : [],
    'errors'   => $errors,
]);
