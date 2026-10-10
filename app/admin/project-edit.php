<?php
declare(strict_types=1);

$id = (int) input('id');
$project = $id ? db_one('SELECT * FROM projects WHERE id = ?', [$id]) : null;
if ($id && !$project) {
    flash('error', 'That project was not found.');
    redirect('admin/projects');
}

$errors = [];
$form = $project ?? ['title' => '', 'slug' => '', 'summary' => '', 'description' => '', 'features' => '', 'image' => '', 'status' => 'ongoing', 'goal_amount' => '', 'sort_order' => 0, 'is_active' => 1];

if (is_post()) {
    csrf_check();

    if (input('action') === 'delete' && $project) {
        db_exec('DELETE FROM projects WHERE id = ?', [$id]);
        delete_upload($project['image']);
        flash('success', 'Project deleted. Past donations to it are kept as general donations.');
        redirect('admin/projects');
    }

    $form = array_merge($form, [
        'title'       => mb_substr(input('title'), 0, 255),
        'summary'     => input('summary'),
        'description' => input('description'),
        'features'    => input('features'),
        'status'      => input('status') === 'completed' ? 'completed' : 'ongoing',
        'goal_amount' => input('goal_amount'),
        'sort_order'  => (int) input('sort_order'),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ]);
    if ($form['title'] === '') {
        $errors[] = 'Enter a project title.';
    }
    if ($form['goal_amount'] !== '' && !ctype_digit((string) $form['goal_amount'])) {
        $errors[] = 'The goal amount must be a whole number of rupees, or empty.';
    }

    $newImage = null;
    if (!$errors && !empty($_FILES['image']['name'])) {
        try {
            $newImage = store_upload($_FILES['image'], 'projects');
        } catch (RuntimeException $ex) {
            $errors[] = $ex->getMessage();
        }
    }
    if (!$errors && !$project && !$newImage) {
        $errors[] = 'Choose a photo for the project.';
    }

    if (!$errors) {
        $slugInput = input('slug');
        $slug = unique_slug('projects', $slugInput !== '' ? $slugInput : $form['title'], $id ?: null);
        $goal = $form['goal_amount'] === '' ? null : (int) $form['goal_amount'];
        $image = $newImage ?? $project['image'];

        if ($project) {
            db_exec(
                'UPDATE projects SET title = ?, slug = ?, summary = ?, description = ?, features = ?, image = ?, status = ?, goal_amount = ?, sort_order = ?, is_active = ? WHERE id = ?',
                [$form['title'], $slug, $form['summary'], $form['description'], $form['features'], $image, $form['status'], $goal, $form['sort_order'], $form['is_active'], $id]
            );
            if ($newImage) {
                delete_upload($project['image']);
            }
        } else {
            db_exec(
                'INSERT INTO projects (title, slug, summary, description, features, image, status, goal_amount, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$form['title'], $slug, $form['summary'], $form['description'], $form['features'], $image, $form['status'], $goal, $form['sort_order'], $form['is_active']]
            );
            $id = (int) db()->lastInsertId();
        }
        flash('success', 'Project saved.');
        redirect('admin/projects/edit?id=' . $id);
    }
    if ($newImage) {
        delete_upload($newImage);
    }
    $form['slug'] = input('slug');
}

admin_render('project-edit', [
    'title'   => $project ? 'Edit project' : 'Add project',
    'form'    => $form,
    'project' => $project,
    'errors'  => $errors,
]);
