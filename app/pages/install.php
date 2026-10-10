<?php
declare(strict_types=1);

// One-time setup: creates the database tables, loads the starting content
// and creates the first admin account. Locks itself once an admin exists.

require APP . '/seed.php';

$errors = [];
$done = false;
$locked = false;

try {
    try {
        db();
    } catch (PDOException $ex) {
        // Database itself may not exist yet (typical on a local XAMPP install).
        $c = config('db');
        $server = new PDO(sprintf('mysql:host=%s;port=%d;charset=%s', $c['host'], $c['port'], $c['charset']), $c['user'], $c['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $server->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', $c['name']) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        db();
    }

    $tables = db_all("SHOW TABLES LIKE 'admins'");
    if ($tables && (int) db_value('SELECT COUNT(*) FROM admins') > 0) {
        $locked = true;
    } else {
        $sql = file_get_contents(ROOT . '/database/schema.sql');
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            db()->exec($statement);
        }
        seed_database();
        upgrade_database();

        if (is_post()) {
            csrf_check();
            $name = input('name');
            $email = strtolower(input('email'));
            $password = $_POST['password'] ?? '';
            if ($name === '') {
                $errors[] = 'Enter your name.';
            }
            if (!valid_email($email)) {
                $errors[] = 'Enter a valid email address.';
            }
            if (strlen($password) < 8) {
                $errors[] = 'The password must be at least 8 characters.';
            }
            if (!$errors) {
                db_exec('INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)', [$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
                $done = true;
            }
        }
    }
} catch (PDOException $ex) {
    $errors[] = 'Could not connect to the database: ' . $ex->getMessage() . ' — check the db settings in app/config.php.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Website setup</title>
    <link href="<?= e(url('assets/css/bootstrap.min.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:560px">
    <h1 class="h3 mb-4">Website setup</h1>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-danger"><?= e($err) ?></div>
    <?php endforeach; ?>

    <?php if ($locked): ?>
        <div class="alert alert-info">The website is already set up. <a href="<?= e(url('admin/login')) ?>">Go to the admin login</a>.</div>
    <?php elseif ($done): ?>
        <div class="alert alert-success">All done. The database is ready and your admin account is created.</div>
        <a class="btn btn-primary" href="<?= e(url('admin/login')) ?>">Log in to the admin panel</a>
        <a class="btn btn-outline-secondary" href="<?= e(url('')) ?>">View the website</a>
    <?php elseif (!$errors || is_post()): ?>
        <p class="text-muted">The database tables are ready and the existing website content has been loaded. Create the first admin account to finish.</p>
        <form method="post" class="card card-body">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="name">Your name</label>
                <input class="form-control" id="name" name="name" value="<?= e(input('name')) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email (used to log in)</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= e(input('email')) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password (at least 8 characters)</label>
                <input class="form-control" type="password" id="password" name="password" minlength="8" required>
            </div>
            <button class="btn btn-primary">Create admin account</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
