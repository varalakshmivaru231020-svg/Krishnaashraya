<?php
declare(strict_types=1);

// Admin -> Impact Numbers: the "Your Contribution Creates Measurable Change" figures on the home page.

/** Icons offered in the picker (Bootstrap Icons names). */
function impact_icons(): array
{
    return ['currency-rupee', 'cash-stack', 'calendar-check', 'bullseye', 'people-fill', 'person-hearts', 'heart-fill', 'book-half',
            'mortarboard-fill', 'house-heart-fill', 'egg-fried', 'cup-hot-fill', 'basket-fill', 'heart-pulse-fill', 'award-fill',
            'trophy-fill', 'star-fill', 'graph-up-arrow', 'emoji-smile-fill', 'gift-fill', 'clock-fill', 'shield-check', 'globe2', 'hand-thumbs-up-fill'];
}

if (is_post()) {
    csrf_check();
    $action = input('action');
    $id = (int) input('id');

    if ($action === 'heading') {
        setting_save('impact_heading', mb_substr(input('impact_heading'), 0, 150));
        flash('success', 'Section heading saved.');
        redirect('admin/impact');
    }

    if ($action === 'save') {
        $label = mb_substr(input('label'), 0, 120);
        $valueInput = str_replace([',', ' '], '', input('value'));
        $icon = in_array(input('icon'), impact_icons(), true) ? input('icon') : 'star-fill';
        $prefix = mb_substr(input('prefix'), 0, 10);
        $suffix = mb_substr(input('suffix'), 0, 10);
        $active = isset($_POST['is_active']) ? 1 : 0;

        if ($label === '' || !preg_match('/^\d{1,9}$/', $valueInput)) {
            flash('error', 'Enter a label and a whole number (for example 60000 or 100).');
        } elseif ($id) {
            db_exec('UPDATE impact_stats SET icon = ?, prefix = ?, value = ?, suffix = ?, label = ?, is_active = ? WHERE id = ?',
                [$icon, $prefix ?: null, (int) $valueInput, $suffix ?: null, $label, $active, $id]);
            flash('success', "\"$label\" saved.");
        } else {
            $order = (int) db_value('SELECT COALESCE(MAX(sort_order), 0) FROM impact_stats') + 10;
            db_exec('INSERT INTO impact_stats (icon, prefix, value, suffix, label, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$icon, $prefix ?: null, (int) $valueInput, $suffix ?: null, $label, $order, $active]);
            flash('success', "\"$label\" added to the home page.");
        }
        redirect('admin/impact');
    }

    $stat = db_one('SELECT * FROM impact_stats WHERE id = ?', [$id]);
    if ($stat && $action === 'toggle') {
        db_exec('UPDATE impact_stats SET is_active = 1 - is_active WHERE id = ?', [$id]);
        flash('success', '"' . $stat['label'] . '" is now ' . ($stat['is_active'] ? 'hidden from' : 'showing on') . ' the home page.');
    } elseif ($stat && $action === 'delete') {
        db_exec('DELETE FROM impact_stats WHERE id = ?', [$id]);
        flash('success', '"' . $stat['label'] . '" deleted.');
    } elseif ($stat && in_array($action, ['up', 'down'], true)) {
        move_row('impact_stats', $id, $action);
    }
    redirect('admin/impact');
}

admin_render('impact', [
    'title'   => 'Impact Numbers',
    'stats'   => db_all('SELECT * FROM impact_stats ORDER BY sort_order, id'),
    'heading' => setting('impact_heading', 'Your Contribution Creates Measurable Change'),
    'icons'   => impact_icons(),
]);
