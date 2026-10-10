<?php
declare(strict_types=1);

$id = (int) input('id');
$item = $id ? db_one('SELECT * FROM testimonials WHERE id = ?', [$id]) : null;
if ($id && !$item) {
    flash('error', 'That testimonial was not found.');
    redirect('admin/testimonials');
}

$errors = [];
$form = $item ?? ['name' => '', 'role' => 'Donor', 'message' => '', 'photo' => '', 'sort_order' => 0, 'is_active' => 1];

if (is_post()) {
    csrf_check();

    if (input('action') === 'delete' && $item) {
        db_exec('DELETE FROM testimonials WHERE id = ?', [$id]);
        delete_upload($item['photo']);
        flash('success', 'Testimonial deleted.');
        redirect('admin/testimonials');
    }

    $form = array_merge($form, [
        'name'       => mb_substr(input('name'), 0, 120),
        'role'       => mb_substr(input('role'), 0, 120),
        'message'    => input('message'),
        'sort_order' => (int) input('sort_order'),
        'is_active'  => isset($_POST['is_active']) ? 1 : 0,
    ]);
    if ($form['name'] === '') {
        $errors[] = 'Enter the person\'s name.';
    }
    if ($form['message'] === '') {
        $errors[] = 'Enter what they said.';
    }

    $photo = $item['photo'] ?? null;
    $newPhoto = null;
    if (!$errors && !empty($_FILES['photo']['name'])) {
        try {
            $newPhoto = store_upload($_FILES['photo'], 'testimonials', 400);
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }

    if (!$errors) {
        if ($newPhoto || isset($_POST['remove_photo'])) {
            delete_upload($photo);
            $photo = $newPhoto;
        }
        if ($item) {
            db_exec('UPDATE testimonials SET name = ?, role = ?, message = ?, photo = ?, sort_order = ?, is_active = ? WHERE id = ?',
                [$form['name'], $form['role'], $form['message'], $photo, $form['sort_order'], $form['is_active'], $id]);
        } else {
            db_exec('INSERT INTO testimonials (name, role, message, photo, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)',
                [$form['name'], $form['role'], $form['message'], $photo, $form['sort_order'], $form['is_active']]);
        }
        flash('success', 'Testimonial saved.');
        redirect('admin/testimonials');
    }
}

admin_render('testimonial-edit', [
    'title'  => $item ? 'Edit testimonial' : 'Add testimonial',
    'form'   => $form,
    'item'   => $item,
    'errors' => $errors,
]);
