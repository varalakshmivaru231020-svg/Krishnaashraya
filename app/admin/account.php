<?php
declare(strict_types=1);

$admin = current_admin();
$errors = [];

if (is_post()) {
    csrf_check();
    $action = input('action');

    if ($action === 'password') {
        $row = db_one('SELECT password_hash FROM admins WHERE id = ?', [$admin['id']]);
        $new = (string) ($_POST['new_password'] ?? '');
        if (!password_verify((string) ($_POST['current_password'] ?? ''), $row['password_hash'])) {
            $errors[] = 'Your current password is not correct.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'The new password must be at least 8 characters.';
        } elseif ($new !== ($_POST['confirm_password'] ?? '')) {
            $errors[] = 'The two new passwords do not match.';
        } else {
            db_exec('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
            session_regenerate_id(true);
            flash('success', 'Password changed.');
            redirect('admin/account');
        }
    }

    if ($action === 'add_admin') {
        $name = mb_substr(input('name'), 0, 100);
        $email = strtolower(input('email'));
        $pass = (string) ($_POST['password'] ?? '');
        if ($name === '' || !valid_email($email)) {
            $errors[] = 'Enter a name and a valid email for the new admin.';
        } elseif (strlen($pass) < 8) {
            $errors[] = 'The new admin\'s password must be at least 8 characters.';
        } elseif (db_value('SELECT id FROM admins WHERE email = ?', [$email])) {
            $errors[] = 'An admin with that email already exists.';
        } else {
            db_exec('INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)', [$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            flash('success', "Admin account created for $email. Share the password with them privately.");
            redirect('admin/account');
        }
    }

    if ($action === 'remove_admin') {
        $removeId = (int) input('admin_id');
        if ($removeId === (int) $admin['id']) {
            $errors[] = 'You cannot remove your own account.';
        } else {
            db_exec('DELETE FROM admins WHERE id = ?', [$removeId]);
            flash('success', 'Admin removed.');
            redirect('admin/account');
        }
    }
}

admin_render('account', [
    'title'  => 'My Account',
    'admin'  => $admin,
    'admins' => db_all('SELECT id, name, email, last_login_at FROM admins ORDER BY id'),
    'errors' => $errors,
]);
