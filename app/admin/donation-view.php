<?php
declare(strict_types=1);

$id = (int) input('id');
$donation = db_one('SELECT d.*, p.title AS project_title, dp.title AS purpose_title FROM donations d LEFT JOIN projects p ON p.id = d.project_id LEFT JOIN donation_purposes dp ON dp.id = d.purpose_id WHERE d.id = ?', [$id]);
if (!$donation) {
    flash('error', 'That donation was not found.');
    redirect('admin/donations');
}

if (is_post()) {
    csrf_check();
    db_exec(
        'UPDATE donations SET followed_up = ?, admin_notes = ? WHERE id = ?',
        [isset($_POST['followed_up']) ? 1 : 0, mb_substr(input('admin_notes'), 0, 5000), $id]
    );
    flash('success', 'Saved.');
    redirect('admin/donations/view?id=' . $id);
}

admin_render('donation-view', [
    'title'    => 'Donation from ' . $donation['name'],
    'donation' => $donation,
    'history'  => db_all('SELECT id, amount_paise, status, created_at FROM donations WHERE (email = ? OR phone = ?) AND id <> ? ORDER BY created_at DESC LIMIT 20', [$donation['email'], $donation['phone'], $id]),
]);
