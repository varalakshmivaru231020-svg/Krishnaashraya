<?php
declare(strict_types=1);

$id = (int) input('id');
$enquiry = db_one('SELECT * FROM enquiries WHERE id = ?', [$id]);
if (!$enquiry) {
    flash('error', 'That enquiry was not found.');
    redirect('admin/enquiries');
}

if (is_post()) {
    csrf_check();
    if (input('action') === 'delete') {
        db_exec('DELETE FROM enquiries WHERE id = ?', [$id]);
        flash('success', 'Enquiry deleted.');
        redirect('admin/enquiries');
    }
    $status = in_array(input('status'), ['new', 'read', 'replied'], true) ? input('status') : 'read';
    db_exec('UPDATE enquiries SET status = ?, admin_notes = ? WHERE id = ?', [$status, mb_substr(input('admin_notes'), 0, 5000), $id]);
    flash('success', 'Saved.');
    redirect('admin/enquiries/view?id=' . $id);
}

// Opening a new enquiry marks it as read.
if ($enquiry['status'] === 'new') {
    db_exec("UPDATE enquiries SET status = 'read' WHERE id = ?", [$id]);
    $enquiry['status'] = 'read';
}

admin_render('enquiry-view', ['title' => 'Enquiry from ' . $enquiry['name'], 'q' => $enquiry]);
