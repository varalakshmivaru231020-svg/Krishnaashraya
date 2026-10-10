<?php
declare(strict_types=1);

// Admin -> Donation Purposes: what donors can choose under "Donate towards".
// List with quick actions; adding and editing happen in a modal form on the same page.

if (is_post()) {
    csrf_check();
    $action = input('action');
    $id = (int) input('id');
    $title = mb_substr(input('title'), 0, 120);
    $description = mb_substr(input('description'), 0, 255);
    $active = isset($_POST['is_active']) ? 1 : 0;

    if ($action === 'save') {
        if ($title === '') {
            flash('error', 'Enter a name for the purpose.');
        } elseif ($id) {
            db_exec('UPDATE donation_purposes SET title = ?, description = ?, is_active = ? WHERE id = ?', [$title, $description ?: null, $active, $id]);
            flash('success', "\"$title\" saved.");
        } else {
            $order = (int) db_value('SELECT COALESCE(MAX(sort_order), 0) FROM donation_purposes') + 10;
            db_exec('INSERT INTO donation_purposes (title, description, sort_order, is_active) VALUES (?, ?, ?, ?)', [$title, $description ?: null, $order, $active]);
            flash('success', "\"$title\" added" . ($active ? ' — donors can choose it now.' : '. Switch it on when you are ready.'));
        }
        redirect('admin/purposes');
    }

    $purpose = db_one('SELECT * FROM donation_purposes WHERE id = ?', [$id]);
    if ($purpose && $action === 'toggle') {
        db_exec('UPDATE donation_purposes SET is_active = 1 - is_active WHERE id = ?', [$id]);
        flash('success', '"' . $purpose['title'] . '" is now ' . ($purpose['is_active'] ? 'hidden from' : 'showing on') . ' the Donate page.');
    } elseif ($purpose && $action === 'delete') {
        // Past donations keep their record; they simply show as "General Donation" afterwards.
        db_exec('DELETE FROM donation_purposes WHERE id = ?', [$id]);
        flash('success', '"' . $purpose['title'] . '" deleted.');
    } elseif ($purpose && in_array($action, ['up', 'down'], true)) {
        move_row('donation_purposes', $id, $action);
    }
    redirect('admin/purposes');
}

admin_render('purposes', [
    'title'    => 'Donation Purposes',
    'purposes' => db_all(
        "SELECT p.*,
                COUNT(d.id) AS donations,
                COALESCE(SUM(CASE WHEN d.status = 'paid' THEN d.amount_paise END), 0) AS raised_paise
           FROM donation_purposes p LEFT JOIN donations d ON d.purpose_id = p.id
          GROUP BY p.id ORDER BY p.sort_order, p.id"
    ),
]);
