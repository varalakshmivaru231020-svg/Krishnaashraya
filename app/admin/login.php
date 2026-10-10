<?php
declare(strict_types=1);

if (current_admin()) {
    redirect('admin');
}

$error = '';
if (is_post()) {
    csrf_check();
    $recentFailures = (int) db_value('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > NOW() - INTERVAL 15 MINUTE', [client_ip()]);
    if ($recentFailures >= 8) {
        $error = 'Too many login attempts. Please wait 15 minutes and try again.';
    } elseif (admin_login(input('email'), (string) ($_POST['password'] ?? ''))) {
        db_exec('DELETE FROM login_attempts WHERE ip = ? OR attempted_at < NOW() - INTERVAL 1 DAY', [client_ip()]);
        $next = $_SESSION['after_login'] ?? '';
        unset($_SESSION['after_login']);
        // Only follow same-site admin paths after login.
        redirect(is_string($next) && preg_match('#^/[^/\\\\]#', $next) && str_contains($next, '/admin') ? $next : 'admin');
    } else {
        db_exec('INSERT INTO login_attempts (ip) VALUES (?)', [client_ip()]);
        usleep(400000);
        $error = 'The email or password is not correct.';
    }
}

admin_render('login', ['title' => 'Log in', 'error' => $error]);
